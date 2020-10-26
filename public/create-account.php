<?php
  session_start();
  ob_start();
  // S'il n'y a pas d'utilisateur connecté, inclure le script de déconnexion
  if (!$_SESSION['username']) {
    include( 'utils/logout.php' );
  }
  // Vérifier le niveau d'accès
  include( 'utils/connect.php' );
  try {
    $query_sql = 'SELECT account_type FROM Users WHERE username = :username LIMIT 1';
    $stmt = $connectedDB->prepare($query_sql);
    $stmt->execute([
      ':username' => $_SESSION["username"]
    ]);
    $user = $stmt->fetch();
  } catch(PDOException $e) {
    echo 'Error: ' . $e->getMessage();
  }
  if ($user['account_type'] != 0) {
    header('location: index.php');
    exit;
  }
  $connectedDB = null;
  require( 'utils/config.php' );
  $page_title = 'Créer un compte';
  $create_account = 'active';

  // Mettre, par défaut, la classe "text-muted" à l'aide du mot de passe et du nom d'utilisateur
  $usernameClass = 'text-muted';
  $passwordClass = 'text-muted';

  if (isset($_POST['create_user']) || isset($_SESSION['postdata']['create_user'])) {
    // Définir les variables et les mettre vides
    $error = [];
    $username = $password = $confirm_password = $account_type = $team = '';

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
      $account_type = trim($_SESSION['postdata']['account-type']);
      $team = trim($_SESSION['postdata']['team']);

      if (isset($username)) {
        // Vérifie si le nom d'utilisateur a seulement des lettres et des chiffres
        if (!preg_match("/^[a-zA-Z\d]*$/",$username)) {
          $error['username'] = true;
        // Vérifie si le nom d'utilisateur est moins de 255 caractères
        } elseif (strlen($username > 255)) {
          $error['username'] = true;
        }
      }

      // Si le nom d'utilisateur a une erreur, changer la classe de l'aide à "text-danger"
      if (isset($error['username'])) {
        $usernameClass = 'text-danger';
      }

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

      try {
        $query_sql = 'SELECT username FROM Users WHERE username=:username LIMIT 1';
        $stmt = $connectedDB->prepare($query_sql);
        $stmt->execute([
          ':username' => $username
        ]);
        $user = $stmt->fetch();
      } catch(PDOException $e) {
        echo 'Error: ' . $e->getMessage();
      }

      // Si l'utilisateur existe
      if ($user) {
        if (strtolower($user['username']) === strtolower($username)) {
          $error['generic'] = 'Le nom d\'utilisateur existe déjà';
        }
      }

      // S'il n'y a aucune erreur
      if (count($error) == 0) {
        try {
          // Encrypter le mot de passe
          $hashed_password = password_hash($password, PASSWORD_DEFAULT, ['cost' => 11]);
          $insert_sql = 'INSERT INTO Users (username, hashed_password, account_type, team)
                        VALUES (:username, :hashed_password, :account_type, :team)';
          $stmt = $connectedDB->prepare($insert_sql);
          $stmt->execute([
            ':username' => $username,
            ':hashed_password' => $hashed_password,
            ':account_type' => $account_type,
            ':team' => $team
          ]);
          $creation_success = true;
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
<?php if ($creation_success) { ?>
  <div class="alert alert-success popup-alert mt-4" role="alert">
    Compte créé avec succès !
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
          <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
            <div class="form-group">
              <label for="username">Nom d'utilisateur</label>
              <input class="form-control" type="text" id="username" name="username" aria-describedby="usernameHelp" required autofocus>
              <small class="form-text <?= $usernameClass ?>" id="usernameHelp">Peut seulement contenir des lettres sans accents et des chiffres.</small>
            </div>
            <div class="form-group">
              <label for="password">Mot de passe</label>
              <input class="form-control" type="password" id="password" name="password" aria-describedby="passwordHelp" required>
              <small class="form-text <?= $passwordClass ?>" id="passwordHelp">Doit contenir : de 8 à 72 caractères, 1 majuscule, 1 minuscule, 1 chiffre, 1 caractère spécial.</small>
            </div>
            <div class="form-group">
              <label for="confirm-password">Confirmer le mot de passe</label>
              <input class="form-control" type="password" id="confirm-password" name="confirm-password" aria-describedby="confirmPasswordHelp" required>
              <small class="form-text text-danger" id="confirmPasswordHelp"><?= $error['confirmPassword'] ?>&nbsp;</small>
            </div>
            <div class="row">
              <fieldset class="form-group col-sm-6">
                <legend>Type de compte</legend>
                <div class="form-check">
                  <input class="form-check-input" type="radio" id="admin" name="account-type" value="0" required>
                  <label class="form-check-label" for="admin">Administrateur</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" id="team-lead" name="account-type" value="1" required>
                  <label class="form-check-label" for="team-lead">Chef d'équipe</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" id="student" name="account-type" value="2" required>
                  <label class="form-check-label" for="student">Étudiant</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" id="client" name="account-type" value="3" required>
                  <label class="form-check-label" for="client">Client</label>
                </div>
              </fieldset>
              <fieldset class="form-group col-sm-6">
                <legend>Équipe</legend>
                <div class="form-check">
                  <input class="form-check-input" type="radio" id="no-team" name="team" value="0" required>
                  <label class="form-check-label" for="no-team">Aucune équipe</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" id="gestionnaires" name="team" value="1" required>
                  <label class="form-check-label" for="gestionnaires">Gestionnaires</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" id="graphistes" name="team" value="2" required>
                  <label class="form-check-label" for="graphistes">Graphistes</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" id="programmeurs"  name="team"value="3" required>
                  <label class="form-check-label" for="programmeurs">Programmeurs</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" id="integrateurs-web" name="team" value="4" required>
                  <label class="form-check-label" for="integrateurs-web">Intégrateurs web</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" id="integrateurs-video" name="team" value="5" required>
                  <label class="form-check-label" for="integrateurs-video">Intégrateurs vidéo</label>
                </div>
              </fieldset>
            </div>
            <div class="form-group">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="show-password">
                <label class="form-check-label" for="show-password">Afficher les mots de passe</label>
              </div>
            </div>
            <small class="text-danger"><?= $error['generic'] ?>&nbsp;</small>
            <button class="btn btn-lg btn-outline-primary btn-block" type="submit" name="create_user">Créer le compte</button>
          </form>
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
