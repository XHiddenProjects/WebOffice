<?php
namespace WebOffice;
use WebOffice\Config, WebOffice\Cryptography;
class MFA{
    private string $label, $issuer, $algo, $secret, $user;
    private int $digits, $period;
    private Cryptography $crypto;
    /**
     * Creates a Multi-factor authenticator 
     * @param string $secret Secret 
     */
    public function __construct(string $user) {
        $config = new Config();
        $this->crypto = new Cryptography();
        $this->user = $user;
        $this->label = $config->read('2fa','label');
        $this->issuer = $config->read('2fa','issuer');
        $this->algo = $config->read('2fa','algorithm');
        $this->digits = (int)$config->read('2fa','digits');
        $this->period = (int)$config->read('2fa','period');
        $this->secret = $this->crypto->encode($user,'base32');
    }
    /**
     * Returns the encoded secret
     * @return string Secret
     */
    public function getSecret(): string{
        return $this->secret;
    }
    /**
     * Returs the TOTP(Time-Based One-Time Password) URL
     * @return string URL
     */
    public function getTOTP(): string{
        return "otpauth://totp/$this->issuer:$this->user?secret=$this->secret&issuer=$this->issuer&algorithm=$this->algo&digits=$this->digits&period=$this->period";
    }
    /**
     * Verifies the code
     * @param int $code Enter the **x** digits of code
     * @return bool TRUE if success, else FALSE
     */
    public function verify(int $code): bool {
        $secret = $this->crypto->decode($this->secret, 'base32'); // Decode the secret
        $time = floor(time() / $this->period);
        $tolerance = 1; // Allow 1 step before and after for time drift

        for ($i = -$tolerance; $i <= $tolerance; $i++) {
            $currentTimeStep = $time + $i;
            $generatedCode = $this->generateTOTP($secret, $currentTimeStep);
            if ($generatedCode === $code) {
                return true; // Valid code
            }
        }
        return false; // No match found
    }

    /**
     * Generate TOTP code based on secret and time step
     */
    private function generateTOTP(string $secret, int $timeStep): int {
        // Convert timeStep to binary data
        $timeBytes = pack('N*', 0) . pack('N*', $timeStep);
        // HMAC hash
        $hash = hash_hmac($this->algo, $timeBytes, $secret, true);
        // Dynamic truncation
        $offset = ord($hash[19]) & 0xf;
        $code = (
            ((ord($hash[$offset]) & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) << 8) |
            (ord($hash[$offset + 3]) & 0xff)
        ) % pow(10, $this->digits);
        return (int)$code;
    }

}