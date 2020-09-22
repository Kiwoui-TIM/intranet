<?php
session_start();
ob_start();

// S'il n'y a pas d'utilisateur connecté, inclure le script de déconnexion
if (!$_SESSION["username"]) {
  include "logout.php";
}

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
    include 'connect.php';

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
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Changer de mot de passe - Intranet</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
  <link rel="stylesheet" href="styles/dashboard.css">
</head>
<body>
  <?php if ($creation_success) { ?>
  <div class="alert alert-success popup-alert mt-4" role="alert">
    Mot de passe changé avec succès !
  </div>
  <?php } ?>

  <nav class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
    <a class="navbar-brand col-md-3 col-lg-2 mr-0 px-3" href="index.php">Kiwoui (<?= $_SESSION["username"] ?>)</a>
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
              <a class="nav-link" href="dashboard.php">
                <span data-feather="home"></span>
                Tableau de bord
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="tasks.php">
                <span data-feather="check-square"></span>
                Tâches
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="milestones.php">
                <span data-feather="flag"></span>
                Jalons
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#">
                <span data-feather="briefcase"></span>
                Projets
              </a>
            </li>
          </ul>

          <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            Gestion de compte
          </h6>
          <ul class="nav flex-column mb-2">
            <li class="nav-item">
              <a class="nav-link active" href="change-password.php">
                <span data-feather="lock"></span>
                Changer de mot de passe
              </a>
            </li>
          </ul>

          <?php
            include 'connect.php';
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
            if ($user['account_type'] == 0) {
          ?>
          <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            Administration
          </h6>
          <ul class="nav flex-column mb-2">
            <li class="nav-item">
              <a class="nav-link" href="create-account.php">
                <span data-feather="user-plus"></span>
                Créer un compte
              </a>
            </li>
          </ul>
          <?php
            }
            $connectedDB = null;
          ?>

          <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            Support
          </h6>
          <ul class="nav flex-column mb-2">
            <li class="nav-item">
              <a class="nav-link" href="https://github.com/Kiwoui-TIM/intranet/issues/new?assignees=JustinVallee&labels=bug&template=rapport-de-bug.md&title=%5BBUG%5D">
                <span data-feather="life-buoy"></span>
                Rapport de bug
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="https://github.com/Kiwoui-TIM/intranet/issues/new?assignees=jakobbouchard&labels=enhancement&template=demande-de-fonctionnalit-.md&title=%5BFEATURE%5D">
                <span data-feather="clipboard"></span>
                Demande de fonctionnalité
              </a>
            </li>
          </ul>
        </div>
      </nav>

      <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
          <h1 class="h2">Changer de mot de passe</h1>
        </div>
        <div class="container">
          <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>" method="post">
            <div class="form-group">
              <label for="username">Nom d'utilisateur</label>
              <select class="form-control" name="username" id="username" required>
                <?php
                  include 'connect.php';
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
                  if ($user['account_type'] == 0) {
                    $stmt = $connectedDB->prepare("SELECT * FROM Users ORDER BY id ASC");
                    $stmt->execute();
                    foreach($stmt as $row) {
                ?>
                  <option value="<?= htmlspecialchars($row['username']) ?>" <?php if ($row['username'] == $_SESSION['username']) echo 'selected' ?> ><?= htmlspecialchars($row['username']) ?></option>
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
            </div>
            <div class="form-group">
              <label for="password">Mot de passe</label>
              <input type="password" class="form-control" id="password" name="password" aria-describedby="passwordHelp" required>
              <small id="passwordHelp" class="form-text <?php echo $passwordClass;?>">Doit contenir : de 8 à 72 caractères, 1 majuscule, 1 minuscule, 1 chiffre, 1 caractère spécial.</small>
            </div>
            <div class="form-group">
              <label for="confirm-password">Confirmer le mot de passe</label>
              <input type="password" class="form-control" id="confirm-password" name="confirm-password" aria-describedby="confirmPasswordHelp" required>
              <small id="confirmPasswordHelp" class="form-text text-danger"><?php echo $error['confirmPassword'];?>&nbsp;</small>
            </div>
            <div class="form-group">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="show-password">
                <label class="form-check-label" for="show-password">Afficher les mots de passe</label>
              </div>
            </div>
            <small class="text-danger"><?php echo $error['generic'];?>&nbsp;</small>
            <button type="submit" name="change_password" class="btn btn-lg btn-primary btn-block">Changer le mot de passe</button>
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
    document.querySelector('#show-password').addEventListener('click', toggleViewPassword);
    const passwordField = document.querySelector('#password');
    const confirmPasswordField = document.querySelector('#confirm-password');

    function toggleViewPassword(e) {
      const checkbox = e.target;
      if (checkbox.checked) {
        passwordField.setAttribute("type", "text");
        confirmPasswordField.setAttribute("type", "text");
      } else {
        passwordField.setAttribute("type", "password");
        confirmPasswordField.setAttribute("type", "password");
      }
    }
  </script>
</body>
</html>
