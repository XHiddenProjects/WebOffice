<?php
namespace WebOffice;
use SQLite3;
use DateTime;
use DateTimeZone;
use WebOffice\tools\CSV;
class BrowserHistory{
    private Utils $utils;
    private array $Browsers;
    private string $currentBrowser='Chromium';
    protected array $histories=[];
    private array $settings=['timestamp'=>'D M d Y h:i:sa \G\M\TO (T)'];
    /**
     * Construct browser history
     * @param array $settings Settings
     */
    public function __construct(array $settings=[]) {
        $config = new Config();
        $this->settings = array_merge($this->settings,$settings);
        if (strncasecmp(PHP_OS, 'WIN', 3) === 0) {
            // Windows
            $username = $config->read('device','username');
            $home = getenv('USERPROFILE');
            if (!$home) {
                // fallback if USERPROFILE isn't set
                $homeDrive = getenv('HOMEDRIVE');
                $homePath = getenv('HOMEPATH');
                if ($homeDrive && $homePath) {
                    $home = "$homeDrive$homePath";
                }
            }
            define('HOME', $home);
        } else 
            // Unix-like systems
            define('HOME', posix_getpwnam($config->read('device','username'))['dir']);
        
        $this->utils = new Utils();
        $this->Browsers = [
            'brave'=>[
                'windows'=>'~/AppData/Local/BraveSoftware/Brave-Browser/User Data',
                'darwin'=>'~/Library/Application Support/BraveSoftware/Brave-Browser',
                'linux'=>'~/.config/BraveSoftware/Brave-Browser'
            ],
            'chrome'=>[
                'windows'=>'~/AppData/Local/Google/Chrome/User Data',
                'darwin'=>'~/Library/Application Support/Google/Chrome',
                'linux'=>'~/.config/google-chrome'
            ],
            'chromium'=>[
                'windows'=>'~/AppData/Local/chromium/User Data',
                'linux'=>'~/.config/chromium'
            ],
            'edge'=>[
                'windows'=>'~/AppData/Local/Microsoft/Edge/User Data',
                'darwin'=>'~/Library/Application Support/Microsoft Edge',
                'linux'=>'~/.config/microsoft-edge-dev'
            ],
            'epic'=>[
                'windows'=>'~/AppData/Local/Epic Privacy Browser/User Data',
                'darwin'=>'~/Library/Application Support/HiddenReflex/Epic'
            ],
            'firefox'=>[
                'windows'=>'~/AppData/Roaming/Mozilla/Firefox/Profiles',
                'darwin'=>'~/Library/Application Support/Firefox/Profiles/',
                'linux'=>'~/.mozilla/firefox'
            ],
            'librewolf'=>[
                'linux'=>'~/.librewolf'
            ],
            'opera'=>[
                'windows'=>'~/AppData/Roaming/Opera Software/Opera Stable',
                'darwin'=>'~/Library/Application Support/com.operasoftware.Opera',
                'linux'=>'~/.config/opera'
            ],
            'operagx'=>[
                'windows'=>'~/AppData/Roaming/Opera Software/Opera GX Stable'
            ],
            'safari'=>[
                'darwin'=>'~/Library/Safari'
            ],
            'vivaldi'=>[
                'windows'=>'~/AppData/Local/Vivaldi/User Data',
                'linux'=>'~/.config/vivaldi'
            ]
            
        ];
        foreach ($this->Browsers as &$browser) {
            foreach ($browser as &$path) {
                if ($path !== '') {
                    $path = rtrim($path, '/') . '/Default/History';
                }
            }
        }
        unset($path);
        print_r($this->Browsers);
    }
    /**
     * Selects the current browser
     * @param string $browser
     * @return BrowserHistory
     */
    public function sel(string $browser): static{
        $this->currentBrowser = $browser;
        return $this;
    }
    /**
     * Config path
     * @param string $path
     * @param string $os
     * @return static
     */
    public function updatePath(string $path, string $os=PHP_OS_FAMILY): static{
        $this->Browsers[strtolower($this->currentBrowser)][strtolower($os)] = preg_replace('/^~/',HOME,$path);
        return $this;
    }

    
    /**
     * Returns browsers history
     * @param string|null $browser Browser name, if null return all available browsers
     * @return void
     */
    public function getHistory(string|null $browser=null): array {
        if ($browser) {
            $browserKey = strtolower($browser);
            if (isset($this->Browsers[$browserKey])) {
                $paths = $this->Browsers[$browserKey];
                // Replace '~' with HOME in each path
                foreach ($paths as $os => $path) {
                    $paths[$os] = preg_replace('/^~/', HOME, $path);
                }
                if(file_exists($paths[strtolower(PHP_OS_FAMILY)])){
                    $func = 'get'.ucfirst(strtolower($browserKey)).'History';
                    $this->histories = $this->$func($paths[strtolower(PHP_OS_FAMILY)]);
                    return $this->histories;
                }
            } else {
                echo "Browser not found.\n";
            }
        } else {
            // For all browsers, process each one
            foreach ($this->Browsers as $browserName => $paths) {
                foreach ($paths as $os => $path) {
                    $this->Browsers[$browserName][$os] = preg_replace('/^~/', HOME, $path);
                }
            }
        }
        return [];
    }

    private function getBraveHistory(string $path): array {
        $db = new SQLite3($path, SQLITE3_OPEN_READONLY);
        $results = $db->query("SELECT last_visit_time, url, title FROM urls");
        $tables = [];
        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
            // Convert WebKit timestamp to Unix timestamp
            // WebKit timestamp: microseconds since 1601-01-01
            $webkitTime = (int)$row['last_visit_time'];
            $webkitTime = (int)$row['last_visit_time'];
            if ($webkitTime > 0) {
                // Convert microseconds to seconds and cast to int
                $unixTime = (int)floor($webkitTime / 1000000) - 11644473600;
                // Create DateTime object with timestamp
                $dateTime = (new DateTime())->setTimestamp($unixTime);
                // Format as desired
                $formattedTime = $dateTime->format($this->settings['timestamp']);
            } else 
                $formattedTime = null; // or handle as needed
            $tables[] = [
                'last_visit_time' => $formattedTime,
                'url' => $row['url'],
                'title' => $row['title']
            ];
        }
        return $tables;
    }
    /**
     * Converts to JSON object
     * @return bool|string JSON object
     */
    public function toJSON(): bool|string{
        return json_encode($this->histories,JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
    }
    /**
     * Converts to a CSV
     * @return string CSV string
     */
    public function toCSV(): string{
        $csv = new CSV();
        return $csv->parse($this->histories);
    }
    /**
     * Converts to a table view
     * @return string Table
     */
    public function toTable():string{
        $csv = new CSV();
        return $csv->toTable($this->toCSV());
    }
}