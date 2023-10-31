<?php
namespace Project;
use Exception;

class Config
{
    public $connection_string;
    public $dbhost;
    public $dbuser;
    public $dbpass;
    public $dbdatabase;
    public $key;
    public $secret;

    public function __construct()
    {
        // Load local .env file
        try{
            $dotenv = @parse_ini_file(__DIR__ . "/../../env.ini");
            //error_log("Dotenv: " . print_r($dotenv, true));
            // DB Credentials
            $this->dbhost = $dotenv["DB_HOST"];
            $this->dbuser = $dotenv["DB_USER"];
            $this->dbpass = $dotenv["DB_PASS"];
            $this->dbdatabase = $dotenv["DB_DATABASE"];
            $this->connection_string = 
                "mysql:
                host=$this->dbhost;
                dbname=$this->dbdatabase;
                charset=utf8mb4";

            // JWT Secret and Key
            $this->key = $dotenv["JWT_KEY"];
            $this->secret = $dotenv["JWT_SECRET"];
        } catch (Exception $e) {
            error_log("Error loading .env file: " . $e->getMessage());
        }
    }
}
