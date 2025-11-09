<?php
namespace WebOffice\table;
use WebOffice\Database, WebOffice\Config, PDO;
class SupportTickets{
    public Database $db;
    public function __construct() {
        $config = new Config();
        $this->db = new Database($config->read('mysql','host'),
    $config->read('mysql','user'),
    $config->read('mysql','psw'),
    $config->read('mysql','db'));
    }
    /**
     * Returns an array data of ticket
     * @param string $ticketID Ticket ID
     * @return array|bool Ticket ID
     */
    public function getTicket(string $ticketID): array|bool{
        $results = $this->db->fetch("SELECT * FROM support_tickets WHERE ticket_id=:ticket_id",['ticket_id'=>$ticketID],PDO::FETCH_ASSOC);
        return $results;
    }
}