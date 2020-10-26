<?php
  session_start();
  ob_start();
  // S'il n'y a pas d'utilisateur connecté, inclure le script de déconnexion
  if (!$_SESSION['username']) {
    include( 'utils/logout.php' );
  }
  require( 'utils/config.php' );
  $page_title = 'Changer de mot de passe';
  $change_password = 'active';

  // Mettre, par défaut, la classe "text-muted" à l'aide du mot de passe
  $passwordClass = 'text-muted';

  if (isset($_POST['change_password']) || isset($_SESSION['postdata']['change_password'])) {
    // Définir les variables et les mettre vides
    $error = [];
    $username = $password = $confirm_password = '';

    // Si la requête est faite via POST, mettre les variables POST dans un array dans SESSION
    // puis retourner à la page qui a fait la requête.
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $_SESSION['postdata'] = $_POST;
      $_POST = array();
      header('Location: ' . $_SERVER['REQUEST_URI'],true,303);
      exit;
    // Si l'array "postdata" existe, changer les variables pour les valeurs entrée par l'utilisateur
    } elseif (array_key_exists('postdata', $_SESSION)) {
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

      // Si le mot de passe a une erreur, changer la classe de l'aide à "text-danger"
      if (isset($error['password'])) {
        $passwordClass = 'text-danger';
      }

      // Si le mot de passe n'a pas d'erreur, afficher si les mots de passe ne correspondent pas.
      if (empty($error['password'])) {
        if ($confirm_password != $password) {
          $error['confirmPassword'] = 'Les mots de passe ne correspondent pas';
        }
      }

      // Inclure la connexion à la base de données
      include( 'utils/connect.php' );

      // S'il n'y a aucune erreur
      if (count($error) == 0) {
        try {
          // Encrypter le mot de passe
          $hashed_password = password_hash($password, PASSWORD_DEFAULT, ['cost' => 11]);
          $update_sql = 'UPDATE Users
                        SET hashed_password = :hashed_password
                        WHERE username = :username';
          $stmt = $connectedDB->prepare($update_sql);
          $stmt->execute([
            ':username' => $username,
            ':hashed_password' => $hashed_password
          ]);
          $change_success = true;
        } catch(PDOException $e) {
          echo 'Error: ' . $e->getMessage();
        }

        // Déconnecter la base de données, détruire les variables
        $connectedDB = null;
        unset($_SESSION['postdata'], $password, $confirm_password);
      }

      unset($_SESSION['postdata'], $password, $confirm_password);
    }
  }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<!-- START INCLUDE META -->
<?php
include( VIEW_META );
?>
<!-- END INCLUDE META -->
</head>
<body class="bg-light">
<?php if ($change_success) { ?>
  <div class="alert alert-success popup-alert mt-4" role="alert">
    Mot de passe changé avec succès !
  </div>
<?php } ?>
<!-- START INCLUDE HEADER -->
<?php
include( VIEW_HEADER );
?>
<!-- END INCLUDE HEADER -->
<!-- START INCLUDE NAVIGATION -->
<?php
include( VIEW_NAVIGATION );
?>
<!-- END INCLUDE NAVIGATION -->
      <main class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
        <div class="container">
          <div class="card my-4 border-0 shadow">
            <div class="card-body">
              <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                <div class="form-group">
                  <label for="username">Nom d'utilisateur</label>
                  <select class="form-control" name="username" id="username" aria-describedby="usernameHelp" required>
                    <option value="" disabled>Sélectionner un utilisateur...</option>
    <?php
  include( 'utils/connect.php' );
  try {
    $query_sql = 'SELECT account_type FROM Users WHERE username = :username LIMIT 1';
    $stmt = $connectedDB->prepare($query_sql);
    $stmt->execute([
      ':username' => $_SESSION['username']
    ]);
    $user = $stmt->fetch();
  } catch(PDOException $e) {
    echo 'Error: ' . $e->getMessage();
  }
  if ($user['account_type'] == 0) {
    $stmt = $connectedDB->prepare('SELECT * FROM Users ORDER BY id ASC');
    $stmt->execute();
    foreach($stmt as $row) {
?>
                    <option value="<?= htmlspecialchars($row['username']) ?>" <?php if ($row['username'] == $_SESSION['username']) echo 'selected' ?>><?= htmlspecialchars($row['username']) ?></option>
<?php
    $connectedDB = null;
    }
  } else {
?>
                    <option value="<?= htmlspecialchars($_SESSION['username']) ?>" selected><?= htmlspecialchars($_SESSION['username']) ?></option>
<?php
  }
?>
                    </select>
                    <small class="form-text text-muted" id="usernameHelp">Si vous n'êtes pas administrateur, vous ne verrez que vous.</small>
                  </div>
                  <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input class="form-control" type="password" id="password" name="password" aria-describedby="passwordHelp" required>
                    <small class="form-text <?= $passwordClass ?>" id="passwordHelp">Doit contenir : de 8 à 72 caractères, 1 majuscule, 1 minuscule, 1 chiffre, 1 caractère spécial.</small>
                  </div>
                  <div class="form-group mb-0">
                    <label for="confirm-password">Confirmer le mot de passe</label>
                    <input class="form-control" type="password" id="confirm-password" name="confirm-password" aria-describedby="confirmPasswordHelp" required>
                    <small class="form-text text-danger" id="confirmPasswordHelp"><?= $error['confirmPassword'] ?>&nbsp;</small>
                  </div>
                  <div class="form-group">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="show-password">
                      <label class="form-check-label" for="show-password">Afficher les mots de passe</label>
                    </div>
                  </div>
                  <small class="text-danger"><?= $error['generic'] ?>&nbsp;</small>
                  <button class="btn btn-lg btn-outline-primary btn-block" type="submit" name="change_password">Changer le mot de passe</button>
                </form>
              </div>
            </div>
          </div>
        </main>
<!-- START INCLUDE FOOTER -->
<?php
include( VIEW_FOOTER );
?>
<!-- END INCLUDE FOOTER -->
  <script>
    document.querySelector('#show-password').addEventListener('click', toggleViewPassword);
    const passwordField = document.querySelector('#password');
    const confirmPasswordField = document.querySelector('#confirm-password');

    function toggleViewPassword(e) {
      const checkbox = e.target;
      if (checkbox.checked) {
        passwordField.setAttribute('type', 'text');
        confirmPasswordField.setAttribute('type', 'text');
      } else {
        passwordField.setAttribute('type', 'password');
        confirmPasswordField.setAttribute('type', 'password');
      }
    }
  </script>
</body>
</html>
