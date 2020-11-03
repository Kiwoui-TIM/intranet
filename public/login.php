<?php
  if (!isset($_SESSION)) {
    session_start();
  }
  ob_start();

  // Importer les constantes et changer le titre de la page
  require( 'utils/config.php' );
  $page_title = 'Connexion';

  include( FUNCTION_LOGIN );
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<!-- START INCLUDE META -->
<?php
  include( VIEW_META );
?>
<!-- END INCLUDE META -->
  <link rel="stylesheet" href="styles/login.css">
</head>
<body class="text-center d-flex align-items-center">
<?php if ($error['generic']) { ?>
  <div class="alert alert-danger popup-alert mt-4" role="alert">
    Mauvais utilisateur ou mot de passe !
  </div>
<?php } ?>

  <form class="form-signin" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
    <object data="images/kiwi_square.svg" type="image/svg+xml" width="200" height="200"></object>
    <h2 class="h3 mb-3 font-weight-normal">Intranet</h2>
    <label class ="sr-only" for="username">Utilisateur</label>
    <input class="form-control" type="text" id="username" name="username" value="<?= $username ?>" placeholder="Utilisateur" required autofocus>
    <label class ="sr-only" for="password">Mot de passe</label>
    <input class="form-control" type="password" id="password" name="password" placeholder="Mot de passe" required>
    <button class="btn btn-lg btn-outline-primary btn-block mt-3" type="submit" name="login_user">Connexion</button>
    <p class="mt-5 mb-3 text-muted">Copyright &copy; 2020 Kiwoui&reg;</p>
  </form>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
</body>
</html>
