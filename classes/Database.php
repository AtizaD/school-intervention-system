<?php
/**
 * Database Class
 * Handles database operations with PDO
 */

class Database {
    private $pdo;
    private $stmt;
    private $error;

    public function __construct() {
        $this->pdo = getDatabaseConnection();
    }

    /**
     * Prepare SQL statement
     */
    public function query($sql) {
        $this->stmt = $this->pdo->prepare($sql);
        return $this;
    }

    /**
     * Bind values to prepared statement
     */
    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
        return $this;
    }

    /**
     * Execute prepared statement
     */
    public function execute() {
        try {
            return $this->stmt->execute();
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            error_log("Database Error: " . $this->error);
            return false;
        }
    }

    /**
     * Fetch all results as array
     */
    public function fetchAll() {
        $this->execute();
        return $this->stmt->fetchAll();
    }

    /**
     * Fetch single result
     */
    public function fetch() {
        $this->execute();
        return $this->stmt->fetch();
    }

    /**
     * Get row count
     */
    public function rowCount() {
        return $this->stmt->rowCount();
    }

    /**
     * Get last inserted ID
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit() {
        return $this->pdo->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollBack() {
        return $this->pdo->rollBack();
    }

    /**
     * Get last error
     */
    public function getError() {
        return $this->error;
    }

    /**
     * Select records with conditions
     */
    public function select($table, $columns = '*', $where = '', $params = []) {
        $sql = "SELECT $columns FROM $table";
        if (!empty($where)) {
            $sql .= " WHERE $where";
        }

        $this->query($sql);

        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $this->bind($key, $value);
            }
        }

        return $this->fetchAll();
    }

    /**
     * Insert record
     */
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";

        $this->query($sql);

        foreach ($data as $key => $value) {
            $this->bind(":$key", $value);
        }

        if ($this->execute()) {
            return $this->lastInsertId();
        }

        return false;
    }

    /**
     * Update record
     */
    public function update($table, $data, $where, $params = []) {
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "$key = :$key";
        }
        $set = implode(', ', $set);

        $sql = "UPDATE $table SET $set WHERE $where";

        $this->query($sql);

        foreach ($data as $key => $value) {
            $this->bind(":$key", $value);
        }

        foreach ($params as $key => $value) {
            $this->bind($key, $value);
        }

        return $this->execute();
    }

    /**
     * Delete record
     */
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM $table WHERE $where";

        $this->query($sql);

        foreach ($params as $key => $value) {
            $this->bind($key, $value);
        }

        return $this->execute();
    }
}
