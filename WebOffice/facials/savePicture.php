<?php
$data = json_decode(file_get_contents('php://input'), true);
// Example data URL (replace this with your actual data URL)
$dataUrl = $data['image']; // truncated for example

// Specify the folder where you want to save the image
$folderPath = dirname(__DIR__).'/facials/';

// Generate a unique filename
$filename = 'image_' . time() . '.png';
$filePath = "$folderPath$filename";

// Extract base64 data from the data URL
if (preg_match('/^data:image\/\w+;base64,/', $dataUrl)) {
    $base64Data = preg_replace('/^data:image\/\w+;base64,/', '', $dataUrl);
    $base64Data = str_replace(' ', '+', $base64Data);
    $imageData = base64_decode($base64Data);
    
    if ($imageData === false) {
        die('Base64 decode failed.');
    }
    
    // Save the decoded image data to a file
    file_put_contents($filePath, $imageData);
} else {
    die('Invalid data URL.');
}
