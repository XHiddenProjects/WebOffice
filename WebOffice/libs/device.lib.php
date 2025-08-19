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

    public function getSerial(): string {
        $os = $this->dd->getOs('short_name');
        $serial = null;
        $password = USERS_DESKTOP_PSW; // or get this from a secure place

        if ($os === 'WIN') {
            // Use WMIC command on Windows
            $command = 'wmic bios get serialnumber 2>&1';
            $output = $this->utils->executeCommand($command, $password);
            if ($output) {
                // Parse the output
                $lines = preg_split('/\r?\n/', trim($output));
                if (isset($lines[1])) {
                    $serial = trim($lines[1]);
                }
            }
        } elseif ($os === 'DAR') {
            // macOS
            $command = 'system_profiler SPHardwareDataType | grep "Serial Number"';
            $output = $this->utils->executeCommand($command, $password);
            if ($output) {
                // Output format: "Serial Number (system): XYZ"
                if (preg_match('/Serial Number.*: (.+)$/', $output, $matches)) {
                    $serial = trim($matches[1]);
                }
            }
        } elseif ($os === 'LIN') {
            // Linux - try dmidecode (requires root)
            $command = 'dmidecode -s system-serial-number';
            $output = $this->utils->executeCommand($command, $password);
            if ($output) {
                $serial = trim($output);
                if (strpos($serial, 'Permission denied') !== false) {
                    $serial = 'Permission denied. Run as root or check permissions.';
                }
            }
        } else {
            $serial = 'Unsupported OS';
        }

        return $serial ?? 'Serial number not found';
    }
    /**
     * Reboots your device or a specified device
     * @param string|null $device Device to reboot (hostname or IP)
     * @return string
     */
    public function reboot(?string $device = null): string {
        $osShortName = strtolower($this->dd->getOs('short_name'));
        $command = '';

        if ($device) {
            // Reboot remote device via SSH (assuming SSH keys are configured)
            switch ($osShortName) {
                case 'win':
                    // For Windows remote reboot, you might use psexec or similar tools
                    // Example: psexec \\$device shutdown /r /t 0
                    $command = "psexec \\\\$device shutdown /r /t 0";
                    break;
                case 'lin':
                case 'dar':
                    // For Linux/macOS remote reboot via SSH
                    $command = "ssh $device sudo reboot";
                    break;
                default:
                    return "Reboot command not supported for remote device on this OS.";
            }
        } else {
            // Reboot local device
            $command = match ($osShortName) {
                'win' => 'shutdown /r /t 0',
                'lin', 'dar' => 'sudo reboot',
                default => ''
            };
            if (empty($command)) {
                return "Reboot command not supported on this OS.";
            }
        }

        if (empty($command)) {
            return "Reboot command could not be constructed.";
        }

        try {
            $this->utils->executeCommand($command);
            return $device ? "Reboot command executed for device $device." : "Reboot command executed.";
        } catch (\Exception $e) {
            return "Failed to reboot" . ($device ? " device $device: " : " device: ") . $e->getMessage();
        }
    }
    /**
     * Shuts down your device or a specified device
     * @param string|null $device Device to shutdown (hostname or IP)
     * @return string
     */
    public function shutdown(?string $device = null): string {
        $osShortName = strtolower($this->dd->getOs('short_name'));
        $command = '';

        if ($device) {
            // Shutdown remote device via SSH or other remote management tools
            switch ($osShortName) {
                case 'win':
                    // For Windows remote shutdown, using psexec
                    $command = "psexec \\\\$device shutdown /s /t 0";
                    break;
                case 'lin':
                case 'dar':
                    // For Linux/macOS remote shutdown via SSH
                    $command = "ssh $device sudo shutdown -h now";
                    break;
                default:
                    return "Shutdown command not supported for remote device on this OS.";
            }
        } else {
            // Shutdown local device
            $command = match ($osShortName) {
                'win' => 'shutdown /s /t 0',
                'lin', 'dar' => 'sudo shutdown -h now',
                default => ''
            };
            if (empty($command)) {
                return "Shutdown command not supported on this OS.";
            }
        }

        if (empty($command)) {
            return "Shutdown command could not be constructed.";
        }

        try {
            $this->utils->executeCommand($command);
            return $device ? "Shutdown command executed for device $device." : "Shutdown command executed.";
        } catch (\Exception $e) {
            return "Failed to shutdown" . ($device ? " device $device: " : " device: ") . $e->getMessage();
        }
    }
}

