<?php
include_once 'init.php';
const ZIP_URL = 'https://github.com/XHiddenProjects/WebOffice/raw/refs/heads/master/WebOffice.zip';

use WebOffice\Files, WebOffice\Config, WebOffice\Utils;
$c = new Config();
$u = new Utils();
define('ZIP_FILE', TEMP_PATH . DS . 'WebOffice.zip');
// Function to get remote file size
function getRemoteFileSize($url):int|false {
    $contextOptions = [
        "ssl" => [
            "verify_peer" => false,
            "verify_peer_name" => false,
        ],
    ];

    $headers = get_headers($url, 1,stream_context_create($contextOptions));
    if ($headers && isset($headers['Content-Length'])) {
        // 'Content-Length' might be an array if multiple headers exist
        if (is_array($headers['Content-Length'])) {
            return (int)end($headers['Content-Length']);
        } else {
            return (int)$headers['Content-Length'];
        }
    }
    return false;
}
/**
 * Returns the hashed of the zip file
 * @param string $filePath File path
 * @return string|bool
 */
function getHash(string $filePath): string|bool{
    global $u;
    // Detect the OS
    $os = PHP_OS;

    // Determine the command based on OS
    if (strtoupper(substr($os, 0, 3)) === 'WIN') {
        // Windows
        $command = "CertUtil -hashfile " . escapeshellarg($filePath) . " SHA256";
        $output = $u->executeCommand($command);
        // Parse the output to extract the hash
        if (preg_match('/^[0-9A-Fa-f]{64}/', $output, $matches)) {
            $hash = $matches[0];
            return $hash;
        } else return false;
    } else {
        // macOS/Linux
        $command = "sha256sum " . escapeshellarg($filePath);
        $output = $u->executeCommand($command);
        // The output is like: "<hash>  filename"
        if (preg_match('/^([a-fA-F0-9]{64})/', $output, $matches)) {
            $hash = $matches[1];
            return $hash;
        } else return false;
    }
}

$response = [];
$f = new Files();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $step = $_POST['step'] ?? '';
    switch ($step) {
        case 'checking':
             // Check if there's enough space (simple check, e.g., 100MB free)
            $freeSpace = disk_free_space(TEMP_PATH);
            // Assuming ZIP size is around 10MB, adjust as needed
            $requiredSpace = (int)getRemoteFileSize(ZIP_URL);
            if (($freeSpace >= $requiredSpace)&&$requiredSpace) {
                $response['status'] = 'success';
                $response['message'] = 'Enough space available.';
            } else {
                $response['status'] = 'fail';
                $response['message'] = "Not enough disk space. Current: {$u->bytes2readable($freeSpace)}, Required: {$u->bytes2readable($requiredSpace)}";
            }
        break;
        case 'download':
            // Download the zip file
            $ch = curl_init(ZIP_URL);
            $fp = fopen(ZIP_FILE, 'w');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 300);
            $success = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            fclose($fp);
            curl_close($ch);

            if ($success && $httpCode == 200 && file_exists(ZIP_FILE)) {
                $fileHash = $f->hash(ZIP_FILE, SHA256);
                if ($f->verify_hash($fileHash,getHash(ZIP_FILE))) {
                    $response['status'] = 'success';
                    $response['message'] = 'Zip downloaded and verified.';
                } else {
                    // Hash mismatch, delete the file
                    unlink(ZIP_FILE);
                    $response['status'] = 'fail';
                    $response['message'] = 'Hash mismatch. Download may be corrupted.';
                }
            } else {
                $response['status'] = 'fail';
                $response['message'] = 'Download failed.';
            }
        break;
        case 'extract':
            // Extract zip to parent directory of TEMP_PATH
            $zip = new ZipArchive();
            if ($zip->open(ZIP_FILE) === TRUE) {
                $extractPath = dirname(TEMP_PATH);
                $extractionFailed = false; // Flag to monitor failure
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $fileName = $zip->getNameIndex($i);
                    if (strpos($fileName, '.venv') !== false) {
                        continue;
                    }
                    if (!@$zip->extractTo($extractPath, $fileName)) {
                        $response['status'] = 'fail';
                        $response['message'] = "No Permission for {$extractPath}/{$fileName}";
                        $extractionFailed = true;
                        break;
                    }
                }
                $zip->close();

                if (!$extractionFailed && !isset($response['status'])) {
                    // If no failures occurred, set success status
                    $response['status'] = 'success';
                    $response['message'] = 'Extraction completed.';
                }
            } else {
                $response['status'] = 'fail';
                $response['message'] = 'Failed to open zip.';
            }
        break;
        case 'finishing':
            // Delete only the zip file
            if (file_exists(ZIP_FILE)) {
                unlink(ZIP_FILE);
                $response['status'] = 'success';
                $response['message'] = 'Cleaned up zip file.';
            } else {
                $response['status'] = 'fail';
                $response['message'] = 'Zip file not found.';
            }
            break;

        default:
            $response['status'] = 'fail';
            $response['message'] = 'Invalid step.';
        break;
    }
    echo json_encode($response);
    exit;
}