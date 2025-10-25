<?php
namespace WebOffice\api\models;

use WebOffice\Database, WebOffice\Security;
use Exception;
include_once dirname(__DIR__,2)."/libs/db.lib.php";
class UsersModel extends Database{
    private Security $security;
    public function __construct(string $host, string $user, string $psw, string $db) {
        parent::__construct($host, $user, $psw, $db);
        $this->security = new Security();
    }
    /**
     * Get users based on filters
     * @param string $filters Associative array of column => value pairs
     * @param int $limit Limit of results
     * @param string $order Order of the results ('ASC' or 'DESC')
     * @return array
     */
    public function getUsers(string $filters = '', $limit = 0, $order = ''): array {
        $params = [];
        $conditions = [];
        $sql = "SELECT * FROM users";

        if (!empty($filters)) {
            $parts = preg_split('/(\|\||&&)/', $filters, -1, PREG_SPLIT_DELIM_CAPTURE);
            $logicOperators = [];

            // Loop through parts and separate conditions and operators
            for ($i = 0; $i < count($parts); $i++) {
                $part = trim($parts[$i]);
                if ($part === '||' || $part === '&&') {
                    // Store the operator for the next condition
                    $logicOperators[] = $part;
                } else {
                    // This is a condition in the form key=value
                    $v = explode('=', $part, 2);
                    if (count($v) == 2) {
                        $conditions[] = "{$v[0]} = ?";
                        $params[] = $v[1];
                    }
                }
            }
            
            // Build the WHERE clause with conditions and operators
            if (!empty($conditions)) {
                
                $sql .= ' WHERE ';
                for ($i = 0; $i < count($conditions); $i++) {
                    if ($i > 0) {
                        // Append the operator before the condition
                        $sql .= ' ' . $logicOperators[$i - 1] . ' ';
                    }
                    $sql .= $conditions[$i];
                }
            }
        }

        // Append ORDER BY clause
        if ($order !== '' && ($order === 'ASC' || $order === 'DESC')) {
            $sql .= " ORDER BY id " . strtoupper($order);
        }

        // Append LIMIT if specified
        if ($limit > 0) {
            $sql .= " LIMIT {$limit}";
        }

        // Fetch user results
        $results = $this->fetchAll($sql, $params);

        // Collect all usernames for MFA fetch
        $usernames = array_column($results, 'username');

        // Prepare placeholders for the MFA query
        $placeholders = rtrim(str_repeat('?,', count($usernames)), ',');


        // Fetch MFA status for all usernames
        if(empty($usernames)) return [];
        else{
            $mfaSql = "SELECT username, 2fa_secret, 2fa_enabled FROM mfa WHERE username IN ($placeholders)";
            $mfaResults = $this->fetchAll($mfaSql, $usernames);
            // Create an associative array for quick lookup
            $mfaMap = [];
            foreach ($mfaResults as $mfa) {
                $mfaMap[$mfa['username']] = [
                    '2fa_secret' => $mfa['2fa_secret'],
                    '2fa_enabled' => $mfa['2fa_enabled']
                ];
            }

            // Merge MFA data into user results
            foreach ($results as &$user) {
                if (isset($mfaMap[$user['username']])) {
                    $user = array_merge($user, $mfaMap[$user['username']]);
                } else {
                    // If no MFA info, set defaults or nulls
                    $user['2fa_secret'] = null;
                    $user['2fa_enabled'] = 0; // or false
                }
            }


            return array_map(function($row): array {
                unset($row['2fa_secret']);
                return array_filter($row, fn($key): bool => !is_int($key), ARRAY_FILTER_USE_KEY);
            }, $results) ?? [];
        }
    }
    public function postUsers(array $data): array|string{
        try{
            return $this->insert('users',$data);
        }catch(Exception $e){
            return $e->getMessage();
        }
    }

    public function putUsers(array $data, array $where): int|string{
        try{
            return $this->update('users',$data,$where);
        }catch(Exception $e){
            return $e->getMessage();
        }
    }
    public function deleteUsers(array $where): int|string{
        try{
            return $this->delete('users',$where)?1:0;
        }catch(Exception $e){
            return $e->getMessage();
        }
    }
}