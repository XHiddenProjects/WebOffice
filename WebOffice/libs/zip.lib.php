<?php
namespace WebOffice;

class Zip
{
    private $zipArchive,$zipFilePath,$isOpen = false;
    /**
     * Summary of __construct
     * @param string $path Filepath to locate the zip file
     */
    public function __construct(string $path = null){
        $this->zipArchive = new \ZipArchive();
        if ($path === null || trim($path) === '') {
            throw new \InvalidArgumentException("Zip file path must be provided.");
        }
        $this->zipFilePath = $path;
    }
    /**
     * Creates an item to the zip file
     * @param string $filePath Filepath
     */
    public function create(bool $canOverwrite=false): Zip|bool {
        if ($this->zipFilePath === null || trim($this->zipFilePath) === '') {
            return false;
        }
        $result = $canOverwrite ? $this->zipArchive->open($this->zipFilePath, \ZipArchive::CREATE|\ZipArchive::OVERWRITE) : $this->zipArchive->open($this->zipFilePath, \ZipArchive::CREATE) ;
        if ($result === true) {
            $this->isOpen = true;
            return $this;
        }
        return false;
    }
    /**
     * Opens an existing zip file
     * @return Zip|bool Returns the Zip object if opened successfully, else false
     */
    public function open(): Zip|bool{
        if ($this->zipFilePath === null || trim($this->zipFilePath) === '') {
            return false;
        }
        $result = $this->zipArchive->open($this->zipFilePath);
        if ($result === true) {
            $this->isOpen = true;
            return $this;
        }
        return false;
    }
    /**
     * Add a file to the zip
     * @param mixed $filePath Filepath
     * @param mixed $localName Filename
     * @return bool
     */
    public function addFile(string $filePath, string $localName = null): bool{
        if (!$this->isOpen) {
            return false;
        }
        $localName ??= basename($filePath);
        return $this->zipArchive->addFile($filePath, $localName);
    }
    /**
     * Adds a folder to the zip
     * @param string $folderPath Folder path to add
     * @param string|null $localName Local name for the folder in the zip
     * @return bool TRUE if the folder was added, else FALSE
     */
    public function addFolder(string $folderPath, string $localName = null): bool {
        if (!$this->isOpen) {
            return false;
        }
        if (!is_dir($folderPath)) {
            return false;
        }
        $localName ??= basename(rtrim($folderPath, DIRECTORY_SEPARATOR));
        $this->zipArchive->addEmptyDir($localName);

        $folderPath = rtrim($folderPath, DIRECTORY_SEPARATOR);

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($folderPath, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            $filePath = $file->getPathname();
            $relativePath = $localName . DIRECTORY_SEPARATOR . substr($filePath, strlen($folderPath) + 1);

            if ($file->isDir()) {
                $this->zipArchive->addEmptyDir($relativePath);
            } else {
                $this->zipArchive->addFile($filePath, $relativePath);
            }
        }
        return true;
    }
    /**
     * Create a file from string
     * @param string $content Content to save in file
     * @param string $localName Filename to save
     * @return bool
     */
    public function addString(string $content, string $localName): bool{
        if (!$this->isOpen) {
            return false;
        }
        return $this->zipArchive->addFromString($localName, $content);
    }
    /**
     * Extracts to a folder
     * @param string $destination Location to extract
     * @return bool
     */
    public function extract(string $destination): bool{
        if (!$this->isOpen) {
            return false;
        }
        return $this->zipArchive->extractTo($destination);
    }
    /**
     * Lists all of the zips 
     * @return array<bool|string>|bool
     */
    public function listContents(): array|bool{
        if (!$this->isOpen) {
            return false;
        }
        $contents = [];
        for ($i = 0; $i < $this->zipArchive->numFiles; $i++) {
            $contents[] = $this->zipArchive->getNameIndex($i);
        }
        return $contents;
    }
    /**
     * Closes ZIP file
     * @return bool
     */
    public function close(): bool{
        if (!$this->isOpen) return false;
        
        $result = $this->zipArchive->close();
        $this->isOpen = false;
        return $result;
    }
    /**
     * Saves the zip file to a specified path
     * @param string $path Path to save the zip file
     * @return bool TRUE if saved successfully, else FALSE
     */
    public function save(): bool {
        if (!$this->isOpen) return false;
        $this->close();
        // Move the zip file to the new path
        if (rename($this->zipFilePath, $this->zipFilePath)) {
            return true;
        }
        return false;
    }
}