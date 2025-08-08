<?php
namespace WebOffice;
use InvalidArgumentException;
class Storage{
    public function __construct() {
        
    }
    /**
     * Messes with cookie storage
     * @param string $name Cookies name
     * @param mixed $value Cookies value
     * @param string $action Actions: _"Store"_/_"Load"_/_"Delete"_
     * @param int $expire Expiration **(by hours)**
     * @param string $path Cookies Path
     * @param string $domain Cookies domain
     * @param bool $secure Secured websites
     * @param bool $httponly Non-secured websites
     * @throws \InvalidArgumentException
     * @return mixed Returned cookie value or null
     */
    public function cookie(string $name, mixed $value = null, string $action = 'store', int $expire = 30, string $path='/', string $domain='', bool $secure=false, bool $httponly=false): mixed {
        switch (strtolower($action)) {
            case 'save':
                // Set a cookie that expires in $expire days
                $seconds = $expire * 3600; // days to seconds
                setcookie($name, $value, time() + $seconds, $path, $domain, $secure, $httponly);
                break;

            case 'load':
                return $_COOKIE[$name] ?? null;

            case 'delete':
                setcookie($name, '', time() - 3600, $path, $domain, $secure, $httponly);
                unset($_COOKIE[$name]);
                break;
            default:
                throw new InvalidArgumentException("Invalid action: $action");
        }
        return null;
    }
    /**
     * Creates a session storage
     * @param string|null $name Sessions name
     * @param mixed $value Sessions value
     * @param string $action Set/Get/Delete/Clear
     * @return mixed Returns the value of session or null
     */
    public function session(string|null $name, mixed $value=null, string $action='set'): mixed{
        switch(strtolower($action)){
            case 'set':
                $_SESSION[$name] = $value;
            break;
            case 'get':
                return $_SESSION[$name]??null;
            case 'delete':
                unset($_SESSION[$name]);
            break;
            case 'clear':
                session_unset();
                session_destroy();
            break;
        }
        return null;
    }
}