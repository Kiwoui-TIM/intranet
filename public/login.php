<?php include 'validation.php'; ?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connexion - Intranet</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
  <link rel="stylesheet" href="styles/login.css">
  <link rel="shortcut icon" href="https://via.placeholder.com/72.png/007bff/fff?text=Kiwoui" type="image/png">
</head>
<body class="text-center d-flex align-items-center">
  <?php if ($error['generic']) { ?>
  <div class="alert alert-danger popup-alert mt-4" role="alert">
    Mauvais utilisateur ou mot de passe !
  </div>
  <?php } ?>

  <form class="form-signin" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
    <img class="mb-4" src="https://via.placeholder.com/72.png/007bff/fff?text=Kiwoui" alt="Logo de Kiwoui" width="72" height="72">
    <h2 class="h3 mb-3 font-weight-normal">Intranet</h2>
    <label class ="sr-only" for="username">Utilisateur</label>
    <input class="form-control" type="text" id="username" name="username" value="<?php echo $username;?>" placeholder="Utilisateur" required autofocus>
    <label class ="sr-only" for="password">Mot de passe</label>
    <input class="form-control" type="password" id="password" name="password" placeholder="Mot de passe" required>
    <button class="btn btn-lg btn-primary btn-block mt-3" type="submit" name="login_user">Connexion</button>
    <p class="mt-5 mb-3 text-muted">&copy; 2020</p>
  </form>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
</body>
</html>
