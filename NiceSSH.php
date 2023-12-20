<?php
/**
 * Class NiceSSH
 * refrences: https://www.php.net/manual/en/ref.ssh2.php
 */
class NiceSSh{
    /**
     * Connect to server via SSH
     * @param string $host
     * @param string $user
     * @param string $pass
     * @return resource
     */
    public function start_session($host, $user, $pass){
        echo 'Connecting to ' . $user . '@' . $host . "\n";
        // starts session with an SSH server
        // https://www.php.net/manual/en/function.ssh2-connect.php
        $session = ssh2_connect($host, 22);

        // ssh2_auth_password() - authenticate over SSH using a plain password
        // https://www.php.net/manual/en/function.ssh2-auth-password.php
        if(!ssh2_auth_password($session, $user, $pass)){
            die('Authentication failed for ' . $user . '@' . $host . "\n");
        }
        echo "Connected! \n";
        return $session;
    }
    /**
     * Execute a command on a remote server
     * @param resource $session
     * @param string $command
     */
    public function exec_command($session,$command){
        // executes a command on a remote server
        // https://www.php.net/manual/en/function.ssh2-exec.php
        echo "executing: " . $command . "\n";
        $stream = ssh2_exec($session, $command);
        // Idk how this works it just does: https://www.php.net/manual/en/function.stream-set-blocking.php
        stream_set_blocking($stream, true);
        $output = stream_get_contents($stream);
        fclose($stream);
        echo $output;
    }
    /**
     * Execute multiple commands on a remote server
     * @param resource $session
     * @param array $commands
     */
    public function exec_commands($session,$commands){
        // executes multiple commands on a remote server
        // https://www.php.net/manual/en/function.ssh2-exec.php
        foreach($commands as $command){
            $this->exec_command($session, $command);
        }
    }

    /**
     * Create a ZIP file of a directory on a remote server
     * @param resource $session
     * @param string $dir
     * @param string $zip_file
     */
    public function retrieve_file($session, $remote_file, $local_file){
        // retrieves a file from the remote server
        // https://www.php.net/manual/en/function.ssh2-scp-recv.php
        if(!ssh2_scp_recv($session, $remote_file, $local_file)){
            die('Failed to retrieve remote file ' . $remote_file . "\n");
        }
    }

    /**
     * Send a file to a remote server
     * @param resource $session
     * @param string $local_file
     * @param string $remote_file
     */
    public function send_file($session, $local_file, $remote_file){
        // sends a file from the local server to the remote server
        // Referenced: https://www.php.net/manual/en/function.ssh2-sftp.php
        // Referenced: https://www.codexpedia.com/php/php-ssh2-upload-and-download-files-through-sftp/
        // Referenced: https://www.php.net/manual/en/function.ssh2-scp-send.php
        echo "Attempting to send: " . $local_file . "\n";
        if(!ssh2_scp_send($session, $local_file, $remote_file)){
            die('Failed to send local file ' . $local_file . "\n");
        }
        echo "Sent: " . $local_file . "\n";
    }

    public function remove_file($session, $file){
        // Removes contents of specified remote path
        // https://www.php.net/manual/en/function.ssh2-sftp-unlink.php
        $sftp = ssh2_sftp($session);
        ssh2_sftp_unlink($sftp, $file);
    }

    public function remove_dir($session, $dir){
        // removes a directory from the remote server
        // https://www.php.net/manual/en/function.ssh2-sftp-rmdir.php
       // Changed the to use exec_command() instead of ssh2_sftp_rmdir() because it doesn't work on non-empty directories
        /* $sftp = ssh2_sftp($session);
        rmdir("ssh2.sftp://$sftp$dir");*/
        echo "removing $dir";
        $command = "rm -rf {$dir}*";
        ssh2_exec($session, $command);
    }

    public function set_chmod_permissions($session, $path, $permissions)
    {
        $command = "chmod {$permissions} {$path}";
        $this->exec_command($session, $command);
    }
}
