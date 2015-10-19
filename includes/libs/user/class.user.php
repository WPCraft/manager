<?php
/**
 * Class: USER
 *
 * This class handles the login issue
 *
 * @package Manager
 * @sub-package Manager-Library
 * @since 1.0.0
 */
if(!class_exists("USER")):
class USER{

    /**
     * Initializer
     */
    public function __construct(){

    }

    /**
     * Register
     * @param $username
     * @param $email
     */
   public function register($username, $email){

        // Initialize the classes we would need
        global $db,$data_hashing,$data_error;

        // Check if the user has set the fields
        if(!isset($username) || !isset($email)){
            $data_error->_set_error("Check all the required fields!","high");
        }else{

            // Check if the email format is correct
            if($this->validateEmail($email) == false){
                  $data_error->_set_error("Please enter a valid email!","high");
            }else {
                // Sanitize the data
                $username = _e($username);
                $email = _e($email);

                // Check if the username or email already exists
                if(mysql_num_rows($db->select("*","user",["OR" => ["username" => $username,"email" => $email]])) != 0 ){
                    $data_error->_set_error("User already exists!","high");
                }else{

                    // Make a Password & activation key for the user
                    $newPassword = $this->randomPassword();
                    $user_activation_key = $data_hashing->_hash('sha1',uniqid() + SALT, SALT);

                        // Insert the user in the database
                        $mydata = [
                            "username" => $username,
                            "email" => $email,
                            "password" => $newPassword,
                            "registration_timestamp" => time(),
                            "ip" => $this->get_user_ip(),
                            "user_active" => "0",
                            "user_banned" => "0",
                            "user_activation_key" => $user_activation_key,
                            "number_of_attempt_to_login" => "0",
                            "last_attempt_to_login_timestamp" => "0"
                        ];
                        $storeTheUserToDB = $db->insert('user', $mydata);

                        // Send the email with an activation key
                        $to = $email;
                        $subject = "Account Verification";

                        $message = "
                          <p>
                          Hi <b>".$username."</b>,
                          Welcome to our site. here is your activation key. Enter it on the site to activate your account.
                          <b>".$user_activation_key."</b>
                          </p>

                        ";

                        $header = "From:no-reply@getmanager.cf \r\n";
                        $header .= "Content-type: text/html\r\n";

                        $retval = mail ($to,$subject,$message,$header);

                        if( $retval == true ){
                            echo "<div class=\"alert alert-success\">Registration process completed. The activation key has been sent in your email account. Please verify it...</div>";
                        }
                        else{
                            echo "<div class=\"alert alert-warning\">Activation email couldn't sent. Please contract Administrator.</div>";
                        }
                }
            }

        }
        /**
         * Show the errors
         **/
        foreach($data_error->error as $data){
          foreach ($data as $string => $priroty) {

            if($priroty = "high"){
              $class = "alert alert-danger";
            }elseif($priroty = "medium"){
              $class = "alert alert-warning";
            }else{
              $class = "alert alert-danger";
            }

            echo '<div class="' . $class . '">' . $string . '</div><br>';
          }
        }
   }

   /**
    *  Verification
    */
    public function verify_user($code,$email){

       global $db,$data_error;

       $email = _e($email);

        $query = $db->select("*","user",[
          "AND" => [
             "user_activation_key" => $code,
             "email" => $email
          ]
        ]);
      $result = mysql_num_rows($query);
      if($result == 1){

        $newPassword = $this->randomPassword();
        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

        // Activate the user
        $activate_data = [
          "password" => $newPasswordHash,
          "user_active" => "1",
          "user_activation_key"  => "0"
        ];
        $activate_rules = ['email' => $email];
        $db->update('user', $activate_data, $activate_rules);


              // Sent an email which contains the password
              $to = $email;
              $subject = "Account Activated";

              $message = "

                <p>
                Hi,
                You have successfully verified your account. Your password is : <b>".$newPassword."</b>
                </p>

              ";

              $header = "From:no-reply@getmanager.cf \r\n";
              $header .= "Content-type: text/html\r\n";

              $retval = mail ($to,$subject,$message,$header);

              if( $retval == true ){
                  echo "<div class=\"alert alert-success\">Verification completed. An password has been sent in your email.</div>";
              }
              else{
                  echo "<div class=\"alert alert-success\">Verification completed. Your new password : ".$newPassword.". Please note it. Because you won't able to see it after this time.</div>";
              }


      }else{
          $data_error->_set_error("Verification error!","high");
      }
      /**
       * Show the errors
       **/
      foreach($data_error->error as $data){

        foreach ($data as $string => $priroty) {

          if($priroty = "high"){
            $class = "alert alert-danger";
          }elseif($priroty = "medium"){
            $class = "alert alert-warning";
          }else{
            $class = "alert alert-danger";
          }

          echo '<div class="' . $class . '">' . $string . '</div><br>';

        }

      }

    }


   /**
    * Login
    * @param $username_or_email
    * @param $password
    */
   public function login($username_or_email, $password){

       global $db,$data_error,$data_hashing;

       // Check if the fields are empty
       if(empty($username_or_email) || empty($password)){
           $data_error->_set_error("Username or Password field is empty!", "high");
       }else{
           // Sanitize the Data
           $username_or_email = _e($username_or_email);
           $password = _e($password);

           // Query The Data of user
           $where = [
               "AND" => [
                 "OR" => [
                   "username" => $username_or_email,
                   "email" => $username_or_email
                 ],
                 "user_active" => "1",
                 "user_banned" => "0"
               ]
           ];
           $query = $db->select("*", "user", $where, 1);
           $userData = mysql_fetch_array($query);
           $storedPassword = $userData['password'];
           if($_REQUEST['rememberme'] = 'on'){ $expireLogin = time() + WEEK_IN_SECONDS;}else{$expireLogin = time() + DAY_IN_SECONDS;}
           $ip = $this->get_user_ip();
           if((time() - $userData['last_attempt_to_login_timestamp']) > 300){
             $updatedata = [ "number_of_attempt_to_login"  => "0", "last_attempt_to_login_timestamp" => time() ];
             $db->update('user', $updatedata, $where);
           }

           if($userData['number_of_attempt_to_login'] > 5){
               $data_error->_set_error("Account is deactivated for 5 minutes", "high");
           }else{
               if(mysql_num_rows($query) == 1){

                   // Match the password with the stored hash
                   if (password_verify($password, $storedPassword)) {

                       // Login Successfull
                       if (session_status() == PHP_SESSION_NONE) {
                           session_start();
                       }
                       $_SESSION['useranme'] = $userData['username'];
                       $_SESSION['userid']   = $userData['id'];
                       $_SESSION['ip']       = $ip;
                       $_SESSION['loginSecret'] = $data_hashing->_hash($type = 'sha1', $data = md5(uniqid() + SALT), SALT);

                       // insert into the session Table
                       $insertData = [
                         "user_id" => $_SESSION['userid'],
                         "content"     => $_SESSION['loginSecret'],
                         "expire"     => $expireLogin
                       ];
                       $db->insert('session', $insertData);

                       // Update The user Table
                       $updatedata = [ "ip" => $ip, "number_of_attempt_to_login"  => "0", "last_attempt_to_login_timestamp" => time() ];
                       $db->update('user', $updatedata, $where);

                       header("Location: login.php?success=1&message=Login Successfull.");

                   }
                   else {
                       $data_error->_set_error("Useranme or Password Invalid!", "high");
                       // Update The user Table
                       $time = time();
                       $updatedata = [ "number_of_attempt_to_login"  => $userData['number_of_attempt_to_login']+1, "last_attempt_to_login_timestamp" => $time ];
                       $db->update('user', $updatedata, $where);
                   }

               }else{
                   $data_error->_set_error("Useranme or Password Invalid!", "high");
               }
           }

       }

       /**
        * Show the errors
        **/
       foreach($data_error->error as $data){
         foreach ($data as $string => $priroty) {

           if($priroty = "high"){
             $class = "alert alert-danger";
           }elseif($priroty = "medium"){
             $class = "alert alert-warning";
           }else{
             $class = "alert alert-danger";
           }

           echo '<div class="' . $class . '">' . $string . '</div><br>';
         }
       }
   }

   /**
    * Check If it is email
    * @param $email
    * @return mixed
    */
   function validateEmail($email) {
       return filter_var($email, FILTER_VALIDATE_EMAIL);
   }

   /**
    * Get user ip
    */
    public function get_user_ip(){
       if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
           $ip = $_SERVER['HTTP_CLIENT_IP'];
       } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
           $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
       } else {
           $ip = $_SERVER['REMOTE_ADDR'];
       }
       return $ip;
    }


   /**
    * Random Password Generator
    */
    function randomPassword() {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    /**
     * Ceck if the user is logged in
     */
     function current_user_is_logged_in(){
        if(isset($_SESSION['useranme']) && isset($_SESSION['userid']) && isset($_SESSION['ip']) && isset($_SESSION['loginSecret'])){
            return true;
        }else{
            return false;
        }
     }

     /**
      * Get current user level
      *
      * 0 : unauthencated user
      * 1 : Subscriber
      * 2 : Contributor
      * 9 :  Editor : can edit post & page
      * 10 : Administrator : can control all over things
      */
      function get_current_user_role(){
          global $db;
          if($this->current_user_is_logged_in()){
              $userid = $_SESSION['userid'];
              $currentUserData = mysql_fetch_array($db->select("*","role",["user_id" => $userid]));
              return $currentUserData['role'];
          }else{
              return 0; // '0' means unauthencated user
          }
      }

      /**
       * Is The Current user has the administrative permission
       */
       function is_current_user_is_admin(){
         if($this->get_current_user_role() == 10){
            return true;
         }else{
           return false;
         }
       }

}
endif;
