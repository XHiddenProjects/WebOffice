<?php
namespace WebOffice;
use DeviceDetector\ClientHints,
DeviceDetector\DeviceDetector,
DeviceDetector\Parser\Device\AbstractDeviceParser,
WebOffice\Utils;
class Device {
    private DeviceDetector $dd;
    private Utils $utils;
    /**
     * Constructor to initialize DeviceDetector with ClientHints
     * @param string $userAgent User agent string, defaults to $_SERVER['HTTP_USER_AGENT']
     */
    public function __construct(string|null $userAgent = null) {
        $userAgent ??= $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        AbstractDeviceParser::setVersionTruncation(-1);
        // Initialize DeviceDetector with ClientHints
        $this->dd = new DeviceDetector($userAgent,ClientHints::factory($_SERVER));
        $this->dd->parse();
        $this->utils = new Utils();
    }
    /**
     * Get the device brand
     * @return string Returns the device brand name or an empty string if not detected
     */
    public function deviceBrand(): string {
        return $this->dd->getBrandName();
    }
    /**
     * Get the device model
     * @return string Returns the device model name or an empty string if not detected
     */
    public function deviceModel(): string {
        return $this->dd->getModel();
    }
    /**
     * Check if the device is a specific type (e.g., mobile, tablet, desktop, etc.)
     * @param string $type Type of device to check (default is 'mobile')
     * @return bool Returns true if the device matches the specified type, false otherwise
     */
    public function is(string $type='mobile'): bool {
        $methodName = "is" . ucfirst(strtolower($type));
        return $this->dd->{$methodName}() ?? false;
    }
    /**
     * Get the user agent string
     * @return string Returns the user agent string
     */
    public function getUserAgent(): string {
        return $this->dd->getUserAgent();
    }
    /**
     * Get the client information
     * @param string $attr Attribute to retrieve from the client information (default is empty)
     * @return array|string|null Returns an array containing client information such as browser, version, and type
     */
    public function getClient(string $attr=''): array|string|null {
        return $this->dd->getClient($attr);
    }
    /**
     * Get the operating system information
     * @param string $attr Attribute to retrieve from the operating system information (default is empty)
     * @return array|string|null Returns an array containing OS information such as name, version, and platform
     */
    public function getOs(string $attr=''): array|string|null {
        return $this->dd->getOs($attr);
    }
    /**
     * Returns the users devices manufacturer
     * @return string Manufacturers name
     */
    public function getManufacturer(): string{
        $manufacturer = 'Unknown';
        // Detect OS
        $os = strtoupper($this->getOs('short_name'));

        if (stripos($os, 'LIN') !== false) {
            // Linux - try to read from DMI data
            $output = shell_exec('cat /sys/class/dmi/id/sys_vendor 2>/dev/null');
            if ($output) {
                $manufacturer = trim($output);
            }
        } elseif (stripos($os, 'DAR') !== false) {
            // macOS - no straightforward way, but system_profiler can help
            $output = shell_exec('system_profiler SPHardwareDataType | grep "Manufacturer"');
            $manufacturer = $output ? trim($output) : 'Apple';
        } elseif (stripos($os, 'WIN') !== false) {
            // Windows - use wmic command
            $output = shell_exec('wmic computersystem get manufacturer 2>&1');
            if ($output) {
                // Parse output
                $lines = preg_split('/\r?\n/', trim($output));
                if (isset($lines[1])) {
                    $manufacturer = trim($lines[1]);
                }
            }
        }
        return $manufacturer;
    }
    /**
     * Returns the devices screen information
     * @return array Device screen information
     */
    public function getScreen(): array{
        return (new Storage())->session('device_info', action:'Get')??[];
    }
}

