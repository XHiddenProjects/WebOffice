<?php
namespace WebOffice;

use ErrorException;
use WebOffice\CSRF;

class Security{
    public const SANITIZE_DEFAULT = "/[^a-zA-Z0-9\s!@#$%^&*()\-_=+\[\]{}|;:\'\",.<>\/?`~]/",
    SANITIZE_EMAIL = "/[^a-zA-Z0-9._@]/",
    SANITIZE_PHONE = "/[^+\d]/",
    SANITIZE_INT = "/[^\d]/",
    SANITIZE_FLOAT = "/[^\d.]/",
    SANITIZE_STRING = "/[^a-zA-Z0-9\s]/",
    SANITIZE_SPECIAL_CHARS = "/[^.*+?\\\^$()\[\]\/{}|@!#%&<>,.~`=-_\s]/",
    SANITIZE_URL = "/[^a-zA-Z0-9\-_\.\~\/\?\=\&\:\%\;]/",
    FILTER_DEFAULT = "/[a-zA-Z0-9\s!@#$%^&*\(\)\-_=\+\[\]\{\}|;:\'\",.<\/?`~]+/",
    FILTER_EMAIL = "/[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/",
    FILTER_PHONE = "/^\s*(?:\+?\d{1,3}[\s.-]?)?(?:\(?\d{3}\)?[\s.-]?)?\d{3}[\s.-]?\d{4}(?:[\s.-]?\d{3,4})*\s*$/",
    FILTER_INT = "/^[+-]?\d+$/",
    FILTER_FLOAT = "/^[+-]?(\d+(\.\d*)?|\.\d+)$/",
    FILTER_DATE = "/(?:(?:\b\d{4}[-\/\.](?:0?[1-9]|1[0-2])[-\/\.](?:0?[1-9]|[12][0-9]|3[01])\b)|(?:\b(?:0?[1-9]|[12][0-9]|3[01])[-\/\.](?:0?[1-9]|1[0-2])[-\/\.]\d{4}\b)|(?:\b\d{4}\s+(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*\s+\d{4}\b)|(?:\b(?:0?[1-9]|[12][0-9]|3[01])\s+(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*\s+\d{4}\b)|(?:\b(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*\s+\d{1,2},?\s+\d{4}\b)|(?:\b\d{1,2}\s+(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*,?\s+\d{4}\b))/",
    FILTER_TIME = "/^(?:(?:0?[1-9]|1[0-2])(?:\:[0-5]\d){1,2}(?:\:[0-5]\d)?\s?(?:AM|PM|am|pm)?|(?:[01]?\d|2[0-3])(?:\:[0-5]\d){1,2}(?:\:[0-5]\d)?)$/",
    FILTER_IPV4 = "/^(?:(?:25[0-5]|2[0-4]\d|1?\d{1,2})\.){3}(?:25[0-5]|2[0-4]\d|1?\d{1,2})$/",
    FILTER_IPV6 = "/^((?:[0-9a-fA-F]{1,4}:){7}[0-9a-fA-F]{1,4}|(?:[0-9a-fA-F]{1,4}:){1,7}:|:(?::[0-9a-fA-F]{1,4}){1,7}|(?:[0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|(?:[0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|(?:[0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|(?:[0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|(?:[0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:(:|[0-9a-fA-F]{1,4}(:[0-9a-fA-F]{1,4}){0,5})|fe80:(?::[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(?:ffff:(?:25[0-5]|2[0-4]\d|1?\d{1,2})(?:\.(?:25[0-5]|2[0-4]\d|1?\d{1,2})){3})|(?:[0-9a-fA-F]{1,4}:){1,4}:(?:25[0-5]|2[0-4]\d|1?\d{1,2})(?:\.(?:25[0-5]|2[0-4]\d|1?\d{1,2})){3})$/i",
    FILTER_ADDR = "/(?:(?:\d+\s+[A-Za-z0-9\s.,'-]+)\s*,?\s*)?(?:[A-Za-z\s]+)?\s*,?\s*(?:[A-Za-z\s]+)?\s*,?\s*(?:[A-Z]{2,}|[A-Za-z\s]+)?\s*,?\s*(?:\d{5}|\d{4,6}|[A-Za-z0-9\s-]+)?\s*,?\s*(?:[A-Za-z\s]+)?/",
    FILTER_URL = "/[(http(s)?):\/\/(www\.)?a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&\/\/=]*)/";
    public function __construct() {
        
    }
    /**
     * Sets security headers
     * @return void
     */
    public function setSecurityHeaders(): void{
        header("Content-Security-Policy: default-src 'self' https://cdn.jsdelivr.net; script-src 'self' https://code.jquery.com/jquery-3.7.1.min.js https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js; object-src 'none';");
        header("X-Content-Type-Options: nosniff");
        header("X-Frame-Options: DENY");
        header("X-XSS-Protection: 1; mode=block");
        header("Referrer-Policy: no-referrer");
    }
    /**
     * Sanitizes the string
     * @param string $input String to sanitize
     * @param string $pattern RegExp to sanitize
     * @return string Sanitized string
     */
    public function sanitize(string $input, string $pattern=self::SANITIZE_DEFAULT): string{
        return htmlspecialchars(preg_replace($pattern,'',$input),ENT_QUOTES);
    }
    /**
     * Filters the string to proper format
     * @param string $input Input to filter
     * @param string $pattern RegExp to filter
     * @return string Filtered string
     */
    public function filter(string $input, string $pattern=self::FILTER_DEFAULT): string{
        if(preg_match($pattern,$input,$matches)) return htmlspecialchars($matches[0],ENT_QUOTES);
        else return '';
    }
    /**
     * Checks the version of the application against the latest version
     * @return bool|int Returns true if the current version is less than the latest version, false otherwise
     */
    public function checkVersion(): bool{
        $v1 = file_get_contents(VERSION);
        $v2 = (new Utils())->request('https://raw.githubusercontent.com/XHiddenProjects/WebOffice/refs/heads/master/VERSION',
        'GET',
        ['headers'=>['User-Agent'=>'WebOffice/1.0']]);
        return @version_compare(trim($v1), trim($v2['response']), '<');
    }
    /**
     * Creates/Verifies CSRF token
     * @param string $action Actions: "Load" or "verify"
     * @param string $token Token input, **if verify**
     * @return bool|string Returns token if loaded, else TRUE/FALSE on verify
     * @throws ErrorException Invalid action
     */
    public function CSRF(string $action='load', string $token=''): bool|string{
        $c = new CSRF();
        $action = strtolower($action);
        if($action==='load') return $c->getToken();
        elseif($action==='verify') return $c->verify($token);
        else throw new ErrorException('Must be a load or verify action');
    }
}