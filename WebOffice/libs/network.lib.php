<?php
namespace WebOffice;
use WebOffice\Utils, WebOffice\Device;

class Network {
    private Utils $utils; 
    private Device $device;
    private int $bytesSent = 0, $bytesReceived = 0;

    public function __construct() {
        $this->utils = new Utils();
        $this->device = new Device();
    }
    
    private function getOsShortName(): string {
        // Get the OS short name in uppercase for comparison
        return strtoupper($this->device->getOs('short_name'));
    }

    /**
     * Pings the server or a specified host
     * @param string $host Host to ping, defaults to 'localhost'
     * @param int $timeout Timeout in seconds
     * @return array|array{output: array, return_var: int} Ping results
     */
    public function ping(string $host = 'localhost', int $timeout=3): array {
        $os = $this->getOsShortName();
        $cmd = strpos($os, 'WIN') === 0 ? 
            "ping -n 1 -w " . ($timeout * 1000) . " $host" :
            "ping -c 1 -W $timeout $host";
        $result = $this->utils->executeCommand($cmd);
        if (isset($result['output']) && is_array($result['output'])) return $result['output'];

        return is_array($result) ? $result : [];
    }

    /**
     * Gets bandwidth/interface info including RX and TX packets/bytes
     * @return array{name: string, rx_packets: int, rx_bytes: int, tx_packets: int, tx_bytes: int}[] Returns an array of network interfaces with their RX and TX stats
     */
    public function getBandwidth(): array {
        $os = $this->getOsShortName();

        // Determine command based on OS
        $cmd = (strpos($os, 'WIN') === 0) ? 
            "netsh interface ipv4 show interfaces" :
            "ifconfig";

        $result = $this->utils->executeCommand($cmd);

        $interfaces = [];

        if (isset($result['output']) && is_array($result['output'])) {
            $outputLines = $result['output'];

            // Initialize variables to hold current interface data
            $currentInterface = null;

            foreach ($outputLines as $line) {
                // For Linux, parse 'ifconfig' output
                if (strpos($cmd, 'ifconfig') !== false) {
                    // Detect interface name line (starts without indentation)
                    if (preg_match('/^(\S+):/', $line, $matches)) {
                        // Save previous interface data if any
                        if ($currentInterface !== null) {
                            $interfaces[] = $currentInterface;
                        }
                        $currentInterface = [
                            'name' => $matches[1],
                            'rx_packets' => 0,
                            'rx_bytes' => 0,
                            'tx_packets' => 0,
                            'tx_bytes' => 0,
                        ];
                    }

                    // Parse RX and TX packets/bytes
                    if ($currentInterface !== null) {
                        if (preg_match('/RX packets (\d+)\s+bytes (\d+)/', $line, $matches)) {
                            $currentInterface['rx_packets'] = (int)$matches[1];
                            $currentInterface['rx_bytes'] = (int)$matches[2];
                        }
                        if (preg_match('/TX packets (\d+)\s+bytes (\d+)/', $line, $matches)) {
                            $currentInterface['tx_packets'] = (int)$matches[1];
                            $currentInterface['tx_bytes'] = (int)$matches[2];
                        }
                    }
                }
                // For Windows, parsing 'netsh' output
                elseif (strpos($cmd, 'netsh') !== false) {
                    // Example line:
                    // "Enabled Interface Name : Ethernet"
                    if (preg_match('/^Enabled Interface Name\s*:\s*(.+)$/', $line, $matches)) {
                        $currentInterface = [
                            'name' => trim($matches[1]),
                            'rx_packets' => 0,
                            'rx_bytes' => 0,
                            'tx_packets' => 0,
                            'tx_bytes' => 0,
                        ];
                    }
                    // Parsing may vary; you might need to adjust based on actual output
                    // Since 'netsh' does not directly show RX/TX packets, you might need a different command
                }
            }
            // Save last interface if any
            if ($currentInterface !== null) {
                $interfaces[] = $currentInterface;
            }
        }

        // Escape output for security
        return array_map(fn($iface): array =>[
                'name' => htmlspecialchars($iface['name'], ENT_QUOTES, 'UTF-8'),
                'rx_packets' => $iface['rx_packets'],
                'rx_bytes' => $iface['rx_bytes'],
                'tx_packets' => $iface['tx_packets'],
                'tx_bytes' => $iface['tx_bytes']
            ], $interfaces);
    }
    /**
     * Gets the total bytes sent and received
     * @return void
     */
    public function send($data): void {
        // Increment bytes sent
        $this->bytesSent += strlen($data);
    }
    /**
     * Gets the total bytes received
     * @return void
     */
    public function receive($data): void {
        // Increment bytes received
        $this->bytesReceived += strlen($data);
    }
    /**
     * Summary of monitor traffic of the network interfaces
     * @return array{sent: int, received: int} Returns an array with total
     */
    public function monitorTraffic(): array{
        return ['sent' => $this->bytesSent, 'received' => $this->bytesReceived];
    }
    public function resetTraffic(): void {
        // Reset the traffic counters
        $this->bytesSent = 0;
        $this->bytesReceived = 0;
    }
    /**
     * Checks if the internet connection is available
     * @return array{connected: bool, strength: string} Returns an array indicating connection status and strength
     */
    public function checkInternetConnection(): array{
        $connected = false;
        $headers = @get_headers('https://dns.google/',true);
        if($headers && strpos($headers[0], '200') !== false) $connected = true;
        return [
            'connected'=>$connected,
            'strength' => $this->internetStrength($connected),
            'latency' => $this->measureLatency(),
            'isWireless'=> $this->isWireless(),
            'connection_name'=>$this->internetName() ?: 'Unknown',
            'connection_secured'=>$this->internetSecurity()
        ];
    }
    /**
     * Checks the internet connection strength
     * @param bool $isConnected Whether the internet is connected
     * @return string Returns a string indicating the connection strength
     */
    private function internetStrength(bool $isConnected): string{
        if($isConnected){
            $latency = $this->measureLatency();
            if($latency === null) return 'No Response';
            if($latency < 50) return 'Excellent';
            elseif($latency < 100) return 'Good';
            elseif($latency < 200) return 'Fair';
            else return 'Poor';
        }else return 'No Connection';
    }
    /**
     * Measures the latency to a known reliable host (e.g., Google DNS)
     * @return float|int|null Returns the latency in milliseconds or null if no response
     */
    private function measureLatency(): float|int|null{
        $start = microtime(true);
        $headers = @get_headers('https://dns.google/', true);
        if($headers){
            $end = microtime(true);
            return ($end - $start) * 1000; // Return latency in milliseconds
        } else {
            return null; // No response, latency cannot be measured
        }
    }
    /**
     * Checks if the device is connected via wireless (Wi-Fi)
     * @return bool Returns true if the device is connected via wireless, false otherwise
     */
    private function isWireless(): bool {
        // Check if the device is wireless based on OS
        $os = $this->getOsShortName();
        if (strpos($os, 'WIN') === 0) {
            // Windows: Check for wireless interfaces
            $interfaces = $this->getBandwidth();
            foreach ($interfaces as $interface) {
                if (stripos($interface['name'], 'Wi-Fi') !== false || stripos($interface['name'], 'Wireless') !== false) {
                    return true;
                }
            }
        } else {
            // Linux/Mac: Check for wireless interfaces
            $result = $this->utils->executeCommand('iwconfig');
            if (isset($result['output']) && is_array($result['output'])) {
                foreach ($result['output'] as $line) {
                    if (stripos($line, 'IEEE 802.11') !== false) {
                        return true;
                    }
                }
            }
        }
        return false;
    }
    /**
     * Gets the name of the internet connection (SSID for wireless)
     * @return string|null Returns the SSID or connection name, or null if not applicable
     */
    private function internetName(): string|null {
        // Get the internet connection name based on OS
        $os = $this->getOsShortName();
        if (strpos($os, 'WIN') === 0) {
            // Windows: Use netsh to get the connection name
            $result = $this->utils->executeCommand('netsh wlan show interfaces');
            if (isset($result['output']) && is_array($result['output'])) {
                foreach ($result['output'] as $line) {
                    if (stripos($line, 'SSID') !== false) {
                        return trim(explode(':', $line)[1] ?? '');
                    }
                }
            }
        } else {
            // Linux/Mac: Use iwgetid to get the SSID
            $result = $this->utils->executeCommand('iwgetid -r');
            if (isset($result['output']) && is_array($result['output'])) {
                return trim(implode('', $result['output']));
            }
        }
        return null;
    }
    /**
     * Checks if the internet connection is secured (e.g., WPA, WPA2, WPA3)
     * @return bool Returns true if the connection is secured, false otherwise
     */
    private function internetSecurity(): bool {
        // Get the internet connection security based on OS
        $os = $this->getOsShortName();
        if (strpos($os, 'WIN') === 0) {
            // Windows: Use netsh to get the security type
            $result = $this->utils->executeCommand('netsh wlan show interfaces');
            if (isset($result['output']) && is_array($result['output'])) {
                foreach ($result['output'] as $line) {
                    if (stripos($line, 'Authentication') !== false) {
                        return true;
                    }
                }
            }
        } else {
            // Linux/Mac: Use iwconfig to get the security type
            $result = $this->utils->executeCommand(' nmcli -f SSID,SECURITY dev wifi list | grep "'.$this->utils->escapeShell($this->internetName()).'"');
            if (isset($result['output']) && is_array($result['output'])) {
                foreach ($result['output'] as $line) {
                    if (stripos($line, 'WPA') !== false || 
                    stripos($line, 'WPA2') !== false ||
                    stripos($line, 'WPA3') !== false) {
                        return true;
                    }
                }
            }
        }
        return false;
    }
}