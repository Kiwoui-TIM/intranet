<?php
  session_start();
  ob_start();

  // Importer les constantes et changer le titre de la page
  require( 'utils/config.php' );
  $page_title = TASKS_TITLE;

  // S'il n'y a pas d'utilisateur connecté, inclure le script de déconnexion
  if (!$_SESSION['username']) {
    include( UTIL_LOGOUT );
  }

  // Vérifier le niveau d'accès
  include( ACCESS_NO_CLIENT );

  // Actions des formulaires/boutons dans le tableau
  include( FUNCTION_CREATE );
  include( FUNCTION_DELETE );
  include( FUNCTION_CLOCK );
  include( FUNCTION_COMPLETE );

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
                  <input type="text" class="form-control" id="name" name="name" placeholder="Nom de la tâche" required>
                    <select class="custom-select" name="milestone" id="milestone" required>
                      <option value="" disabled selected>Choisir un jalon...</option>
<?php
  include( UTIL_CONNECT );

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

  if ($user['account_type'] == 0) {
    try {
      $sql_query = 'SELECT Milestones.id,
                          Milestones.name,
                          Projects.name AS project
                    FROM Milestones
                    INNER JOIN Projects ON Milestones.project = Projects.id
                    WHERE Milestones.completed = 0
                    ORDER BY Milestones.project, Milestones.id ASC';
      $stmt = $connectedDB->prepare($sql_query);
      $stmt->execute();
    } catch(PDOException $e) {
      echo 'Error: ' . $e->getMessage();
    }

  } else {
    try {
      $sql_query = 'SELECT Milestones.id,
                           Milestones.name,
                           Projects.name AS project
                    FROM Milestones
                    INNER JOIN Projects ON Milestones.project = Projects.id
                    WHERE Milestones.team = :team AND Milestones.completed = 0
                    ORDER BY Milestones.project, Milestones.id ASC';
      $stmt = $connectedDB->prepare($sql_query);
      $stmt->execute([
        ':team' => $_SESSION['team']
      ]);
    } catch(PDOException $e) {
      echo 'Error: ' . $e->getMessage();
    }
  }

  foreach($stmt as $milestone) {
?>
                    <option value="<?= htmlspecialchars($milestone['id']) ?>">[<?= htmlspecialchars($milestone['project']) ?>] <?= htmlspecialchars($milestone['name']) ?></option>
<?php
  }
?>
                  </select>
                  <input class="form-control" type="date" id="due_date" name="due_date" required>
                  <div class="input-group-append">
                    <button class="btn btn-outline-primary" type="submit" name="create_item" value="Tasks">Créer une tâche</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
<?php
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

  if ($user['account_type'] == 0) {
    try {
      $sql_query = 'SELECT Milestones.id,
                           Milestones.name,
                           Projects.name AS project
                    FROM Milestones
                    INNER JOIN Projects ON Milestones.project = Projects.id
                    WHERE Milestones.completed = 0
                    ORDER BY Milestones.project, Milestones.id ASC';
      $stmt = $connectedDB->prepare($sql_query);
      $stmt->execute();
    } catch(PDOException $e) {
      echo 'Error: ' . $e->getMessage();
    }

  } else {
    try {
      $sql_query = 'SELECT Milestones.id,
                           Milestones.name,
                           Projects.name AS project
                    FROM Milestones
                    INNER JOIN Projects ON Milestones.project = Projects.id
                    WHERE (Milestones.team = :team AND Milestones.completed = 0)
                    ORDER BY Milestones.project, Milestones.id ASC';
      $stmt = $connectedDB->prepare($sql_query);
      $stmt->execute([
        ':team' => $_SESSION['team']
      ]);
    } catch(PDOException $e) {
      echo 'Error: ' . $e->getMessage();
    }
  }

  foreach($stmt as $milestone) {
    try {
      $sql_query = 'SELECT id FROM Tasks
                    WHERE milestone = :milestone';
      $stmt = $connectedDB->prepare($sql_query);
      $stmt->execute([
        ':milestone' => $milestone['0']
      ]);
    } catch(PDOException $e) {
      echo 'Error: ' . $e->getMessage();
    }

    if(!empty($stmt->fetch())) {
?>
          <div class="card my-4 border-0 shadow">
            <div class="card-header bg-white">
              <h3 class="h4"><?= htmlspecialchars($milestone['project']) ?></h3>
            </div>
            <div class="card-body">
              <div class="m-2 p-3 bg-light rounded shadow-sm">
                <h4 class="h5 border-bottom border-gray pb-2 mb-0"><?= htmlspecialchars($milestone['name']) ?></h4>
<?php
      try {
        $sql_query = 'SELECT *
                      FROM Tasks
                      WHERE (completed = 0 AND student = :student AND milestone = :milestone)
                      ORDER BY due_date ASC';
        $stmt = $connectedDB->prepare($sql_query);
        $stmt->execute([
          ':student' => $_SESSION['id'],
          ':milestone' => $milestone['id']
        ]);
      } catch(PDOException $e) {
        echo 'Error: ' . $e->getMessage();
      }

      foreach($stmt as $task) {
        if ($task['due_date'] < date('Y-m-d')) {
?>
                <div class="media pt-3 border-bottom border-gray">
                  <form class="mr-2" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <button class="btn btn-sm btn-square btn-danger" type="submit" name="complete_item" value="Tasks">
                      <span data-feather="x"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $task['id'] ?>">
                  </form>
                  <div class="media-body pb-3 mb-0 small lh-125">
                    <div class="d-flex justify-content-between align-items-center w-100">
                      <strong class="text-danger"><?= htmlspecialchars($task['name']) ?></strong>
                    </div>
                    <span class="d-block text-danger"><strong><?= htmlspecialchars($task['time_spent']) ?>h</strong> - <?= htmlspecialchars($task['due_date']) ?></span>
                  </div>
                  <form class="ml-2" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <button class="btn btn-sm btn-square <?= $task['clock'] ? 'btn-warning' : 'btn-info' ?>" type="submit" name="clock_item" value="Tasks">
                      <span data-feather="clock"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $task['id'] ?>">
                  </form>
                  <form class="ml-2" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <button class="btn btn-sm btn-square btn-secondary" type="submit" name="edit_task">
                      <span data-feather="edit"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $task['id'] ?>">
                  </form>
                  <form class="ml-2" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <button class="btn btn-sm btn-square btn-outline-danger" type="submit" name="delete_item" value="Tasks">
                      <span data-feather="trash-2"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $task['id'] ?>">
                  </form>
                </div>
<?php
        } else {
?>
                <div class="media pt-3 border-bottom border-gray">
                  <form class="mr-2" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <button class="btn btn-sm btn-square btn-danger" type="submit" name="complete_item" value="Tasks">
                      <span data-feather="x"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $task['id'] ?>">
                  </form>
                  <div class="media-body pb-3 mb-0 small lh-125">
                    <div class="d-flex justify-content-between align-items-center w-100">
                      <strong><?= htmlspecialchars($task['name']) ?></strong>
                    </div>
                    <span class="d-block"><strong><?= htmlspecialchars($task['time_spent']) ?>h</strong> - <?= htmlspecialchars($task['due_date']) ?></span>
                  </div>
                  <form class="ml-2" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <button class="btn btn-sm btn-square <?= $task['clock'] ? 'btn-warning' : 'btn-info' ?>" type="submit" name="clock_item" value="Tasks">
                      <span data-feather="clock"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $task['id'] ?>">
                  </form>
                  <form class="ml-2" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <button class="btn btn-sm btn-square btn-secondary" type="submit" name="edit_task">
                      <span data-feather="edit"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $task['id'] ?>">
                  </form>
                  <form class="ml-2" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <button class="btn btn-sm btn-square btn-outline-danger" type="submit" name="delete_item" value="Tasks">
                      <span data-feather="trash-2"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $task['id'] ?>">
                  </form>
                </div>
<?php
        }
      }

      try {
        $sql_query = 'SELECT *
                      FROM Tasks
                      WHERE (completed = 1 AND student = :student AND milestone = :milestone)
                      ORDER BY due_date ASC';
        $stmt = $connectedDB->prepare($sql_query);
        $stmt->execute([
          ':student' => $_SESSION['id'],
          ':milestone' => $milestone['id']
        ]);
      } catch(PDOException $e) {
        echo 'Error: ' . $e->getMessage();
      }

      foreach($stmt as $task) {
?>
                <div class="media text-muted pt-3 border-bottom border-gray">
                  <form class="mr-2" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <button class="btn btn-sm btn-square btn-success" type="submit" name="complete_item" value="Tasks">
                      <span data-feather="check"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $task['id'] ?>">
                  </form>
                  <div class="media-body pb-3 mb-0 small lh-125">
                    <div class="d-flex justify-content-between align-items-center w-100">
                      <strong><del><?= htmlspecialchars($task['name']) ?></del></strong>
                    </div>
                    <span class="d-block"><strong><?= htmlspecialchars($task['time_spent']) ?>h</strong> - <?= htmlspecialchars($task['due_date']) ?></span>
                  </div>
                  <form class="ml-2" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <button class="btn btn-sm btn-square <?= $task['clock'] ? 'btn-warning' : 'btn-info' ?>" type="submit" name="clock_item" value="Tasks" disabled>
                      <span data-feather="clock"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $task['id'] ?>">
                  </form>
                  <form class="ml-2" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <button class="btn btn-sm btn-square btn-secondary" type="submit" name="edit_task" disabled>
                      <span data-feather="edit"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $task['id'] ?>">
                  </form>
                  <form class="ml-2" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <button class="btn btn-sm btn-square btn-outline-danger" type="submit" name="delete_item" value="Tasks">
                      <span data-feather="trash-2"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $task['id'] ?>">
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
