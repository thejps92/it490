<?php
require (__DIR__ . "/lib/Deployer.php");
/**
 * Get input from the user
 * @param $prompt
 * @return string
 */
function getInput($prompt)
{
    echo $prompt;
    $input = trim(fgets(STDIN));
    return $input;
}

/**
 * Main function
 * runs the deployer and prompts the user for input
 */
function main()
{
    $deploy = new Deployer();

    while (true) {
        echo "Select an option:\n";
        echo "1. Deploy from environment\n";
        echo "2. Rollback version\n";
        echo "3. Rollback package\n";
        echo "4. Exit\n";

        $choice = getInput("Enter your choice (1-4): ");

        switch ($choice) {
            case '1':
                $environment = getInput("Enter the environment to deploy from (dev or qa): ");
                $deploy->deploy_from($environment);
                break;
            case '2':
                $version_id = getInput("Enter the version ID to rollback: ");
                $deploy->rollback_version($version_id);
                break;
            case '3':
                $package_name = getInput("Enter the package name to rollback: ");
                $deploy->rollback_package($package_name);
                break;
            case '4':
                echo "Exiting...\n";
                exit(0);
            default:
                echo "Invalid choice. Please try again.\n";
        }
    }
}

main();
