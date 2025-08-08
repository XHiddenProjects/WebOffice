<?php
namespace WebOffice\Security;
use WebOffice\Cryptography;
class JWT {
    private $secretKey;
    private $alg = 'HS256';
    private Cryptography $crypto;

    public function __construct($secretKey) {
        $this->secretKey = $secretKey;
        $this->crypto = new Cryptography();
    }

    /**
     * Encode (create) a JWT token with data and optional expiration.
     * @param array $payload Data to include in token
     * @param int|null $exp Expiration time as UNIX timestamp (optional)
     * @return string JWT token
     */
    public function encode(array $payload, $exp = null): string {
        $header = [
            'typ' => 'JWT',
            'alg' => $this->alg
        ];

        // Add standard claims
        $claims = $payload;
        if ($exp !== null) {
            $claims['exp'] = time()+$exp;
        }

        $base64UrlHeader = $this->crypto->encode(json_encode($header));
        $base64UrlClaims = $this->crypto->encode(json_encode($claims));

        $unsignedToken = "$base64UrlHeader.$base64UrlClaims";

        $signature = $this->sign($unsignedToken);
        return "$unsignedToken.$signature";
    }

    /**
     * Decode (verify) a JWT token.
     * @param string $token
     * @return array|false Returns payload array if valid, false if invalid
     */
    public function decode($token): mixed {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        [$base64UrlHeader, $base64UrlClaims, $signature] = $parts;

        $unsignedToken = "$base64UrlHeader.$base64UrlClaims";

        if (!$this->verify($unsignedToken, $signature)) {
            return false;
        }

        $jsonClaims = json_decode($this->crypto->decode($base64UrlClaims), true);
        if ($jsonClaims === null) {
            return false;
        }

        // Check expiration if present
        if (isset($jsonClaims['exp']) && time() > $jsonClaims['exp']) {
            return false; // Token expired
        }

        return $jsonClaims;
    }

    /**
     * Sign the data with secret key using HMAC SHA256
     */
    private function sign($data): string {
        $signature = hash_hmac('SHA256', $data, $this->secretKey, true);
        return $this->crypto->encode($signature);
    }
    /**
     * Verify the signature
     */
    private function verify($data, $signature): bool {
        $expectedSig = $this->sign($data);
        return hash_equals($expectedSig, $signature);
    }
}