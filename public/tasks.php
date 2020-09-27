<?php
session_start();
ob_start();

// S'il n'y a pas d'utilisateur connecté, inclure le script de déconnexion
if (!$_SESSION["username"]) {
  include 'logout.php';
}

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
if ($user['account_type'] == 2) {
  header('location: dashboard.php');
  exit;
}
$connectedDB = null;

if (isset($_POST['add_task']) || isset($_SESSION['postdata']['add_task'])) {
  // Définir les variables et les mettre vides
  $name = $milestone = $due_date = '';

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
    $milestone = trim($_SESSION['postdata']['milestone']);
    $due_date = trim($_SESSION['postdata']['due_date']);
    $sql_query = 'INSERT INTO Tasks (name, student, milestone, creation_date, due_date)
                   VALUES (:name, :student, :milestone, :creation_date, :due_date)';
    $stmt = $connectedDB->prepare($sql_query);
    $stmt->execute([
      ':name' => $name,
      ':student' => $_SESSION['id'],
      ':milestone' => $milestone,
      ':creation_date' => date('Y-m-d'),
      ':due_date' => $due_date
    ]);
    unset($_SESSION['postdata']);
    $connectedDB = null;
  }
}

if (isset($_POST['delete_task']) || isset($_SESSION['postdata']['delete_task'])) {
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
    $sql_query = 'DELETE FROM Tasks WHERE id = :id';
    $stmt = $connectedDB->prepare($sql_query);
    $stmt->execute([
      ':id' => $id
    ]);
    unset($_SESSION['postdata']);
    $connectedDB = null;
  }
}

if (isset($_POST['clock_task']) || isset($_SESSION['postdata']['clock_task'])) {
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

    $sql_query = 'SELECT time_spent, clock FROM Tasks WHERE id = :id';
    $stmt = $connectedDB->prepare($sql_query);
    $stmt->execute([
      ':id' => $id
    ]);
    $task = $stmt->fetch();

    if ($task['clock']) {
      $cur_timestamp = time();
      $prev_timestamp = $task['clock'];
      $sql_query = 'UPDATE Tasks SET clock = NULL WHERE id = :id';
      $stmt = $connectedDB->prepare($sql_query);
      $stmt->execute([
        ':id' => $id
      ]);

      $time_spent = ($cur_timestamp - $prev_timestamp) / 3600 + $task['time_spent'];
      $sql_query = 'UPDATE Tasks SET time_spent = :time_spent WHERE id = :id';
      $stmt = $connectedDB->prepare($sql_query);
      $stmt->execute([
        ':id' => $id,
        ':time_spent' => $time_spent
      ]);
    } else {
      $cur_timestamp = time();
      $sql_query = 'UPDATE Tasks SET clock = :timestamp WHERE id = :id';
      $stmt = $connectedDB->prepare($sql_query);
      $stmt->execute([
        ':id' => $id,
        ':timestamp' => $cur_timestamp
      ]);
    }
    unset($_SESSION['postdata']);
    $connectedDB = null;
  }
}

if (isset($_POST['task_completion']) || isset($_SESSION['postdata']['task_completion'])) {
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

    $sql_query = 'SELECT completed FROM Tasks WHERE id = :id';
    $stmt = $connectedDB->prepare($sql_query);
    $stmt->execute([
      ':id' => $id
    ]);
    $task = $stmt->fetch();

    if ($task['completed'] == 0) {
      $sql_query = 'UPDATE Tasks SET completed = \'1\' WHERE id = :id';
      $stmt = $connectedDB->prepare($sql_query);
      $stmt->execute([
        ':id' => $id
      ]);
    } else {
      $sql_query = 'UPDATE Tasks SET completed = \'0\' WHERE id = :id';
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
  <title>Liste des tâches - Intranet</title>
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
              <a class="nav-link" href="dashboard.php">
                <span data-feather="home"></span>
                Tableau de bord
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link active" href="tasks.php">
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
              <a class="nav-link" href="projects.php">
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
          <h1 class="h2">Liste des tâches</h1>
        </div>
        <div class="container">
          <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <div class="form-group">
              <label class="h6" for="name">Nom de la tâche</label>
              <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="form-row">
              <div class="form-group col-md-6">
                <label class="h6" for="milestone">Jalon</label>
                <select class="form-control" name="milestone" id="milestone" required>
                  <?php
                    include 'connect.php';
                    $sql_query = 'SELECT Milestones.id, Milestones.name, Projects.name FROM Milestones
                                  INNER JOIN Projects ON Milestones.project = Projects.id
                                  ORDER BY Milestones.project, Milestones.id ASC';
                    $stmt = $connectedDB->prepare($sql_query);
                    $stmt->execute();
                    foreach($stmt as $row) {
                  ?>
                    <option value="<?= htmlspecialchars($row['0']) ?>">[<?= htmlspecialchars($row['2']) ?>] <?= htmlspecialchars($row['1']) ?></option>
                  <?php
                    }
                  ?>
                </select>
              </div>
              <div class="form-group col-md-6">
                <label class="h6" for="due_date">Date d'échéance</label>
                <input type="date" class="form-control" id="due_date" name="due_date" required>
              </div>
            </div>
            <button class="btn btn-lg btn-primary btn-block" type="submit" name="add_task">Ajouter une tâche</button>
          </form>
          <h2>Tâches</h2>
          <table class="table table-bordered table-hover table-sm">
            <thead class="thead-dark">
              <tr class="d-flex">
                <th class="col-6">Tâche</th>
                <th class="col-2">Date d'échéance</th>
                <th class="col-1">Temps</th>
                <th class="col-1 text-center">Pointage</th>
                <th class="col-1 text-center">Complétion</th>
                <th class="col-1 text-center">Supprimer</th>
              </tr>
            </thead>
            <tbody>
        <?php
          $stmt = $connectedDB->prepare("SELECT * FROM Tasks WHERE (completed = 0 AND student = :student) ORDER BY due_date ASC");
          $stmt->execute([
            ':student' => $_SESSION['id']
          ]);
          foreach($stmt as $row) {
            if ($row['due_date'] < date('Y-m-d')) {
        ?>
          <tr class="d-flex table-danger">
            <td class="col-6"><?= htmlspecialchars($row['name']) ?></td>
            <td class="col-2"><?= htmlspecialchars($row['due_date']) ?></td>
            <td class="col-1"><?= htmlspecialchars($row['time_spent']) ?>h</td>
            <td class="col-1 text-center">
              <form method="POST">
                <button type="submit" class="btn btn-sm btn-info" name="clock_task">
                  <span data-feather="clock"></span>
                </button>
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
              </form>
            </td>
            <td class="col-1 text-center">
              <form method="POST">
                <button type="submit" class="btn btn-sm btn-danger" name="task_completion">
                  <span data-feather="x"></span>
                </button>
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
              </form>
            </td>
            <td class="col-1 text-center">
              <form method="POST">
                <button type="submit" class="btn btn-sm btn-outline-danger" name="delete_task">
                  <span data-feather="trash-2"></span>
                </button>
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
              </form>
            </td>
          </tr>
        <?php
            } else {
        ?>
          <tr class="d-flex">
            <td class="col-6"><?= htmlspecialchars($row['name']) ?></td>
            <td class="col-2"><?= htmlspecialchars($row['due_date']) ?></td>
            <td class="col-1"><?= htmlspecialchars($row['time_spent']) ?>h</td>
            <td class="col-1 text-center">
              <form method="POST">
                <button type="submit" class="btn btn-sm btn-info" name="clock_task">
                  <span data-feather="clock"></span>
                </button>
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
              </form>
            </td>
            <td class="col-1 text-center">
              <form method="POST">
                <button type="submit" class="btn btn-sm btn-danger" name="task_completion">
                  <span data-feather="x"></span>
                </button>
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
              </form>
            </td>
            <td class="col-1 text-center">
              <form method="POST">
                <button type="submit" class="btn btn-sm btn-outline-danger" name="delete_task">
                  <span data-feather="trash-2"></span>
                </button>
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
              </form>
            </td>
          </tr>
        <?php
            }
          }
        ?>

        <?php
          $stmt = $connectedDB->prepare("SELECT * FROM Tasks WHERE (completed = 1 AND student = :student) ORDER BY due_date ASC");
          $stmt->execute([
            ':student' => $_SESSION['id']
          ]);
          foreach($stmt as $row) {
        ?>
          <tr class="d-flex table-secondary text-muted">
            <td class="col-6"><del><?= htmlspecialchars($row['name']) ?></del></td>
            <td class="col-2"><del><?= htmlspecialchars($row['due_date']) ?></del></td>
            <td class="col-1"><?= htmlspecialchars($row['time_spent']) ?>h</td>
            <td class="col-1 text-center">
              <form method="POST">
                <button type="submit" class="btn btn-sm btn-info" name="clock_task" disabled>
                  <span data-feather="clock"></span>
                </button>
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
              </form>
            </td>
            <td class="col-1 text-center">
              <form method="POST">
                <button type="submit" class="btn btn-sm btn-success" name="task_completion">
                  <span data-feather="check"></span>
                </button>
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
              </form>
            </td>
            <td class="col-1 text-center">
              <form method="POST">
                <button type="submit" class="btn btn-sm btn-outline-danger" name="delete_task">
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
