<?php
/**
 * Class: DB
 *
 * This class handles all the database related issues. Such as connecting database server,
 * selecting database, the the base query etc.
 *
 * @package Manager
 * @sub-package Manager-Library
 * @since 1.0.0
 */
if(!class_exists("DB")):
class DB{

    protected $db_host;

    protected $db_user;

    protected $db_pass;

    protected $db_name;

    protected $db_collate;

    protected $db_charset;

    protected $table_prefix;

    public $connection;

    public $pdo;

    public $selection;

    /**
     * Initialize the class
     */
    public function __construct($db_host, $db_user, $db_pass, $db_name, $db_collate, $db_charset, $table_prefix){

        // Set Credentials and protect them.
        $this->db_host      = $db_host;
        $this->db_user      = $db_user;
        $this->db_pass      = $db_pass;
        $this->db_name     = $db_name;
        $this->db_collate   = $db_collate;
        $this->db_charset   = $db_charset;
        $this->table_prefix = $table_prefix;

        // Connect the database
        $this->db_connect();

        // Select The Database
        $this->db_select();

    }

    /**
     * The Connection Method
     */
    public function db_connect(){
        $connect = mysql_connect($this->db_host, $this->db_user, $this->db_pass);
        if(!$connect){
            die("Connection Failed. Error Message:" . mysql_error());
        }
        else{
            $this->connection = $connect;
        }
    }

    /**
     * The Selection Method
     */
    public function db_select(){
        $select = mysql_select_db($this->db_name, $this->connection);
        if(!$select){
            die("Selection Failed. Error Message:" . mysql_error());
        }
        else{
            $this->selection = $select;
        }
    }

    /**
     * Disconnect
     */
    public function db_disconnect(){
        if($this->connection){
            mysql_close($this->connection);
        }else{
            // do nothing
        }
    }


    /**
     * ##########################################################################################
     * Queries
     * ##########################################################################################
     */
    public function query($query){
        echo $query;
        return mysql_query($query, $this->connection);
    }

    /**
     * ################## Helper ##################
     */

    public function exec($query){
        return $this->query($query);
    }

    public function quote($string){
        str_replace('"', "", $string);
        str_replace("'", "", $string);
        return '"' . $string . '"';
    }

    protected function column_quote($string){
        return '"' . str_replace('.', '"."', preg_replace('/(^#|\(JSON\)\s*)/', '', $string)) . '"';
    }

    protected function insert_column_quote($string){
        return '' . str_replace('.', '"."', preg_replace('/(^#|\(JSON\)\s*)/', '', $string)) . '';
    }

    protected function column_push($columns){
        if ($columns == '*')
        {
            return $columns;
        }

        if (is_string($columns))
        {
            $columns = array($columns);
        }

        $stack = array();

        foreach ($columns as $key => $value)
        {
            preg_match('/([a-zA-Z0-9_\-\.]*)\s*\(([a-zA-Z0-9_\-]*)\)/i', $value, $match);

            if (isset($match[ 1 ], $match[ 2 ]))
            {
                array_push($stack, $this->column_quote( $match[ 1 ] ) . ' AS ' . $this->column_quote( $match[ 2 ] ));
            }
            else
            {
                array_push($stack, $this->column_quote( $value ));
            }
        }

        return implode($stack, ',');
    }

    protected function array_quote($array){
        $temp = array();

        foreach ($array as $value)
        {
            $temp[] = is_int($value) ? $value : $this->quote($value);
        }

        return implode($temp, ',');
    }

    protected function inner_conjunct($data, $conjunctor, $outer_conjunctor){
        $haystack = array();

        foreach ($data as $value)
        {
            $haystack[] = '(' . $this->data_implode($value, $conjunctor) . ')';
        }

        return implode($outer_conjunctor . ' ', $haystack);
    }

    protected function fn_quote($column, $string){
        return (strpos($column, '#') === 0 && preg_match('/^[A-Z0-9\_]*\([^)]*\)$/', $string)) ?

            $string :

            $this->quote($string);
    }

    protected function data_implode($data, $conjunctor, $outer_conjunctor = null){
        $wheres = array();

        foreach ($data as $key => $value)
        {
            $type = gettype($value);
            $key = str_replace('"', '', $key);
            if (
                preg_match("/^(AND|OR)(\s+#.*)?$/i", $key, $relation_match) &&
                $type == 'array'
            )
            {
                $wheres[] = 0 !== count(array_diff_key($value, array_keys(array_keys($value)))) ?
                    '(' . $this->data_implode($value, ' ' . $relation_match[ 1 ]) . ')' :
                    '(' . $this->inner_conjunct($value, ' ' . $relation_match[ 1 ], $conjunctor) . ')';
            }
            else
            {
                preg_match('/(#?)([\w\.\-]+)(\[(\>|\>\=|\<|\<\=|\!|\<\>|\>\<|\!?~)\])?/i', $key, $match);
                $column = $this->insert_column_quote($match[ 2 ]);

                if (isset($match[ 4 ]))
                {
                    $operator = $match[ 4 ];

                    if ($operator == '!')
                    {
                        switch ($type)
                        {
                            case 'NULL':
                                $wheres[] = $column . ' IS NOT NULL';
                                break;

                            case 'array':
                                $wheres[] = $column . ' NOT IN (' . $this->array_quote($value) . ')';
                                break;

                            case 'integer':
                            case 'double':
                                $wheres[] = $column . ' != ' . $value;
                                break;

                            case 'boolean':
                                $wheres[] = $column . ' != ' . ($value ? '1' : '0');
                                break;

                            case 'string':
                                $wheres[] = $column . ' != ' . $this->fn_quote($key, $value);
                                break;
                        }
                    }

                    if ($operator == '<>' || $operator == '><')
                    {
                        if ($type == 'array')
                        {
                            if ($operator == '><')
                            {
                                $column .= ' NOT';
                            }

                            if (is_numeric($value[ 0 ]) && is_numeric($value[ 1 ]))
                            {
                                $wheres[] = '(' . $column . ' BETWEEN ' . $value[ 0 ] . ' AND ' . $value[ 1 ] . ')';
                            }
                            else
                            {
                                $wheres[] = '(' . $column . ' BETWEEN ' . $this->insert_column_quote($value[ 0 ]) . ' AND ' . $this->quote($value[ 1 ]) . ')';
                            }
                        }
                    }

                    if ($operator == '~' || $operator == '!~')
                    {
                        if ($type == 'string')
                        {
                            $value = array($value);
                        }

                        if (!empty($value))
                        {
                            $like_clauses = array();

                            foreach ($value as $item)
                            {
                                if (preg_match('/^(?!%).+(?<!%)$/', $item))
                                {
                                    $item = '%' . $item . '%';
                                }

                                $like_clauses[] = $column . ($operator === '!~' ? ' NOT' : '') . ' LIKE ' . $this->fn_quote($key, $item);
                            }

                            $wheres[] = implode(' OR ', $like_clauses);
                        }
                    }

                    if (in_array($operator, array('>', '>=', '<', '<=')))
                    {
                        if (is_numeric($value))
                        {
                            $wheres[] = $column . ' ' . $operator . ' ' . $value;
                        }
                        elseif (strpos($key, '#') === 0)
                        {
                            $wheres[] = $column . ' ' . $operator . ' ' . $this->fn_quote($key, $value);
                        }
                        else
                        {
                            $wheres[] = $column . ' ' . $operator . ' ' . $this->quote($value);
                        }
                    }
                }
                else
                {
                    switch ($type)
                    {
                        case 'NULL':
                            $wheres[] = $column . ' IS NULL';
                            break;

                        case 'array':
                            $wheres[] = $column . ' IN (' . $this->array_quote($value) . ')';
                            break;

                        case 'integer':
                        case 'double':
                            $wheres[] = $column . ' = ' . $value;
                            break;

                        case 'boolean':
                            $wheres[] = $column . ' = ' . ($value ? '1' : '0');
                            break;

                        case 'string':
                            $wheres[] = $column . ' = ' . $this->fn_quote($key, $value);
                            break;
                    }
                }
            }
        }


        return implode($conjunctor . ' ', $wheres);
    }

    protected function where_clause($where) {
        $where_clause = '';

        if (is_array($where))
        {
            $where_keys = array_keys($where);
            $where_AND = preg_grep("/^AND\s*#?$/i", $where_keys);
            $where_OR = preg_grep("/^OR\s*#?$/i", $where_keys);

            $single_condition = array_diff_key($where, array_flip(
                explode(' ', 'AND OR GROUP ORDER HAVING LIMIT LIKE MATCH')
            ));

            if ($single_condition != array())
            {
                $where_clause = ' WHERE ' . $this->data_implode($single_condition, '');
            }

            if (!empty($where_AND))
            {
                $value = array_values($where_AND);
                $where_clause = ' WHERE ' . $this->data_implode($where[ $value[ 0 ] ], ' AND');
            }

            if (!empty($where_OR))
            {
                $value = array_values($where_OR);
                $where_clause = ' WHERE ' . $this->data_implode($where[ $value[ 0 ] ], ' OR');
            }

            if (isset($where[ 'MATCH' ]))
            {
                $MATCH = $where[ 'MATCH' ];

                if (is_array($MATCH) && isset($MATCH[ 'columns' ], $MATCH[ 'keyword' ]))
                {
                    $where_clause .= ($where_clause != '' ? ' AND ' : ' WHERE ') . ' MATCH ("' . str_replace('.', '"."', implode($MATCH[ 'columns' ], '", "')) . '") AGAINST (' . $this->insert_column_quote($MATCH[ 'keyword' ]) . ')';
                }
            }

            if (isset($where[ 'GROUP' ]))
            {
                $where_clause .= ' GROUP BY ' . $this->insert_column_quote($where[ 'GROUP' ]);

                if (isset($where[ 'HAVING' ]))
                {
                    $where_clause .= ' HAVING ' . $this->data_implode($where[ 'HAVING' ], ' AND');
                }
            }

            if (isset($where[ 'ORDER' ]))
            {
                $rsort = '/(^[a-zA-Z0-9_\-\.]*)(\s*(DESC|ASC))?/';
                $ORDER = $where[ 'ORDER' ];

                if (is_array($ORDER))
                {
                    if (
                        isset($ORDER[ 1 ]) &&
                        is_array($ORDER[ 1 ])
                    )
                    {
                        $where_clause .= ' ORDER BY FIELD(' . $this->insert_column_quote($ORDER[ 0 ]) . ', ' . $this->array_quote($ORDER[ 1 ]) . ')';
                    }
                    else
                    {
                        $stack = array();

                        foreach ($ORDER as $column)
                        {
                            preg_match($rsort, $column, $order_match);

                            array_push($stack, '"' . str_replace('.', '"."', $order_match[ 1 ]) . '"' . (isset($order_match[ 3 ]) ? ' ' . $order_match[ 3 ] : ''));
                        }

                        $where_clause .= ' ORDER BY ' . implode($stack, ',');
                    }
                }
                else
                {
                    preg_match($rsort, $ORDER, $order_match);

                    $where_clause .= ' ORDER BY "' . str_replace('.', '"."', $order_match[ 1 ]) . '"' . (isset($order_match[ 3 ]) ? ' ' . $order_match[ 3 ] : '');
                }
            }

            if (isset($where[ 'LIMIT' ]))
            {
                $LIMIT = $where[ 'LIMIT' ];

                if (is_numeric($LIMIT))
                {
                    $where_clause .= ' LIMIT ' . $LIMIT;
                }

                if (
                    is_array($LIMIT) &&
                    is_numeric($LIMIT[ 0 ]) &&
                    is_numeric($LIMIT[ 1 ])
                )
                {
                    $where_clause .= ' LIMIT ' . $LIMIT[ 0 ] . ',' . $LIMIT[ 1 ];
                }
            }
        }
        else
        {
            if ($where != null)
            {
                $where_clause .= '"' . $where . '"';
            }
        }


        return $where_clause;

    }

    function get_table_name($string){
        return $this->table_prefix . $string;
    }

    /**
     * ################## Helper ##################
     */

    /**
     * Select Query
     *
     * Example
     * ==================
     * $mydata = [ "user_name" => "foo", "email" => "foo@bar.com" ]; $db->select('*', 'account', $mydata);
     * ==================
     */
    public function select($column, $table_name, $wheres, $limit = null){
        $table = $this->get_table_name($table_name);

        if($limit == null){
            $sql = "SELECT " . $column . "  FROM $table ". $this->where_clause($wheres) ."";
        }else{
            $sql = "SELECT " . $column . "  FROM $table ". $this->where_clause($wheres) ." LIMIT ". $limit ."";
        }

        return $this->exec($sql);
    }

    /**
     * Insert Query
     *
     * Example
     * ==================
     * $mydata = [ "user_name" => "admin", "email"     => "mypass"]; $db->insert('account', $mydata);
     * ==================
     *
     */
    public function insert($table, $datas)
    {
        $lastId = array();

        // Check indexed or associative array
        if (!isset($datas[ 0 ]))
        {
            $datas = array($datas);
        }

        foreach ($datas as $data)
        {
            $values = array();
            $columns = array();

            foreach ($data as $key => $value)
            {

                array_push($columns, $this->insert_column_quote($key));

                switch (gettype($value))
                {
                    case 'NULL':
                        $values[] = 'NULL';
                        break;

                    case 'array':
                        preg_match("/\(JSON\)\s*([\w]+)/i", $key, $column_match);

                        $values[] = isset($column_match[ 0 ]) ?
                            $this->quote(json_encode($value)) :
                            $this->quote(serialize($value));
                        break;

                    case 'boolean':
                        $values[] = ($value ? '1' : '0');
                        break;

                    case 'integer':
                    case 'double':
                    case 'string':
                        $values[] = $this->fn_quote($key, $value);
                        break;
                }
            }
            $table = $this->get_table_name($table);
            $this->exec('INSERT INTO ' . $table . ' (' . implode(', ', $columns) . ') VALUES (' . implode($values, ', ') . ')');

        }

    }

    /**
     * Update Query
     *
     * Example
     * ==================
     * $mydata = [ "user_name" => "admin", "email"  => "mypass"]; $where= ['email' => 'foo@bar.com']; $db->update('account', $mydata, $where);
     * ==================
     */
    public function update($table, $data, $where = null)
    {
        $fields = array();

        foreach ($data as $key => $value)
        {
            preg_match('/([\w]+)(\[(\+|\-|\*|\/)\])?/i', $key, $match);

            if (isset($match[ 3 ]))
            {
                if (is_numeric($value))
                {
                    $fields[] = $this->insert_column_quote($match[ 1 ]) . ' = ' . $this->insert_column_quote($match[ 1 ]) . ' ' . $match[ 3 ] . ' ' . $value;
                }
            }
            else
            {
                $column = $this->insert_column_quote($key);

                switch (gettype($value))
                {
                    case 'NULL':
                        $fields[] = $column . ' = NULL';
                        break;

                    case 'array':
                        preg_match("/\(JSON\)\s*([\w]+)/i", $key, $column_match);

                        $fields[] = $column . ' = ' . $this->quote(
                                isset($column_match[ 0 ]) ? json_encode($value) : serialize($value)
                            );
                        break;

                    case 'boolean':
                        $fields[] = $column . ' = ' . ($value ? '1' : '0');
                        break;

                    case 'integer':
                    case 'double':
                    case 'string':
                        $fields[] = $column . ' = ' . $this->fn_quote($key, $value);
                        break;
                }
            }
        }
        $table = $this->get_table_name($table);
        str_replace('"', "", $fields);
        str_replace("'", "", $fields);
        return $this->exec('UPDATE ' . $table . ' SET ' . implode(', ', $fields) . $this->where_clause($where));
    }

    /**
     * Delete Query
     *
     * Example
     * ==================
     * $myData = ["user_name" => 'admin']; $db->delete('account',$myData);
     * ==================
     */
    public function delete($table, $where){
        $table = $this->get_table_name($table);
        return $this->exec('DELETE FROM ' . $table . ' ' . $this->where_clause($where));
    }

}
endif;
