<?php
namespace WebOffice;
use WebOffice\Utils, WebOffice\Device, WebOffice\Server;

class Network {
    private Utils $utils; 
    private Device $device;
    private int $bytesSent = 0, $bytesReceived = 0;
    private Server $server;

    public function __construct() {
        $this->utils = new Utils();
        $this->device = new Device();
        $this->server = new Server();
    }
    
    private function getOsShortName(): string {
        // Get the OS short name in uppercase for comparison
        return strtoupper($this->device->getOs('short_name'));
    }

    /**
     * Pings the server or a specified host and returns detailed statistics.
     * @param string $host Host to ping, defaults to 'localhost'
     * @param int $timeout Timeout in seconds
     * @return array Ping results
     */
    public function ping(string $host = 'localhost', int $timeout=3): array {
        $os = $this->getOsShortName();
        $cmd = strpos($os, 'WIN') === 0 ? 
            "ping -n 1 -w " . ($timeout * 1000) . " $host" :
            "ping -c 1 -W $timeout $host";
        $result = $this->utils->executeCommand($cmd);
        $output = [
            'host' => $host,
            'ip' => null,       // Added for IP address
            'bytes' => null,
            'time' => null,
            'icmp_seq' => null,
            'ttl' => null,
            'transmitted' => null,
            'received' => null,
            'loss' => null,
            'min' => null,
            'avg' => null,
            'max' => null,
            'mdev' => null,
        ];

        if (strpos($os, 'WIN') === 0) {
            // Windows parsing
            // Example: Reply from 8.8.8.8: bytes=32 time=14ms TTL=117
            if (preg_match('/Reply from ([^:]+): bytes=\d+ .*?time[=<]*([\d]+)ms TTL=(\d+)/i', $result, $matches)) {
                $output['ip'] = $matches[1];
                $output['bytes'] = null; // Not extracted in this pattern, but can be added if needed
                $output['time'] = (int)$matches[2];
                $output['ttl'] = (int)$matches[3];
            }
            // Additional parsing for IP
            if (!$output['ip']) {
                // Try to extract IP from other lines if necessary
                if (preg_match('/Pinging ([^ ]+) \[([^\]]+)\]/i', $result, $matches)) {
                    $output['ip'] = $matches[2];
                }
            }
        } else {
            // Unix/Linux parsing
            // Example: 64 bytes from 8.8.8.8: icmp_seq=1 ttl=117 time=14.2 ms
            if (preg_match('/(\d+) bytes from ([^:]+): .*?icmp_seq=(\d+) .*?ttl=(\d+) .*?time=([\d\.]+) ms/', $result, $matches)) {
                $output['bytes'] = (int)$matches[1];
                $output['ip'] = trim(preg_replace("/$host|\(|\)/",'',$matches[2]));
                $output['icmp_seq'] = (int)$matches[3];
                $output['ttl'] = (int)$matches[4];
                $output['time'] = (float)$matches[5];
            }

            // Parse summary line: '--- host ping statistics ---'
            if (preg_match('/(\d+) packets transmitted, (\d+) received, ([\d]+)% packet loss/', $result, $matches)) {
                $output['transmitted'] = (int)$matches[1];
                $output['received'] = (int)$matches[2];
                $output['loss'] = (int)$matches[3];
            }

            // Parse min/avg/max/mdev from the rtt line
            if (preg_match('/rtt min\/avg\/max\/mdev = ([\d\.]+)\/([\d\.]+)\/([\d\.]+)\/([\d\.]+) ms/', $result, $matches)) {
                $output['min'] = (float)$matches[1];
                $output['avg'] = (float)$matches[2];
                $output['max'] = (float)$matches[3];
                $output['mdev'] = (float)$matches[4];
            }
        }

        return $output;
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

        // Execute command and validate result
        $result = $this->utils->executeCommand($cmd);

        if (!is_string($result)) {
            // If result is not a string, return empty array or handle error
            return [];
        }

        $outputLines = preg_split('/\r?\n/', $result);
        $interfaces = [];

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
                // Additional parsing logic for RX/TX stats may be required here
            }
        }
        // Save last interface if any
        if ($currentInterface !== null) {
            $interfaces[] = $currentInterface;
        }

        // Escape output for security
        return array_map(fn($iface): array => [
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
            $result = explode('\n',$this->utils->executeCommand('iwconfig'));
                foreach ($result as $line) {
                    if (stripos($line, 'IEEE 802.11') !== false) {
                        return true;
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
            $result = explode('\n',$this->utils->executeCommand('netsh wlan show interfaces'));
                foreach ($result as $line) {
                    if (stripos($line, 'SSID') !== false) {
                        return trim(explode(':', $line)[1] ?? '');
                    }
                }
            
        } else {
            // Linux/Mac: Use iwgetid to get the SSID
            $result = explode('\n',$this->utils->executeCommand('iwgetid -r'));
            return trim(implode('', $result));
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
            $result = explode('\n',$this->utils->executeCommand('netsh wlan show interfaces'));
            if (isset($result) && is_array($result)) {
                foreach ($result as $line) {
                    if (stripos($line, 'Authentication') !== false) {
                        return true;
                    }
                }
            }
        } else {
            // Linux/Mac: Use iwconfig to get the security type
            $result = explode('\n',$this->utils->executeCommand(' nmcli -f SSID,SECURITY dev wifi list | grep "'.$this->internetName().'"'));
            if (isset($result) && is_array($result)) {
                foreach ($result as $line) {
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
    /**
     * Returns the connected devices to the network with IP, MAC, Hostname, Interface, Type, and Device
     * @return array{ip: string, mac: string, name: string, interface: string, type: string, device: string} Connected Devices
     */
    public function connectedDevices(): array {
        $devices = [];

        // Detect the operating system
        $os = $this->getOsShortName();

        // Helper function to identify device type
        // Moved outside of main method for proper scope
        function identifyDevice($hostname, $mac): string {
            // Define device pattern categories with regex patterns
            $deviceLanPatterns = [
                'TV' => [
                    '/tivo.*\.lan/i',
                    '/samsung.*tv\.lan|Samsung\.lan/i',
                    '/lg.*-tv\.lan/i',
                    '/sony.*bravia\.lan/i',
                    '/smart.*tv\.lan/i',
                    '/android.*tv\.lan/i'
                ],
                'Router' => [
                    '/gateway.*\.lan|_gateway/i',
                    '/router.*\.lan/i',
                    '/netgear.*\.lan/i',
                    '/tplink.*\.lan/i',
                    '/dlink.*\.lan/i',
                    '/cisco.*\.lan/i'
                ],
                'Amazon' => [
                    '/amazon.*echo\.lan/i',
                    '/alexa\.lan/i',
                    '/amazon.*device\.lan/i'
                ],
                'Phones' => [
                    '/iphone.*\.lan/i',
                    '/android.*\.lan/i',
                    '/galaxy.*\.lan/i',
                    '/pixel.*\.lan/i'
                ],
                'Tablets' => [
                    '/ipad.*\.lan/i',
                    '/tablet.*\.lan/i',
                    '/galaxytab.*\.lan/i'
                ],
                'Speakers' => [
                    '/sonos.*\.lan/i',
                    '/googlehome.*\.lan/i',
                    '/amazonecho.*\.lan/i',
                    '/bose.*\.lan/i'
                ],
                'Cameras' => [
                    '/nestcam.*\.lan/i',
                    '/ringcamera.*\.lan/i',
                    '/hikvision.*\.lan/i'
                ],
                'Computers' => [
                    '/laptop.*\.lan/i',
                    '/desktop.*\.lan/i',
                    '/macbook.*\.lan/i',
                    '/pc.*\.lan/i'
                ],
                'Ethernet'=>[
                    '/amazon.*\.lan/',
                    '/Ethernet/',
                    '/eth\d+/',               
                    '/LAN/',                  
                    '/CAT\d+/',               
                    '/GigabitEthernet/',
                    '/10G Ethernet/',
                    '/RJ45/',    
                    '/Network Cable/',
                    '/Ethernet Cable/',
                    '/Patch Cable/',
                    '/UTP/',
                    '/STP/',
                ]
                // Add more categories and regex patterns as needed
            ];

            $hostnameLower = strtolower($hostname);
            $macLower = strtolower($mac);

            // Check hostname patterns against device categories using regex
            foreach ($deviceLanPatterns as $category => $patterns) {
                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $hostnameLower)) {
                        return $category;
                    }
                }
            }

            // Basic heuristic checks for common device types
            if (strpos($hostnameLower, 'tv') !== false || strpos($hostnameLower, 'smart') !== false) {
                return 'TV';
            } elseif (
                strpos($hostnameLower, 'phone') !== false || 
                strpos($hostnameLower, 'mobile') !== false || 
                strpos($hostnameLower, 'android') !== false || 
                strpos($hostnameLower, 'ios') !== false
            ) {
                return 'Phone';
            } elseif (
                strpos($hostnameLower, 'camera') !== false || 
                strpos($hostnameLower, 'cam') !== false
            ) {
                return 'Camera';
            } elseif (
                strpos($hostnameLower, 'laptop') !== false || 
                strpos($hostnameLower, 'notebook') !== false || 
                strpos($hostnameLower, 'desktop') !== false || 
                strpos($hostnameLower, 'pc') !== false
            ) {
                return 'Computer';
            } elseif (strpos($hostnameLower, 'tablet') !== false) {
                return 'Tablet';
            }

            // MAC address prefix heuristics for known vendors (optional)
            $ouiPrefixes = [
                '00:1a:2b' => 'Apple',
                '00:1b:63' => 'Apple',
                'd4:3a:11' => 'Samsung',
            ];

            foreach ($ouiPrefixes as $prefix => $vendor) {
                if (strpos($macLower, $prefix) === 0) {
                    if ($vendor === 'Apple') {
                        return 'Apple Device';
                    } elseif ($vendor === 'Samsung') {
                        return 'Samsung Device';
                    }
                    // Add more vendor-based classifications as needed
                }
            }

            // Default fallback
            return 'Unknown';
        }

        if (stripos($os, 'WIN') === 0) {
            // Windows: Use 'arp -a'
            $output = $this->utils->executeCommand('arp -a');

            if ($output !== null) {
                preg_match_all('/\s*(\d+\.\d+\.\d+\.\d+)\s+([\w-]+)\s+(\w+)/', $output, $matches);
                if (isset($matches[1])) {
                    foreach ($matches[1] as $index => $ip) {
                        $mac = $matches[2][$index];
                        $type = $matches[3][$index]; // 'dynamic' or 'static'
                        $interface = 'N/A'; // Could be improved with additional commands
                        $name = $this->server->hostname($ip);
                        $deviceType = identifyDevice($name, $mac);

                        $devices[] = [
                            'ip' => $ip,
                            'mac' => $mac,
                            'name' => $name,
                            'interface' => $interface,
                            'type' => $type,
                            'device' => $deviceType
                        ];
                    }
                }
            }
        } elseif (stripos($os, 'DAR') === 0 || stripos($os, 'FREE') === 0 || stripos($os, 'LIN') === 0) {
            // Mac/Linux: Use 'arp -a'
            $output = $this->utils->executeCommand('arp -a');

            if ($output !== null) {
                preg_match_all('/\((\d+\.\d+\.\d+\.\d+)\)\s+at\s+([0-9a-f:]{17})\s+(?:\[.*\])?\s+on\s+(\w+)/i', $output, $matches);

                if (isset($matches[1])) {
                    foreach ($matches[1] as $index => $ip) {
                        $mac = $matches[2][$index];
                        $interface = $matches[3][$index];
                        $name = $this->server->hostname($ip);
                        $deviceType = identifyDevice($name, $mac);

                        $devices[] = [
                            'ip' => $ip,
                            'mac' => $mac,
                            'name' => $name,
                            'interface' => $interface,
                            'type' => 'dynamic', // Default assumption
                            'device' => $deviceType
                        ];
                    }
                }
            }
        }

        return $devices;
    }
    /**
     * Looks up the hostname based on IP address
     * @param string $ip IP address
     * @return string|null Hostname else NULL
     */
    public function lookup(string $ip): string|null {
        // Filter IP for security
        $ip = (new Security())->filter($ip, Security::FILTER_IPV4);
        
        // Check if nslookup is available
        if ($this->utils->executeCommand('which nslookup')) {
            $command = "nslookup $ip";
        } else {
            // Fallback if nslookup is not available
            return null;
        }

        // Execute command
        $output = $this->utils->executeCommand($command);

        // Parse output to extract hostname
        if (strpos($output, 'name =') !== false) {
            // For nslookup output
            preg_match('/name = (.+)/', $output, $matches);
            if (!empty($matches[1])) {
                return trim($matches[1]);
            }
        }

        // Hostname not found or output format not recognized
        return null;
    }
}