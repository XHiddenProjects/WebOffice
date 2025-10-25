<?php

use WebOffice\Database;

# cryptography
const MD2 = 'md2';
const MD4 = 'md4';
const SHA1 = 'sha1';
const SHA256 = 'sha256';
const SHA384 = 'sha384';
const SHA512 = 'sha512';
const RIPEMD128 = 'ripemd128';
const RIPEMD160 = 'ripemd160';
const RIPEMD256 = 'ripemd256';
const RIPEMD320 = 'ripemd320';
const WHIRLPOOL = 'whirlpool';
const TIGER128_3 = 'tiger128,3';
const TIGER160_3 = 'tiger160,3';
const TIGER192_3 = 'tiger192,3';
const TIGER128_4 = 'tiger128,4';
const TIGER160_4 = 'tiger160,4';
const TIGER192_4 = 'tiger192,4';
const SNEFRU = 'snefru';
const GOST = 'gost';
const ADLER32 = 'adler32';
const CRC32 = 'crc32';
const CRC32B = 'crc32B';
const HAVAL128_3 = 'haval128,3';
const HAVAL160_3 = 'haval160,3';
const HAVAL192_3 = 'haval192,3';
const HAVAL224_3 = 'haval224,3';
const HAVAL256_3 = 'haval256,3';
const HAVAL128_4 = 'haval128,4';
const HAVAL160_4 = 'haval160,4';
const HAVAL192_4 = 'haval192,4';
const HAVAL224_4 = 'haval224,4';
const HAVAL256_4 = 'haval256,4';
const HAVAL128_5 = 'haval128,5';
const HAVAL160_5 = 'haval160,5';
const HAVAL192_5 = 'haval192,5';
const HAVAL224_5 = 'haval224,5';
const HAVAL256_5 = 'haval256,5';

include_once 'vendor/autoloader.php';
ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL);


# Definitions
define('BASE',dirname(__FILE__));
define('URL', getBaseUrl());
const DS = DIRECTORY_SEPARATOR;
define('CONFIG_PATH',BASE.DS.'configuration');
define('TEMP_PATH',BASE.DS.'temp');

define('DOCUMENTS_PATH',BASE.DS.'documents');
define('POWERPOINT_PATH',BASE.DS.'powerpoints');
define('SPREADSHEETS_PATH',BASE.DS.'spreadsheets');
define('OFFICE_PATH',BASE.DS.'office');

define('POLICIES_PATH',BASE.DS.'policies');

define('BACKUP_PATH',BASE.DS.'backups');
define('OS',strtoupper(PHP_OS));
if(!defined('LANGUAGE_PATH')) define('LANGUAGE_PATH',BASE.DS.'locales');
define('ADDONS_PATH',BASE.DS.'addons');
define('ADDONS_URL',URL.DS.'addons');
define('THEMES_PATH',BASE.DS.'themes');
define('THEMES_URL',URL.DS.'themes');
define('ASSETS_PATH',BASE.DS.'assets');
define('ASSETS_URL',URL.DS.'assets');
define('UPLOAD_PATH',BASE.DS.'uploads');
define('UPLOAD_URL',URL.DS.'uploads');


define('VERSION', file_exists(BASE . DS . 'VERSION') ? BASE . DS . 'VERSION' : '1.0.0');
define('LOG',BASE.DS.'logs');
define('DATA_PATH',BASE.DS.'data');

define('DOCUMENTATIONS_PATH',BASE.DS.'documentations');


use WebOffice\Security, WebOffice\Config, WebOffice\Files;
$config = new Config();
define('LANGUAGE',array_map(fn($e): string=>strtolower($e),explode(',',$config->read('settings','lang'))) ?? ['en','us']);


date_default_timezone_set($config->read('settings','timezone') ?? 'UTC');

$sec = new Security();
$sec->setSecurityHeaders();
$sec->enforceHTTPS();
$sec->sessionStart();

$_SESSION['URL_TOP_LAYER'] = URL;





if($sec->checkVersion()) die('Your WebOffice version is outdated. Please update to the latest version.');

function getBaseUrl(): string {
    // Check if HTTPS is used
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || 
                $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

    // Get the server name (domain)
    $host = $_SERVER['HTTP_HOST'];

    // Get the script path
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);

    // Combine to get the base URL
    $baseUrl = "$protocol$host";

    // Append the directory if not root
    if ($scriptDir != '/' && $scriptDir != '\\') {
        $baseUrl .= $scriptDir;
    }

    // Ensure trailing slash
    $baseUrl = rtrim($baseUrl, '/');

    return $baseUrl;
}


$f = new Files();

if(!$f->exists(TEMP_PATH)) $f->createFolder('temp');
if(!$f->exists(BACKUP_PATH)) $f->createFolder('backups');
if(!$f->exists(LOG)) $f->createFolder('logs');
if(!$f->exists(DATA_PATH)) $f->createFolder('data');
if(!$f->exists(UPLOAD_PATH)) $f->createFolder('uploads',0777);
# Change permissions for files/folder
@chmod(dirname(__FILE__).'/files',0777);
# Temp
if (!isset($_SESSION['last_cleanup'])) $_SESSION['last_cleanup'] = time();

function cleanTemp(): void {
    global $f;
    $files = $f->scan(TEMP_PATH);
    foreach ($files as $file) {
        $filePath = TEMP_PATH . DS . $file;
        if (is_dir($filePath)) $f->deleteFolder($filePath);
        else $f->deleteFile($filePath);
    }
}

// Register a tick function to check every time the script executes
register_tick_function(function(): void {
    global $config;
    if(isset($_SESSION['last_cleanup'])){
        $currentTime = time();
        if (($currentTime - $_SESSION['last_cleanup']) >= (int)$config->read('settings','temp')) {
            cleanTemp();
            $_SESSION['last_cleanup'] = $currentTime;
        }
    }
});

declare(ticks=1);

# Database
$db = new Database($config->read('mysql','host'),
$config->read('mysql','user'),
$config->read('mysql','psw'),
$config->read('mysql','db'));

$db->createTable('users', [
    'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
    'username' => 'VARCHAR(255) NOT NULL UNIQUE',
    'first_name'=>'VARCHAR(50) NOT NULL',
    'middle_name'=>'VARCHAR(50) DEFAULT NULL',
    'last_name'=>'VARCHAR(50) NOT NULL',
    'password' => 'TEXT NOT NULL',
    'email' => 'VARCHAR(255) NOT NULL UNIQUE',
    'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
    'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
    'last_login' => 'TIMESTAMP NULL',
    'permissions' => 'TEXT DEFAULT NULL',
    'ip_address' => 'VARCHAR(45) DEFAULT NULL',
    'user_agent' => 'TEXT DEFAULT NULL',
    'status' => 'ENUM(\'active\', \'inactive\', \'banned\') DEFAULT \'active\'',
    'last_activity' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
    'profile_picture' => 'VARCHAR(255) DEFAULT NULL',
    'bio' => 'TEXT DEFAULT NULL',
    'preferences' => 'TEXT DEFAULT NULL',
    'language' => 'VARCHAR(10) DEFAULT \'en\'',
    'timezone' => 'VARCHAR(50) DEFAULT \'UTC\'',
    'last_password_change' => 'TIMESTAMP NULL',
    'account_locked' => 'BOOLEAN DEFAULT FALSE',
    'lock_expiration' => 'TIMESTAMP NULL',
]);
$db->createTable('mfa',[
    'username'=>'VARCHAR(255) PRIMARY KEY',
    '2fa_secret' => 'VARCHAR(255) DEFAULT NULL',
    '2fa_enabled' => 'BOOLEAN DEFAULT FALSE',
]);

$db->createTable('documents',[
    'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
    'document_id' => 'VARCHAR(255) NOT NULL UNIQUE',
    'user_id' => 'INT NOT NULL',
    'title' => 'VARCHAR(255) NOT NULL',
    'encrypted_key' => 'VARCHAR(255) DEFAULT NULL',
    'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
    'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
    'visibility' => 'ENUM(\'public\', \'private\', \'restricted\') DEFAULT \'private\'',
    'tags' => 'TEXT DEFAULT NULL',
    'version' => 'INT DEFAULT 1',
    'file_path' => 'VARCHAR(255) DEFAULT NULL',
]);
$db->createTable('powerpoints',[
    'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
    'presentation_id' => 'VARCHAR(255) NOT NULL UNIQUE',
    'user_id' => 'INT NOT NULL',
    'title' => 'VARCHAR(255) NOT NULL',
    'encrypted_key' => 'VARCHAR(255) DEFAULT NULL',
    'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
    'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
    'visibility' => 'ENUM(\'public\', \'private\', \'restricted\') DEFAULT \'private\'',
    'tags' => 'TEXT DEFAULT NULL',
    'version' => 'INT DEFAULT 1',
    'file_path' => 'VARCHAR(255) DEFAULT NULL',
]);
$db->createTable('spreadsheets',[
    'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
    'spreadsheet_id' => 'VARCHAR(255) NOT NULL UNIQUE',
    'user_id' => 'INT NOT NULL',
    'title' => 'VARCHAR(255) NOT NULL',
    'encrypted_key' => 'VARCHAR(255) DEFAULT NULL',
    'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
    'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
    'visibility' => 'ENUM(\'public\', \'private\', \'restricted\') DEFAULT \'private\'',
    'tags' => 'TEXT DEFAULT NULL',
    'version' => 'INT DEFAULT 1',
    'file_path' => 'VARCHAR(255) DEFAULT NULL',
]);
$db->createTable('mail',[
    'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
    'user_id' => 'INT NOT NULL',
    'subject' => 'VARCHAR(255) NOT NULL',
    'body' => 'TEXT NOT NULL',
    'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
    'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
    'status' => 'ENUM(\'sent\', \'draft\', \'archived\', \'deleted\') DEFAULT \'draft\'',
    'attachments' => 'TEXT DEFAULT NULL',
    'recipient' => 'VARCHAR(255) NOT NULL',
    'encrypted_key' => 'VARCHAR(255) DEFAULT NULL',
]);
$db->createTable('attempts', [
    'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
    'attempted_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
    'ip_address' => 'VARCHAR(45) NOT NULL',
    'user_agent' => 'TEXT NOT NULL',
    'attempt' => 'INT NOT NULL',
]);
$db->createTable('devices', [
    'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
    'name' => 'VARCHAR(255)',            // Name of the device
    'type' => 'VARCHAR(100)',            // Type of device (e.g., smartphone, tablet)
    'brand' => 'VARCHAR(100)',           // Brand of the device
    'model' => 'VARCHAR(100)',          // Model of the device
    'manufacturer' => 'VARCHAR(100)',    // Manufacturer or brand
    'serial_number' => 'VARCHAR(255)',   // Unique serial number
    'purchase_date' => 'DATE',           // Date of purchase
    'warranty_expiry' => 'DATE',         // Warranty expiry date
    'status' => 'VARCHAR(50)',           // Current status (e.g., active, inactive, maintenance)
    'location' => 'VARCHAR(255)',        // Physical location or assigned user
    'ip_address' => 'VARCHAR(45)',       // IP address if networked
    'mac_address' => 'VARCHAR(17)',      // MAC address
    'os' => 'VARCHAR(100)',              // Operating system
    'asset_tag' => 'VARCHAR(100) UNIQUE NOT NULL', // Asset tag identifier
    'history'=>'JSON NULL',              // Devices History
    'notes' => 'TEXT'                    // Additional notes
]);
$db->createTable('passkeys',[
    'id'=>'INT AUTO_INCREMENT PRIMARY KEY',
    'user_id'=>'INT NOT NULL',
    'credential_id'=>'VARCHAR(255) NOT NULL UNIQUE KEY',
    'public_key'=>'TEXT NOT NULL',
    'sign_count'=>'INT DEFAULT 0',
    'transports'=>'VARCHAR(255)',
    'attestation_object'=>'BLOB',
    'created_at'=>'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
    'updated_at'=>'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
]);

$db->createTable('rate',[
    'id'=>'INT AUTO_INCREMENT PRIMARY KEY',
    'ip_address' => 'VARCHAR(45) NOT NULL',
    'timestamp'=>'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
    'requests'=>'INT DEFAULT 0',
    'path'=>'VARCHAR(250) NOT NULL'
]);

$db->createTable('version_history', [
    'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
    'item_id' => 'INT NOT NULL',
    'version_number' => 'INT NOT NULL',
    'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
    'author_id' => 'INT NOT NULL',
    'description' => 'TEXT DEFAULT NULL',
    'content' => 'TEXT DEFAULT NULL',
    'additional_metadata' => 'TEXT DEFAULT NULL'
]);

$db->createTable('support_tickets', [
    'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
    'ticket_id' => 'VARCHAR(255) NOT NULL UNIQUE',
    'user_id' => 'INT NOT NULL',
    'subject' => 'VARCHAR(255) NOT NULL',
    'description' => 'TEXT NOT NULL',
    'status' => 'ENUM(\'open\', \'in_progress\', \'resolved\', \'closed\') DEFAULT \'open\'',
    'category'=>'VARCHAR(255) NOT NULL',
    'priority' => 'ENUM(\'low\', \'medium\', \'high\', \'urgent\') DEFAULT \'medium\'',
    'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
    'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
    'assigned_to' => 'JSON DEFAULT NULL',
    'comments' => 'JSON DEFAULT NULL', // Store comments as JSON array
    'attachments' => 'LONGTEXT DEFAULT NULL', // Store attachment file paths as JSON array
]);

$db->createTable('gps_points',[
    'serial_number'=>'VARCHAR(255) PRIMARY KEY',
    'latitude'=>'DOUBLE NOT NULL',
    'longitude'=>'DOUBLE NOT NULL',
    'accuracy'=>'DOUBLE NOT NULL',
    'altitude'=>'DOUBLE NULL',
    'altitudeAccuracy'=>'DOUBLE NULL',
    'speed'=>'DOUBLE NULL',
    'timestamp'=>'TIMESTAMP'
]);