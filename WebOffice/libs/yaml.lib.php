<?php
namespace WebOffice\tools;

class YAML {
    public function __construct() {
    }
    /**
     * Parses YAML to array
     * @param string $yaml YAML string
     * @return array Array of the YAML
     */
    public function parse(string $yaml): array {
        $lines = preg_split('/\r\n|\r|\n/', trim($yaml));
        $result = [];
        $stack = [&$result];
        $indentLevels = [0];

        foreach ($lines as $line) {
            if (trim($line) === '' || preg_match('/^\s*#/', $line)) {
                continue;
            }

            $indent = strspn($line, ' ');
            $content = trim($line);

            // Pop from stack if indentation decreased
            while ($indent < end($indentLevels)) {
                array_pop($indentLevels);
                array_pop($stack);
            }

            $currentRef = &$stack[count($stack) - 1];

            if (strpos($content, ':') !== false) {
                // Key-value or nested map
                list($key, $value) = explode(':', $content, 2);
                $key = trim($key);
                $value = trim($value);

                if ($value === '') {
                    // Nested structure
                    $currentRef[$key] = [];
                    $stack[] = &$currentRef[$key];
                    $indentLevels[] = $indent + 2; // assuming 2 spaces per indent
                } else {
                    // Scalar value
                    $currentRef[$key] = $this->convertValue($value);
                }
            } elseif (substr($content, 0, 1) === '-') {
                // List item
                $item = trim(substr($content, 1));
                $item = $this->convertValue($item);

                // Check if currentRef is an array; if not, convert it
                if (!is_array($currentRef)) {
                    $currentRef = [];
                }

                // Append to current list
                $currentRef[] = $item;

                // If the list item is a nested map (e.g., '- key: value'), handle accordingly
                if (strpos($item, ':') !== false && is_string($item)) {
                    // For nested maps in list
                    list($nestedKey, $nestedValue) = explode(':', $item, 2);
                    $nestedKey = trim($nestedKey);
                    $nestedValue = trim($nestedValue);
                    $currentRef[count($currentRef) - 1] = [];
                    $currentRef[count($currentRef) - 1][$nestedKey] = $this->convertValue($nestedValue);
                }
            }
        }

        return $this->cleanup($result);
    }

    private function convertValue($value): mixed {
        if (in_array(strtolower($value), ['null', '~'])) {
            return null;
        }
        if (strtolower($value) === 'true') {
            return true;
        }
        if (strtolower($value) === 'false') {
            return false;
        }
        if (is_numeric($value)) {
            return $value + 0;
        }
        return $value;
    }

    private function cleanup($data): mixed {
        if (is_array($data)) {
            // Recursively clean up
            foreach ($data as &$value) {
                $value = $this->cleanup($value);
            }
        }
        return $data;
    }
    /**
     * Converts YAML to JSON
     * @param string $yaml YAML String
     * @param int $flags JSON flags
     * @return bool|string JSON String after parse
     */
    public function toJSON(string $yaml, int $flags=JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT): bool|string{
        return json_encode($this->parse($yaml),$flags);
    }
}