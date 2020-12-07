<?php
// Analyse le fichier ini et le transformer en variables
$ini = parse_ini_file('./utils/config/app.ini.php');
$DB_host = $ini['db_host'];
$DB_name = $ini['db_name'];
$DB_user = $ini['db_username'];
$DB_password = $ini['db_password'];

try {
  // Tente de se connecter à la base de données avec le charset SQL en utilsant PDO
  $connectedDB = new PDO("mysql:host=$DB_host;dbname=$DB_name;charset=utf8", $DB_user, $DB_password);
  // Change le mode d'erreur PDO à Exception, afin de pouvoir les voir
  $connectedDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // S'il y a une erreur de type PDOException, afficher le message d'erreur
} catch(PDOException $e) {
  echo 'Error: ' . $e->getMessage();
}
?>
