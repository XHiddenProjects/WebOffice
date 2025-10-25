<?php
namespace WebOffice;
include_once dirname(__DIR__).'/init.php';
class Config{
    private string $config;
    /**
     * Creates a config object
     * @param string $name Configuration name
     * @param string $path Path to configuration, leave empty to use DEFAULT config path
     */
    public function __construct(string $name='config',string $path='') {
        $this->config = defined('CONFIG_PATH') ? CONFIG_PATH.DS."$name.ini" : "$path/".rtrim($name,'/').".ini";
    }
    /**
     * Will return the value based on the conjunctions path of the ini array
     * @param string[] ...$conjunctions Path to select results
     * @return mixed The results of the selected path
     */
    public function read(string ...$conjunctions): mixed {
        // Parse the INI file with sections
        $configData = parse_ini_file($this->config, true);
        
        // Start from the root of the config data
        $current = $configData;

        // Loop through each conjunction to traverse nested array
        foreach ($conjunctions as $conjunction) {
            if (is_array($current) && array_key_exists($conjunction, $current)) {
                $current = $current[$conjunction];
            } else {
                // Return null or throw exception if path is invalid
                return null;
            }
        }

        return $current;
    }
    /**
     * Creates a configuration if the config file hasn't been made
     * @param array $data Data to store as a configuration
     * @return bool If the data was successfully created it is TRUE, else FALSE 
     */
    public function create(array $data): bool {
        if (!file_exists($this->config)) {
            $iniContent = '';

            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    // Create a section
                    $iniContent .= "[$key]\n";
                    foreach ($value as $subKey => $subValue) {
                        $iniContent .= "$subKey = $subValue\n";
                    }
                    $iniContent .= "\n"; // Add a newline between sections
                } else {
                    // No section, simple key-value
                    $iniContent .= "$key = $value\n";
                }
            }

            // Write to the config file
            file_put_contents($this->config, $iniContent);
            return true;
        } else return false;
    }
    /**
    * Update the config file
    * @param array $data Data to update
    * @return void
    */
    public function update(array $data): void {
        $configData = [];
        if (file_exists($this->config)) {
            $configData = parse_ini_file($this->config, true);
        }
        foreach ($data as $key => $value) 
            $configData[$key] = is_array($value) ? ($configData[$key] = isset($configData[$key]) && is_array($configData[$key]) ?  array_merge($configData[$key], $value) : $value) : $value; 
        

        // Rebuild the INI content
        $iniContent = '';

        foreach ($configData as $section => $sectionData) {
            if (is_array($sectionData)) {
                // This is a section
                $iniContent .= "[$section]\n";
                foreach ($sectionData as $key => $value) {
                    $iniContent .= "$key = $value\n";
                }
                $iniContent .= "\n"; // Add a newline between sections
            } else {
                // This is a key-value outside of sections
                $iniContent .= "$section = $sectionData\n";
            }
        }

        // Write the updated content back to the file
        file_put_contents($this->config, $iniContent);
    }
    /**
     * Removes the config file
     * @return bool TRUE if removed, else FALSE
     */
    public function remove(): bool{
        return @unlink($this->config);
    }
}