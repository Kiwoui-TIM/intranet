<?php
  session_start();
  ob_start();
  // S'il n'y a pas d'utilisateur connecté, inclure le script de déconnexion
  if (!$_SESSION['username']) {
    include( 'utils/logout.php' );
  }
  // Vérifier le niveau d'accès
  include( 'utils/connect.php' );
  try {
    $sql_query = 'SELECT account_type FROM Users WHERE username = :username LIMIT 1';
    $stmt = $connectedDB->prepare($sql_query);
    $stmt->execute([
      ':username' => $_SESSION["username"]
    ]);
    $user = $stmt->fetch();
  } catch(PDOException $e) {
    echo 'Error: ' . $e->getMessage();
  }
  if ($user['account_type'] > 1) {
    header('location: index.php');
    exit;
  }
  $connectedDB = null;
  require( 'utils/config.php' );
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
      include( 'utils/connect.php' );
      $name = trim($_SESSION['postdata']['name']);
      $project = trim($_SESSION['postdata']['project']);
      $due_date = trim($_SESSION['postdata']['due_date']);
      $team = trim($_SESSION['postdata']['team']);

      try {
        $sql_query = 'INSERT INTO Milestones (name, project, team, due_date)
                      VALUES (:name, :project, :team, :due_date)';
        $stmt = $connectedDB->prepare($sql_query);
        $stmt->execute([
          ':name' => $name,
          ':project' => $project,
          ':team' => $team,
          ':due_date' => $due_date
        ]);
      } catch(PDOException $e) {
        echo 'Error: ' . $e->getMessage();
      }

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
      include( 'utils/connect.php' );
      $id = trim($_SESSION['postdata']['id']);

      try {
        $sql_query = 'DELETE FROM Milestones WHERE id = :id';
        $stmt = $connectedDB->prepare($sql_query);
        $stmt->execute([
          ':id' => $id
        ]);
      } catch(PDOException $e) {
        echo 'Error: ' . $e->getMessage();
      }

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
      include( 'utils/connect.php' );
      $id = trim($_SESSION['postdata']['id']);

      try {
        $sql_query = 'SELECT completed FROM Milestones WHERE id = :id';
        $stmt = $connectedDB->prepare($sql_query);
        $stmt->execute([
          ':id' => $id
        ]);
      } catch(PDOException $e) {
        echo 'Error: ' . $e->getMessage();
      }

      $milestone = $stmt->fetch();

      if ($milestone['completed'] == 0) {
        try {
          $sql_query = 'UPDATE Milestones SET completed = \'1\' WHERE id = :id';
          $stmt = $connectedDB->prepare($sql_query);
          $stmt->execute([
            ':id' => $id
          ]);
        } catch(PDOException $e) {
          echo 'Error: ' . $e->getMessage();
        }
      } else {
        try {
          $sql_query = 'UPDATE Milestones SET completed = \'0\' WHERE id = :id';
          $stmt = $connectedDB->prepare($sql_query);
          $stmt->execute([
            ':id' => $id
          ]);
        } catch(PDOException $e) {
          echo 'Error: ' . $e->getMessage();
        }
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
<body class="bg-light">
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
        <div class="container">
          <div class="card my-4 border-0 shadow">
            <div class="card-body">
              <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post">
                <div class="input-group input-group-lg">
                  <input type="text" class="form-control" id="name" name="name" placeholder="Nom du jalon" required>
                  <select class="custom-select" name="project" id="project" required>
                    <option value="" disabled selected>Choisir un projet...</option>
<?php
  include( 'utils/connect.php' );

  try {
    $sql_query = 'SELECT id, name FROM Projects
                  ORDER BY id ASC';
    $stmt = $connectedDB->prepare($sql_query);
    $stmt->execute();
  } catch(PDOException $e) {
    echo 'Error: ' . $e->getMessage();
  }

  foreach($stmt as $project) {
?>
                    <option value="<?= htmlspecialchars($project['id']) ?>"><?= htmlspecialchars($project['name']) ?></option>
<?php
  }
?>
                  </select>
                  <select class="custom-select" name="team" id="team" required>
                    <option value="" disabled selected>Choisir une équipe...</option>
                    <option value="2">Graphistes</option>
                    <option value="3">Programmeurs</option>
                    <option value="4">Intégrateurs web</option>
                    <option value="5">Intégrateurs vidéo</option>
                  </select>
                  <input class="form-control" type="date" id="due_date" name="due_date" required>
                  <div class="input-group-append">
                    <button class="btn btn-outline-primary" type="submit" name="add_milestone">Créer un jalon</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
<?php
  try {
    $sql_query = 'SELECT id, name
                  FROM Projects
                  WHERE completed = 0
                  ORDER BY id ASC';
    $stmt = $connectedDB->prepare($sql_query);
    $stmt->execute();
  } catch(PDOException $e) {
    echo 'Error: ' . $e->getMessage();
  }

  foreach($stmt as $project) {
    try {
      $sql_query = 'SELECT id
                    FROM Milestones
                    WHERE project = :project';
      $stmt = $connectedDB->prepare($sql_query);
      $stmt->execute([
        ':project' => $project['id']
      ]);
    } catch(PDOException $e) {
      echo 'Error: ' . $e->getMessage();
    }

    if(!empty($stmt->fetch())) {
?>
          <div class="card my-4 border-0 shadow">
            <div class="card-header bg-white">
              <h3 class="h4"><?= htmlspecialchars($project['name']) ?></h3>
            </div>
            <div class="card-body">
              <div class="m-2 p-3 bg-light rounded shadow-sm">
<?php
      try {
        $sql_query = 'SELECT Milestones.id,
                            Milestones.name,
                            Milestones.due_date,
                            Teams.name AS team
                      FROM Milestones
                      INNER JOIN Teams ON Milestones.team = Teams.id
                      WHERE (completed = 0 AND project = :project)
                      ORDER BY Milestones.due_date ASC';
        $stmt = $connectedDB->prepare($sql_query);
        $stmt->execute([
          ':project' => $project['id']
        ]);
      } catch(PDOException $e) {
        echo 'Error: ' . $e->getMessage();
      }

      foreach($stmt as $milestone) {
        if ($milestone['due_date'] < date('Y-m-d')) {
?>
                <div class="media pt-3 border-bottom border-gray">
                  <form class="mr-2" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <button class="btn btn-sm btn-square btn-danger" type="submit" name="milestone_completion">
                      <span data-feather="x"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $milestone['id'] ?>">
                  </form>
                  <div class="media-body pb-3 mb-0 small lh-125">
                    <div class="d-flex justify-content-between align-items-center w-100">
                      <strong class="text-danger"><?= htmlspecialchars($milestone['name']) ?></strong>
                    </div>
                    <span class="d-block text-danger"><strong><?= htmlspecialchars($milestone['team']) ?></strong> - <?= htmlspecialchars($milestone['due_date']) ?></span>
                  </div>
                  <form class="ml-2" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <button class="btn btn-sm btn-square btn-info" type="submit" name="edit_milestone">
                      <span data-feather="edit"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $milestone['id'] ?>">
                  </form>
                  <form class="ml-2" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <button class="btn btn-sm btn-square btn-outline-danger" type="submit" name="delete_milestone">
                      <span data-feather="trash-2"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $milestone['id'] ?>">
                  </form>
                </div>
<?php
      } else {
?>
                <div class="media pt-3 border-bottom border-gray">
                  <form class="mr-2" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <button class="btn btn-sm btn-square btn-danger" type="submit" name="milestone_completion">
                      <span data-feather="x"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $milestone['id'] ?>">
                  </form>
                  <div class="media-body pb-3 mb-0 small lh-125">
                    <div class="d-flex justify-content-between align-items-center w-100">
                      <strong><?= htmlspecialchars($milestone['name']) ?></strong>
                    </div>
                    <span class="d-block"><strong><?= htmlspecialchars($milestone['team']) ?></strong> - <?= htmlspecialchars($milestone['due_date']) ?></span>
                  </div>
                  <form class="ml-2" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <button class="btn btn-sm btn-square btn-info" type="submit" name="edit_milestone">
                      <span data-feather="edit"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $milestone['id'] ?>">
                  </form>
                  <form class="ml-2" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <button class="btn btn-sm btn-square btn-outline-danger" type="submit" name="delete_milestone">
                      <span data-feather="trash-2"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $milestone['id'] ?>">
                  </form>
                </div>
<?php
        }
      }

      try {
        $sql_query = 'SELECT Milestones.id,
                            Milestones.name,
                            Milestones.due_date,
                            Teams.name AS team
                      FROM Milestones
                      INNER JOIN Teams ON Milestones.team = Teams.id
                      WHERE (completed = 1 AND project = :project)
                      ORDER BY Milestones.due_date ASC';
        $stmt = $connectedDB->prepare($sql_query);
        $stmt->execute([
          ':project' => $project['id']
        ]);
      } catch(PDOException $e) {
        echo 'Error: ' . $e->getMessage();
      }

      foreach($stmt as $milestone) {
?>
                <div class="media text-muted pt-3 border-bottom border-gray">
                  <form class="mr-2" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <button class="btn btn-sm btn-square btn-success" type="submit" name="milestone_completion">
                      <span data-feather="check"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $milestone['id'] ?>">
                  </form>
                  <div class="media-body pb-3 mb-0 small lh-125">
                    <div class="d-flex justify-content-between align-items-center w-100">
                      <strong><del><?= htmlspecialchars($milestone['name']) ?></del></strong>
                    </div>
                    <span class="d-block"><strong><?= htmlspecialchars($milestone['team']) ?></strong> - <?= htmlspecialchars($milestone['due_date']) ?></span>
                  </div>
                  <form class="ml-2" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <button class="btn btn-sm btn-square btn-info" type="submit" name="edit_milestone" disabled>
                      <span data-feather="edit"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $milestone['id'] ?>">
                  </form>
                  <form class="ml-2" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <button class="btn btn-sm btn-square btn-outline-danger" type="submit" name="delete_milestone">
                      <span data-feather="trash-2"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $milestone['id'] ?>">
                  </form>
                </div>
<?php
      }
?>
              </div>
            </div>
          </div>
<?php
    }
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
