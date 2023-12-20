<?php

class Config
{

    // Cluster credentials
    public $dbHost;
    public $dbUser;
    public $dbPass;

    public $dmzHost;
    public $dmzUser;
    public $dmzPass;

    public $feHost;
    public $feUser;
    public $fePass;

    //Deployment DB Credentials
    public $connection_string;
    public $dbhost;
    public $dbuser;
    public $dbpass;
    public $dbdatabase;

    public function __construct($environment)
    {
        try {
            $ini = parse_ini_file(__DIR__ . "/deploy.ini", true);
            switch ($environment) {
                case "dev":
                    // Database VM
                    $this->dbHost = $ini['dev']['DB_HOST'];
                    $this->dbUser = $ini['dev']['DB_USER'];
                    $this->dbPass = $ini['dev']['DB_PASS'];
                    // DMZ VM
                    $this->dmzHost = $ini['dev']['DMZ_HOST'];
                    $this->dmzUser = $ini['dev']['DMZ_USER'];
                    $this->dmzPass = $ini['dev']['DMZ_PASS'];
                    // Frontend VM
                    $this->feHost = $ini['dev']['FE_HOST'];
                    $this->feUser = $ini['dev']['FE_USER'];
                    $this->fePass = $ini['dev']['FE_PASS'];
                    break;
                case "qa":
                    // Database VM
                    $this->dbHost = $ini['qa']['DB_HOST'];
                    $this->dbUser = $ini['qa']['DB_USER'];
                    $this->dbPass = $ini['qa']['DB_PASS'];
                    // DMZ VM
                    $this->dmzHost = $ini['qa']['DMZ_HOST'];
                    $this->dmzUser = $ini['qa']['DMZ_USER'];
                    $this->dmzPass = $ini['qa']['DMZ_PASS'];
                    // Frontend VM
                    $this->feHost = $ini['qa']['FE_HOST'];
                    $this->feUser = $ini['qa']['FE_USER'];
                    $this->fePass = $ini['qa']['FE_PASS'];
                    break;
                case "prod":
                    // Database VM
                    $this->dbHost = $ini['prod']['DB_HOST'];
                    $this->dbUser = $ini['prod']['DB_USER'];
                    $this->dbPass = $ini['prod']['DB_PASS'];
                    // DMZ VM
                    $this->dmzHost = $ini['prod']['DMZ_HOST'];
                    $this->dmzUser = $ini['prod']['DMZ_USER'];
                    $this->dmzPass = $ini['prod']['DMZ_PASS'];
                    // Frontend VM
                    $this->feHost = $ini['prod']['FE_HOST'];
                    $this->feUser = $ini['prod']['FE_USER'];
                    $this->fePass = $ini['prod']['FE_PASS'];
                    break;
                case "db":
                    $this->dbhost = $ini['Database']["DB_HOST"];
                    $this->dbuser = $ini['Database']["DB_USER"];
                    $this->dbpass = $ini['Database']["DB_PASS"];
                    $this->dbdatabase = $ini['Database']["DB_DATABASE"];
                    $this->connection_string = "mysql:host=$this->dbhost;dbname=$this->dbdatabase;charset=utf8mb4";
                    break;
                default:
                    throw new Exception("Invalid environment");
            }
        } catch (Exception $e) {
            error_log("Error loading deploy.ini file: " . $e->getMessage());
        }
    }
}
