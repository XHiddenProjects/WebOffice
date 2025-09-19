<?php
namespace WebOffice;
use WebOffice\Security, WebOffice\Server, WebOffice\Database, WebOffice\Config, WebOffice\Storage;
use PDO;
class Users{
    private string $user;
    private Security $security;
    private Server $server;
    private Database $database;
    private Storage $storage;
    /**
     * Select the user to get information, else uses the current user
     * @param string $username Username (optional)
     */
    public function __construct(?string $username='') {
        $this->user = $username;
        $this->security = new Security();
        $this->server = new Server();
        $config = new Config();
        $this->database = new Database($config->read('mysql','host'),
    $config->read('mysql','user'),
    $config->read('mysql','psw'),
    $config->read('mysql','db'));
        $this->storage = new Storage();
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
                    if (strncasecmp(PHP_OS, 'WIN', 3) === 0) {
                        // Windows
                        $remoteIp = shell_exec('ipconfig');
                        if (preg_match('/IPv4 Address[.\s]*:\s*([\d.]+)/i', $remoteIp, $matches)) {
                            $remoteIp = $matches[1];
                        }
                    } elseif (strncasecmp(PHP_OS, 'DARWIN', 6) === 0 || strncasecmp(PHP_OS, 'Linux', 5) === 0) {
                        // macOS or Linux
                        $remoteIp = shell_exec("ifconfig");
                        if (!$remoteIp) {
                            // fallback for Linux with ip command
                            $remoteIp = shell_exec('ip addr');
                        }
                        ;
                        if (preg_match_all('/inet\s+([\d.]+)\s/', $remoteIp, $matches)) {
                            $remoteIp = preg_replace('/inet\s+/','',$matches[0][count($matches[0])-1]);
                        }
                    }
                    $remoteIp = trim($remoteIp);
                    return $this->security->filter($remoteIp, SECURITY::FILTER_IPV4);
                }
                
                return $this->security->filter($remoteIp, SECURITY::FILTER_IPV4);
            } else {
                return 'UNKNOWN';
            }
        }
    }
    /**
     * Returns the username
     * @return string|null Current or searched username
     */
    public function getUsername(): ?string{
        return $this->user==='' ? 
        base64_decode($this->storage->session(name: 'weboffice_auth',action: 'get')??$this->storage->cookie(name: 'weboffice_auth',action:'load')) : 
        $this->database->fetch("SELECT * FROM users WHERE username = :user",['user'=>$this->user])['username']??'';
    }
    /**
     * Returns the users language
     * @return string
     */
    public function getLanguage():string{
        $langs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        $primary_lang = $langs[0];
        $lang_code = substr($primary_lang, 0, 2);
        return $lang_code??'';
    }
    /**
     * Checks if user is admin
     * @return bool TRUE if admin, else FALSE
     */
    public function isAdmin(): bool{
        if($this->user==='')
            return $this->database->fetch("SELECT * FROM users WHERE username='{$this->getUsername()}'")['permissions']==='admin'??false;
        else
            return $this->database->fetch("SELECT * FROM users WHERE username='{$this->user}'")['permissions']==='admin'??false;
    }
    /**
     * Checks if user is member
     * @return bool TRUE if Member, else FALSE
     */
    public function isMember(): bool{
        if($this->user===''){
            return $this->database->fetch("SELECT * FROM users WHERE username='{$this->getUsername()}'")['permissions']==='member'??false;
        }else
            return $this->database->fetch("SELECT * FROM users WHERE username='{$this->user}'")['permissions']==='member'??false;
    }
    /**
     * Checks if user is moderator
     * @return bool TRUE if moderator, else FALSE
     */
    public function isModerator(): bool{
        if($this->user==='')
            return $this->database->fetch("SELECT * FROM users WHERE username='{$this->getUsername()}'")['permissions']==='moderator'??false;
        else
            return $this->database->fetch("SELECT * FROM users WHERE username='{$this->user}'")['permissions']==='moderator'??false;
    }
    /**
     * Lists all users
     * @return array List of users
     */
    public function list():array{
        return $this->database->fetchAll("SELECT username FROM users",[],PDO::FETCH_ASSOC);
    }
    /**
     * Updates the last activity timestamp for a user
     * @param string $username Username
     * @param int $timestamp Unix timestamp
     * @return bool TRUE if updated, else FALSE
     */
    public function getLastActivity(string $username): ?int{
        $result = $this->database->fetch("SELECT last_activity FROM users WHERE username = :username", ['username' => $username], PDO::FETCH_ASSOC);
        return strtotime($result['last_activity']) ?? null;
    }

}