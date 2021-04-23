<?php
    declare(strict_types=1); // declare strict types
    function _help(string $self, string $error_message = "", bool $override_error_message = FALSE): void { // define the help function
        $usage = <<<ENDHELP
Usage: php $self <command> [args]

Available Commands:
    help    Displays this help message and exits.
    dump    Dumps the database structure and data, minus anything related to
            the MyPHPFramework structure itself. Requires config file name
            parameter to be provided.
    restore Restores the database structure and data, minus anything related to
            the MyPHPFramework structure itself. Requires config file name
            parameter to be provided.
            BEWARE: only data dumped by the `mpf dump` tool will be handled
            correctly, the user is the solely responsible entity for any data
            corrupted or lost due to misuse of this tool!
    module  Performs the <action> with the modules. Requires first argument
            to be present and a valid action.

Available Module Actions:
    list    Lists all installed modules and their versions.
    add     Downloads a framework module, an optional version can be
            specified, or a "?" can be used to list all available versions.
    update  Updates all the modules unless a list of modules is specified.
    remove  Deletes a module. Warning: no other action is performed, for
            example the inclusion of the module in the code is not reverted.


ENDHELP;
        if (strtoupper(substr(PHP_OS, 0, 3)) === "WIN") { // shame on you Windows, you don't have easy error handling
            if(!$override_error_message) echo $usage; // dump help message to the console (only if override is disabled)
            if(!empty($error_message)) { // check and write an additional error message
                echo "Error:";
                echo $error_message;
            }
        } else { // yuppie, we're running under *nix!
            $handler = fopen("php://stderr", "w"); // open standard error
            if(!$override_error_message) fwrite($handler, $usage); // write help message (only if override is disabled)
            if(!empty($error_message)) { // check and write an additional error message
                fwrite($handler, "Error:\n".$error_message);
            }
            fclose($handler); // close the handler
        }
        exit(-1);
    }
    function required_argument(?string $arg): bool { // common parameter check function
        return isset($arg) && !empty($arg) ? TRUE : FALSE;
    }
    function include_db_config(array $argv): void { // database file check function
        if(sizeof($argv) < 3 || !required_argument($argv[2])) _help($argv[0], "Database configuration file not provided!\n\n"); // check config file presence
        require($argv[2]); // require the PHP file, getting the config variables
        if(
            !isset($global_config) ||
            !array_key_exists("db", $global_config) ||
            !array_key_exists("host", $global_config["db"]) ||
            !array_key_exists("port", $global_config["db"]) ||
            !array_key_exists("user", $global_config["db"]) ||
            !array_key_exists("pass", $global_config["db"]) ||
            !array_key_exists("name", $global_config["db"])
        ) _help($argv[0], "Database configuration not present in the provided file!\n\n"); // check for presence of all required database connection parameters
    }
    function _cache(array $cached_modules): void {
        if(is_dir(".cache")) {}
    }

    if(sizeof($argv) < 2 || !required_argument($argv[1])) _help($argv[0], "No <command> provided!\n\n"); // check for user input presence
    switch($argv[1]) { // act according to said input
        case "help":
            _help($argv[0]);
        case "dump":
            include_db_config($argv);
            break;
        case "restore":
            include_db_config($argv);
            break;
        case "module":
            if(sizeof($argv) < 3 || !required_argument($argv[2])) _help($argv[0], "Module <action> not provided!\n\n"); // check module action presence
            switch($argv[2]) {
                case "list":
                    $cache = []; // initialize cache
                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_USERAGENT, "MyPHPFramework `mpf` CLI tool client"); // set the user agent (required on GitHub)
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // return an incapsulated string result
                    curl_setopt($curl, CURLOPT_URL, "https://api.github.com/orgs/myphpfw/repos"); // get all the org repo from GH's APIs
                    $api_repos = json_decode(curl_exec($curl), TRUE);
                    foreach($api_repos as $repo) { // loop through the repositories
                        if($repo["id"] === 326477370) continue; // exclude the parent (myphpfw) core repository
                        print($repo["name"].":\n");
                        curl_setopt($curl, CURLOPT_URL, "https://api.github.com/repos/myphpfw/".$repo["name"]."/git/refs/tags"); // grab the tags from the APIs
                        $api_repo_tags = json_decode(curl_exec($curl), TRUE);
                        foreach($api_repo_tags as $key => $tag) { // list the tags (versions) of each module, with a maximum of 5 items
                            if(strcmp(gettype($key), "integer") === 0 && $key < 5) print("  ".basename($tag["ref"], "/")."\n"); // from "refs/tags/x.x.x" print only "x.x.x"
                        }
                    }
                    curl_close($curl); // close the curl handler
                    break;
                case "add":
                    if(sizeof($argv) < 4 || !required_argument($argv[3])) _help($argv[0], "Module to add not provided!\n\n"); // check module presence
                    break;
                case "update":
                    break;
                case "remove":
                    if(sizeof($argv) < 4 || !required_argument($argv[3])) _help($argv[0], "Module to remove not provided!\n\n"); // check module presence
                    break;
                default:
                    _help($argv[0], "Invalid module <action> provided!\n\n");
            }
            break;
        default:
            _help($argv[0], "An invalid input was provided.\nKindly check your previous command.\n\n");
    }
