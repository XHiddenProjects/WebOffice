<?php
namespace WebOffice;
use WebOffice\Utils;
class Files{
    public function __construct() {

    }
    /**
     * Scans a folder
     * @param string $path Folder path to scan
     * @return array|bool
     */
    public function scan(string $path): array|bool{
        return array_values(array_diff(scandir($path),['.','..']));
    }
    /**
     * Creates a file
     * @param string $filename Filename
     * @param string $context Data to insert into the the file
     * @param int $perm Permission
     * @return bool TRUE if the file is created, else FALSE
     */
    public function createFile(string $filename, string $context='', int $perm=0777): bool{
        $f = fopen($filename,'w+',true);
        fwrite($f,$context);
        @chmod($filename,$perm);
        return @fclose($f);
    }
    /**
     * Creates a folder
     * @param string $name Folder name
     * @param int $permissions Folder permission
     * @param bool $recursive Can be created inside a created folder
     * @return bool TRUE if the folder is created, else FALSE
     */
    public function createFolder(string $name, int $permissions=0777, bool $recursive=true): bool{
        return @mkdir($name,$permissions,$recursive);
    }
    /**
     * Deletes file
     * @param string $filename Filename to delete
     * @return bool TRUE if deleted, else FALSE
     */
    public function deleteFile(string $filename): bool{
        return @unlink($filename);
    }
    /**
     * Deletes folder
     * @param string $folder Folder to delete
     * @return bool TRUE if deleted, else FALSE
     */
    public function deleteFolder(string $folder): bool {
        if (!is_dir($folder)) return false;
        $files = $this->scan($folder);
        foreach ($files as $file) {
            $filePath = $folder . DIRECTORY_SEPARATOR . $file;
            if (is_dir($filePath)) $this->deleteFolder($filePath);
            else $this->deleteFile($filePath);
        }
        rmdir($folder);
        return true;
    }
    /**
     * Changes the permission of the file
     * @param string $path Path to change permission
     * @param int $perm Permission of the file
     * @return bool TRUE if permission has been changed, else FALSE
     */
    public function permission(string $path, int $perm): bool{
        return @chmod($path, $perm);
    }
    /**
     * Checks if path is a file/dir
     * @param string $path Path to check
     * @param string $act Action: File: **file** | Dir: **dir**, **folder**, or **directory**
     * @return bool TRUE if the path is set action, else FALSE
     */
    public function is(string $path, string $act='file'): bool{
        $act = strtolower($act);
        if($act==='file')
            return is_file($path);
        elseif($act==='dir'||$act==='folder'||$act='directory') return is_dir($path);
        else return false;
    }
    /**
     * Returns the modified time
     * @param string $filepath Filepath
     * @return bool|int Returns the Unix timestamp
     */
    public function modifiedTime(string $filepath): bool|int{
        return filemtime($filepath);
    }
    /**
     * Returns the file changed time
     * @param string $filepath Filepath
     * @return bool|int Returns the Unix timestamp
     */
    public function changeTime(string $filepath): bool|int{
        return filectime($filepath);
    }
    /**
     * Returns the files last access time
     * @param string $filepath Filepath
     * @return bool|int Returns the Unix timestamp
     */
    public function actionTime(string $filepath): bool|int{
        return fileatime($filepath);
    }
    /**
     * Creates a symbolic link
     * @param string $target
     * @param string $link
     * @return bool
     */
    public function createSymlink(string $target, string $link): bool {
        return symlink($target, $link);
    }
    /**
     * Returns the size of the folder/file
     * @param string $path Path
     * @return bool|int Filesize (bytes)
     */
    public function size(string $path): bool|int {
        if ($this->is($path, 'dir')) {
            $totalSize = 0;
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
            );
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $totalSize += $file->getSize();
                }
            }
            return $totalSize;
        } else {
            return filesize($path);
        }
    }
    /**
     * Summary of convert
     * @param string|int|float $size Filesize in Human-readable or in bytes
     * @param string $to **Readable** or **Bytes**
     * @return int|float|string Human-readable format or in bytes
     */
    public function convert(string|int|float $size, string $to='readable'): int|float|string{
        $utils = new Utils();
        $to=strtolower($to);
        return strcmp($to,'readable')==0 ? (string)$utils->bytes2readable($size) : $utils->readable2bytes($size);
    }
    /**
     * Checks if the filepath exists
     * @param string $path Path
     * @return bool TRUE if exits, else FALSE
     */
    public function exists(string $path):bool{
        return @file_exists($path);
    }
    /**
     * Hashes/Verifies hash file
     * @param string $file File
     * @param string $algo Algorithm
     * @return bool|string Returns the hashed file
     */
    public function hash(string $file,string $algo='sha256'): bool|string{
        return hash_file($algo,$file);
    }
    /**
     * Verifies hashed file
     * @param string $hash Hashed file
     * @param string $input Hash input
     * @return bool TRUE if hashed is matched, else FALSE
     */
    public function verify_hash(string $hash, string $input): bool{
        return hash_equals($hash, $input);
    }

    /**
     * Writes JSON data to a file atomically.
     *
     * @param string $filename The target filename.
     * @param mixed $data The data to encode as JSON.
     * @param int $flags JSON encoding options (default: JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).
     * @return bool True on success, false on failure.
     */
    public function writeJsonAtomic(string $filename, $data, int $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE): bool
    {
        $dir = dirname($filename);
        
        // Create parent directories if they don't exist
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) return false;
        }

        $tempFile = tempnam($dir, 'tmp');

        if ($tempFile === false) return false;
        

        $lockFile = "$tempFile.lock";

        $lockHandle = fopen($lockFile, 'c');
        if ($lockHandle === false) {
            unlink($tempFile);
            return false;
        }

        // Acquire exclusive lock
        if (!flock($lockHandle, LOCK_EX)) {
            fclose($lockHandle);
            unlink($tempFile);
            unlink($lockFile);
            return false;
        }

        // Encode JSON data
        $json = json_encode($data, $flags);
        if ($json === false) {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
            unlink($tempFile);
            unlink($lockFile);
            return false;
        }

        // Write JSON to temp file
        $writeResult = false;
        if (file_put_contents($tempFile, $json) !== false) $writeResult = true;
        

        // Release lock and close handle
        flock($lockHandle, LOCK_UN);
        fclose($lockHandle);

        // Remove lock file
        unlink($lockFile);

        if (!$writeResult) {
            unlink($tempFile);
            return false;
        }

        // Atomically rename temp file to target file
        $renamed = false;
        if (DIRECTORY_SEPARATOR === '\\') {
            // Windows: unlink target if exists before renaming
            if (file_exists($filename)) unlink($filename);
            $renamed = rename($tempFile, $filename);
        } else $renamed = rename($tempFile, $filename);
        

        if (!$renamed) unlink($tempFile);
        

        return $renamed;
    }

    /**
     * Writes plain text content to a file atomically.
     *
     * @param string $filename The target filename.
     * @param string $content The content to write.
     * @return bool True on success, false on failure.
     */
    public function writeFileAtomic(string $filename, string $content): bool
    {
        $dir = dirname($filename);

        // Create parent directories if they don't exist
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                return false;
            }
        }

        $tempFile = tempnam($dir, 'tmp');

        if ($tempFile === false) {
            return false;
        }

        $lockFile = "$tempFile.lock'";

        $lockHandle = fopen($lockFile, 'c');
        if ($lockHandle === false) {
            unlink($tempFile);
            return false;
        }

        // Acquire exclusive lock
        if (!flock($lockHandle, LOCK_EX)) {
            fclose($lockHandle);
            unlink($tempFile);
            unlink($lockFile);
            return false;
        }

        // Write content to temp file
        $writeResult = false;
        if (file_put_contents($tempFile, $content) !== false) {
            $writeResult = true;
        }

        // Release lock and close handle
        flock($lockHandle, LOCK_UN);
        fclose($lockHandle);

        // Remove lock file
        unlink($lockFile);

        if (!$writeResult) {
            unlink($tempFile);
            return false;
        }

        // Atomically rename temp file to target file
        $renamed = false;
        if (DIRECTORY_SEPARATOR === '\\') {
            // Windows: unlink target if exists
            if (file_exists($filename)) unlink($filename);
            $renamed = rename($tempFile, $filename);
        } else $renamed = rename($tempFile, $filename);
        if (!$renamed) unlink($tempFile);
        return $renamed;
    }

}