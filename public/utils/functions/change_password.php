<?php
  if (isset($_POST['change_password']) || isset($_SESSION['postdata']['change_password'])) {

    // Si la requête est faite via POST, mettre les variables POST dans un array dans SESSION
    // puis retourner à la page qui a fait la requête.
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $_SESSION['postdata'] = $_POST;
      $_POST = array();
      header('Location: ' . $_SERVER['REQUEST_URI'],true,303);
      exit;

    // Si l'array "postdata" existe, changer les variables pour les valeurs entrée par l'utilisateur
    } elseif (array_key_exists('postdata', $_SESSION)) {
      // Définir les variables et les mettre vides
      $error = [];

      $username = trim($_SESSION['postdata']['username']);
      $password = trim($_SESSION['postdata']['password']);
      $confirm_password = trim($_SESSION['postdata']['confirm-password']);

      if (isset($password)) {

        // Vérifie si le mot de passe est au moins 8 caractères
        if (strlen($password) < 8) {
          $error['password'] = true;

        // Vérifie si le mot de passe est moins de 72 caractères
        } elseif (strlen($password) > 72) {
          $error['password'] = true;

        // Vérifie si le mot de passe a au moins une majuscule, une minuscule, un chiffre et un caractère spécial
        } elseif (!preg_match("/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[!@#$%?&*()=+_,\'.\";:{}<>[\]\/\\|\^`~\-]).*$/", $password)) {
          $error['password'] = true;
        }
      }

      // Si le mot de passe n'a pas d'erreur, afficher si les mots de passe ne correspondent pas.
      if (empty($error['password'])) {
        if ($confirm_password != $password) {
          $error['confirmPassword'] = 'Les mots de passe ne correspondent pas';
        }
      }

      include( UTIL_CONNECT );

      // S'il n'y a aucune erreur
      if (count($error) == 0) {
        try {
          // Encrypter le mot de passe
          $hashed_password = password_hash($password, PASSWORD_DEFAULT, ['cost' => 11]);
          $sql_query = "UPDATE Users
                        SET    hashed_password = :hashed_password
                        WHERE  username = :username";
          $stmt = $connectedDB->prepare($sql_query);
          $stmt->execute([
            ':hashed_password' => $hashed_password,
            ':username' => $username
          ]);
          $change_success = true;

        } catch(PDOException $e) {
          echo 'Error: ' . $e->getMessage();
        }
      }

      // Déconnecter la base de données, détruire les variables
      $connectedDB = null;
      unset($_SESSION['postdata'], $password, $confirm_password);
    }
  }
?>
