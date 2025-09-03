<?php
namespace WebOffice;
use resource;
/**
 * Class TCP
 * Provides methods to manage TCP socket connections, data transmission,
 * and error handling within the WebOffice namespace.
 */
class TCP {
    /**
     * @var resource|null The socket resource
     */
    private $socket;

    /**
     * @var string The server hostname or IP address
     */
    private $host;

    /**
     * @var int The server port number
     */
    private $port;

    /**
     * @var bool Connection status flag
     */
    private $connected = false;

    /**
     * Constructor
     * Initializes connection parameters with optional host and port.
     *
     * @param string $host Server hostname or IP address (default: '127.0.0.1')
     * @param int $port Server port number (default: 80)
     */
    public function __construct($host = '127.0.0.1', $port = 80) {
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * Establish a TCP connection to the specified host and port.
     *
     * @return bool True on success, false on failure.
     */
    public function connect(): bool {
        // Attempt to create a stream socket connection
        $this->socket = @stream_socket_client("tcp://{$this->host}:{$this->port}", $errno, $errstr, 5);
        if ($this->socket === false) {
            // Log connection error
            $this->logError("Connection failed: $errstr ($errno)");
            return false;
        }
        // Mark connection as established
        $this->connected = true;
        return true;
    }

    /**
     * Close the active TCP connection.
     */
    public function disconnect(): void {
        if ($this->socket) {
            fclose($this->socket);
            $this->socket = null;
            $this->connected = false;
        }
    }

    /**
     * Send data over the established TCP connection.
     *
     * @param string $data Data to send
     * @return bool True on success, false on failure.
     */
    public function send(string $data): bool {
        if (!$this->connected) {
            $this->logError('Not connected to server.');
            return false;
        }
        // Write data to socket
        $bytesSent = fwrite($this->socket, $data);
        if ($bytesSent === false) {
            // Log error if sending fails
            $this->logError('Failed to send data.');
            return false;
        }
        return true;
    }

    /**
     * Receive data from the TCP connection.
     *
     * @param int $length Number of bytes to read (default: 1024)
     * @return string|false Received data or false on failure.
     */
    public function receive($length = 1024): bool|string {
        if (!$this->connected) {
            $this->logError('Not connected to server.');
            return false;
        }
        // Read data from socket
        $data = fread($this->socket, $length);
        if ($data === false) {
            $this->logError('Failed to read data.');
            return false;
        }
        return $data;
    }

    /**
     * Send data and wait for a response.
     *
     * @param string $data Data to send
     * @param int $responseLength Number of bytes to read for response (default: 1024)
     * @return string|false Response data or false on failure.
     */
    public function sendAndReceive(string $data, int $responseLength = 1024): bool|string {
        if (!$this->send($data)) {
            return false;
        }
        return $this->receive($responseLength);
    }

    /**
     * Check if the TCP connection is currently active.
     *
     * @return bool True if connected, false otherwise.
     */
    public function isConnected(): bool {
        return $this->connected;
    }

    /**
     * Retrieve the raw socket resource.
     *
     * @return resource|null The socket resource or null if not connected.
     */
    public function getSocket(): resource|null {
        return $this->socket;
    }

    /**
     * Update connection parameters (host and port).
     *
     * @param string $host New host IP or hostname
     * @param int $port New port number
     */
    public function setConnectionParameters(string $host, int $port): void {
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * Log error messages.
     * Customize this method to integrate with your logging system.
     *
     * @param string $message Error message to log
     */
    private function logError($message): void {
        // For now, just output to error log
        error_log("[WebOffice\\TCP] $message");
    }

    /**
     * Get current connection information.
     *
     * @return array Associative array with host, port, and connection status.
     */
    public function getConnectionInfo(): array {
        return [
            'host' => $this->host,
            'port' => $this->port,
            'connected' => $this->connected
        ];
    }

    /**
     * Check if the socket is still alive.
     *
     * @return bool True if socket is active, false if timed out or closed.
     */
    public function isAlive(): bool {
        if (!$this->socket) {
            return false;
        }
        // Use stream_get_meta_data to check socket status
        $meta = stream_get_meta_data($this->socket);
        return !$meta['timed_out'];
    }
}