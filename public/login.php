<?php
if (!isset($_SESSION)) {
  session_start();
}
ob_start();

// LOGIN USER
if (isset($_POST['login_user']) || isset($_SESSION['postdata']['login_user'])) {
  // define variables and set to empty values
  $error = [];
  $username = $password = '';

  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_SESSION['postdata'] = $_POST;
    $_POST = array();
    header('Location: ' . $_SERVER['REQUEST_URI'],true,303);
    exit;
  } elseif (array_key_exists('postdata', $_SESSION)) {
    $username = trim($_SESSION['postdata']['username']);
    $password = trim($_SESSION['postdata']['password']);

    include( 'connect.php' );

    try {
      $query_sql = 'SELECT id, username, hashed_password, team FROM Users WHERE username=:username LIMIT 1';
      $stmt = $connectedDB->prepare($query_sql);
      $stmt->execute([':username' => $username]);
      $user = $stmt->fetch();
    } catch(PDOException $e) {
      echo 'Error: ' . $e->getMessage();
    }

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
    unset($_SESSION['postdata'], $password);
  }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connexion - Intranet</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
  <link rel="stylesheet" href="styles/login.css">
  <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
  <link rel="manifest" href="/site.webmanifest">
  <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#52de63">
  <meta name="apple-mobile-web-app-title" content="Kiwoui Intranet">
  <meta name="application-name" content="Kiwoui Intranet">
  <meta name="msapplication-TileColor" content="#52de63">
  <meta name="theme-color" content="#52de63">
</head>
<body class="text-center d-flex align-items-center">
<?php if ($error['generic']) { ?>
  <div class="alert alert-danger popup-alert mt-4" role="alert">
    Mauvais utilisateur ou mot de passe !
  </div>
<?php } ?>

  <form class="form-signin" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>" method="POST">
    <img src="images/logo_kiwi_square.svg" alt="Logo de Kiwoui" width="200" height="200">
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
