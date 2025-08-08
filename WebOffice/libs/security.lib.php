<?php
namespace WebOffice;

use ErrorException;
use WebOffice\Security\JWT;
use WebOffice\Security\MFA;
use WebOffice\Security\CSRF;
use WebOffice\Security\Rate;
use WebOffice\Server;

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
    FILTER_SSN = "/^(?!000|666|9\d{2})\d{3}-(?!00)\d{2}-(?!0000)\d{4}$/",
    FILTER_URL = "/[(http(s)?):\/\/(www\.)?a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&\/\/=]*)/",
    MASK_SSN = "/(\d{3})-(\d{2})/",
    MASK_CARD_PAN = "/(\d{12})|(\d{4}) ?(\d{4}) ?(\d{4})/";
    public function __construct() {
        
    }
    /**
     * Sets security headers
     * @param array{header:string} $headers List of headers
     * @return void Sets the header of the website
     */
    public function setSecurityHeaders(array $headers=[]): void{
        header("Content-Security-Policy: default-src 'self' https://cdn.jsdelivr.net; script-src 'self' https://code.jquery.com/jquery-3.7.1.min.js https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js; object-src 'none';");
        header("X-Content-Type-Options: nosniff");
        header("X-Frame-Options: DENY");
        header("X-XSS-Protection: 1; mode=block");
        header("Referrer-Policy: no-referrer");
        header("Content-Security-Policy: frame-ancestors 'none'");
        if(!empty($headers)){
            foreach($headers as $header){
                $header = $this->preventXSS($header);
                if($this->preventRequestSmuggling($header)) header($header);
                    
                
            }
            print_r(headers_list());
        }
    }
    /**
     * Audits the code in the 
     * @param string $directory Directory path to audit
     * @return void
     */
    public function auditCode(string $directory = ADDONS_PATH): void {
        $issues = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory)
        );
        foreach ($iterator as $file) {
            if ($file->isFile() && pathinfo($file->getFilename(), PATHINFO_EXTENSION) === 'php') {
                $content = file_get_contents($file->getRealPath());
                
                $dangerousFunctions = [
                    'shell_exec(',
                    'eval(',
                    'exec(',
                    'system(',
                    'passthru(',
                    'popen(',
                    'proc_open(',
                    'create_function(',
                    'assert('
                ];

                foreach ($dangerousFunctions as $func) {
                    if (strpos($content, $func) !== false) {
                        $issueMsg = "Potential unsafe usage of '{$func}' in: " . $file->getRealPath();
                        $issues[] = $issueMsg;
                        $this->logSecurityEvent('code_audit', $issueMsg);
                        break;
                    }
                }

                // Check for usage of $_GET, $_POST without sanitization
                if (preg_match('/\$_(GET|POST|REQUEST)/', $content) && !strpos($content, 'filter_input') && !strpos($content, 'sanitize')) {
                    $issueMsg = "Possible unsafe input handling in: " . $file->getRealPath();
                    $issues[] = $issueMsg;
                    $this->logSecurityEvent('code_audit', $issueMsg);
                }

                // Check if error_reporting is not set
                if (strpos($content, 'error_reporting') === false) {
                    $issueMsg = "Error reporting not configured in: " . $file->getRealPath();
                    $issues[] = $issueMsg;
                    $this->logSecurityEvent('code_audit', $issueMsg);
                }

                // Add more checks as needed...
            }
        }

        if (!empty($issues)) {
            foreach ($issues as $issue) {
                $this->logSecurityEvent('code_audit', $issue);
            }
        } else {
            // No issues found, optionally log
            $this->logSecurityEvent('code_audit', "Code audit completed: No issues found.");
        }
    }
    /**
     * Removes script tag to prevent XSS
     * @param string $str String to sanitize
     * @return string|null Sanitized string
     */
    public function preventXSS(string $str): string|null{
        return preg_replace('/<script.*?>.*?<\/script>/','',$str);
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
    /**
     * Multi-factor authentication
     * @param string $user Username
     * @param string $action GET or VERIFY
     * @param int $code  Verification code
     * @return bool|string TRUE if verified, else FALSE. Returns TOTP URL if action="get"
     */
    public function MFA(string $user, string $action='GET', int $code=000000): bool|string{
        $m = new MFA($user);
        $action = strtolower($action);
        if($action==='get')
            return $m->getTOTP();
        else
            return $m->verify((int)$code);
    }
    /**
     * Checks for JSON Web Token
     * @param string $secret Secret
     * @param array $payload Payload
     * @param string $token Token(Encoded payload)
     * @param string $action Encode/Decode
     * @param int $exp Expiration
     * @return array|bool|string Results of the key
     */
    public function JWT(string $secret,  array $payload=[], string $token='', string $action='encode', int $exp=3600): array|bool|string{
        $jwt = new JWT($secret);
        $action = strtolower($action);
        return strcmp($action,'encode')==0 ? $jwt->encode($payload,$exp) : $jwt->decode($token);
    }
    /**
     * Prevents request smuggling and header injection
     * @param string|null $header Optional header string to validate before setting
     * @return bool TRUE if safe, otherwise FALSE
     */
    public function preventRequestSmuggling(?string $header = null): bool {
        if ($header === null) {
            // Check all existing headers for CRLF injection
            $headers = headers_list();
            foreach ($headers as $hdr) {
                if (preg_match('/\r\n|\n|\r||\\\\r|\\\\n/', $hdr)) {
                    // Log the incident
                    $this->logSecurityEvent("Potential request smuggling attempt detected: header contains CRLF characters");
                    return false;
                }
            }
        } else {
            // Validate the header string before setting
            // Detect dangerous CR, LF characters that could indicate injection
            if (preg_match('/\r\n|\n|\r|\\\\r|\\\\n/', $header)) {
                $this->logSecurityEvent("Header injection attempt detected in header string");
                return false;
            }
            // You can sanitize or encode the header here if needed before setting
        }

        // Check for conflicting Content-Length and Transfer-Encoding headers
        if (isset($_SERVER['HTTP_CONTENT_LENGTH']) && isset($_SERVER['HTTP_TRANSFER_ENCODING'])) {
            $contentLength = intval($_SERVER['HTTP_CONTENT_LENGTH']);
            $transferEncoding = $_SERVER['HTTP_TRANSFER_ENCODING'];

            if ($contentLength > 0 && strcasecmp($transferEncoding, 'chunked') === 0) {
                $this->logSecurityEvent("Conflicting Content-Length and Transfer-Encoding headers");
                return false;
            }
        }

        // All checks passed
        return true;
    }

    /**
     * Security event
     * @param string $name Log name
     * @param string $event Event to log
     * @return void
     */
    public function logSecurityEvent(string $name='security', string $event): void {
        if (error_reporting() === 0) {
            // Error reporting is disabled; do not log
            return;
        }

        // Check if errors are being logged
        if (!ini_get('log_errors')) {
            // Logging is disabled; optionally, you can enable it or skip
            return;
        }
        $log = ERROR_LOG.DS."$name.log";
        error_log("[".date('Y-m-d H:i:s.u')."] $event".PHP_EOL,3,$log);
    }
    /**
     * Hits the user base on the rate limit
     * @return bool TRUE if the rate is good, else false
     */
    public function rateLimit(): bool{
        $rate = new Rate();
        if($rate->hit()) return true;
        else {
            header("HTTP/1.1 429 Too Many Requests");
            return false;
        } 
    }
    /**
     * Enforces HTTPS unless on localhost
     * @return void
     */
    public function enforceHTTPS(): void {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        // Check if running on localhost
        if (strpos($host, 'localhost') !== false || $host === '127.0.0.1') return;
        if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
            $httpsUrl = "https://$host{$_SERVER['REQUEST_URI']}";
            header("Location: $httpsUrl");
            exit();
        }
    }
    /**
     * Regenerates a new session
     * @param bool $deleteOldCookie Deletes any old cookies
     * @return bool TRUE on success, else FALSE
     */
    public function regenerateSession(bool $deleteOldCookie=false): bool{
        return @session_regenerate_id($deleteOldCookie);
    }
    /**
     * Prevents session fixation
     * @return bool TRUE on success, else FALSE
     */
    public function preventSessionFixation(): bool{
        $this->regenerateSession(true);
        $params = session_get_cookie_params();
        return @session_set_cookie_params([
            'lifetime'=>$params['lifetime'],
            'path'=>$params['path'],
            'domain'=>$params['domain'],
            'secure'=>true,
            'httponly'=>true,
            'samesite'=>'Strict'
        ]);
    }
    /**
     * Performing a penetration test
     * @return array{disabled_functions: bool|string, php_version: string, port_443_open: string, port_80_open: string}
     */
    public function penTest(): array{
        $server = new Server();
        $results = [
            'port_80_open'=>$server->checkPort('127.0.0.1', 80)[0]['status'],
            'port_443_open'=>$server->checkPort('127.0.0.1', 443)[0]['status'],
            'php_version'=>PHP_VERSION,
            'disabled_functions'=>ini_get('disable_functions')
        ];
        return $results;
    }
    /**
     * Generate an SRI hash for a local file.
     *
     * @param string $filePath Path to the file
     * @param string $algorithm Hash algorithm (default: 'sha384')
     * @return string|false The SRI hash string or false on failure
     */
    public function generateSRI($filePath, $algorithm = 'sha384'): bool|string {
        if (!file_exists($filePath)) {
            return false; // File does not exist
        }
        // Get the file contents
        $fileData = file_get_contents($filePath);
        if ($fileData === false) {
            return false;
        }
        $hash = hash($algorithm, $fileData, true);
        $base64Hash = base64_encode($hash);
        return "$algorithm-$base64Hash";
    }
    /**
     * Masks a specific part of the string based on a regex, replacing only the matched digits with mask characters.
     * @param string $input String to mask
     * @param string $regExp RegExp to match and mask
     * @param string $mask Mask character (default: *)
     * @return string|null Masked string or null if no match
     */
    public function mask(string $input, string $regExp, string $mask='*'): ?string {
        // Use preg_replace_callback to process each match
        $result = preg_replace_callback($regExp, function($matches) use ($mask): mixed {
            // $matches[0] is the full match, $matches[1], $matches[2], ... are groups
            // We'll replace only the groups (e.g., digits) with the mask, keeping the original parts intact
            $maskedGroups = [];
            for ($i = 1; $i < count($matches); $i++) {
                // For each group, replace its characters with the mask character
                if (is_string($matches[$i])) {
                    $maskedGroups[$i] = str_repeat($mask, strlen($matches[$i]));
                } else {
                    // In case the group is not a string (unlikely), just keep it
                    $maskedGroups[$i] = $matches[$i];
                }
            }
            $maskedString = $matches[0];
            foreach ($maskedGroups as $index => $maskedPart) {
                $originalGroup = $matches[$index];
                $maskedString = str_replace($originalGroup, $maskedPart, $maskedString);
            }

            return $maskedString;
        }, $input);
        if ($result !== $input) {
            return $result;
        } else {
            return null; // no match found
        }
    }
    /**
     * Hash your password
     * @param string $input Password
     * @param string $algo Algorithm
     * @param array $options Options
     * @return string Hashed password
     */
    public function hashPsw(string $input, string $algo=PASSWORD_DEFAULT, array $options=[]): string{
        return password_hash($input,$algo,$options);
    }
    /**
     * Verifies the raw password with the hashed password 
     * @param string $input Password
     * @param string $hash Hashed password
     * @return bool TRUE if password matches, else FALSE
     */
    public function verify(string $input, string $hash): bool{
        return password_verify($input,$hash);
    }
    /**
     * Starts the session
     * @return bool
     */
    public function sessionStart(): bool{
        if (session_status() !== PHP_SESSION_ACTIVE) return session_start();
        else return true;
    }
}