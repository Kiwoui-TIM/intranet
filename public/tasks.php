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
  $account_type = $stmt->fetch();
} catch(PDOException $e) {
  echo 'Error: ' . $e->getMessage();
}
if ($account_type == 2) {
  header('location: dashboard.php');
  exit;
}
$connectedDB = null;

if (isset($_POST['add_task']) || isset($_SESSION['postdata']['add_task'])) {
  // Définir les variables et les mettre vides
  $name = $student_id = $milestone = $due_date = '';

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

    if ($task['task_completion'] == 0) {
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
    <a class="navbar-brand col-md-3 col-lg-2 mr-0 px-3" href="index.php">Kiwoui</a>
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
              <a class="nav-link" href="create-account.php">
                <span data-feather="user-plus"></span>
                Créer un compte
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
          <h2>Current Todos</h2>
          <table class="table table-striped">
            <thead class="thead-dark">
              <th>Task</th>
              <th>Due date</th>
              <th>Completion</th>
              <th>Delete</th>
            </thead>
            <tbody>
        <?php
          $stmt = $connectedDB->prepare("SELECT * FROM Tasks WHERE completed = 0 ORDER BY id DESC");
          $stmt->execute();
          foreach($stmt as $row) {
        ?>
          <tr>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['due_date']) ?></td>
            <td>
              <form method="POST">
                <button type="submit" name="task_completion">Uncomplete</button>
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
              </form>
            </td>
            <td>
              <form method="POST">
                <button type="submit" name="delete_task">Delete</button>
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
              </form>
            </td>
          </tr>
        <?php
          }
        ?>

        <?php
          $stmt = $connectedDB->prepare("SELECT * FROM Tasks WHERE completed = 1 ORDER BY id DESC");
          $stmt->execute();
          foreach($stmt as $row) {
        ?>
          <tr class="">
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['due_date']) ?></td>
            <td>
              <form method="POST">
                <button type="submit" name="task_completion">Complete</button>
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
              </form>
            </td>
            <td>
              <form method="POST">
                <button type="submit" name="delete_task">Delete</button>
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