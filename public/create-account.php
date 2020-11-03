<?php
  session_start();
  ob_start();

  // Importer les constantes et changer le titre de la page
  require( 'utils/config.php' );
  $page_title = CREATE_ACC_TITLE;

  // S'il n'y a pas d'utilisateur connecté, inclure le script de déconnexion
  if (!$_SESSION['username']) {
    include( UTIL_LOGOUT );
  }

  // Vérifier le niveau d'accès
  include( ACCESS_ADMIN_ONLY );

  // Créer un utilisateur
  include( FUNCTION_NEW_ACC );
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
          <div class="card my-4 border-0 shadow">
            <div class="card-body">
              <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                <div class="row">
                  <div class="col-md-8">
                    <div class="form-group">
                      <label class="h6" for="username">Nom d'utilisateur</label>
                      <input class="form-control" type="text" id="username" name="username" aria-describedby="usernameHelp" required autofocus>
                      <small class="form-text <?= $error['username'] ? 'text-danger' : 'text-muted' ?>" id="usernameHelp">Peut seulement contenir des lettres sans accents et des chiffres.</small>
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
                  </div>
                  <div class="col-md-4">
                    <fieldset class="form-group">
                      <legend class="h6">Type de compte</legend>
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
                    <fieldset class="form-group">
                      <legend class="h6">Équipe</legend>
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
                </div>
                <small class="text-danger"><?= $error['generic'] ?>&nbsp;</small>
                <button class="btn btn-lg btn-outline-primary btn-block" type="submit" name="create_user">Créer le compte</button>
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
