<?php
namespace WebOffice;
use WebOffice\Utils, WebOffice\Database, WebOffice\Config, WebOffice\Zip, WebOffice\Files, WebOffice\Security;
include_once dirname(__DIR__).'/init.php';
class Server{
    private Utils $utils;
    private Database $db;
    public function __construct(){
        $this->utils = new Utils();
        $config = new Config();
        $this->db = new Database($config->read('mysql', 'host'), 
                                $config->read('mysql', 'user'), 
                                $config->read('mysql', 'psw'), 
                                $config->read('mysql', 'db'));
        
    }
    public function getServerName(): string {
        return $_SERVER['SERVER_NAME'] ?? 'localhost';
    }
    public function getServerPort(): int {
        return (int)($_SERVER['SERVER_PORT'] ?? 80);
    }
    public function getServerProtocol(): string {
        return $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1';
    }
    public function getServerSoftware(): string {
        return $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
    }
    public function runTime(): int {
        return (int)($_SERVER['REQUEST_TIME'] ?? time());
    }
    public function getRequestMethod(): string {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }
    /**
     * Start the server (XAMPP/WAMP/MAMP or PHP built-in)
     */
    public function start(): void {
        if (strncasecmp(OS, 'WIN', 3) === 0) {
            // Windows: Try WAMP/XAMPP
            if (file_exists('C:\\xampp\\xampp-control.exe')) {
                $this->utils->executeCommand('start /B C:\\xampp\\xampp-control.exe start');
            } elseif (file_exists('C:\\wamp64\\wampmanager.exe')) {
                $this->utils->executeCommand('start /B C:\\wamp64\\wampmanager.exe');
            } else {
                // Fallback to PHP built-in server
                $this->startPhpBuiltIn();
            }
        } elseif (file_exists('/Applications/MAMP/bin/start.sh')) {
            // Mac: MAMP
            $this->utils->executeCommand('sh /Applications/MAMP/bin/start.sh');
        } elseif (file_exists('/opt/lampp/lampp')) {
            // Linux: XAMPP
            $this->utils->executeCommand('sudo /opt/lampp/lampp start');
        } else {
            // Fallback to PHP built-in server
            $this->startPhpBuiltIn();
        }
    }

    /**
     * Stop the server (XAMPP/WAMP/MAMP or PHP built-in)
     */
    public function stop(): void {
        if (strncasecmp(OS, 'WIN', 3) === 0) {
            if (file_exists('C:\\xampp\\xampp-control.exe')) {
                $this->utils->executeCommand('start /B C:\\xampp\\xampp-control.exe stop');
            } elseif (file_exists('C:\\wamp64\\wampmanager.exe')) {
                // WAMP does not have a direct CLI stop, so you may need to kill the process
                $this->utils->executeCommand('taskkill /IM wampmanager.exe /F');
            } else {
                $this->stopPhpBuiltIn();
            }
        } elseif (file_exists('/Applications/MAMP/bin/stop.sh')) {
            $this->utils->executeCommand('sh /Applications/MAMP/bin/stop.sh');
        } elseif (file_exists('/opt/lampp/lampp')) {
            $this->utils->executeCommand('sudo /opt/lampp/lampp stop');
        } else {
            $this->stopPhpBuiltIn();
        }
    }

    /**
     * Reset the server by stopping and starting it again
     */
    public function reset(): void {
        if (strncasecmp(OS, 'WIN', 3) === 0) {
            if (file_exists('C:\\xampp\\xampp-control.exe')) {
                // Restart XAMPP using the correct command to avoid stopping Apache
                $this->utils->executeCommand('start /B C:\\xampp\\xampp-control.exe restart');
            } elseif (file_exists('C:\\wamp64\\wampmanager.exe')) {
                // WAMP does not have a direct restart, so fallback to stop/start
                $this->stop();
                $this->start();
            } else {
                $this->resetPhpBuiltIn();
            }
        } elseif (file_exists('/Applications/MAMP/bin/start.sh')) {
            // Restart MAMP by stopping and starting
            $this->utils->executeCommand('sh /Applications/MAMP/bin/stop.sh');
            $this->utils->executeCommand('sh /Applications/MAMP/bin/start.sh');
        } elseif (file_exists('/opt/lampp/lampp')) {
            // Restart XAMPP on Linux
            $this->utils->executeCommand('sudo /opt/lampp/lampp restart');
        } else {
            $this->resetPhpBuiltIn();
        }
    }

    /**
     * Reset PHP built-in server (for fallback)
     */
    private function resetPhpBuiltIn(): void {
        $this->stopPhpBuiltIn();
        $this->startPhpBuiltIn();
    }
    /**
     * Checks if the server is running
     * @return bool TRUE if server is running, else FALSE
     */
    public function isRunning(): bool {
        if (strncasecmp(OS, 'WIN', 3) === 0) {
            // Check for XAMPP/WAMP processes
            $outputString = $this->utils->executeCommand('tasklist');
            $output = preg_split('/\r?\n/', $outputString);
            foreach ($output as $line) {
                if (stripos($line, 'xampp-control.exe') !== false || stripos($line, 'wampmanager.exe') !== false) {
                    return true;
                }
            }
        } elseif (file_exists('/Applications/MAMP/bin/start.sh')) {
            // Mac: Check for MAMP Apache process
            $result = $this->utils->executeCommand('ps aux | grep "/Applications/MAMP/Library/bin/httpd" | grep -v grep');
            $output = $result['output']??'';
            if (!empty($output)) {
                return true;
            }
        } elseif (file_exists('/opt/lampp/lampp')) {
            // Linux: Check for XAMPP Apache process
            $result = $this->utils->executeCommand('ps aux | grep "/opt/lampp/bin/httpd" | grep -v grep');
            $output = $result['output']??'';
            if (!empty($output)) {
                return true;
            }
        } else {
            // Fallback to PHP built-in server check
            return $this->isPhpBuiltInRunning();
        }
        return false;
    }

    /**
     * Start PHP built-in server (for fallback)
     */
    private function startPhpBuiltIn(): void {
        $host = $this->getServerName();
        $port = $this->getServerPort();
        $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? getcwd();
        $cmd = sprintf('php -S %s:%d -t %s', $host, $port, $docRoot);
        $pidFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'server.pid';
        $this->utils->executeCommand("$cmd > /dev/null 2>&1 & echo $! > $pidFile")['output'];
    }

    /**
     * Stop PHP built-in server (for fallback)
     */
    private function stopPhpBuiltIn(): void {
        $pidFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'server.pid';
        if (file_exists($pidFile)) {
            $pid = (int)file_get_contents($pidFile);
            if ($pid > 0) {
                if (function_exists('posix_kill')) {
                    posix_kill($pid, 9);
                } else {
                    $this->utils->executeCommand("kill -9 $pid")['output'];
                }
            }
            unlink($pidFile);
        }
    }

    /**
     * Check if PHP built-in server is running (for fallback)
     */
    private function isPhpBuiltInRunning(): bool {
        $pidFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'server.pid';
        if (file_exists($pidFile)) {
            $pid = (int)file_get_contents($pidFile);
            if ($pid > 0) {
                if (strncasecmp(OS, 'WIN', 3) === 0) {
                    // Execute command and get output as string
                    $output = $this->utils->executeCommand("wmic process where ProcessId=$pid get ProcessId");
                    // Check if output contains the PID
                    if (strpos($output, (string)$pid) !== false) {
                        return true;
                    }
                } else {
                    // Execute command and get output as string
                    $output = $this->utils->executeCommand("ps -p $pid");
                    // Check if output contains the PID
                    if (strpos($output, (string)$pid) !== false) {
                        return true;
                    }
                }
            }
        }
        return false;
    }
    /**
     * Returns the servers health information
     * @return array{database_connected: bool, memory: array{limit: bool|string, peak_usage: string, status: string, usage: string, request_method: string, run_time: int, server_name: string, server_port: int, server_protocol: string, server_running: bool, server_software: string}} Servers health
     */
    public function health(): array {
        $status = [
            'server_running' => $this->isRunning(),
            'server_name' => $this->getServerName(),
            'server_port' => $this->getServerPort(),
            'server_protocol' => $this->getServerProtocol(),
            'server_software' => $this->getServerSoftware(),
            'request_method' => $this->getRequestMethod(),
            'run_time' => $this->runTime(),
            'database_connected' => $this->db !== null,
            'memory'=>[
                'usage'=>$this->utils->bytes2readable(memory_get_usage(true)),
                'peak_usage'=>$this->utils->bytes2readable(memory_get_peak_usage(true)),
                'limit'=>ini_get('memory_limit'),
                'status'=>((memory_get_usage(true) / $this->utils->readable2bytes(ini_get('memory_limit'))) < 0.8 ? 'OK' : 'High')
            ],
        ];
        return $status;
    }
    /**
     * Backs up a the servers data
     * @param string $backup Backup name
     * @return void
     */
    public function backup(string $backup='backup'): void {
        $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? getcwd();

        // Use the provided backup name, ensure .zip extension
        $backupFileName = preg_match('/\.zip$/i', $backup) ? $backup : "$backup.zip";
        $backupPath = BACKUP_PATH.DS.$backupFileName;


        // Use zip.lib.php and db.lib.php
        $zip = new Zip($backupPath);

        

        // Add document root files to backup
        $zip->create(true);


        // Scan and add document root files to backup using Files class
        $files = new Files();
        $fileList = $files->scan($docRoot); // Recursively scan all files
        foreach ($fileList as $file) {
            $relativePath = str_replace($docRoot . DS, '', $file);
            if (is_dir($docRoot.DS.$file)) {
                $zip->addFolder($docRoot.DS.$file, $file);
            } else {
                $zip->addFile($docRoot.DS.$file,$file);
            }
        }


        // Optionally backup database if needed
        if ($this->db !== null) 
            $this->db->backup($backupPath);

        $zip->save();
        $this->db->close();
    }
    /**
     * Resolve hostname from IP address
     * @param string $ipName IP Address or name
     * @return string Returns the hostname
     */
    public function hostname(string $ipName): string {
        $isIP = (new Security())->filter($ipName,Security::FILTER_IPV4);
        $sel = !empty($isIP) ? $isIP : $ipName;
        $hostname = $isIP ? getHostByAddr($sel) : getHostByName($sel);
        return $hostname !== false ? $hostname : '';
    }
}