<?php
/**
 * Login & Register File
 */
require_once( dirname( __FILE__ ) . '/load.php');
if(isset($_GET['action'])) {
    $action = $_GET['action'];
}
else{
    $action = "login";
}
if($action == 'login'):
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="includes/assets/css/login_register.css">
        <title>Login</title>
    </head>
    <body>
        <div class="login-form">
            <div class="head">
                <img src="includes/assets/images/default-avater.gif" alt=""/>
            </div>
            <form action="<?php $_SERVER['PHP_SELF']; ?>" method="post">
                <li>
                    <input type="text" class="text" value="Username" onfocus="this.value = '';" onblur="if (this.value == '') {this.value = 'Usename';}" ><a href="#" class=" icon user"></a>
                </li>
                <li>
                    <input type="password" value="Password" onfocus="this.value = '';" onblur="if (this.value == '') {this.value = 'Password';}"><a href="#" class=" icon lock"></a>
                </li>
                <div class="p-container">
                    <label for="rememberme" class="checkbox"><input type="checkbox" name="rememberme" checked>Remember Me</label>
                    <input type="submit" value="Login" >
                    <a href="?action=register" class="other-info-register">Create a new account?</a>
                </div>
            </form>
        </div>
        <script src="includes/assets/js/jquery.min.js"></script>
        <script src="includes/assets/js/javascript.js"></script>
    </body>
</html>
<?php
elseif($action == 'register'):
?>
<!doctype html>
<html lang="en">
    <head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="includes/assets/css/login_register.css">
    <title>Register</title>
    </head>
    <body>
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
                <input type="submit" value="Signup" >
            </div>
        </form>
        </div>
        <script src="includes/assets/js/jquery.min.js"></script>
        <script src="includes/assets/js/javascript.js"></script>
    </body>
</html>
<?php
else:
    die("Sorry, Unknown Request!");
endif;?>

