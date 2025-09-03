<?php
namespace WebOffice;

use LDAP\Connection;

/**
 * Class LDAP
 * Encapsulates LDAP connection and search functionalities for Active Directory or other LDAP servers.
 */
class LDAP {
    /**
     * @var string The LDAP server host address
     */
    protected string $host;

    /**
     * @var int The port number for LDAP connection (default is 389)
     */
    protected int $port;

    /**
     * @var Connection|null|bool The LDAP connection resource or null if not connected
     */
    protected null|bool|Connection $connection = null;

    /**
     * @var string The distinguished name (DN) used for binding/authentication
     */
    protected string $bindDn;

    /**
     * @var string The password for binding/authentication
     */
    protected string $password;

    /**
     * Constructor initializes LDAP server connection parameters.
     *
     * @param string $host LDAP server hostname or IP address
     * @param int $port LDAP server port (default 389)
     * @param string|array $bindDn DN used to bind/authenticate with LDAP server
     * @param string $password Password for the bind DN
     */
    public function __construct(string $host, int $port = 389, array|string $bindDn = '', string $password = '') {
        $this->host = $host;
        $this->port = $port;
        $this->bindDn = is_array($bindDn) ? $this->buildDistinguishedName($bindDn) : $bindDn;
        $this->password = $password;
    }

    /**
     * Establishes connection to the LDAP server and performs bind if credentials provided.
     *
     * @return static Returns current instance for chaining.
     * @throws \Exception If connection or bind fails.
     */
    public function connect(): static {
        // Attempt to connect to LDAP server
        $this->connection = ldap_connect($this->host, $this->port);
        if (!$this->connection) {
            throw new \Exception("Could not connect to LDAP server");
        }

        // Set LDAP options for protocol version and referrals
        ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->connection, LDAP_OPT_REFERRALS, 0);

        // Bind to LDAP server if credentials are provided
        if (!empty($this->bindDn) && !empty($this->password)) {
            $bind = ldap_bind($this->connection, $this->bindDn, $this->password);
            if (!$bind) throw new \Exception("LDAP bind failed");
        }
        return $this;
    }

    /**
     * Checks if the LDAP connection is active.
     *
     * @return bool True if connected, false otherwise.
     */
    public function isConnected(): bool {
        return $this->connection !== null;
    }

    /**
     * Re-binds to the LDAP server with current credentials.
     *
     * @return bool True on success, false on failure.
     * @throws \Exception If not connected.
     */
    public function rebind(): bool {
        if (!$this->connection) {
            throw new \Exception("Not connected to LDAP");
        }
        return ldap_bind($this->connection, $this->bindDn, $this->password);
    }

    /**
     * Performs an LDAP search with the specified base DN, filter, and attributes.
     *
     * @param string $baseDn Base distinguished name to start the search.
     * @param string $filter LDAP filter string to specify search criteria.
     * @param array $attributes List of attributes to retrieve (optional).
     * @return array Array of LDAP entries matching the search.
     * @throws \Exception If search fails or not connected.
     */
    public function search(string $baseDn, string $filter, array $attributes = []): array {
        if (!$this->connection) {
            throw new \Exception("Not connected to LDAP");
        }

        // Execute LDAP search
        $result = ldap_search($this->connection, $baseDn, $filter, $attributes);
        if (!$result) {
            throw new \Exception("LDAP search failed: " . ldap_error($this->connection));
        }

        // Retrieve and return entries
        $entries = ldap_get_entries($this->connection, $result);
        return $entries;
    }

    /**
     * Adds a new LDAP entry.
     *
     * @param string $dn Distinguished Name of the new entry.
     * @param array $attributes Attributes for the new entry.
     * @return bool True if the addition is successful.
     * @throws \Exception If not connected or addition fails.
     */
    public function addEntry(string $dn, array $attributes): bool {
        if (!$this->connection) {
            throw new \Exception("Not connected to LDAP");
        }
        $result = ldap_add($this->connection, $dn, $attributes);
        if (!$result) {
            throw new \Exception("Failed to add entry: " . ldap_error($this->connection));
        }
        return true;
    }

    /**
     * Deletes an LDAP entry.
     *
     * @param string $dn Distinguished Name of the entry to delete.
     * @return bool True if deletion is successful.
     * @throws \Exception If not connected or deletion fails.
     */
    public function deleteEntry(string $dn): bool {
        if (!$this->connection) {
            throw new \Exception("Not connected to LDAP");
        }
        $result = ldap_delete($this->connection, $dn);
        if (!$result) {
            throw new \Exception("Failed to delete entry: " . ldap_error($this->connection));
        }
        return true;
    }

    /**
     * Modifies attributes of an LDAP entry.
     *
     * @param string $dn Distinguished Name of the entry to modify.
     * @param array $modifications Array of modifications in LDAP format.
     * @return bool True if modification is successful.
     * @throws \Exception If not connected or modification fails.
     */
    public function modifyEntry(string $dn, array $modifications): bool {
        if (!$this->connection) {
            throw new \Exception("Not connected to LDAP");
        }
        $result = ldap_modify($this->connection, $dn, $modifications);
        if (!$result) {
            throw new \Exception("Failed to modify entry: " . ldap_error($this->connection));
        }
        return true;
    }

    /**
     * Renames or moves an LDAP entry.
     *
     * @param string $dn Current distinguished name.
     * @param string $newRdn New Relative Distinguished Name.
     * @param string|null $newParent New parent DN (null if same parent).
     * @param bool $deleteOldRdn Whether to delete the old RDN.
     * @return bool True if rename/move successful.
     * @throws \Exception If not connected or operation fails.
     */
    public function renameEntry(string $dn, string $newRdn, ?string $newParent = null, bool $deleteOldRdn = true): bool {
        if (!$this->connection) {
            throw new \Exception("Not connected to LDAP");
        }
        $result = ldap_rename($this->connection, $dn, $newRdn, $newParent, $deleteOldRdn);
        if (!$result)
            throw new \Exception("Failed to rename/move entry: " . ldap_error($this->connection));
        
        return true;
    }

    /**
     * Closes the LDAP connection.
     */
    public function close(): void {
        if ($this->connection) {
            ldap_unbind($this->connection);
            $this->connection = null;
        }
    }

    /**
     * Generates an LDAP filter string based on attribute and value.
     *
     * @param string $attribute The LDAP attribute to filter on (e.g., 'cn', 'sAMAccountName').
     * @param string $value The value to match for the attribute.
     * @param string $operator The LDAP comparison operator (default '=')
     * @return string The LDAP filter string.
     */
    public function Filter(string $attribute, string $value, string $operator = '='): string {
        // Escape special characters in value
        $escapedValue = $this->escapeLDAPFilterValue($value);
        // Build the filter string
        return "($attribute$operator$escapedValue)";
    }

    /**
     * Escapes special characters in LDAP filter values.
     *
     * @param string $value The value to escape.
     * @return string The escaped value.
     */
    protected function escapeLDAPFilterValue(string $value): string {
        $search = ['\\', '*', '(', ')', "\0"];
        $replace = ['\\5c', '\\2a', '\\28', '\\29', '\\00'];
        return str_replace($search, $replace, $value);
    }

    /**
     * Builds a Distinguished Name (DN) string from components.
     *
     * @param array $components Associative array of DN components, e.g.,
     *                          ['CN' => 'User', 'OU' => 'Users', 'DC' => ['domain', 'com']]
     * @return string The constructed DN string.
     */
    public function buildDistinguishedName(array $components): string {
        $dnParts = [];
        foreach ($components as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $val) {
                    $dnParts[] = "$key=$val";
                }
            } else {
                $dnParts[] = "$key=$value";
            }
        }
        return implode(',', $dnParts);
    }
    /**
     * Retrieves an LDAP entry by its distinguished name.
     *
     * @param string $dn The distinguished name of the entry.
     * @param array $attributes Attributes to retrieve, default all.
     * @return array|null The LDAP entry or null if not found.
     */
    public function getEntry(string $dn, array $attributes = []): ?array {
        if (!$this->connection) {
            throw new \Exception("Not connected");
        }
        $result = ldap_read($this->connection, $dn, '(objectClass=*)', $attributes);
        if (!$result) return null;
        $entries = ldap_get_entries($this->connection, $result);
        return $entries[0] ?? null;
    }
}