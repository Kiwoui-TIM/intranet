<?php
session_start();
ob_start();
// S'il n'y a pas d'utilisateur connecté, inclure le script de déconnexion
if (!$_SESSION['username']) {
  include 'logout.php';
}
// Vérifier le niveau d'accès
include( 'connect.php' );
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
if ($user['account_type'] == 3) {
  header('location: index.php');
  exit;
}
$connectedDB = null;
require( 'config.php' );
$page_title = 'Liste des tâches';
$tasks = 'active';

// Actions des formulaires/boutons dans le tableau

// Ajouter une tâche
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
    include( 'connect.php' );
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
    include( 'connect.php' );
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
    include( 'connect.php' );
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
    include( 'connect.php' );
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
<!-- START INCLUDE META -->
<?php
include( VIEW_META );
?>
<!-- END INCLUDE META -->
</head>
<body>
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
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
          <h1 class="h2"><?= $page_title ?></h1>
        </div>
        <div class="container">
          <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post">
            <div class="form-group">
              <label class="h6" for="name">Nom de la tâche</label>
              <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="form-row">
              <div class="form-group col-md-6">
                <label class="h6" for="milestone">Jalon</label>
                <select class="form-control" name="milestone" id="milestone" required>
                  <option value="" disabled selected>Sélectionner un jalon...</option>
<?php
  include( 'connect.php' );
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
    $sql_query = 'SELECT Milestones.id, Milestones.name, Projects.name FROM Milestones
                  INNER JOIN Projects ON Milestones.project = Projects.id
                  WHERE Milestones.completed = 0
                  ORDER BY Milestones.project, Milestones.id ASC';
    $stmt = $connectedDB->prepare($sql_query);
    $stmt->execute();
  } else {
    $sql_query = 'SELECT Milestones.id, Milestones.name, Projects.name FROM Milestones
                  INNER JOIN Projects ON Milestones.project = Projects.id
                  WHERE Milestones.team = :team AND Milestones.completed = 0
                  ORDER BY Milestones.project, Milestones.id ASC';
    $stmt = $connectedDB->prepare($sql_query);
    $stmt->execute([
      ':team' => $_SESSION['team']
    ]);
  }
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
                <input class="form-control" type="date" id="due_date" name="due_date" required>
              </div>
            </div>
            <button class="btn btn-lg btn-outline-primary btn-block" type="submit" name="add_task">Ajouter une tâche</button>
          </form>
<?php
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
    $sql_query = 'SELECT Milestones.id, Milestones.name, Projects.name FROM Milestones
                  INNER JOIN Projects ON Milestones.project = Projects.id
                  WHERE Milestones.completed = 0
                  ORDER BY Milestones.project, Milestones.id ASC';
    $stmt = $connectedDB->prepare($sql_query);
    $stmt->execute();
  } else {
    $sql_query = 'SELECT Milestones.id, Milestones.name, Projects.name FROM Milestones
                  INNER JOIN Projects ON Milestones.project = Projects.id
                  WHERE Milestones.team = :team AND Milestones.completed = 0
                  ORDER BY Milestones.project, Milestones.id ASC';
    $stmt = $connectedDB->prepare($sql_query);
    $stmt->execute([
      ':team' => $_SESSION['team']
    ]);
  }
  foreach($stmt as $milestone_row) {
?>
          <h3>[<?= htmlspecialchars($milestone_row['2']) ?>] <?= htmlspecialchars($milestone_row['1']) ?></h3>
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
    $stmt = $connectedDB->prepare("SELECT * FROM Tasks WHERE (completed = 0 AND student = :student AND milestone = :milestone) ORDER BY due_date ASC");
    $stmt->execute([
      ':student' => $_SESSION['id'],
      ':milestone' => $milestone_row['id']
    ]);
    foreach($stmt as $row) {
      if ($row['due_date'] < date('Y-m-d')) {
?>
              <tr class="d-flex table-danger">
                <td class="col-6"><?= htmlspecialchars($row['name']) ?></td>
                <td class="col-2"><?= htmlspecialchars($row['due_date']) ?></td>
                <td class="col-1"><?= htmlspecialchars($row['time_spent']) ?>h</td>
                <td class="col-1 text-center <?php if ($row['clock']) {echo 'table-warning';} ?>">
                  <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <button class="btn btn-sm btn-info <?php if ($row['clock']) {echo 'btn-warning';} ?>" type="submit" name="clock_task">
                      <span data-feather="clock"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                  </form>
                </td>
                <td class="col-1 text-center">
                  <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <button class="btn btn-sm btn-danger" type="submit" name="task_completion">
                      <span data-feather="x"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                  </form>
                </td>
                <td class="col-1 text-center">
                  <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <button class="btn btn-sm btn-outline-danger" type="submit" name="delete_task">
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
                <td class="col-1 text-center <?php if ($row['clock']) {echo 'table-warning';} ?>">
                  <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <button class="btn btn-sm btn-info <?php if ($row['clock']) {echo 'btn-warning';} ?>" type="submit" name="clock_task">
                      <span data-feather="clock"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                  </form>
                </td>
                <td class="col-1 text-center">
                  <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <button class="btn btn-sm btn-danger" type="submit" name="task_completion">
                      <span data-feather="x"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                  </form>
                </td>
                <td class="col-1 text-center">
                  <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <button class="btn btn-sm btn-outline-danger" type="submit" name="delete_task">
                      <span data-feather="trash-2"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                  </form>
                </td>
              </tr>
<?php
      }
    }
    $stmt = $connectedDB->prepare("SELECT * FROM Tasks WHERE (completed = 1 AND student = :student AND milestone = :milestone) ORDER BY due_date ASC");
    $stmt->execute([
      ':student' => $_SESSION['id'],
      ':milestone' => $milestone_row['id']
    ]);
    foreach($stmt as $row) {
?>
              <tr class="d-flex table-secondary text-muted">
                <td class="col-6"><del><?= htmlspecialchars($row['name']) ?></del></td>
                <td class="col-2"><del><?= htmlspecialchars($row['due_date']) ?></del></td>
                <td class="col-1"><?= htmlspecialchars($row['time_spent']) ?>h</td>
                <td class="col-1 text-center <?php if ($row['clock']) {echo 'table-warning';} ?>">
                  <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <button class="btn btn-sm btn-info <?php if ($row['clock']) {echo 'btn-warning';} ?>" type="submit" name="clock_task" disabled>
                      <span data-feather="clock"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                  </form>
                </td>
                <td class="col-1 text-center">
                  <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <button class="btn btn-sm btn-success" type="submit" name="task_completion">
                      <span data-feather="check"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                  </form>
                </td>
                <td class="col-1 text-center">
                  <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <button class="btn btn-sm btn-outline-danger" type="submit" name="delete_task">
                      <span data-feather="trash-2"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                  </form>
                </td>
              </tr>
<?php
    }
?>
            </tbody>
          </table>
<?php
  }
  $connectedDB = null;
?>
        </div>
      </main>
<!-- START INCLUDE FOOTER -->
<?php
include( VIEW_FOOTER );
?>
<!-- END INCLUDE FOOTER -->
</body>
</html>
