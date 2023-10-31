
<?php

// Turn error reporting on
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Pull in db.php so we can access the variables from it
require_once(__DIR__ . "/../lib/db.php");

use Project\db;

$count = 0;
try {
    foreach (glob(__DIR__ . "/*.sql") as $filename) {

        $sql[$filename] = file_get_contents($filename);
    }

    if (isset($sql) && $sql && count($sql) > 0) {
        echo "Found " . count($sql) . " files...\n";
        ksort($sql);
        $db = new db();
        $stmt = $db->prepare("show tables");
        $stmt->execute();
        $count++;
        $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $t = [];
        foreach ($tables as $row) {
            foreach ($row as $key => $value) {
                array_push($t, $value);
            }
        }
        foreach ($sql as $key => $value) {
            echo "Running: $key\n";
            echo "--------------------------------\n";
            echo $value . "\n";
            echo "--------------------------------\n";
            $lines = explode("(", $value, 2);
            if (count($lines) > 0) {
                $line = $lines[0];
                $line = preg_replace('!\s+!', ' ', $line);
                $line = str_ireplace("create table", "", $line);
                $line = str_ireplace("if not exists", "", $line);
                $line = str_ireplace("`", "", $line);
                $line = trim($line);
                if (in_array($line, $t)) {
                    echo "Blocked from running, table found in 'show tables' results. [This is ok, it reduces redundant DB calls]\n\n";
                    continue;
                }
            }
            $stmt = $db->prepare($value);
            try {
                $result = $stmt->execute();
            } catch (PDOException $e) {
                //
            }
            $count++;
            $error = $stmt->errorInfo();
            echo "Status: " . ($error[0] === "00000" ? "Success" : "Error") . "\n";
            echo var_export($error, true) . "\n";
            echo "\n";
        }
        echo "Init complete, used approximately $count db calls.\n";
    } else {
        echo "Didn't find any files, please check the directory/directory contents/permissions (note files must end in .sql)\n";
    }
    $db = null;
} catch (Exception $e) {
    echo $e->getMessage();
    exit("Something went wrong");
}
