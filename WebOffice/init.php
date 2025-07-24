<?php

use WebOffice\Database;
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

include_once 'vendor/autoloader.php';
# Definitions
define('BASE',dirname(__FILE__));
define('URL', getBaseUrl());
const DS = DIRECTORY_SEPARATOR;
define('CONFIG_PATH',BASE.DS.'configuration');
define('TEMP_PATH',BASE.DS.'temp');
define('DOCS_PATH',BASE.DS.'docs');
define('POWERPOINT_PATH',BASE.DS.'powerpoints');
define('SPREADSHEETS_PATH',BASE.DS.'spreadsheets');
define('OFFICE_PATH',BASE.DS.'office');
define('BACKUP_PATH',BASE.DS.'backups');
define('OS',strtoupper(PHP_OS));
define('LANGUAGE_PATH',BASE.DS.'languages');
define('ADDONS_PATH',BASE.DS.'addons');
define('ASSETS_PATH',BASE.DS.'assets');
define('ASSETS_URL',URL.'/assets');
define('VERSION', file_exists(BASE . DS . 'VERSION') ? BASE . DS . 'VERSION' : '1.0.0');


use WebOffice\Security, WebOffice\Config, WebOffice\Files;
$config = new Config();
define('LANGUAGE',array_map(fn($e): string=>strtolower($e),explode(',',$config->read('settings','lang'))) ?? ['en','us']);


date_default_timezone_set($config->read('settings','timezone') ?? 'UTC');

$sec = new Security();
$sec->setSecurityHeaders();

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

if(!file_exists(TEMP_PATH)) $f->createFolder('temp');
if(!file_exists(BACKUP_PATH)) $f->createFolder('backups');
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
    $currentTime = time();
    if (($currentTime - $_SESSION['last_cleanup']) >= (int)$config->read('settings','temp')) {
        cleanTemp();
        $_SESSION['last_cleanup'] = $currentTime;
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
    'password' => 'VARCHAR(255) NOT NULL',
    'email' => 'VARCHAR(255) NOT NULL UNIQUE',
    'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
    'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
    'last_login' => 'TIMESTAMP NULL',
    'permissions' => 'TEXT DEFAULT NULL',
    '2fa_secret' => 'VARCHAR(255) DEFAULT NULL',
    '2fa_enabled' => 'BOOLEAN DEFAULT FALSE',
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

$db->createTable('documents',[
    'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
    'document_id' => 'VARCHAR(255) NOT NULL UNIQUE',
    'user_id' => 'INT NOT NULL',
    'title' => 'VARCHAR(255) NOT NULL',
    'content' => 'TEXT NOT NULL',
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
    'content' => 'TEXT NOT NULL',
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
    'content' => 'TEXT NOT NULL',
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
    'user_id' => 'INT NOT NULL',
    'attempted_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
    'ip_address' => 'VARCHAR(45) NOT NULL',
    'user_agent' => 'TEXT NOT NULL',
    'attempt' => 'INT NOT NULL',
]);

