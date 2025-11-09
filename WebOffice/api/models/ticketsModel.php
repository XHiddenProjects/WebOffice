<?php
namespace WebOffice\api\models;

use WebOffice\Database, WebOffice\Security;
use Exception;
include_once dirname(__DIR__,2)."/libs/db.lib.php";
class TicketsModel extends Database{
    private Security $security;
    public function __construct(string $host, string $user, string $psw, string $db) {
        parent::__construct($host, $user, $psw, $db);
        $this->security = new Security();
    }
    public function getTickets(string $filters, int $limit=0, string $order=''): array {
        $params = [];
        $conditions = [];
        $sql = "SELECT * FROM support_tickets";

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
        return $results ?: [];
    }
    /**
     * Creates a support ticket
     * @param array $data Ticket information
     * @return array|string Ticket success
     */
    public function postTickets(array $data): array|string{
        try{
            return $this->insert('support_tickets',$data);
        }catch(Exception $e){
            return $e->getMessage();
        }
    }
    /**
     * Updates the tickets
     * @param array $data Ticket data
     * @param array $where Where to update it
     * @return int|string
     */
    public function putTickets(array $data, array $where): int|string{
        try{
            return $this->update('support_tickets',$data,$where);
        }catch(Exception $e){
            return $e->getMessage();
        }
    }
}