<?php
namespace WebOffice;
use WebOffice\Device, WebOffice\Utils;
class Domain {
    private string $domainName;
    private string $username;
    private string $password;
    private Device $device;
    private Utils $utils;
    /**
     * Domain controller
     * @param string $domainName Domain Name
     * @param string $username Username
     * @param string $password Password
     */
    public function __construct(string $domainName, string $username, string $password) {
        $this->domainName = $domainName;
        $this->username = $username;
        $this->password = $password;
        $this->device = new Device();
        $this->utils = new Utils();
    }
    /**
     * Join into the domain
     * @return string Returns the message on success and failure
     */
    public function join(): string {
        $os = strtolower($this->device->getOs('short_name'));
        if (strpos($os, 'win') !== false) {
            return $this->joinWindows();
        } elseif (strpos($os, 'lin') !== false) {
            return $this->joinLinux();
        } elseif (strpos($os, 'dar') !== false) {
            return $this->joinMac();
        } else {
            return "Unsupported operating system.";
        }
    }
    /**
     * Leave the domain
     * @return string
     */
    public function leave(): string {
        $os = strtolower($this->device->getOs('short_name'));
        if (strpos($os, 'win') !== false) {
            return $this->leaveWindows();
        } elseif (strpos($os, 'lin') !== false) {
            return $this->leaveLinux();
        } elseif (strpos($os, 'dar') !== false) {
            return $this->leaveMac();
        } else {
            return "Unsupported operating system.";
        }
    }

    private function leaveWindows(): string {
        $command = sprintf(
            'netdom remove %s /domain:%s /UserD:%s /PasswordD:%s /reboot:Yes',
            gethostname(),
            escapeshellarg($this->domainName),
            escapeshellarg($this->username),
            escapeshellarg($this->password)
        );
        $results = $this->utils->exec($command);
        $output = $results['output'];
        $return_var = $results['result_code'];

        if ($return_var === 0) {
            // Verify if still part of the domain
            $verifyCommand = 'systeminfo | findstr /C:"Domain"';
            $verifyResults = $this->utils->exec($verifyCommand);
            $domainInfo = implode("\n", $verifyResults['output']);

            if (strpos($domainInfo, $this->domainName) === false) {
                return "Successfully left the domain on Windows.";
            } else {
                return "Leave command reported success, but still part of the domain.";
            }
        } else {
            return "Failed to leave domain on Windows. Output: " . implode("\n", $output);
        }
    }

    private function leaveLinux(): string {
        // Remove the domain join
        $command = 'sudo realm leave ' . escapeshellarg($this->domainName);
        $results = $this->utils->exec($command);
        $output = $results['output'];
        $return_var = $results['result_code'];
        print_r($command);
        if ($return_var === 0) {
            // Verify domain removal
            $verifyCommand = 'realm list';
            $verifyResults = $this->utils->exec($verifyCommand);
            $joinedDomains = implode("\n", $verifyResults['output']);

            if (strpos($joinedDomains, $this->domainName) === false) {
                return "Successfully left the domain on Linux.";
            } else {
                return "Failed to leave the domain on Linux.";
            }
        } else {
            return "Failed to leave domain on Linux. Output: " . implode("\n", $output);
        }
    }

    private function leaveMac(): string {
        // Remove the computer from AD domain
        $command = 'sudo dsconfigad -remove -force';
        $results = $this->utils->exec($command);
        $output = $results['output'];
        $return_var = $results['result_code'];

        if ($return_var === 0) {
            // Verify removal
            $verifyCommand = 'dsconfigad -show';
            $verifyResults = $this->utils->exec($verifyCommand);
            $verifiedOutput = implode("\n", $verifyResults['output']);

            if (strpos($verifiedOutput, 'Active Directory Domain') === false) {
                return "Successfully left the domain on Mac.";
            } else {
                return "Failed to leave the domain on Mac.";
            }
        } else {
            return "Failed to leave domain on Mac. Output: " . implode("\n", $output);
        }
    }

    private function joinWindows(): string {
        $command = sprintf(
            'netdom join %s /domain:%s /userD:%s /passwordD:%s /reboot:Yes',
            gethostname(),
            escapeshellarg($this->domainName),
            escapeshellarg($this->username),
            escapeshellarg($this->password)
        );
        $results = $this->utils->exec($command);
        $output = $results['output'];
        $return_var = $results['result_code'];

        if ($return_var === 0) {
            // Verify domain membership
            $verifyCommand = 'systeminfo | findstr /C:"Domain"';
            $verifyResults = $this->utils->exec($verifyCommand);
            $domainInfo = implode("\n", $verifyResults['output']);

            if (strpos($domainInfo, $this->domainName) !== false) {
                return "Successfully joined the domain on Windows.";
            } else {
                return "Join command reported success, but domain not verified.";
            }
        } else {
            return "Failed to join domain on Windows. Output: " . implode("\n", $output);
        }
    }

    private function joinLinux(): string {
        $command = sprintf(
            'echo %s | sudo realm join %s -U %s',
            escapeshellarg($this->password),
            escapeshellarg($this->domainName),
            escapeshellarg($this->username)
        );
        $results = $this->utils->exec($command);
        $output = $results['output'];
        $return_var = $results['result_code'];

        if ($return_var === 0) {
            // Verify domain membership
            $verifyCommand = 'realm list';
            $verifyResults = $this->utils->exec($verifyCommand);
            $joinedDomains = implode("\n", $verifyResults['output']);
            if (strpos($joinedDomains, $this->domainName) !== false) {
                return "Successfully joined the domain on Linux.";
            } else {
                return "Domain not verified.";
            }
        } else {
            return "Failed to join domain on Linux. Output: " . implode("\n", $output);
        }
    }
    private function joinMac(): string {
        $command = sprintf(
            'sudo dsconfigad -add %s -username %s -password %s',
            escapeshellarg($this->domainName),
            escapeshellarg($this->username),
            escapeshellarg($this->password)
        );
        $results = $this->utils->exec($command);
        $output = $results['output'];
        $return_var = $results['result_code'];

        if ($return_var === 0) {
            // Verify domain membership
            $verifyCommand = 'dsconfigad -show';
            $verifyResults = $this->utils->exec($verifyCommand);
            $verifiedOutput = implode("\n", $verifyResults['output']);

            if (strpos($verifiedOutput, $this->domainName) !== false) {
                return "Successfully joined the domain on Mac.";
            } else {
                return "Join command reported success, but domain not verified.";
            }
        } else {
            return "Failed to join domain on Mac. Output: " . implode("\n", $output);
        }
    }
}