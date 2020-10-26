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
    $query_sql = 'SELECT account_type
                  FROM Users
                  WHERE username = :username LIMIT 1';
    $stmt = $connectedDB->prepare($query_sql);
    $stmt->execute([
      ':username' => $_SESSION['username']
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
  require( 'utils/config.php' );
  $page_title = 'Liste de toutes les tâches';
  $all_tasks = 'active';
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
<?php
  include( 'utils/connect.php' );
  $sql_query = 'SELECT id, username FROM Users
                WHERE account_type != 3
                ORDER BY id ASC';
  $stmt = $connectedDB->prepare($sql_query);
  $stmt->execute();

  foreach($stmt as $student_row) {
    $sql_query = 'SELECT id FROM Tasks
                  WHERE student = :student';
    $stmt = $connectedDB->prepare($sql_query);
    $stmt->execute([
      ':student' => $student_row['id']
    ]);

    if(!empty($stmt->fetch())) {
?>
          <h2><?= $student_row['username'] ?></h2>
          <table class="table table-bordered table-hover table-sm">
            <thead class="thead-dark">
              <tr class="d-flex">
                <th class="col-3">Nom</th>
                <th class="col-3">Jalon</th>
                <th class="col-3">Projet</th>
                <th class="col-1">Temps</th>
                <th class="col-2">Échéance</th>
              </tr>
            </thead>
            <tbody>
<?php
      $sql_query = 'SELECT
                      Tasks.name AS task_name,
                      Tasks.due_date,
                      Tasks.time_spent,
                      Milestones.name AS milestone_name,
                      Milestones.project AS milestone_project,
                      Projects.name AS project_name
                    FROM Tasks
                      LEFT JOIN Milestones ON Tasks.milestone = Milestones.id
                      LEFT JOIN Projects ON Milestones.project = Projects.id
                    WHERE (Tasks.completed = 0 AND Tasks.student = :student)
                    ORDER BY Tasks.id ASC';
      $stmt = $connectedDB->prepare($sql_query);
      $stmt->execute([
        ':student' => $student_row['id']
      ]);

      foreach($stmt as $row) {
        if ($row['due_date'] < date('Y-m-d')) {
?>
              <tr class="d-flex table-danger">
                <td class="col-3"><?= htmlspecialchars($row['task_name']) ?></td>
                <td class="col-3"><?= htmlspecialchars($row['milestone_name']) ?></td>
                <td class="col-3"><?= htmlspecialchars($row['project_name']) ?></td>
                <td class="col-1"><?= htmlspecialchars($row['time_spent']) ?>h</td>
                <td class="col-2"><?= htmlspecialchars($row['due_date']) ?></td>
              </tr>
<?php
        } else {
?>
              <tr class="d-flex">
                <td class="col-3"><?= htmlspecialchars($row['task_name']) ?></td>
                <td class="col-3"><?= htmlspecialchars($row['milestone_name']) ?></td>
                <td class="col-3"><?= htmlspecialchars($row['project_name']) ?></td>
                <td class="col-1"><?= htmlspecialchars($row['time_spent']) ?>h</td>
                <td class="col-2"><?= htmlspecialchars($row['due_date']) ?></td>
              </tr>
<?php
      }
    }
    $sql_query = 'SELECT
                    Tasks.name AS task_name,
                    Tasks.due_date,
                    Tasks.time_spent,
                    Milestones.name AS milestone_name,
                    Milestones.project AS milestone_project,
                    Projects.name AS project_name
                  FROM Tasks
                    LEFT JOIN Milestones ON Tasks.milestone = Milestones.id
                    LEFT JOIN Projects ON Milestones.project = Projects.id
                  WHERE (Tasks.completed = 1 AND Tasks.student = :student)
                  ORDER BY Tasks.id ASC';
    $stmt = $connectedDB->prepare($sql_query);
    $stmt->execute([
      ':student' => $student_row['id']
    ]);

    foreach($stmt as $row) {
?>
              <tr class="d-flex table-secondary text-muted">
                <td class="col-3"><?= htmlspecialchars($row['task_name']) ?></td>
                <td class="col-3"><?= htmlspecialchars($row['milestone_name']) ?></td>
                <td class="col-3"><?= htmlspecialchars($row['project_name']) ?></td>
                <td class="col-1"><?= htmlspecialchars($row['time_spent']) ?>h</td>
                <td class="col-2"><?= htmlspecialchars($row['due_date']) ?></td>
              </tr>
<?php
    }
?>
            </tbody>
          </table>
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
