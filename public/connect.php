<?php
$ini = parse_ini_file('config/app.ini.php');
$DB_host = $ini['db_host'];
$DB_name = $ini['db_name'];

try {
  $connectedDB = new PDO("mysql:host=$DB_host;dbname=$DB_name;charset=utf8", $ini['db_username'], $ini['db_password']);
  // set the PDO error mode to exception
  $connectedDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
  echo 'Error: ' . $e->getMessage();
}
?>
