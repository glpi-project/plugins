<?php
/**
 * loadcsv.php
 *
 * This script loads a CSV file into
 * the database, it was originally
 * written to port the list maintained
 * by Indepnet, given to us in CSV format
 */

/**
 * Read command line options
 */
function parseOpts() {
    $options = [
        'hostname' => 'h',
        'username' => 'u',
        'password' => 'p',
        'database' => 'd',
        'filename' => 'f'
    ];

    $required = [
        'hostname',
        'username',
        'database',
        'filename'
    ];

    $missing = [];

    $opts = '';
    foreach ($options as $long => $short) {
        $opts .= $short.':';
    }
    $opts = getopt($opts);
    foreach ($options as $long => $short) {
        if (isset($opts[$short])) {
            $options[$long] = $opts[$short];
        } else {
            if (in_array($long, $required)) {
                $missing[$short] = $long;
            }
            $options[$long] = false;
        }
    }

    if (sizeof($missing) > 0) {
        echo "usage: php loadcsv.php -h mysql_hostname -d mysql_database -u mysql_username [-p mysql_password]\n";
        echo "Errors:\n";
        foreach ($missing as $short => $long) {
            echo '  - Missing required option -'.$short.' to set '.$long."\n";
        }
        die();
    }

    return $options;
}

/**
 * Ensure connection with MySQL
 */
function fetch_db_connection($hostname, $username, $password, $database) {
    $mysqli = mysqli_connect($hostname, $username, $password, $database);
    if (mysqli_connect_errno($mysqli)) {
        die('Failed to connect to MySQL:' . mysqli_connect_error());
    } else {
        echo "Connected to MySQL...\n";
    }
    return $mysqli;
}

/**
 * Parse CSV input with ';' delimiter
 */
function parse_csv($filename) {
    $content = file_get_contents($filename);
    if (!$content) {
        die('Cannot read CSV input.');
    } else {
        echo "Opened CSV...\n";
    }
    $content = str_getcsv($content, ';');

    $modules = array_map(function($line) {
        return str_getcsv($line, ";");
    }, file($filename));

    foreach ($modules as $k => $module) {
        $modules[$k] = [
            'xml_url' => $module[1],
            'date_added' => $module[2],
            'xml_crc' => $module[3],
            'active' => ($module[4] == 'yes' ? true : false),
            'date_updated' => $module[5]
        ];
    }

    return $modules;
}

/**
 * Prepare all the queries that are going to be
 * executed in order to load the content of the
 * csv file
 */
function buildQueries($modules) {
    $queries = [];
    foreach ($modules as $module) {
        $query = 'INSERT INTO plugin(xml_url, xml_crc, date_added, date_updated, active) VALUES (';
        $query .=         
                   '"'.$module['xml_url'].'",'.
                   '"'.$module['xml_crc'].'",'.
                   '"'.$module['date_added'].'",'.
                   '"'.$module['date_updated'].'",'.
                   ($module['active'] ? 1 : 0);
        $query .= ');'."\n";
        $queries[] = $query;
    }
    return $queries;
}

/**
 * Performs the load of the CSV file into the database
 */
function main() {
    $mysql;
    $opts = parseOpts();

    $hostname = $opts['hostname'];
    $username = $opts['username'];
    $password = ($opts['password'] != false ? $opts['password'] : '');
    $database = $opts['database'];
    $filename = $opts['filename'];

    $mysql = fetch_db_connection($hostname, $username, $password, $database);

    $modules = parse_csv($filename);
    $queries = buildQueries($modules);

    $count = 0;
    foreach ($queries as $query) {
        $res = $mysql->query($query);
        if ($res) {
            $count++;
        }
    }
    echo "Inserted ".$count." plugins in the database via CSV.\n";
}

main();