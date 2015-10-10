<?php
/**
 * Class: DATA_HASHING
 *
 * This class will handle the error's
 *
 * @package Manager
 * @sub-package Manager-Library
 * @since 1.0.0
 */
if(!class_exists("DATA_HASHING")):
class DATA_HASHING{

    /**
     * Initializer
     */
    function __construct(){

    }

    /**
     * Hashing Method
     *
     * @link http://php.net/manual/en/function.hash.php
     */
    public function _hash($type = 'md5', $data = '', $salt = null){
        return hash($type, $data . $salt);
    }

    /**
     * Verify Hashing
     */
    public function _hash_verify($type = 'md5', $data = '', $salt = null, $hash){
        $realHashing = hash($type, $data . $salt);
        $hashByClient = $hash;
        if($realHashing == $hashByClient){
            return true;
        }
        else{
            false;
        }
    }

    /**
     * Password hashing
     *
     * @use password_hash() – used to hash the password.
     * @use password_verify() – used to verify a password against its hash.
     * @use password_needs_rehash() – used when a password needs to be rehashed.
     * @use password_get_info() – returns the name of the hashing algorithm and various options used while hashing.
     *
     * These function are shiped with php 5.5. Older version users can use these also, because we have included
     * the PasswordCompat project by ircmacell. see https://github.com/ircmaxell/password_compat for more
     */
}
endif;

