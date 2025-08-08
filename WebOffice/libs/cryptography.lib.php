<?php
namespace WebOffice;
class Cryptography {
    public function __construct() {
        
    }
    
    /**
     * Encode string to base64
     * @param string $data Data to encode
     * @return string Encoded data
     */
    // Simple base32 encode/decode implementation
    private function base32_encode($data): string {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $binary = '';
        foreach (str_split($data) as $char) {
            $binary .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }
        $fiveBitGroups = str_split($binary, 5);
        $base32 = '';
        foreach ($fiveBitGroups as $group) {
            $base32 .= $alphabet[bindec(str_pad($group, 5, '0', STR_PAD_RIGHT))];
        }
        $padding = 8 - (strlen($base32) % 8);
        if ($padding < 8) {
            $base32 .= str_repeat('=', $padding);
        }
        return $base32;
    }

    private function base32_decode($data): string {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $data = rtrim($data, '=');
        $binary = '';
        foreach (str_split($data) as $char) {
            $pos = strpos($alphabet, $char);
            if ($pos === false) continue;
            $binary .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }
        $eightBitGroups = str_split($binary, 8);
        $decoded = '';
        foreach ($eightBitGroups as $group) {
            if (strlen($group) < 8) continue;
            $decoded .= chr(bindec($group));
        }
        return $decoded;
    }

    public function encode(string $data, string $method='base64'): string {
        switch (strtolower($method)) {
            case 'base64':
                return base64_encode($data);
            case 'url':
                return urlencode($data);
            case 'hex':
                return bin2hex($data);
            case 'base32':
                return $this->base32_encode($data);
            default:
                throw new \InvalidArgumentException("Unsupported encoding method: $method");
        }
    }
    /**
     * Decode base64 to string
     * @param string $data Data to decode
     * @return string Decoded string
     */
    public function decode(string $data, string $method='base64'): string {
        switch (strtolower($method)) {
            case 'base64':
                return base64_decode($data);
            case 'url':
                return urldecode($data);
            case 'hex':
                return hex2bin($data);
            case 'base32':
                return $this->base32_decode($data);
            default:
                throw new \InvalidArgumentException("Unsupported decoding method: $method");
        }
    }
    /**
     * Encrypt data using a key
     * @param string $data Data to encrypt
     * @param string $key Encryption key
     * @param string $enc Encryption method (default: 'aes-256-cbc')
     * @return string Encrypted data
     */
    public function encrypt(string $data, string $key, string $enc='aes-256-cbc'): string {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($enc));
        $encrypted = openssl_encrypt($data, $enc, $key, 0, $iv);
        if ($encrypted === false) {
            throw new \RuntimeException('Encryption failed: ' . openssl_error_string());
        }
        // Return the IV and encrypted data, base64-encoded
        return base64_encode("$iv$encrypted");
    }
    /**
     * Decrypt data using a key
     * @param string $data Data to decrypt
     * @param string $key Decryption key
     * @param string $enc Encryption method (default: 'aes-256-cbc')
     * @return string Decrypted data
     */
    public function decrypt(string $data, string $key, string $enc='aes-256-cbc'): string {
        $data = base64_decode($data);
        $ivLength = openssl_cipher_iv_length($enc);
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);
        $decrypted = openssl_decrypt($encrypted, $enc, $key, 0, $iv);
        if ($decrypted === false) throw new \RuntimeException('Decryption failed: ' . openssl_error_string());
        return $decrypted;
    }
}