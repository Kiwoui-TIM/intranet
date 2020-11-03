<?php
  session_start();
  ob_start();

  // Importer les constantes et changer le titre de la page
  require( 'utils/config.php' );
  $page_title = ALL_TASKS_TITLE;

  // S'il n'y a pas d'utilisateur connecté, inclure le script de déconnexion
  if (!$_SESSION['username']) {
    include( UTIL_LOGOUT );
  }
  // Vérifier le niveau d'accès
  include( ACCESS_ADMIN_ONLY );
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
        <div class="container">
          <div class="card my-4 border-0 shadow">
            <div class="card-body">
              <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="GET">
                <div class="input-group input-group-lg">
                  <select class="custom-select" name="project" id="project" required>
                    <option value="" disabled <?= empty($_GET['project']) ? 'selected' : null ?>>Choisir un projet...</option>
<?php
  include( UTIL_CONNECT );

  try {
    $sql_query = 'SELECT *
                  FROM   Projects
                  ORDER  BY id ASC';
    $projects = $connectedDB->prepare($sql_query);
    $projects->execute();
  } catch(PDOException $e) {
    echo 'Error: ' . $e->getMessage();
  }

  foreach($projects as $project) {
?>
                    <option value="<?= htmlspecialchars($project['id']) ?>" <?= $_GET['project'] == $project['id'] ? 'selected' : null ?>>
                      <?= htmlspecialchars($project['name']) ?>
                    </option>
<?php
  }
?>
                  </select>
                  <div class="input-group-append">
                    <button class="btn btn-outline-primary" type="submit">Afficher</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
<?php
  if (!empty($_GET['project'])) {
    // Project querying
    try {
      $sql_query = 'SELECT id,
                           name,
                           completed
                    FROM   Projects
                    WHERE  id = :project
                    ORDER  BY id ASC';
      $projects = $connectedDB->prepare($sql_query);
      $projects->execute([
        ':project' => $_GET['project']
      ]);
      $project = $projects->fetch();
    } catch(PDOException $e) {
      echo 'Error: ' . $e->getMessage();
    }
?>
          <div class="card my-4 border-0 shadow">
            <div class="card-header bg-white">
              <h3 class="h4">
                <?= htmlspecialchars($project['name']) ?>
                <?= $project['completed'] == 1 ? '<span class="badge badge-success">Complété</span>' : '<span class="badge badge-danger">Incomplet</span>' ?>
              </h3>
            </div>
            <div class="card-body">
<?php
    // Milestone querying
    $task_exists = false;
    try {
      $sql_query = 'SELECT id,
                           name,
                           completed
                    FROM   Milestones
                    WHERE  project = :project
                    ORDER  BY id ASC';
      $milestones = $connectedDB->prepare($sql_query);
      $milestones->execute([
        ':project' => $project['id']
      ]);
    } catch(PDOException $e) {
      echo 'Error: ' . $e->getMessage();
    }

    foreach ($milestones as $milestone) {
?>
              <div class="m-2 p-3 bg-light rounded shadow-sm">
                <h4 class="h5 border-bottom border-gray pb-2 mb-0">
                  <?= htmlspecialchars($milestone['name']) ?>
                  <?= $milestone['completed'] == 1 ? '<span class="badge badge-success">Complété</span>' : '<span class="badge badge-danger">Incomplet</span>' ?>
                </h4>
<?php
      try {
        $sql_query = 'SELECT *
                      FROM   Users
                      ORDER  BY id ASC';
        $students = $connectedDB->prepare($sql_query);
        $students->execute();
      } catch(PDOException $e) {
        echo 'Error: ' . $e->getMessage();
      }

      foreach ($students as $student) {
        try {
          $sql_query = 'SELECT id
                        FROM   Tasks
                        WHERE  milestone = :milestone AND student = :student';
          $tasks = $connectedDB->prepare($sql_query);
          $tasks->execute([
            ':milestone' => $milestone['id'],
            ':student' => $student['id']
          ]);
        } catch(PDOException $e) {
          echo 'Error: ' . $e->getMessage();
        }

        if(!empty($tasks->fetch())) {
          $task_exists = true;
?>
                <div class="m-2 p-3 bg-white rounded shadow-sm">
                  <h5 class="h6 border-bottom border-gray pb-2 mb-0">
                    <?= htmlspecialchars($student['username']) ?>
                  </h5>
<?php

          try {
            $sql_query = 'SELECT *
                          FROM   Tasks
                          WHERE  completed = 0 AND student = :student AND milestone = :milestone
                          ORDER  BY due_date ASC';
            $tasks = $connectedDB->prepare($sql_query);
            $tasks->execute([
              ':student' => $student['id'],
              ':milestone' => $milestone['id']
            ]);
          } catch(PDOException $e) {
            echo 'Error: ' . $e->getMessage();
          }

          foreach($tasks as $task) {
            if ($task['due_date'] < date('Y-m-d')) {
?>
                  <div class="media pt-3 border-bottom border-gray">
                    <div class="media-body pb-3 mb-0 lh-125">
                      <div class="d-flex justify-content-between align-items-center w-100">
                        <strong class="text-danger"><?= htmlspecialchars($task['name']) ?></strong>
                      </div>
                      <span class="d-block text-danger"><strong><?= htmlspecialchars($task['time_spent']) ?>h</strong> - <?= htmlspecialchars($task['due_date']) ?></span>
                    </div>
                  </div>
<?php
            } else {
?>
                  <div class="media pt-3 border-bottom border-gray">
                    <div class="media-body pb-3 mb-0 lh-125">
                      <div class="d-flex justify-content-between align-items-center w-100">
                        <strong><?= htmlspecialchars($task['name']) ?></strong>
                      </div>
                      <span class="d-block"><strong><?= htmlspecialchars($task['time_spent']) ?>h</strong> - <?= htmlspecialchars($task['due_date']) ?></span>
                    </div>
                  </div>
<?php
            }
          }

          try {
            $sql_query = 'SELECT *
                          FROM   Tasks
                          WHERE  completed = 1 AND student = :student AND milestone = :milestone
                          ORDER  BY due_date ASC';
            $tasks = $connectedDB->prepare($sql_query);
            $tasks->execute([
              ':student' => $_SESSION['id'],
              ':milestone' => $milestone['id']
            ]);
          } catch(PDOException $e) {
            echo 'Error: ' . $e->getMessage();
          }

          foreach($tasks as $task) {
?>
                  <div class="media text-muted pt-3 border-bottom border-gray">
                    <div class="media-body pb-3 mb-0 lh-125">
                      <div class="d-flex justify-content-between align-items-center w-100">
                        <strong><del><?= htmlspecialchars($task['name']) ?></del></strong>
                      </div>
                      <span class="d-block"><strong><?= htmlspecialchars($task['time_spent']) ?>h</strong> - <?= htmlspecialchars($task['due_date']) ?></span>
                    </div>
                  </div>
<?php
          }
?>
                </div>
<?php
        }
      }
      if (!$task_exists) {
?>
                <h5 class="h6 m-2">
                  Aucune tâche trouvée
                </h5>
<?php
      }
      $task_exists = false;
?>
              </div>
<?php
    }
?>
            </div>
          </div>
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
