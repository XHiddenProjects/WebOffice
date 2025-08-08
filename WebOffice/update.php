<?php
include_once 'init.php';
const ZIP_URL = 'https://github.com/XHiddenProjects/WebOffice/raw/refs/heads/master/WebOffice.zip';
define('ZIP_FILE', TEMP_PATH . DS . 'WebOffice.zip');
$response = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $step = $_POST['step'] ?? '';
    switch ($step) {
        case 'checking':
            // Check if there's enough space (simple check, e.g., 100MB free)
            $freeSpace = disk_free_space(TEMP_PATH);
            // Assuming ZIP size is around 10MB, adjust as needed
            $requiredSpace = 100 * 1024 * 1024; // 100MB
            if ($freeSpace >= $requiredSpace) {
                $response['status'] = 'success';
                $response['message'] = 'Enough space available.';
            } else {
                $response['status'] = 'fail';
                $response['message'] = 'Not enough disk space.';
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
                $response['status'] = 'success';
                $response['message'] = 'Zip downloaded.';
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
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $fileName = $zip->getNameIndex($i);
                    // Skip extracting .htaccess files
                    if (strpos($fileName, '.htaccess') !== false||
                        strpos($fileName, '.venv') !== false) continue;
                    
                    // Extract individual file
                    $fileInfo = pathinfo($fileName);
                    $destPath = $extractPath . DIRECTORY_SEPARATOR . $fileName;
                    // Create directory if needed
                    if (!$zip->extractTo($extractPath, $fileName)) {
                        // Handle error
                        error_log("Failed to extract $fileName");
                    }
                }
                $zip->close();
                $response['status'] = 'success';
                $response['message'] = 'Extraction completed.';
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