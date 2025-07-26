<?php
namespace WebOffice;
class Utils{
    public function __construct() {
        
    }
    /**
     * Parse type to a different type
     * @param mixed $item Value to convert
     * @param string $from Datatype from
     * @param string $to Converted datatype
     * @return mixed Converted type
     */
    public function parse($item, string $from, string $to): mixed{
        $from = strtolower($from);
        $to = strtolower($to);
        switch ($from) {
            case 'string':
                $value = (string)$item;
                break;
            case 'boolean':
                $value = (bool)$item;
                break;
            case 'int':
            case 'integer':
                $value = (int)$item;
                break;
            case 'float':
            case 'double':
                $value = (float)$item;
                break;
            default:
                return $item;
        }
        switch ($to) {
            case 'string':
                return (string)$value;
            case 'boolean':
                if (is_string($value)) {
                    $lower = strtolower($value);
                    if (in_array($lower, ['true', '1', 'yes', 'on'])) {
                        return true;
                    } elseif (in_array($lower, ['false', '0', 'no', 'off'])) {
                        return false;
                    } else {
                        return (bool)$value;
                    }
                }
                return (bool)$value;
            case 'int':
            case 'integer':
                return (int)$value;
            case 'float':
            case 'double':
                return (float)$value;
            default:
                return $value;
        }
    }
    /**
     * Send a request
     * @param string $url URL to request
     * @param string $method GET, POST, PUT, DELETE, and PATCH methods
     * @param array $headers Headers with values
     * @param mixed $body
     * @throws \Exception
     * @return array{response: bool|string, status_code: mixed}
     */
    public function request(string $url, string $method='GET', array $headers=[], ?string $body=null): array {
        $ch = curl_init();

        // Set URL
        curl_setopt($ch, CURLOPT_URL, $url);

        // Return response instead of outputting
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Set HTTP method
        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                if ($body !== null) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
                }
                break;
            case 'PUT':
            case 'DELETE':
            case 'PATCH':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
                if ($body !== null) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
                }
                break;
            case 'GET':
                // GET is default, no additional setting needed
                break;
            default:
                // For any other methods, set custom request
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
                if ($body !== null) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
                }
                break;
        }

        // Set headers if provided
        if (!empty($headers)) {
            $formattedHeaders = [];
            foreach ($headers as $key => $value) {
                if (is_int($key)) {
                    // When headers are just values without keys
                    $formattedHeaders[] = $value;
                } else {
                    // When headers are key-value pairs
                    if (is_array($value)) {
                        // If a header value is an array, join its elements
                        $valueString = implode(', ', $value);
                        $formattedHeaders[] = "$key: $valueString";
                    } else {
                        $formattedHeaders[] = "$key: $value";
                    }
                }
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $formattedHeaders);
        }

        // Execute request
        $response = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            throw new \Exception("cURL error: $error_msg");
        }

        // Get info (e.g., status code)
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return ['status_code' => $statusCode, 'response' => $response];
    }
    /**
     * Generates a UUID
     * @return string UUID
     */
    public function generateUUID(): string{
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
    /**
     * Generates a random color
     * @param string $type Color type to generate. HEX, RGB, and HSL
     * @return string Color
     */
    public function generateColor(string $type='hex'): string {
        switch (strtolower($type)) {
            case 'hex':
                return '#' . str_pad(dechex(random_int(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
            case 'rgb':
                return 'rgb(' . random_int(0, 255) . ', ' . random_int(0, 255) . ', ' . random_int(0, 255) . ')';
            case 'hsl':
                $h = random_int(0, 360);
                $s = random_int(0, 100);
                $l = random_int(0, 100);
                return "hsl($h, $s%, $l%)";
            default:
                // Default to hex if unknown type
                return '#' . str_pad(dechex(random_int(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
        }
    }
    /**
     * Execute a shell command
     * @param string $command Command to execute
     * @throws \RuntimeException If the command fails
     * @return bool|string|null Output and return status
     */
    public function executeCommand(string $command): bool|string|null{
        $command = $this->escapeShell($command);
        $output = shell_exec($command);
        return $output;
    }
    /**
     * Escape a shell command
     * @param string $command Command to escape
     * @return string Escaped command
     */
    private function escapeShell(string $command): string {
        return escapeshellcmd($command);
    }
    /**
     * Convert bytes to a human-readable format
     * @param int $bytes Number of bytes
     * @param int $precision Number of decimal places
     * @return string Human-readable size
     */
    public function bytes2readable(int $bytes, int $precision = 2): string {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$precision}f %s", $bytes / pow(1024, $factor), $units[$factor]);
    }
    /**
     * Convert a human-readable size to bytes
     * @param string $size Human-readable size (e.g., "10MB", "2.5GB")
     * @return int Size in bytes
     */
    public function readable2bytes(string $size): int {
        $units=['B'=>0,'KB'=>1,'MB'=>2,'GB'=>3,'TB'=>4,'PB'=>5,'EB'=>6,'ZB'=>7,'YB'=>8,'K'=>1,'M'=>2,'G'=>3,'T'=>4,'P'=>5,'E'=>6,'Z'=>7,'Y'=>8];
        $size = trim($size);
        $unit = strtoupper(preg_replace('/[0-9.]/', '', $size));
        $unit = rtrim($unit, 'B'); // Remove trailing 'B' if present
        if ($unit === '')
            $unit = 'B';
        elseif (isset($units["{$unit}B"])) 
            $unit.='B';
        
        $value = floatval(preg_replace('/[^\d.]/', '', $size));
        return isset($units[$unit]) ? (int)($value * pow(1024, $units[$unit])) : (int)$value;
    }
}