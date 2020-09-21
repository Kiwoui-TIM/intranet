<?php
session_start();
ob_start();

if (!$_SESSION["username"]) {
  include "logout.php";
}

$passwordClass = 'text-muted';

if (isset($_POST['create_user']) || isset($_SESSION['postdata']['create_user'])) {
  // define variables and set to empty values
  $error = [];
  $username = $password = $confirm_password = '';

  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_SESSION['postdata'] = $_POST;
    $_POST = array();
    header('Location: ' . $_SERVER['REQUEST_URI'],true,303);
    exit;
  } elseif (array_key_exists('postdata', $_SESSION)) {
    $username = trim($_SESSION['postdata']['username']);
    $password = trim($_SESSION['postdata']['password']);
    $confirm_password = trim($_SESSION['postdata']['confirm-password']);

    if (isset($username)) {
      // check if username only contains letters and numbers
      if (!preg_match("/^[a-zA-Z\d]*$/",$username)) {
        $error['username'] = true;
      } elseif (strlen($username > 255)) {
        $error['username'] = true;
      }
    }

    if (isset($password)) {
      if (strlen($password) < 8) {
        $error['password'] = true;
      } elseif (strlen($password) > 72) {
        $error['password'] = true;
      } elseif (!preg_match("/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[!@#$%?&*()=+_,\'.\";:{}<>[\]\/\\|\^`~\-]).*$/", $password)) {
        $error['password'] = true;
      }
    }

    if (isset($error['password'])) {
      $passwordClass = 'text-danger';
    }

    if (empty($error['password'])) {
      if ($confirm_password != $password) {
        $error['confirmPassword'] = 'Les mots de passe ne correspondent pas';
      }
    }

    include 'connect.php';

    try {
      $query_sql = 'SELECT username, email FROM Users WHERE username=:username OR email=:email LIMIT 1';
      $stmt = $connectedDB->prepare($query_sql);
      $stmt->execute([
        ':username' => $username,
        ':email' => $email
      ]);
      $user = $stmt->fetch();
    } catch(PDOException $e) {
      echo 'Error: ' . $e->getMessage();
    }

    if ($user) { // if user exists
      if (strtolower($user['username']) === strtolower($username)) {
        $error['generic'] = 'Le nom d\'utilisateur existe déjà';
      }
    }

    // DB Insert
    if (count($error) == 0) {
      try {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT, ['cost' => 11]);
        $insert_sql = 'INSERT INTO Users (username, email, hashed_password)
        VALUES (:username, :email, :hashed_password)';
        $stmt = $connectedDB->prepare($insert_sql);
        $stmt->execute([
          ':username' => $username,
          ':email' => $email,
          ':hashed_password' => $hashed_password
        ]);
      } catch(PDOException $e) {
        echo 'Error: ' . $e->getMessage();
      }

      $to = 'Jakob.Bouchard@outlook.com';
      $subject = '[KIWOUI INTRANET] Nouveau compte';
      $txt = 'Le compte ' . $username . ' (' . $accountType . ') vient d\'être créé.';
      $headers = 'From: intranet@jakobbouchard.dev';
      mail($to,$subject,$txt,$headers);

      $connectedDB = null;
      $_SESSION['username'] = $username;
      unset($_SESSION['postdata'], $password, $confirm_password);
      header('Location: landing-page.php',true,303);
      exit;
    }

    unset($_SESSION['postdata'], $password, $confirm_password);
  }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Créer un compte - Intranet</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
  <link rel="stylesheet" href="styles/dashboard.css">
</head>
<body>
  <nav class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
    <a class="navbar-brand col-md-3 col-lg-2 mr-0 px-3" href="#">Kiwoui</a>
    <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-toggle="collapse" data-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <ul class="navbar-nav px-3">
      <li class="nav-item text-nowrap">
        <a class="nav-link" href="logout.php">Déconnexion</a>
      </li>
    </ul>
  </nav>

  <div class="container-fluid">
    <div class="row">
      <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
        <div class="sidebar-sticky pt-3">
          <ul class="nav flex-column">
            <li class="nav-item">
              <a class="nav-link" href="dashboard">
                <span data-feather="home"></span>
                Tableau de bord
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#">
                <span data-feather="check-square"></span>
                Tâches
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#">
                <span data-feather="users"></span>
                Clients
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#">
                <span data-feather="file-text"></span>
                Rapports
              </a>
            </li>
          </ul>

          <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            Gestion de compte
          </h6>
          <ul class="nav flex-column mb-2">
            <li class="nav-item">
              <a class="nav-link" href="create-account.php">
                <span data-feather="lock"></span>
                Changer de mot passe
              </a>
            </li>
          </ul>

          <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            Administration
          </h6>
          <ul class="nav flex-column mb-2">
            <li class="nav-item">
              <a class="nav-link active" href="create-account.php">
                <span data-feather="user-plus"></span>
                Créer un compte <span class="sr-only">(current)</span>
              </a>
            </li>
          </ul>
        </div>
      </nav>

      <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
          <h1 class="h2">Créer un compte</h1>
        </div>
        <div class="container">
          <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>" method="post">
            <div class="form-group">
              <label for="username">Nom d'utilisateur</label>
              <input type="text" class="form-control" id="username" name="username" aria-describedby="usernameHelp"  required autofocus>
              <small id="usernameHelp" class="form-text text-muted">Peut seulement contenir des lettres sans accents et des chiffres.</small>
            </div>
            <div class="form-group">
              <label for="password">Mot de passe</label>
              <input type="password" class="form-control" id="password" name="password" aria-describedby="passwordHelp" required>
              <small id="passwordHelp" class="form-text <?php echo $passwordClass;?>">Doit contenir : 1 majuscule, 1 minuscule, 1 chiffre, 1 caractère spécial.</small>
            </div>
            <div class="form-group">
              <label for="confirm-password">Confirmer le mot de passe</label>
              <input type="password" class="form-control" id="confirm-password" name="confirm-password" aria-describedby="confirmPasswordHelp"  required>
              <small id="confirmPasswordHelp" class="form-text text-danger"><?php echo $error['confirmPassword'];?>&nbsp;</small>
            </div>
            <div class="error-msg text-danger"><?php echo $error['generic'];?>&nbsp;</div>
            <button type="submit" name="create_user" class="btn btn-lg btn-primary btn-block">Créer le compte</button>
          </form>
        </div>
      </main>
    </div>
  </div>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.9.0/feather.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
  <script src="script/dashboard.js"></script>
  <script>
    document.querySelector('#password');
  </script>
</body>
</html>
