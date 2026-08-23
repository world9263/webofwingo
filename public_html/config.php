<?php
/*
This file contains database config.phpuration assuming you are running mysql using user "root" and password ""
*/

define('DB_SERVER', $_ENV['MYSQLHOST'] ?? 'localhost');
if (!defined('DB_PORT')) { define('DB_PORT', $_ENV['MYSQLPORT'] ?? '3306'); }
define('DB_USERNAME', $_ENV['MYSQLUSER'] ?? 'aviatorp_demo1');
define('DB_PASSWORD', $_ENV['MYSQLPASSWORD'] ?? 'aviatorp_demo1');
define('DB_NAME', $_ENV['MYSQLDATABASE'] ?? 'aviatorp_demo1');

// Try connecting to the Database
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);

//Check the connection
if($conn == false){
    dir('Error: Cannot connect');
    Echo"Fail";
}

?>