<?php
namespace WebOffice;

use Exception, PDO, PDOException, WebOffice\Zip;
class Database{
    protected PDO|null $db=null;
    /**
     * Creates a Database connection with MySQL
     * @param string $host Host
     * @param string $user Username
     * @param string $psw Password
     * @param string $db Database name
     * @throws \Exception
     */
    public function __construct(string $host, string $user, string $psw, string $db='') {
        // Create initial PDO connection without database to check/create database if needed
        $dsn = "mysql:host=$host;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $psw, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        if ($db) {
            // Check if the database exists
            $stmt = $pdo->query("SHOW DATABASES LIKE '".strtolower($db)."'");
            if ($stmt->rowCount() == 0) {
                // Create the database if it doesn't exist
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `".strtolower($db)."`");
            }
            // Connect to the specific database
            $this->db = new PDO("mysql:host=$host;dbname=".strtolower($db).";charset=utf8mb4", $user, $psw, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
        } else throw new Exception('You must have a database');
    }
    /**
     * Creates a table with columns based on the provided data array.
     * 
     * @param string $table Table name
     * @param array $data Associative array where keys are column names and values are example data
     * @param PDO $pdo PDO database connection
     * @return bool TRUE if the table is connect, else FALSE
     */
    public function createTable(string $table, array $data): bool {
        // Start building the SQL statement
        $columns = [];
        foreach ($data as $columnName => $value) {
            $columnName = preg_replace('/[^a-zA-Z0-9_]/', '', $columnName);
            $columns[] = "`$columnName` $value";
        }

        $columnsSql = implode(', ', $columns);
        $sql = "CREATE TABLE IF NOT EXISTS `".strtolower($table)."` ($columnsSql)";

        // Execute the query
        try {
            $this->db->exec($sql);
            return true;
        } catch (PDOException $e) {
            echo "Error creating table: " . $e->getMessage();
            return false;
        }
    }
    /**
     * Removes table from database
     * @param string $table Table name
     * @return bool If TRUE successfully removed, else 
     */
    public function removeTable(string $table): bool{
        // Assuming $this->pdo is your PDO connection
        $sql = "DROP TABLE IF EXISTS " . $this->db->quote(strtolower($table));
        try {
            $this->db->exec($sql);
            return true;
        } catch (PDOException $e) {
            echo "Error dropping table: " . $e->getMessage();
            return false;
        }
    }
    /**
     * Executes a prepared SELECT statement and returns a single record.
     *
     * @param string $sql - The base SQL query without conditions
     * @param array $params - Associative array of parameters to bind to the query
     *                       (e.g., ['id' => 1])
     * @param string $conditions - Additional SQL conditions (e.g., 'status = :status AND age > :age')
     * @return array|false - Associative array of the fetched record or false if none found
     */
    public function fetch(string $sql, array $params = [], string $conditions = ''): mixed {
        // Append conditions if any
        $fullSql = $sql;
        if (!empty($conditions)) $fullSql .= " WHERE $conditions";
        $stmt = $this->db->prepare($fullSql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    /**
     * Executes a prepared SELECT statement and returns all matching records.
     *
     * @param string $sql - The base SQL query without conditions
     * @param array $params - Associative array of parameters to bind to the query
     *                       (e.g., ['status' => 'active'])
     * @param string $conditions - Additional SQL conditions (e.g., 'category = :category OR type = :type')
     * @return array - Array of associative arrays, each representing a record
     */
    public function fetchAll(string $sql, array $params = [], string $conditions = ''): array {
        // Append conditions if any
        $fullSql = $sql;
        if (!empty($conditions)) $fullSql .= " WHERE $conditions";
        $stmt = $this->db->prepare($fullSql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    /**
     * Inserts data into a specified table using PDO.
     *
     * @param string $table The name of the table where data will be inserted.
     * @param array $data An associative array of column => value pairs to insert.
     * @return bool Returns true on success or false on failure.
     */
    public function insert(string $table, array $data): bool {
        // Extract column names from the data array
        $columns = array_keys($data);
        // Create placeholders for prepared statement
        $placeholders = array_map(fn($column): string=>":$column", $columns);

        // Build the SQL INSERT statement
        $sql = "INSERT INTO {$table} (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";

        try {
            // Prepare the statement
            $stmt = $this->db->prepare($sql);

            // Bind values to placeholders
            foreach ($data as $column => $value) {
                $stmt->bindValue(":$column", $value);
            }
            // Execute the statement
            return $stmt->execute();
        } catch (PDOException $e) {
            // Handle exception (log error, rethrow, etc.)
            // For now, just return false
            return false;
        }
    }
    /**
     * Counts the number of rows in the specified table.
     *
     * @param string $table The name of the table to count rows from.
     * @return int The number of rows in the table.
     */
    public function count(string $table): int{
        $stmt = $this->db->prepare("SELECT * FROM {$table}");
        $stmt->execute();
        return $stmt->rowCount();
    }
    /**
     * Deletes a record from the specified table based on a condition.
     *
     * @param string $table The name of the table to delete from.
     * @param string $condition The condition for deletion (e.g., "id = :id").
     * @param array $params Associative array of parameters for the condition (e.g., ['id' => 5]).
     */
    public function delete(string $table, string $condition, array $params = []): void{
        $sql = "DELETE FROM {$table} WHERE {$condition}";
        $stmt = $this->db->prepare($sql);

        // Bind parameters if provided
        foreach ($params as $key => $value) 
            $stmt->bindValue(":$key", $value);
        $stmt->execute();
    }
    /**
     * Close PDO connection
     * @return void
     */
    public function close(): void{
        $this->db = null;
    }
    /**
     * Backs up mySQL database
     * @return bool TRUE if backed up, else FALSE
     */
    public function backup(string $path): bool{
        $tables = [];
        $stmt = $this->db->query("SHOW TABLES");
        while($row = $stmt->fetch(PDO::FETCH_NUM)) $tables[] = $row[0];
        $sqlDump = '';
        foreach($tables as $table){
              // Drop table if exists
            $sqlDump .= "DROP TABLE IF EXISTS `$table`;\n";

            // Get CREATE TABLE statement
            $stmtCreate = $this->db->query("SHOW CREATE TABLE `$table`");
            $rowCreate = $stmtCreate->fetch(PDO::FETCH_ASSOC);
            $createStmt = $rowCreate['Create Table'] . ";\n\n";

            $sqlDump .= $createStmt;

            // Get all data from the table
            $rows = $this->db->query("SELECT * FROM `$table`")->fetchAll();

            if ($rows) {
                $columns = array_keys($rows[0]);
                foreach ($rows as $rowData) {
                    $values = [];
                    foreach ($columns as $col) {
                        $values[] = $this->db->quote($rowData[$col]);
                    }
                    $sqlDump .= "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
                }
                $sqlDump .= "\n";
            }
            
        }

        

        $backupFile = "backup_" . date("Ymd_His") . ".sql";
        $zip = new Zip($path);
        $zip->addString($sqlDump,$backupFile);
        return $zip->close();
    }
}

