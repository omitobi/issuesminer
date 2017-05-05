<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 20/03/2017
 * Time: 12:41
 */


/**
* Start the server with 'php serve 8001' the port can be changed
* Kill the process on the terminal 'ps -ef | grep php'
* Then pick the process_id, and use 'kill [:process_id]' or 'kill -9 [:process_id] to force kill'
*/

$cust_port = isset($argv[1]) ? escapeshellarg($argv[1]) : 8001;
$port = $cust_port;
$message = "Started server on localhost:{$port}";
$comm = system("php -S localhost:{$port} -t ./public 2>&1", $output);
//print_r ("Started app on localhost:{$port} at ".time());
//print_r ($output);

exit($comm);