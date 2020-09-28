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
if ($user['account_type'] == 2) {
  header('location: dashboard.php');
  exit;
}
$connectedDB = null;
require( 'config.php' );
$page_title = 'Liste des jalons';
$milestones = 'active';

// Actions des formulaires/boutons dans le tableau

// Ajouter les jalons
if (isset($_POST['add_milestone']) || isset($_SESSION['postdata']['add_milestone'])) {
  // Définir les variables et les mettre vides
  $name = $project = $due_date = $team = '';

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
    $project = trim($_SESSION['postdata']['project']);
    $due_date = trim($_SESSION['postdata']['due_date']);
    $team = trim($_SESSION['postdata']['team']);
    $sql_query = 'INSERT INTO Milestones (name, project, team, due_date)
                   VALUES (:name, :project, :team, :due_date)';
    $stmt = $connectedDB->prepare($sql_query);
    $stmt->execute([
      ':name' => $name,
      ':project' => $project,
      ':team' => $team,
      ':due_date' => $due_date
    ]);
    unset($_SESSION['postdata']);
    $connectedDB = null;
  }
}

// Supprimer les jalons
if (isset($_POST['delete_milestone']) || isset($_SESSION['postdata']['delete_milestone'])) {
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
    $sql_query = 'DELETE FROM Milestones WHERE id = :id';
    $stmt = $connectedDB->prepare($sql_query);
    $stmt->execute([
      ':id' => $id
    ]);
    unset($_SESSION['postdata']);
    $connectedDB = null;
  }
}

// Compléter les jalons
if (isset($_POST['milestone_completion']) || isset($_SESSION['postdata']['milestone_completion'])) {
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

    $sql_query = 'SELECT completed FROM Milestones WHERE id = :id';
    $stmt = $connectedDB->prepare($sql_query);
    $stmt->execute([
      ':id' => $id
    ]);
    $milestone = $stmt->fetch();

    if ($milestone['completed'] == 0) {
      $sql_query = 'UPDATE Milestones SET completed = \'1\' WHERE id = :id';
      $stmt = $connectedDB->prepare($sql_query);
      $stmt->execute([
        ':id' => $id
      ]);
    } else {
      $sql_query = 'UPDATE Milestones SET completed = \'0\' WHERE id = :id';
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
          <h1 class="h2">Liste des jalons</h1>
        </div>
        <div class="container">
          <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <div class="form-group">
              <label class="h6" for="name">Nom du jalon</label>
              <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="form-row">
              <div class="form-group col-md-6">
                <label class="h6" for="project">Projet</label>
                <select class="form-control" name="project" id="project" required>
                  <option value="" disabled selected>Sélectionner un projet...</option>
<?php
  include 'connect.php';
  $sql_query = 'SELECT id, name FROM Projects
                ORDER BY id ASC';
  $stmt = $connectedDB->prepare($sql_query);
  $stmt->execute();
  foreach($stmt as $row) {
?>
                  <option value="<?= htmlspecialchars($row['id']) ?>"><?= htmlspecialchars($row['name']) ?></option>
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
            <fieldset class="form-group">
              <legend class="h6">Équipe</legend>
              <div class="form-row">
                <div class="col-sm-3">
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="team" id="graphistes" value="2">
                    <label class="form-check-label" for="graphistes">Graphistes</label>
                  </div>
                </div>
                <div class="col-sm-3">
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="team" id="programmeurs" value="3">
                    <label class="form-check-label" for="programmeurs">Programmeurs</label>
                  </div>
                </div>
                <div class="col-sm-3">
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="team" id="integrateurs-web" value="4">
                    <label class="form-check-label" for="integrateurs-web">Intégrateurs web</label>
                  </div>
                </div>
                <div class="col-sm-3">
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="team" id="integrateurs-video" value="5">
                    <label class="form-check-label" for="integrateurs-video">Intégrateurs vidéo</label>
                  </div>
                </div>
              </div>
            </fieldset>
            <button class="btn btn-lg btn-primary btn-block" type="submit" name="add_milestone">Ajouter un jalon</button>
          </form>
<?php
  $sql_query = 'SELECT id, name FROM Projects
                ORDER BY id ASC';
  $stmt = $connectedDB->prepare($sql_query);
  $stmt->execute([
    ':team' => $_SESSION['team']
  ]);
  foreach($stmt as $project_row) {
?>
          <h2><?= htmlspecialchars($project_row['name'])?></h2>
          <table class="table table-bordered table-hover table-sm">
            <thead class="thead-dark">
              <tr class="d-flex">
                <th class="col-6">Nom</th>
                <th class="col-2">Date d'échéance</th>
                <th class="col-2">Équipe</th>
                <th class="col-1 text-center">Complétion</th>
                <th class="col-1 text-center">Supprimer</th>
              </tr>
            </thead>
            <tbody>
<?php
    $sql_query = 'SELECT Milestones.id, Milestones.name, Milestones.due_date, Teams.name FROM Milestones
                  INNER JOIN Teams ON Milestones.team = Teams.id
                  WHERE (completed = 0 AND project = :project) ORDER BY Milestones.due_date ASC';
    $stmt = $connectedDB->prepare($sql_query);
    $stmt->execute([
      ':project' => $project_row['id']
    ]);
    foreach($stmt as $row) {
      if ($row['2'] < date('Y-m-d')) {
?>
              <tr class="d-flex table-danger">
                <td class="col-6"><?= htmlspecialchars($row['1']) ?></td>
                <td class="col-2"><?= htmlspecialchars($row['2']) ?></td>
                <td class="col-2"><?= htmlspecialchars($row['3']) ?></td>
                <td class="col-1 text-center">
                  <form method="POST">
                    <button type="submit" class="btn btn-sm btn-danger" name="milestone_completion">
                      <span data-feather="x"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $row['0'] ?>">
                  </form>
                </td>
                <td class="col-1 text-center">
                  <form method="POST">
                    <button type="submit" class="btn btn-sm btn-outline-danger" name="delete_milestone">
                      <span data-feather="trash-2"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $row['0'] ?>">
                  </form>
                </td>
              </tr>
<?php
    } else {
?>
              <tr class="d-flex">
                <td class="col-6"><?= htmlspecialchars($row['1']) ?></td>
                <td class="col-2"><?= htmlspecialchars($row['2']) ?></td>
                <td class="col-2"><?= htmlspecialchars($row['3']) ?></td>
                <td class="col-1 text-center">
                  <form method="POST">
                    <button type="submit" class="btn btn-sm btn-danger" name="milestone_completion">
                      <span data-feather="x"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $row['0'] ?>">
                  </form>
                </td>
                <td class="col-1 text-center">
                  <form method="POST">
                    <button type="submit" class="btn btn-sm btn-outline-danger" name="delete_milestone">
                      <span data-feather="trash-2"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $row['0'] ?>">
                  </form>
                </td>
              </tr>
<?php
      }
    }

    $sql_query = 'SELECT Milestones.id, Milestones.name, Milestones.due_date, Teams.name FROM Milestones
                  INNER JOIN Teams ON Milestones.team = Teams.id
                  WHERE (completed = 1 AND project = :project) ORDER BY Milestones.due_date ASC';
    $stmt = $connectedDB->prepare($sql_query);
    $stmt->execute([
      ':project' => $project_row['id']
    ]);
    foreach($stmt as $row) {
?>
              <tr class="d-flex table-secondary text-muted">
                <td class="col-6"><del><?= htmlspecialchars($row['1']) ?></del></td>
                <td class="col-2"><del><?= htmlspecialchars($row['2']) ?></del></td>
                <td class="col-2"><del><?= htmlspecialchars($row['3']) ?></del></td>
                <td class="col-1 text-center">
                  <form method="POST">
                    <button type="submit" class="btn btn-sm btn-success"  name="milestone_completion">
                      <span data-feather="check"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $row['0'] ?>">
                  </form>
                </td>
                <td class="col-1 text-center">
                  <form method="POST">
                    <button type="submit" class="btn btn-sm btn-outline-danger" name="delete_milestone">
                      <span data-feather="trash-2"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $row['0'] ?>">
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
