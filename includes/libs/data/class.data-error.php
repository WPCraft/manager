<?php
/**
 * Class: DATA_ERROR
 *
 *This class will handle the error's
 *
 * @package Manager
 * @sub-package Manager-Library
 * @since 1.0.0
 */
if(!class_exists("DATA_ERROR")):
class DATA_ERROR{

    public $error = [];

    /**
     * Initializer
     */
    public function __construct(){
        // Does Nothing
    }

    /**
     * Set Error
     *
     * Example
     * ==================
     * $data_error->_set_error("this is the text", "high");
     * ==================
     *
     * To Show the error
     * ==================
     * foreach($data_error->error as $data){foreach ($data as $string => $priroty) {echo $string;}}
     * ==================
     */
    public function _set_error($data, $priority){
        $error_array = array(
            $data => $priority
        );
        array_push($this->error, $error_array);
    }

}
endif;