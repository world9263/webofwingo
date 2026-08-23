<?php
// Define database credentials
$servername = $_ENV['MYSQLHOST'] ?? 'localhost';
$dbport = $_ENV['MYSQLPORT'] ?? '3306';
$username = $_ENV['MYSQLUSER'] ?? 'aviatorp_demo1';
$password = $_ENV['MYSQLPASSWORD'] ?? 'aviatorp_demo1';
$dbname = $_ENV['MYSQLDATABASE'] ?? 'aviatorp_demo1';

// Create PDO object and connect to database
$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

// Define SQL statements
$sqlStatements = [
  "TRUNCATE TABLE `abbetrec`",
  "TRUNCATE TABLE `abbetting`",
  "TRUNCATE TABLE `beconebetrec`",
  "TRUNCATE TABLE `beconebetting`",
  "TRUNCATE TABLE `betrec`",
  "TRUNCATE TABLE `betting`",
  "TRUNCATE TABLE `crashbetrecord`",
  "TRUNCATE TABLE `crashgamerecord`",
  "TRUNCATE TABLE `emredbetrec`",
  "TRUNCATE TABLE `emredbetting`",
  "TRUNCATE TABLE `giftrec`",
  "TRUNCATE TABLE `saprebetrec`",
  "TRUNCATE TABLE `saprebetting`",
  "TRUNCATE TABLE `verify`",
  "TRUNCATE TABLE `vipbetrec`",
  "TRUNCATE TABLE `vipbetting`"
];

// Execute SQL statements
foreach ($sqlStatements as $sql) {
  $stmt = $conn->prepare($sql);
  $stmt->execute();
}
?>
