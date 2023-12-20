<?php

require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/Zip.php';
require_once __DIR__ . '/NiceSSH.php';
/**
 * Class Deployer
 * @package Deploy
 * @property Config $config
 * Deploys the application to a remote server
 */
class Deployer
{
    private $devConfig;
    private $qaConfig;
    private $prodConfig;
    // Target Folder to retrieve from and send to
    //TODO: Change this to the actual target folder: /var/www/audionook/
    private $targetDir = "/var/www/audionook/";
    // Local Folder to store on Deployment Server
    private $localDir = '';
    /**
     * Deployer constructor.
     */
    function __construct()
    {
        $this->devConfig = new Config('dev');
        $this->qaConfig = new Config('qa');
        $this->prodConfig = new Config('prod');
        $this->localDir = realpath(__DIR__ . "/../builds") . '/';
    }

    function deploy_from($environment)
    {
        switch ($environment) {
            case 'dev':
                echo "Deploying to QA\n";
                $this->deploy($environment, $this->devConfig, $this->qaConfig);
                break;
            case 'qa':
                echo "Deploying to PROD\n";
                $this->deploy($environment, $this->qaConfig, $this->prodConfig);
                break;
            default:
                echo 'Invalid environment';
                return;
        }
    }
    function deploy($environment, $srcConf, $destConf)
    {
        //retrieve and stores zips from source
        $packages = $this->retrieve_zips($environment, $srcConf);
        // send zips and unzip at destination
        $this->send_zips($packages, $destConf);
        echo "Deployed to $environment\n";
    }
    function retrieve_zips($environment, $srcConf)
    {
        $zip = new Zip();
        $ssh = new NiceSSH();
        $date = date("Y-m-d-H-i");

        // DB package retrieval
        $dbSess = $ssh->start_session($srcConf->dbHost, $srcConf->dbUser, $srcConf->dbPass);
        $dbPackage = $this->name_package($environment, 'db', $date);
        $create_db_zip = $zip->create_zip($this->targetDir, $dbPackage);
        $ssh->exec_command($dbSess, $create_db_zip);
        echo "Created $dbPackage \n";
        $ssh->retrieve_file($dbSess, dirname($this->targetDir) . '/' . $dbPackage, $this->localDir . $dbPackage);
        $ssh->remove_file($dbSess, dirname($this->targetDir) . '/' . $dbPackage);
        echo "Retrieved and Locally Stored DB Package\n";

        // DMZ package retrieval
        $dmzSess = $ssh->start_session($srcConf->dmzHost, $srcConf->dmzUser, $srcConf->dmzPass);
        $dmzPackage = $this->name_package($environment, 'dmz', $date);
        $create_dmz_zip = $zip->create_zip($this->targetDir, $dmzPackage);
        $ssh->exec_command($dmzSess, $create_dmz_zip);
        $ssh->retrieve_file($dmzSess, dirname($this->targetDir) . '/' . $dmzPackage, $this->localDir . $dmzPackage);
        $ssh->remove_file($dmzSess, dirname($this->targetDir) . '/' . $dmzPackage);
        echo "Retrieved and Locally Stored DMZ Package\n";

        // FE session and package retrieval
        $feSess = $ssh->start_session($srcConf->feHost, $srcConf->feUser, $srcConf->fePass);
        $fePackage = $this->name_package($environment, 'fe', $date);
        $create_fe_zip = $zip->create_zip($this->targetDir, $fePackage);
        $ssh->exec_command($feSess, $create_fe_zip);
        $ssh->retrieve_file($feSess, dirname($this->targetDir) . '/' . $fePackage, $this->localDir . $fePackage);
        $ssh->remove_file($feSess, dirname($this->targetDir) . '/' . $fePackage);
        echo "Retrieved and Locally Stored FE Package\n";
        // others
        // Store in DB
        $packages = [
            ['type' => 'db', 'package_name' => $dbPackage],
            ['type' => 'dmz', 'package_name' => $dmzPackage],
            ['type' => 'fe', 'package_name' => $fePackage]
        ];
        $this->insert_into_db($environment, $date, $packages);
        return $packages;
    }
    function name_package($environment, $package_type, $version_date)
    {
        $package_name = $environment . '_' . $package_type . '_' . $version_date . '.zip';
        return $package_name;
    }

    function insert_into_db($environment, $date, $packages)
    {
        require_once __DIR__ . '/utils/get_db.php';
        $db = get_db();
        try {
            $stmt = $db->prepare('INSERT INTO Versions (version_date) VALUES (?)');
            $stmt->bindParam(1, $date);
            $stmt->execute();

            $version_id = $db->lastInsertId();

            $stmt = $db->prepare('INSERT INTO Packages (version_id, environment, package_type, package_name) VALUES (?, ?, ?, ?)');
            foreach ($packages as $package) {
                $stmt->bindParam(1, $version_id);
                $stmt->bindParam(2, $environment);
                $stmt->bindParam(3, $package['type']);
                $stmt->bindParam(4, $package['package_name']);
                $stmt->execute();
            }
            echo "Insered in DB";
        } catch (PDOException $e) {
            error_log("Database error: " . var_export($e, true));
            $this == null;
        }
    }
    function send_zips($packages, $destConf)
    {
        $zip = new Zip();
        $ssh = new NiceSSH();

        // Sending to DB
        $dbSess = $ssh->start_session($destConf->dbHost, $destConf->dbUser, $destConf->dbPass);
        $ssh->remove_dir($dbSess, $this->targetDir); // clears out the target dir
        $ssh->send_file($dbSess, $this->localDir . $packages[0]['package_name'], $this->targetDir . $packages[0]['package_name']);
        $unzip_db = $zip->unzip($this->targetDir . $packages[0]['package_name'], $this->targetDir);
        $ssh->exec_commands($dbSess, $unzip_db);
        $ssh->remove_file($dbSess, $this->targetDir . $packages[0]['package_name']); 
        $ssh->exec_command($dbSess, 'cd ' . $this->targetDir . ' && composer install && echo \'' . $destConf->dbPass . '\' | sudo -S service apache2 restart');
        // Sending to DMZ
        $dmzSess = $ssh->start_session($destConf->dmzHost, $destConf->dmzUser, $destConf->dmzPass);
        $ssh->remove_dir($dmzSess, $this->targetDir);
        $ssh->send_file($dmzSess, $this->localDir . $packages[1]['package_name'], $this->targetDir . $packages[1]['package_name']);
        $unzip_dmz = $zip->unzip($this->targetDir . $packages[1]['package_name'], $this->targetDir);
        $ssh->exec_commands($dmzSess, $unzip_dmz);
        $ssh->remove_file($dmzSess, $this->targetDir . $packages[1]['package_name']);
        $ssh->exec_command($dmzSess, 'cd ' . $this->targetDir . ' && composer install && echo \'' . $destConf->dmzPass . '\' | sudo -S service apache2 restart');
        // Sending to Frontend
        $feSess = $ssh->start_session($destConf->feHost, $destConf->feUser, $destConf->fePass);
        $ssh->remove_dir($feSess, $this->targetDir);
        $ssh->send_file($feSess, $this->localDir . $packages[2]['package_name'], $this->targetDir . $packages[2]['package_name']);
        $unzip_fe = $zip->unzip($this->targetDir . $packages[2]['package_name'], $this->targetDir);
        $ssh->exec_commands($feSess, $unzip_fe);
        $ssh->remove_file($feSess, $this->targetDir . $packages[2]['package_name']);
        $ssh->exec_command($feSess, 'cd ' . $this->targetDir . ' && composer install && echo \'' . $destConf->fePass . '\' | sudo -S service apache2 restart');
    }

    function rollback_version($version_id)
    {
        require_once __DIR__ . '/utils/get_db.php';
        $db = get_db();
        try {
            $stmt = $db->prepare('SELECT environment, package_name FROM Packages WHERE version_id = ?');
            $stmt->bindParam(1, $version_id);
            $stmt->execute();
            $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $environment = $packages[0]['environment'];
            $destConf = null;
            switch ($environment) {
                case 'dev':
                    $destConf = $this->devConfig;
                    break;
                case 'qa':
                    $destConf = $this->qaConfig;
                    break;
                default:
                    echo 'Invalid environment';
                    return;
            }
            $this->send_zips($packages, $destConf);
        } catch (PDOException $e) {
            error_log("Database error: " . var_export($e, true));
            $this == null;
        }
    }

    function rollback_package($package_name){
        $parts = explode('_', $package_name);
        $environment = $parts[0];
        $cluster_type = $parts[1];
        $destConf = null;
        switch ($environment) {
            case 'dev':
                $destConf = $this->devConfig;
                break;
            case 'qa':
                $destConf = $this->qaConfig;
                break;
            default:
                echo 'Invalid environment';
                return;
        }
        $ssh = new NiceSSH();
        $zip = new Zip();
        $session = null;
        $pass = null;
        switch($cluster_type){
            case 'db':
                $session = $ssh->start_session($destConf->dbHost, $destConf->dbUser, $destConf->dbPass);
                $pass = $destConf->dbPass;
                break;
            case 'dmz':
                $session = $ssh->start_session($destConf->dmzHost, $destConf->dmzUser, $destConf->dmzPass);
                $pass = $destConf->dmzPass;
                break;
            case 'fe':
                $session = $ssh->start_session($destConf->feHost, $destConf->feUser, $destConf->fePass);
                $pass = $destConf->fePass;
                break;
            default:
                echo 'Invalid cluster type';
                return;
        }
        $ssh->remove_dir($session, $this->targetDir); // clears out the target dir
        $ssh->send_file($session, $this->localDir . $package_name, $this->targetDir . $package_name);
        $unzip_package = $zip->unzip($this->targetDir . $package_name, $this->targetDir);
        $ssh->exec_commands($session, $unzip_package);        
        $ssh->remove_file($session, $this->targetDir . $package_name);
        $ssh->exec_command($session, 'cd ' . $this->targetDir . ' && composer install && echo \'' . $pass . '\' | sudo -S service apache2 restart');

    }
}
/*
$deploy = new Deployer();
$packages = $deploy->deploy_from('dev');*/
