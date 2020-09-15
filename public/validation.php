<?php
if (!isset($_SESSION)) {
  session_start();
}
ob_start();

// LOGIN USER
if (isset($_POST['login_user']) || isset($_SESSION["postdata"]["login_user"])) {
  // define variables and set to empty values
  $error = [];
  $username = $password = "";

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $_SESSION["postdata"] = $_POST;
    $_POST = array();
    header('Location: ' . $_SERVER['REQUEST_URI'],true,303);
    exit;
  } elseif (array_key_exists('postdata', $_SESSION)) {
    $username = trim($_SESSION["postdata"]["username"]);
    $password = trim($_SESSION["postdata"]["password"]);

    include 'connect.php';

    try {
      $query_sql = "SELECT username, hashed_password FROM Users WHERE username=:username LIMIT 1";
      $stmt = $connectedDB->prepare($query_sql);
      $stmt->execute([':username' => $username]);
      $user = $stmt->fetch();
    } catch(PDOException $e) {
      echo "Error: " . $e->getMessage();
    }

    if (password_verify($password, $user["hashed_password"])) {
      session_regenerate_id(true);
      $_SESSION['username'] = $username;
      header('location: dashboard.php');
      exit;
    } else {
      $error["generic"] = "Mauvais utilisateur ou mot de passe";
    }
    unset($_SESSION["postdata"], $password);
  }
}
?>