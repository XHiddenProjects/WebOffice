<?php
header("Content-Type: application/json");

// Execute speedtest-cli with environment cleared
$speedtest = shell_exec('env -u LD_LIBRARY_PATH speedtest --simple');

// Parse output lines
$lines = explode("\n", trim($speedtest));
$result = [
    'download'=>[
        'value'=>0,
        'label'=>''
    ],
    'upload'=>[
        'value'=>0,
        'label'=>''
    ]
];

foreach ($lines as $line) {
    if (strpos($line, 'Download:') !== false) {
        // Extract float value from line, e.g., "Download: 123.45 Mbit/s"
        if (preg_match('/Download:\s*([\d.]+)/', $line, $matches)) {
            $result['download'] = [
                'value' => floatval($matches[1]),
                'label' => trim(str_replace('Download:', '', $line))
            ];
        }
    } elseif (strpos($line, 'Upload:') !== false) {
        // Extract float value from line, e.g., "Upload: 67.89 Mbit/s"
        if (preg_match('/Upload:\s*([\d.]+)/', $line, $matches)) {
            $result['upload'] = [
                'value' => floatval($matches[1]),
                'label' => trim(str_replace('Upload:', '', $line))
            ];
        }
    }
}

// Output JSON
echo json_encode($result,JSON_UNESCAPED_SLASHES);