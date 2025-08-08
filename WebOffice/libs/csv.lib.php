<?php
namespace WebOffice\tools;

class CSV {
    private string $splicer=',', $newLine='\n';
    /**
     * Creates a CSV class
     * @param string $splicer Splicer for each row
     * @param string $newLine new line for each column
     */
    public function __construct(string $splicer=',',string $newLine='\n') {
        $this->splicer = $splicer;
        $this->newLine = $newLine;
    }
    /**
     * Parses array into a CSV
     * @param array $arr Array to parse
     * @param string $splicer Splicer to use (Default: ,) 
     * @param string $newLine New line character(s) (Default: \n)
     * @return string Parsed CSV
     */
    public function parse(array $arr): string {
        if (empty($arr)) {
            return '';
        }

        // Get the headers from the keys of the first row
        $headers = array_keys($arr[0]);
        $csvLines = [];

        // Create CSV header row
        $csvLines[] = $this->escapeRow($headers, $this->splicer);

        // Loop through each row and escape fields
        foreach ($arr as $row) {
            $fields = [];
            foreach ($headers as $header) {
                $fields[] = $row[$header] ?? '';
            }
            $csvLines[] = $this->escapeRow($fields, $this->splicer);
        }

        // Join all lines into a single CSV string with the specified new line character(s)
        return implode($this->newLine, $csvLines);
    }
    /**
     * Converts CSV to Array
     * @param string $csv CSV string
     * @param string $splicer Splicer to for col
     * @param string $newLine new line (rows)
     * @return array Array of the CSV
     */
    public function toArray(string $csv): array {
        $lines = preg_split("/$this->newLine/", $csv);
        $result = [];
        if (empty($lines) || trim($lines[0]) === '') {
            return [];
        }

        // Parse headers
        $headers = $this->parseRow($lines[0], $this->splicer);
        
        // Parse each subsequent line into associative array
        for ($i = 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if ($line === '') {
                continue; // Skip empty lines
            }
            $fields = $this->parseRow($line, $this->splicer);
            
            $row = [];
            foreach ($headers as $index => $header) {
                $row[$header] = $fields[$index] ?? '';
            }
            $result[] = $row;
        }
        return $result;
    }

    /**
     * Helper method to parse a CSV row into array of fields
     */
    private function parseRow(string $row, string $splicer): array {
        $fields = [];
        $field = '';
        $inQuotes = false;
        $length = strlen($row);
        $i = 0;
        while ($i < $length) {
            $char = $row[$i];

            if ($char === '"') {
                if ($inQuotes && $i + 1 < $length && $row[$i + 1] === '"') {
                    // Escaped quote
                    $field .= '"';
                    $i += 2;
                    continue;
                } else {
                    $inQuotes = !$inQuotes;
                }
            } elseif ($char === $splicer && !$inQuotes) {
                $fields[] = $field;
                $field = '';
            } else {
                $field .= $char;
            }
            $i++;
        }
        $fields[] = $field;
        return $fields;
    }

    private function escapeRow(array $row, string $splicer = ','): string {
        $escapedFields = array_map(function($field): array|string {
            if ($field === null) {
                $field = '';
            }
            $field = str_replace('"', '""', $field);
            if (strpos($field, '"') !== false || strpos($field, ',') !== false || strpos($field, "\n") !== false) {
                $field = "\"$field\"";
            }
            return $field;
        }, $row);
        return implode($splicer, $escapedFields);
    }
    /**
     * Validates the CSV string
     * @param string $csv CSV String
     * @param string $splicer Splicer
     * @param string $newLine New line
     * @return bool TRUE if CSV is valid, else FALSE
     */
    public function validate(string $csv): bool {
        $lines = preg_split('/' . preg_quote($this->newLine, '/') . '/', $csv);
        
        if (empty($lines) || trim($lines[0]) === '') {
            return false; // No data
        }
        
        // Parse headers to determine expected number of fields
        $headers = $this->parseRow($lines[0], $this->splicer);
        $expectedFieldCount = count($headers);

        // Validate each subsequent line
        for ($i = 1; $i < count($lines); $i++) {
            $line = $lines[$i]; // Do not trim here if you want to preserve empty lines
            // Remove trimming if you want to check for empty line as-is
            // For validation, trimming might be useful, but if you want to keep empty lines, just assign directly
            $line = trim($line);

            // Do not skip empty lines; process them
            if ($line === '') {
                // Decide whether empty lines are valid or not.
                // For example, if empty lines are valid, continue
                continue;
            }

            $fields = $this->parseRow($line, $this->splicer);
            if (count($fields) !== $expectedFieldCount) {
                return false; // Mismatch in number of fields
            }
            // Additional validation can be added here
        }

        return true; // All checks passed
    }
    /**
     * Converts CSV to a table
     * @param string $csv CSV code
     * @return string Table
     */
    public function toTable(string $csv): string {
        $arr = $this->toArray($csv);
        if (empty($arr)) {
            return '<table class="table table-striped"><thead><tr><th>No data available</th></tr></thead></table>';
        }
        
        // Get headers from the first array element
        $headers = array_keys($arr[0]);
        
        // Start building the HTML table
        $html = '<table class="table table-striped">';
        
        // Generate header section
        $html .= '<thead><tr>';
        foreach ($headers as $header) {
            $html .= '<th>' . htmlspecialchars($header) . '</th>';
        }
        $html .= '</tr></thead>';

        // Generate body section
        $html .= '<tbody>';
        foreach ($arr as $row) {
            $html .= '<tr>';
            foreach ($headers as $header) {
                $cell = $row[$header] ?? '';
                $html .= '<td>' . htmlspecialchars($cell) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';

        $html .= '</table>';
        return $html;
    }
}