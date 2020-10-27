<?php
  if (isset($_POST['login_user']) || isset($_SESSION['postdata']['login_user'])) {

    // Si la requête est faite via POST, mettre les variables POST dans un array dans SESSION
    // puis retourner à la page qui a fait la requête.
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $_SESSION['postdata'] = $_POST;
      $_POST = array();
      header('Location: ' . $_SERVER['REQUEST_URI'],true,303);
      exit;

    } elseif (array_key_exists('postdata', $_SESSION)) {
      // Définir les variables et les mettre vides
      $error = [];

      $username = trim($_SESSION['postdata']['username']);
      $password = trim($_SESSION['postdata']['password']);

      include( UTIL_CONNECT );

      // S'il n'y a aucune erreur
      if (count($error) == 0) {

        // Récupérer l'utilisateur
        try {
          $sql_query = "SELECT id,
                               username,
                               hashed_password,
                               team
                        FROM   Users
                        WHERE  username = :username
                        LIMIT  1";
          $stmt = $connectedDB->prepare($sql_query);
          $stmt->execute([
            ':username' => $username
          ]);
          $user = $stmt->fetch();

        } catch(PDOException $e) {
          echo 'Error: ' . $e->getMessage();
        }

        // Si les identifiants sont corrects, générer la session et naviguer au tableau de bord
        if (password_verify($password, $user['hashed_password'])) {
          session_regenerate_id(true);
          $_SESSION['id'] = $user['id'];
          $_SESSION['team'] = $user['team'];
          $_SESSION['username'] = $username;
          header('location: index.php');
          exit;

        } else {
          $error['generic'] = 'Mauvais utilisateur ou mot de passe';
        }
      }

      // Déconnecter la base de données, détruire les variables
      $connectedDB = null;
      unset($_SESSION['postdata'], $password, $confirm_password);
    }
  }
?>
