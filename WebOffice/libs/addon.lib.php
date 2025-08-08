<?php
namespace WebOffice;
use WebOffice\Config, WebOffice\Security;
class Addons{
    private $validAddonFiles = [];
    public function __construct() {
        // Scan all PHP files in the addons directory and check their namespaces
        $addonsDir = dirname(__DIR__).'/addons/';
        (new Security())->auditCode($addonsDir);
        $allowedNamespaces = [
            'WebOffice\\Addons\\Office',
            'WebOffice\\Addons\\PowerPoint',
            'WebOffice\\Addons\\Documents',
            'WebOffice\\Addons\\Spreadsheets'
        ];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($addonsDir)
        );
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $contents = file_get_contents($file->getPathname());
                if (preg_match('/namespace\s+([a-zA-Z0-9\\\\]+);/', $contents, $matches)) {
                    $namespace = $matches[1];
                    if (in_array($namespace, $allowedNamespaces)) 
                        $this->validAddonFiles[] = $file->getPathname();
                    else throw new \Exception("Invalid namespace '$namespace' in file: " . $file->getPathname());
                }
            }
        }

        $forbiddenNamespaces = [
            'WebOffice\\Database',
            'WebOffice\\Server',
            'WebOffice\\Network'
        ];
        foreach ($this->validAddonFiles as $file) {
            $contents = file_get_contents($file);
            foreach ($forbiddenNamespaces as $forbiddenNamespace) {
                if (strpos($contents, $forbiddenNamespace) !== false) 
                    throw new \Exception("Forbidden namespace $forbiddenNamespace found in file: $file");
            }
        }

    }
    /**
     * Creates a hook
     * @param string $type Execution type
     * @return mixed Values returned by the hook method or null if the method does not exist
     */
    public function hook(string $type): mixed{
        foreach ($this->validAddonFiles as $file) {
            include_once $file;
            $className = $this->getClassNameFromFile($file);
            if (class_exists($className) && method_exists($className, $type)) {
                $addonInstance = new $className();
                return $addonInstance->$type();
            }
        }
        return null;
    }
    /**
     * Helper to get the fully qualified class name from a file
     */
    private function getClassNameFromFile(string $file): string {
        $contents = file_get_contents($file);
        preg_match('/namespace\s+([a-zA-Z0-9\\\\]+);/', $contents, $nsMatch);
        preg_match('/class\s+([a-zA-Z0-9_]+)/', $contents, $classMatch);
        if (isset($nsMatch[1]) && isset($classMatch[1])) {
            return "$nsMatch[1]\\$classMatch[1]";
        }
        return '';
    }
    /**
     * Checks if any addon is enabled
     * @return bool True if any addon is enabled, false otherwise
     */
    public function isEnabled(): bool{
        foreach ($this->validAddonFiles as $file) {
            $c = new Config(basename($file,'.plg.php'));
            if($c->read('enabled')) return true;
            else return false;
        }
        return false; // Default to false if no enabled method is found
    }
}