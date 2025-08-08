<?php
namespace WebOffice\Security;
use WebOffice\Config, WebOffice\Database, WebOffice\Users;
class Rate{
    private int $limit, $window;
    private Config $config;
    private Users $users;
    protected Database $db;
    public function __construct() {
        $this->config = new Config();
        
        $this->limit = (int)$this->config->read('security','request_max');
        $this->window = (int)$this->config->read('security','window_seconds');
        $this->users = new Users();
        $this->db = new Database(
            $this->config->read('mysql','host'),
            $this->config->read('mysql','user'),
            $this->config->read('mysql','psw'),
            $this->config->read('mysql','db')
        );
    }
    /**
     * Sets the limit of requests
     * @param int $limit Limit
     * @return Rate
     */
    public function setLimit(int $limit): static{
        $this->limit = $limit;
        return $this;
    }
    /**
     * Sets the timeout
     * @param int $window Windows seconds
     * @return Rate
     */
    public function setWindow(int $window): static{
        $this->window = $window;
        return $this;
    }
    /**
     * Adds a hit to the users IP address to prevent over requests
     * @return bool TRUE if hit was successful, else FALSE
     */
    public function hit(): bool {
        $ip = $this->users->getIP();
        $path = $_SERVER['REQUEST_URI'];
        // Check if record exists for this IP and path
        $record = $this->db->fetch(
            "SELECT * FROM rate WHERE ip_address = :ip AND path = :path", 
            ['ip' => $ip, 'path' => $path]
        );

        $now = time();

        if ($record) {
            // Calculate the age of the record
            $timestamp = strtotime($record['timestamp']);
            $age = $now - $timestamp;

            if ($age > $this->window) {
                // Reset requests and timestamp for this IP+path
                $this->db->delete('rate', 'id = :id', ['id' => $record['id']]);
                $this->db->insert('rate', [
                    'ip_address' => $ip,
                    'timestamp' => date('Y-m-d H:i:s'),
                    'requests' => 1,
                    'path' => $path
                ]);
                return true; // Allowed
            } else {
                // Within window
                if ($record['requests'] >= $this->limit) {
                    // Exceeded limit
                    return false; // Block request
                } else {
                    // Increment requests
                    $this->db->delete('rate', 'id = :id', ['id' => $record['id']]);
                    $newRequests = $record['requests'] + 1;
                    $this->db->insert('rate', [
                        'ip_address' => $ip,
                        'timestamp' => date('Y-m-d H:i:s'),
                        'requests' => $newRequests,
                        'path' => $path
                    ]);
                    return true; // Allowed
                }
            }
        } else {
            // Create a new record for this IP+path
            $this->db->insert('rate', [
                'ip_address' => $ip,
                'timestamp' => date('Y-m-d H:i:s'),
                'requests' => 1,
                'path' => $path
            ]);
            return true; // Allowed
        }
    }
}