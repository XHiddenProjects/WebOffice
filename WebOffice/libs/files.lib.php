<?php
namespace WebOffice;
class Files{
    public function __construct() {

    }
    /**
     * Scans a folder
     * @param string $path Folder path to scan
     * @return array|bool
     */
    public function scan(string $path): array|bool{
        return array_diff(scandir($path),['.','..']);
    }
    /**
     * Creates a file
     * @param string $filename Filename
     * @param string $context Data to insert into the the file
     * @return bool TRUE if the file is created, else FALSE
     */
    public function createFile(string $filename, string $context=''): bool{
        $f = fopen($filename,'w+',true);
        fwrite($f,$context);
        return @fclose($f);
    }
    /**
     * Creates a folder
     * @param string $name Folder name
     * @return bool TRUE if the folder is created, else FALSE
     */
    public function createFolder(string $name): bool{
        return @mkdir($name,0777,true);
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
}