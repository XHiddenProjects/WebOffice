<?php
namespace WebOffice;

use WebOffice\Storage;
use WebOffice\Config;

class CSRF{
    private Storage $s;
    private Config $c;

    public function __construct() {
        $this->s = new Storage();
        $this->c = new Config();
        $this->generate();
    }

    /**
     * Generates a random token
     * @return void
     */
    public function generate(): void{
        $n = $this->c->read('security','csrf_name');
        if(empty($this->s->session(name: $n,action: 'get'))){
            $timestamp = time();
            $token = "{$this->salt()}|$timestamp";
            $this->s->session($n, [
                'token'=>bin2hex($token),
                'expire'=>$timestamp+(int)$this->c->read('security','csrf_token_expiry')
            ]);
        }
    }

    /**
     * Verifies token
     * @param string $token Token to verify
     * @return bool
     */
    public function verify(string $token): bool{
        $n = $this->c->read('security','csrf_name');
        $v = $this->s->session(name: $n, action: 'get');

        if(empty($v)){
            // No token exists, generate a new one
            $this->generate();
            return false;
        }

        if(!hash_equals($v['token'], $token)){
            // Token mismatch
            return false;
        }

        if(time() > (int)$v['expire']){
            // Token expired, generate a new one
            $this->s->session($n, null, 'delete');
            $this->generate();
            return false;
        }

        // Token is valid, delete and regenerate for next use
        $this->s->session($n, null, 'delete');
        $this->generate();
        return true;
    }

    /**
     * Generates salt
     * @return string Salted string
     */
    private function salt(): string{
        return bin2hex(random_bytes((int)$this->c->read('security','csrf_token_length')));
    }

    /**
     * Returns the current token
     * @return string Generated token
     */
    public function getToken(): string{
        $n = $this->c->read('security','csrf_name');
        $sessionData = $this->s->session($n, action: 'get');
        return $sessionData['token'] ?? '';
    }
}