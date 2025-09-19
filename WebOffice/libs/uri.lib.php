<?php
namespace WebOffice;
class URI{
    public function __construct() {}
    /**
     * Converts a string to a slug
     * @param string $string String to convert
     * @return string Slug
     */
    public function slugify(string $string): string {
        // Convert to lowercase
        $string = strtolower($string);
        // Replace spaces and special characters with hyphens
        $string = preg_replace('/[^\w\s-]/', '', $string); // Remove special characters
        $string = preg_replace('/[\s-]+/', '-', $string); // Replace multiple spaces or hyphens with a single hyphen
        $string = trim($string, '-'); // Trim hyphens from the beginning and end
        return $string;
    }
    /**
     * Parses a URL and returns its components
     * @param string $url URL to parse
     * @return array Associative array of URL components
     */
    public function parseURL(string $url): array {
        $components = parse_url($this->slugify($url));
        if ($components === false) throw new \InvalidArgumentException("Invalid URL: $url");
        
        // Ensure all components are present
        return [
            'scheme' => $components['scheme'] ?? '',
            'host' => $components['host'] ?? '',
            'port' => $components['port'] ?? null,
            'user' => $components['user'] ?? '',
            'pass' => $components['pass'] ?? '',
            'path' => $components['path'] ?? '',
            'query' => $components['query'] ?? '',
            'fragment' => $components['fragment'] ?? ''
        ];
    }
    /**
     * Builds a URL from its components
     * @param array $components Associative array of URL components
     * @return string Constructed URL
     */
    public function buildURL(array $components): string {
        $url = '';
        if (!empty($components['scheme'])) {
            $url .= $components['scheme'] . '://';  
        }
        if (!empty($components['user'])) {
            $url .= $components['user'];
            if (!empty($components['pass'])) {
                $url .= ':' . $components['pass'];
            }
            $url .= '@';
        }
        if (!empty($components['host'])) {
            $url .= $components['host'];
        }
        if (!empty($components['port'])) {
            $url .= ':' . $components['port'];
        }
        if (!empty($components['path'])) {
            $url .= $components['path'];
        }
        if (!empty($components['query'])) {
            $url .= '?' . $components['query'];
        }
        if (!empty($components['fragment'])) {
            $url .= '#' . $components['fragment'];
        }
        return $url;
    }
    /**
     * Gets the scheme of a URL
     * @param string $url URL to parse
     * @return string Scheme
     */
    public function getScheme(string $url): string {
        $components = $this->parseURL($url);
        return $components['scheme'] ?? '';
    }
    /**
     * Gets the host of a URL
     * @param string $url URL to parse
     * @return string Host
     */
    public function getHost(string $url): string {
        $components = $this->parseURL($url);
        return $components['host'] ?? '';  
    }
    /**
     * Gets the path of a URL
     * @param string $url URL to parse
     * @return string Path
     */
    public function getPath(string $url): string {
        $components = $this->parseURL($url);
        return $components['path'] ?? '';
    }
    /**
     * Converts the path to array
     * @param string $url URL
     * @return string[] Array pth
     */
    public function arrPath(string $url): array{
        return array_values(array_filter(explode('/',$url),fn($i)=>$i!==''));
    }
    /**
     * Gets the query of a URL
     * @param string $url URL to parse
     * @return string Query
     */
    public function getQuery(string $url): string {
        $components = $this->parseURL($url);
        return $components['query'] ?? '';
    }
    /**
     * Gets the fragment of a URL
     * @param string $url URL to parse
     * @return string Fragment
     */
    public function getFragment(string $url): string {
        $components = $this->parseURL($url);
        return $components['fragment'] ?? '';
    }
    /**
     * Gets the port of a URL
     * @param string $url URL to parse
     * @return int|null Port number or null if not specified
     */
    public function getPort(string $url): ?int {
        $components = $this->parseURL($url);
        return $components['port'] ?? null;
    }
    /**
     * Gets the user of a URL
     * @param string $url URL to parse
     * @return string User
     */
    public function getUser(string $url): string {
        $components = $this->parseURL($url);
        return $components['user'] ?? '';
    }
    /**
     * Gets the password of a URL
     * @param string $url URL to parse
     * @return string Password
     */
    public function getPass(string $url): string {
        $components = $this->parseURL($url);
        return $components['pass'] ?? '';
    }
    /**
     * Builds the query structure
     * @param array $queries Query structure in array
     * @return string Valid query structure
     */
    public function buildQuery(array $queries): string{
        return http_build_query($queries);
    }
    /**
     * Checks for matched URL path
     * @param string|string[] $match Path or Paths based on the URL
     * @return bool TRUE if the URL matches the path else FALSE
     */
    public function match(string|array $match): bool{
        $match = is_string($match) ? explode('/',$match) : $match;
        $currentPath = $this->arrPath($_SERVER['REQUEST_URI']);

        if(count($currentPath)>count($match)) 
            array_splice($currentPath, 0, count($currentPath)-count($match));

        if(empty(array_diff($match,$currentPath))) return true;
        else return false;
    }
}