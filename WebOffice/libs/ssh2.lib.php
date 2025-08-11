<?php
namespace WebOffice;

/*
 * Class SSH2
 * Provides SSH connection management and remote file operations.
 */
class SSH2 {
    private $connection; // SSH connection resource
    private $host;
    private $port;
    private $username;
    private $password;
    private Files $files;

    /**
     * Constructor to initialize SSH2 class
     */
    public function __construct() {
        $this->connection = null;
        $this->host = '';
        $this->port = 22; // Default SSH port
        $this->username = '';
        $this->password = '';
        $this->files = new Files();
    }

    /**
     * Connect to an SSH server with credentials
     * @param string $host - Hostname or IP
     * @param int $port - SSH port, default 22
     * @param string $username - SSH username
     * @param string $password - SSH password
     * @return bool - success
     */
    public function connect($host, $port = 22, $username, $password): bool {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;

        // Establish SSH connection
        $this->connection = ssh2_connect($host, $port);
        if ($this->connection) {
            // Authenticate
            if (ssh2_auth_password($this->connection, $username, $password)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Disconnect SSH connection
     */
    public function disconnect(): void {
        $this->connection = null; // PHP cleanup
    }

    /**
     * Execute a command on the remote server
     * @param string $command
     * @return string|false - command output or false on failure
     */
    public function executeCommand($command): bool|string {
        if (!$this->connection) return false;

        $stream = ssh2_exec($this->connection, $command);
        if (!$stream) return false;

        stream_set_blocking($stream, true);
        $output = stream_get_contents($stream);
        fclose($stream);
        return $output;
    }

    /**
     * Upload local file to remote server
     * @param string $localFile - Path to local file
     * @param string $remoteFile - Destination path on remote server
     * @return bool - success
     */
    public function uploadFile($localFile, $remoteFile): bool {
        if (!$this->connection || !file_exists($localFile)) return false;
        $sftp = ssh2_sftp($this->connection);
        $remoteStream = fopen("ssh2.sftp://$sftp$remoteFile", 'w');
        $localStream = fopen($localFile, 'r');

        if (!$remoteStream || !$localStream) return false;

        // Read local file and write to remote
        while ($data = fread($localStream, 1024)) {
            fwrite($remoteStream, $data);
        }

        fclose($localStream);
        fclose($remoteStream);
        return true;
    }

    /**
     * Download remote file to local system
     * @param string $remoteFile - Path to remote file
     * @param string $localFile - Destination path locally
     * @return bool - success
     */
    public function downloadFile($remoteFile, $localFile): bool {
        if (!$this->connection) return false;
        $sftp = ssh2_sftp($this->connection);
        $remoteStream = fopen("ssh2.sftp://$sftp$remoteFile", 'r');
        $localStream = fopen($localFile, 'w');

        if (!$remoteStream || !$localStream) return false;

        // Read remote file and write locally
        while ($data = fread($remoteStream, 1024)) {
            fwrite($localStream, $data);
        }

        fclose($remoteStream);
        fclose($localStream);
        return true;
    }

    /**
     * List files in a remote directory
     * @param string $directory - Path to directory
     * @return array|bool - List of files or false on failure
     */
    public function listDirectory($directory): array|bool {
        if (!$this->connection) return false;
        $sftp = ssh2_sftp($this->connection);
        $dir = "ssh2.sftp://$sftp$directory";

        if (!is_dir($dir)) return false;

        $files = $this->files->scan($dir);
        return $files;
    }

    /**
     * Create directory on remote server
     * @param string $directory - Directory path to create
     * @param int $permissions - Permissions for new directory
     * @return bool - success
     */
    public function createDirectory($directory, $permissions = 0755): bool {
        if (!$this->connection) return false;
        $sftp = ssh2_sftp($this->connection);
        $dirPath = "ssh2.sftp://$sftp$directory";

        if (is_dir($dirPath)) return true; // Already exists
        return $this->files->createFolder($dirPath, $permissions, true);
    }

    /**
     * Delete a remote file
     * @param string $filePath - Path to file
     * @return bool - success
     */
    public function deleteFile($filePath): bool {
        if (!$this->connection) return false;
        $sftp = ssh2_sftp($this->connection);
        $fullPath = "ssh2.sftp://$sftp$filePath";
        return unlink($fullPath);
    }

    /**
     * Delete a remote directory
     * @param string $directory - Directory path
     * @return bool - success
     */
    public function deleteDirectory($directory): bool {
        if (!$this->connection) return false;
        $sftp = ssh2_sftp($this->connection);
        $dirPath = "ssh2.sftp://$sftp$directory";
        return rmdir($dirPath);
    }

    /**
     * Check if a remote file exists
     * @param string $filePath - Path to file
     * @return bool - true if exists
     */
    public function fileExists($filePath): bool {
        if (!$this->connection) return false;
        $sftp = ssh2_sftp($this->connection);
        $fullPath = "ssh2.sftp://$sftp$filePath";
        return file_exists($fullPath);
    }

    /**
     * Change permissions of remote file/directory
     * @param string $path - Path to file or directory
     * @param int $permissions - Permissions (e.g., 0755)
     * @return bool - success
     */
    public function changePermissions($path, $permissions): bool {
        if (!$this->connection) return false;
        $sftp = ssh2_sftp($this->connection);
        $fullPath = "ssh2.sftp://$sftp$path";
        return chmod($fullPath, $permissions);
    }

    /**
     * Get current working directory (local PHP getcwd)
     * Note: SSH sessions do not maintain a persistent cwd
     */
    public function getWorkingDirectory(): string|false {
        return getcwd(); // Local PHP working directory
    }

    /**
     * Change directory on remote server
     * Note: This executes 'cd' command, but persistence is not guaranteed
     * @param string $directory - Directory to change to
     * @return bool - success
     */
    public function changeDirectory($directory): bool {
        if (!$this->connection) return false;
        $command = "cd " . escapeshellarg($directory);
        $result = $this->executeCommand($command);
        // Check if command executed successfully
        return $result !== false;
    }

    /**
     * Retrieve server info – e.g., output of 'uname -a'
     * @return string - server info string
     */
    public function getServerInfo(): string {
        if (!$this->connection) return '';
        return $this->executeCommand('uname -a');
    }

    /**
     * Start an interactive shell session (if needed)
     * @return resource|false - shell stream or false
     */
    public function startShell(): mixed {
        if (!$this->connection) return false;
        $shellStream = ssh2_shell($this->connection);
        return $shellStream;
    }

    /**
     * Close SSH connection
     */
    public function close(): void {
        $this->disconnect();
    }
}