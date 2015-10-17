<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="includes/assets/css/login_register.css">
        <title>Login</title>
    </head>
    <body class="wrapper">
<?php
/**
 * Login & Register File
 */
require_once( dirname( __FILE__ ) . '/load.php');

if(isset($_REQUEST['success'])){
    if(!isset($_REQUEST['message'])){
      echo "<div class=\"alert alert-success\">Operation Successfull.</div>";
    }else{
      echo "<div class=\"alert alert-success\"> " . $_REQUEST['message'] . "</div>";
    }
}

if(!isset($_REQUEST['doing'])){
    $showForm = true;
}else{
    $showForm = false;
}

function theForm(){
    if(isset($_REQUEST['action'])) {
        $action = $_REQUEST['action'];
    }
    else{
        $action = "login";
    }
    if($action == 'login'):
    ?>
            <div class="login-form">
                <div class="head">
                    <img src="includes/assets/images/default-avater.gif" alt=""/>
                </div>
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                    <li>
                        <input type="text" name="emailoruser" class="text" placeholder="Username or Email"><a href="#" class=" icon user"></a>
                    </li>
                    <li>
                        <input type="password" name="password" placeholder="Password"><a href="#" class=" icon lock"></a>
                    </li>
                    <div class="p-container">
                        <label for="rememberme" class="checkbox"><input type="checkbox" name="rememberme" checked>Remember Me</label>
                        <input type="hidden" name="doing" value="login">
                        <input type="submit" value="Login" >
                        <a href="?action=register" class="other-info-register">Create a new account?</a>
                    </div>
                </form>
            </div>

    <?php
    elseif($action == 'register'):
    ?>
            <div class="login-form">
            <div class="head">
                <img src="includes/assets/images/default-avater.gif" alt=""/>
            </div>
            <form action="<?php $_SERVER['PHP_SELF']; ?>" method="post">
                <li>
                    <input type="text" class="text" name="username" placeholder="Username"><a href="#" class="icon user"></a>
                </li>
                <li>
                    <input type="text" name="email" placeholder="Email"><a href="#" class="icon lock"></a>
                </li>
                <div class="p-container">
                    <div class="clear"><a href="?action=login" class="other-info">Already have an account?</a></div>
                    <input type="hidden" name="doing" value="register">
                    <input type="submit" value="Signup" >
                </div>
            </form>
            </div>
    <?php
    elseif($_REQUEST['action'] = "verify"):

      global $user;
      
      if(isset($_REQUEST['code']) && isset($_REQUEST['email']) && $_REQUEST['action'] = "verify"){
          $user->verify_user($_REQUEST['code'],$_REQUEST['email']);
      }else{
        die("Sorry, Unknown Request!");
      }

    else:
        die("Sorry, Unknown Request!");
    endif;

}

if(isset($_REQUEST['doing'])){

  if($_REQUEST['doing'] == 'login'){
      $user->login($_REQUEST['emailoruser'],$_REQUEST['password']);
  }elseif($_REQUEST['doing'] == 'register'){
      $user->register($_REQUEST['username'],$_REQUEST['email']);
  }else{}

}

theForm();



?>

  <script src="includes/assets/js/jquery.min.js"></script>
  <script src="includes/assets/js/javascript.js"></script>
</body>
</html>
