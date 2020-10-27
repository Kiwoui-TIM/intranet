<?php
  session_start();
  ob_start();

  // Importer les constantes et changer le titre de la page
  require( 'utils/config.php' );
  $page_title = CHANGE_PWD_TITLE;

  // S'il n'y a pas d'utilisateur connecté, inclure le script de déconnexion
  if (!$_SESSION['username']) {
    include( UTIL_LOGOUT );
  }

  // Changer de mot de passe
  include( FUNCTION_NEW_PWD );
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
<body>
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
                  <label class="h6" for="username">Nom d'utilisateur</label>
                  <select class="form-control" name="username" id="username" aria-describedby="usernameHelp" required>
                    <option value="" disabled>Sélectionner un utilisateur...</option>
<?php
  include( UTIL_CONNECT );
  try {
    $sql_query = 'SELECT account_type
                  FROM Users
                  WHERE username = :username LIMIT 1';
    $stmt = $connectedDB->prepare($sql_query);
    $stmt->execute([
      ':username' => $_SESSION['username']
    ]);
    $user = $stmt->fetch();
  } catch(PDOException $e) {
    echo 'Error: ' . $e->getMessage();
  }

  if ($user['account_type'] == 0) {
    try {
      $sql_query = 'SELECT username
                    FROM Users
                    ORDER BY id ASC';
      $stmt = $connectedDB->prepare($sql_query);
      $stmt->execute();
    } catch(PDOException $e) {
      echo 'Error: ' . $e->getMessage();
    }

    foreach($stmt as $row) {
?>
                    <option value="<?= htmlspecialchars($row['username']) ?>" <?= $row['username'] == $_SESSION['username'] ? 'selected' : null ?>><?= htmlspecialchars($row['username']) ?></option>
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
                    <label class="h6" for="password">Mot de passe</label>
                    <input class="form-control" type="password" id="password" name="password" aria-describedby="passwordHelp" required>
                    <small class="form-text <?= $error['password'] ? 'text-danger' : 'text-muted' ?>" id="passwordHelp">Doit contenir : de 8 à 72 caractères, 1 majuscule, 1 minuscule, 1 chiffre, 1 caractère spécial.</small>
                  </div>
                  <div class="form-group mb-0">
                    <label class="h6" for="confirm-password">Confirmer le mot de passe</label>
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
