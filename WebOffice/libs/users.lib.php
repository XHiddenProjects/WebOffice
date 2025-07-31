<?php
namespace WebOffice;
use WebOffice\Security, WebOffice\Server;
class Users{
    private string $user;
    private Security $security;
    private Server $server;
    /**
     * Select the user to get information, else uses the current user
     * @param string $username Username (optional)
     */
    public function __construct(?string $username='') {
        $this->user = $username;
        $this->security = new Security();
        $this->server = new Server();
    }
    /**
     * Returns the users IP address
     * @return string IP Address
     */
    public function getIP(): string {
        if ($this->user) {
            return '';
        } else {
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                // IP from shared internet
                return $this->security->filter($_SERVER['HTTP_CLIENT_IP'], SECURITY::FILTER_IPV4);
            } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                // IP passed through proxies
                $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                return $this->security->filter(trim($ipList[0]), SECURITY::FILTER_IPV4); // First IP in the list
            } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
                // Remote IP address
                $remoteIp = $_SERVER['REMOTE_ADDR'];

                // Check if the IP is localhost
                if ($remoteIp === '127.0.0.1' || $remoteIp === '::1') {
                    // Return server's local IP address
                    $localIp = $this->server->hostname(getHostName()); // Gets server's IP
                    return $this->security->filter($localIp, SECURITY::FILTER_IPV4);
                }
                
                return $this->security->filter($remoteIp, SECURITY::FILTER_IPV4);
            } else {
                return 'UNKNOWN';
            }
        }
    }
}