<?php
namespace WebOffice;
use WebOffice\Security, WebOffice\Server, WebOffice\Database, WebOffice\Config, WebOffice\Storage;
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
    /**
     * Returns the username
     * @return string Current or searched username
     */
    public function getUsername(): string{
        return $this->user==='' ? 
        $this->storage->session('weboffice_auth')??$this->storage->cookie('weboffice_auth',action:'Load') : 
        $this->database->fetch("SELECT * FROM users",['user'=>$this->user],'username = :user')['username']??'';
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
    public function create(string $password, string $email, array $permissions=[], string $status='inactive', string $bio='', string $pfp='', string $timezone=''): bool{
        if($this->user==='') throw new \ErrorException('You must have user in the root parameter');
        if($this->getUsername()) return false;
        $device = new Device();
        $security = new Security();
        return $this->database->insert('users',[
            'username'=>$this->user,
            'password'=>$security->hashPsw($password,PASSWORD_BCRYPT,[
                'cost'=>12
            ]),
            'email'=>$security->filter($email,Security::FILTER_EMAIL),
            'permissions'=>json_encode($permissions,JSON_UNESCAPED_SLASHES),
            'ip_address'=>$this->getIP(),
            'user_agent'=>$device->getUserAgent(),
            'status'=>$status,
            'bio'=>$bio,
            'profile_picture'=>$pfp,
            'language'=>$this->getLanguage(),
            'timezone'=>$timezone
        ]);
    }
}