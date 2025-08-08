<?php
session_start(); // To store challenge and user info between requests

header('Content-Type: application/json');

$action = $_GET['action'] ?? ''; // 'register' or 'login'

// Dummy user data, replace with your database logic
$users = [
    'user1' => [
        'id' => base64_encode(random_bytes(16)),
        'name' => 'User One',
        'credentials' => [] // Store credential public keys here after registration
    ]
];

if ($action === 'register') {
    // Generate registration options
    $challenge = bin2hex(random_bytes(32));
    $_SESSION['challenge'] = $challenge;

    $publicKeyCredentialCreationOptions = [
        'rp' => [
            'name' => 'Example RP'
        ],
        'user' => [
            'id' => base64_encode(random_bytes(16)),
            'name' => 'user1',
            'displayName' => 'User One'
        ],
        'pubKeyCredParams' => [
            ['type' => 'public-key', 'alg' => -7], // ES256
            ['type' => 'public-key', 'alg' => -257] // RS256
        ],
        'timeout' => 60000,
        'attestation' => 'direct',
        'challenge' => base64_encode($challenge),
        ' authenticatorSelection' => [
            'userVerification' => 'preferred'
        ]
    ];

    echo json_encode($publicKeyCredentialCreationOptions);
} elseif ($action === 'login') {
    // Generate assertion options
    $challenge = bin2hex(random_bytes(32));
    $_SESSION['challenge'] = $challenge;

    // Normally, you'd fetch the user's registered credential IDs from your database
    $userCredentials = [
        // Example credential ID from registration, base64
        'credentialId' => base64_encode('sample-credential-id')
    ];

    $publicKeyCredentialRequestOptions = [
        'challenge' => base64_encode($challenge),
        'timeout' => 60000,
        'rpId' => 'localhost', // or your domain
        'allowCredentials' => [
            [
                'type' => 'public-key',
                'id' => $userCredentials['credentialId']
            ]
        ],
        'userVerification' => 'preferred'
    ];

    echo json_encode($publicKeyCredentialRequestOptions);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle verification of credential/assertion
    $data = json_decode(file_get_contents('php://input'), true);

    // Determine if registration or login based on some data
    // For simplicity, assume login if credential contains 'authenticatorData'
    if (isset($data['authenticatorData'])) {
        // Verify assertion
        $expectedChallenge = $_SESSION['challenge'];
        // Verify challenge, signature, etc.
        // (This is a simplified example; actual verification is more complex)

        $clientDataJSON = base64_decode($data['clientDataJSON']);
        $authenticatorData = base64_decode($data['authenticatorData']);
        $signature = base64_decode($data['signature']);
        $credentialId = $data['id'];

        // Verify challenge
        // Verify signature with stored public key
        // For demonstration, assume success
        echo json_encode(['status' => 'ok', 'message' => 'Assertion verified']);
    } else {
        // Registration verification
        // Save credential public key, attestation data, etc.
        // For demonstration, assume success
        echo json_encode(['status' => 'ok', 'message' => 'Registration verified']);
    }
} else {
    echo json_encode(['error' => 'Invalid request']);
}
