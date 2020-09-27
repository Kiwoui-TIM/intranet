<?php
session_start();
ob_start();

// S'il n'y a pas d'utilisateur connecté, inclure le script de déconnexion
if (!$_SESSION["username"]) {
  include 'logout.php';
}

// Vérifier le niveau d'accès
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
if ($user['account_type'] != 0) {
  header('location: index.php');
  exit;
}
$connectedDB = null;

// Ajouter les jalons
if (isset($_POST['add_project']) || isset($_SESSION['postdata']['add_project'])) {
  // Définir les variables et les mettre vides
  $name = $client = '';

  // Si la requête est faite via POST, mettre les variables POST dans un array dans SESSION
  // puis retourner à la page qui a fait la requête.
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_SESSION['postdata'] = $_POST;
    $_POST = array();
    header('Location: ' . $_SERVER['REQUEST_URI'],true,303);
    exit;
    // Si l'array "postdata" existe, changer les variables pour les valeurs entrée par l'utilisateur
  } elseif (array_key_exists('postdata', $_SESSION)) {
    include 'connect.php';
    $name = trim($_SESSION['postdata']['name']);
    $client = trim($_SESSION['postdata']['client']);
    $sql_query = 'INSERT INTO Projects (name, client)
                   VALUES (:name, :client)';
    $stmt = $connectedDB->prepare($sql_query);
    $stmt->execute([
      ':name' => $name,
      ':client' => $client
    ]);
    unset($_SESSION['postdata']);
    $connectedDB = null;
  }
}

// Supprimer les jalons
if (isset($_POST['delete_project']) || isset($_SESSION['postdata']['delete_project'])) {
  // Définir les variables et les mettre vides
  $id = '';

  // Si la requête est faite via POST, mettre les variables POST dans un array dans SESSION
  // puis retourner à la page qui a fait la requête.
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_SESSION['postdata'] = $_POST;
    $_POST = array();
    header('Location: ' . $_SERVER['REQUEST_URI'],true,303);
    exit;
    // Si l'array "postdata" existe, changer les variables pour les valeurs entrée par l'utilisateur
  } elseif (array_key_exists('postdata', $_SESSION)) {
    include 'connect.php';
    $id = trim($_SESSION['postdata']['id']);
    $sql_query = 'DELETE FROM Projects WHERE id = :id';
    $stmt = $connectedDB->prepare($sql_query);
    $stmt->execute([
      ':id' => $id
    ]);
    unset($_SESSION['postdata']);
    $connectedDB = null;
  }
}

// Compléter les jalons
if (isset($_POST['project_completion']) || isset($_SESSION['postdata']['project_completion'])) {
  // Définir les variables et les mettre vides
  $id = '';

  // Si la requête est faite via POST, mettre les variables POST dans un array dans SESSION
  // puis retourner à la page qui a fait la requête.
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_SESSION['postdata'] = $_POST;
    $_POST = array();
    header('Location: ' . $_SERVER['REQUEST_URI'],true,303);
    exit;
    // Si l'array "postdata" existe, changer les variables pour les valeurs entrée par l'utilisateur
  } elseif (array_key_exists('postdata', $_SESSION)) {
    include 'connect.php';
    $id = trim($_SESSION['postdata']['id']);

    $sql_query = 'SELECT completed FROM Projects WHERE id = :id';
    $stmt = $connectedDB->prepare($sql_query);
    $stmt->execute([
      ':id' => $id
    ]);
    $project = $stmt->fetch();

    if ($project['completed'] == 0) {
      $sql_query = 'UPDATE Projects SET completed = \'1\' WHERE id = :id';
      $stmt = $connectedDB->prepare($sql_query);
      $stmt->execute([
        ':id' => $id
      ]);
    } else {
      $sql_query = 'UPDATE Projects SET completed = \'0\' WHERE id = :id';
      $stmt = $connectedDB->prepare($sql_query);
      $stmt->execute([
        ':id' => $id
      ]);
    }
    $connectedDB = null;
    unset($_SESSION['postdata']);
  }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Liste des projets - Intranet</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
  <link rel="stylesheet" href="styles/dashboard.css">
</head>
<body>
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
              <a class="nav-link" href="index.php">
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
              <a class="nav-link active" href="projects.php">
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
              <a class="nav-link" href="change-password.php">
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
          <h1 class="h2">Liste des projets</h1>
        </div>
        <div class="container">
          <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <div class="form-row">
              <div class="form-group col-md-9">
                <label class="h6" for="name">Nom du projet</label>
                <input type="text" class="form-control" id="name" name="name" required>
              </div>
              <div class="form-group col-md-3">
                <label class="h6" for="client">Client</label>
                <select class="form-control" name="client" id="client" required>
                  <?php
                    include 'connect.php';
                    $sql_query = 'SELECT id, username FROM Users
                                  WHERE account_type = 2
                                  ORDER BY id ASC';
                    $stmt = $connectedDB->prepare($sql_query);
                    $stmt->execute();
                    foreach($stmt as $row) {
                  ?>
                    <option value="<?= htmlspecialchars($row['id']) ?>"><?= htmlspecialchars($row['username']) ?></option>
                  <?php
                    }
                  ?>
                </select>
              </div>
            </div>
            <button class="btn btn-lg btn-primary btn-block" type="submit" name="add_project">Créer un projet</button>
          </form>
          <h2>Projets</h2>
          <table class="table table-bordered table-hover table-sm">
            <thead class="thead-dark">
              <tr class="d-flex">
                <th class="col-8">Nom</th>
                <th class="col-2">Client</th>
                <th class="col-1 text-center">Complétion</th>
                <th class="col-1 text-center">Supprimer</th>
              </tr>
            </thead>
            <tbody>
        <?php
          $sql_query = 'SELECT Projects.id, Projects.name, Users.username FROM Projects
                        INNER JOIN Users ON Projects.client = Users.id
                        WHERE completed = 0 ORDER BY Projects.id ASC';
          $stmt = $connectedDB->prepare($sql_query);
          $stmt->execute();
          foreach($stmt as $row) {
        ?>
          <tr class="d-flex">
            <td class="col-8"><?= htmlspecialchars($row['1']) ?></td>
            <td class="col-2"><?= htmlspecialchars($row['2']) ?></td>
            <td class="col-1 text-center">
              <form method="POST">
                <button type="submit" class="btn btn-sm btn-danger" name="project_completion">
                  <span data-feather="x"></span>
                </button>
                <input type="hidden" name="id" value="<?= $row['0'] ?>">
              </form>
            </td>
            <td class="col-1 text-center">
              <form method="POST">
                <button type="submit" class="btn btn-sm btn-outline-danger" name="delete_project">
                  <span data-feather="trash-2"></span>
                </button>
                <input type="hidden" name="id" value="<?= $row['0'] ?>">
              </form>
            </td>
          </tr>
        <?php
          }
        ?>

        <?php
          $sql_query = 'SELECT Projects.id, Projects.name, Users.username FROM Projects
                        INNER JOIN Users ON Projects.client = Users.id
                        WHERE completed = 1 ORDER BY Projects.id ASC';
          $stmt = $connectedDB->prepare($sql_query);
          $stmt->execute();
          foreach($stmt as $row) {
        ?>
          <tr class="d-flex table-secondary text-muted">
            <td class="col-8"><del><?= htmlspecialchars($row['name']) ?></del></td>
            <td class="col-2"><del><?= htmlspecialchars($row['username']) ?></del></td>
            <td class="col-1 text-center">
              <form method="POST">
                <button type="submit" class="btn btn-sm btn-success"  name="project_completion">
                  <span data-feather="check"></span>
                </button>
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
              </form>
            </td>
            <td class="col-1 text-center">
              <form method="POST">
                <button type="submit" class="btn btn-sm btn-outline-danger" name="delete_project">
                  <span data-feather="trash-2"></span>
                </button>
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
              </form>
            </td>
          </tr>
        <?php
          }
          $connectedDB = null;
        ?>
        </div>
      </main>
    </div>
  </div>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.9.0/feather.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
  <script src="script/dashboard.js"></script>
</body>
</html>
