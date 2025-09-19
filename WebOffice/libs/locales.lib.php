<?php
namespace WebOffice;
use WebOffice\tools\Markdown, WebOffice\Files;

if(!defined('LANGUAGE_PATH')) define('LANGUAGE_PATH',dirname(__DIR__).'/locales');
class Locales {
    private string $path;
    private Markdown $markdown;

    /**
     * Creates a language object
     * @param string $lang Users Language
     * @param string $path Language PATH
     */
    public function __construct(string $lang, string $path=LANGUAGE_PATH) {
        $this->path = "$path/$lang.json";
        $this->markdown = new Markdown();
    }

    /**
     * Loads the language file and processes it with Markdown
     * @param string|string[] $index Optional index to retrieve specific entry
     * @param bool $parse Parses text to Markdown (Default: true)
     * @return array|string|null Processed array or string
     */
    public function load(string|array $index='', bool $parse=true): array|string|null {
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

        // If no index provided, process entire data
        if (!$index) {
            return array_map(fn($i) => $this->process($i), $data);
        }

        // If index is an array, treat it as nested keys
        $keys = is_array($index) ? $index : explode('.',$index);

        $current = $data;
        foreach ($keys as $key) {
            if (!is_array($current) || !isset($current[$key])) return null;
            $current = $current[$key];
        }

        return $parse ? $this->process($current) : $current;
    }

    /**
     * Recursively processes an array or string with Markdown
     * @param mixed $item Array or string
     * @return mixed Processed item
     */
    private function process($item) {
        if (is_array($item)) {
            return $this->processArray($item);
        } elseif (is_string($item)) {
            return $this->markdown->parse($item);
        } else {
            return $item; // For other data types, return as is
        }
    }

    /**
     * Recursively processes an array with Markdown
     * @param array $array
     * @return array Processed array
     */
    private function processArray(array $array): array {
        return array_map(fn($value) => $this->process($value), $array);
    }
    /**
     * List all the locale files
     * @return array|bool
     */
    public function list(): array|bool{
        $files = new Files();
        return $files->scan(LANGUAGE_PATH);
    }
}