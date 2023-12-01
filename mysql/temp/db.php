<?php

namespace Project;

use PDO;
use PDOException;
require_once(__DIR__ . "/Config.php");
use Project\Config;
class db extends PDO
{

    public function __construct()
    {
        $config = new Config();
        try {
            parent::__construct($config->connection_string, $config->dbuser, $config->dbpass);
            $this->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error: " . var_export($e, true));
            $this == null;
        }
    }
    public function exec_query($query, $params = [], $fetchMode = PDO::FETCH_ASSOC)
    {
        $stmt = $this->prepare($query);
        try {
            $r = $stmt->execute($params);
            if (strpos($query, 'INSERT') === 0 || strpos($query, 'UPDATE') === 0 || strpos($query, 'DELETE') === 0) {
                return $stmt->rowCount();
            } else {
                return $stmt->fetchAll($fetchMode);
            }
        } catch (PDOException $e) {
            error_log("Error executing query: " . $query);
            error_log("Query params: " . var_export($params, true));
            error_log("Error message: " . $e->getMessage());
            return false;
        }
    }

    public function last_insert_id()
    {
        return $this->lastInsertId();
    }
}
