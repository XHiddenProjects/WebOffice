<?php
namespace WebOffice;
use DeviceDetector\ClientHints;
use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\AbstractDeviceParser;
class Device {
    private DeviceDetector $dd;
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
    }
    /**
     * Get the device type
     * @param string $attr Optional attribute to retrieve from the device type
     * @return array|string|null Returns the device type or null if not detected
     */
    public function getDeviceType(string $attr=''): array|string|null {
        return $this->dd->getOs($attr);
    }
    /**
     * Get the device brand
     * @return string Returns the device brand name or an empty string if not detected
     */
    public function getDeviceBrand(): string {
        return $this->dd->getBrandName();
    }
    /**
     * Get the device model
     * @return string Returns the device model name or an empty string if not detected
     */
    public function getDeviceModel(): string {
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
    
}

