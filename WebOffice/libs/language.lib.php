<?php
namespace WebOffice;
class Language{
    private string $path;
    public function __construct(string $lang, string $path=LANGUAGE_PATH) {
        $this->path = $path.DS.$lang.'.json';
    }
    /**
     * Loads the language file
     * @return array Associative array of language strings
     */
    public function load(): array {
        if (!file_exists($this->path)) {
            throw new \Exception("Language file not found: $this->path");
        }
        $content = file_get_contents($this->path);
        if ($content === false) {
            throw new \Exception("Failed to read language file: $this->path");
        }
        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("JSON decode error: " . json_last_error_msg());
        }
        return $data;
    }
}