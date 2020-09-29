<?php
session_start();
ob_start();
// S'il n'y a pas d'utilisateur connecté, inclure le script de déconnexion
if (!$_SESSION['username']) {
  include( 'logout.php' );
}
// Vérifier le niveau d'accès
include( 'connect.php' );
try {
  $query_sql = 'SELECT account_type FROM Users WHERE username = :username LIMIT 1';
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
require( 'config.php' );
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
<?php
  include( 'connect.php' );
  $sql_query = 'SELECT id, username FROM Users
                WHERE account_type != 3
                ORDER BY id ASC';
  $stmt = $connectedDB->prepare($sql_query);
  $stmt->execute();
  foreach($stmt as $student_row) {
?>
          <h2><?= $student_row['username'] ?></h2>
          <table class="table table-bordered table-hover table-sm">
            <thead class="thead-dark">
              <tr class="d-flex">
                <th class="col-8">Nom</th>
                <th class="col-2">Date d'échéance</th>
                <th class="col-2">Temps</th>
              </tr>
            </thead>
            <tbody>
<?php
    $sql_query = 'SELECT * FROM Tasks
                  WHERE (completed = 0 AND student = :student) ORDER BY id ASC';
    $stmt = $connectedDB->prepare($sql_query);
    $stmt->execute([
      ':student' => $student_row['id']
    ]);
    foreach($stmt as $row) {
      if ($row['due_date'] < date('Y-m-d')) {
?>
              <tr class="d-flex table-danger">
                <td class="col-8"><?= htmlspecialchars($row['name']) ?></td>
                <td class="col-2"><?= htmlspecialchars($row['due_date']) ?></td>
                <td class="col-2"><?= htmlspecialchars($row['time_spent']) ?>h</td>
              </tr>
<?php
      } else {
?>
              <tr class="d-flex">
                <td class="col-8"><?= htmlspecialchars($row['name']) ?></td>
                <td class="col-2"><?= htmlspecialchars($row['due_date']) ?></td>
                <td class="col-2"><?= htmlspecialchars($row['time_spent']) ?>h</td>
              </tr>
<?php
      }
    }
    $sql_query = 'SELECT * FROM Tasks
                  WHERE (completed = 1 AND student = :student) ORDER BY id ASC';
    $stmt = $connectedDB->prepare($sql_query);
    $stmt->execute([
      ':student' => $student_row['id']
    ]);
    foreach($stmt as $row) {
?>
              <tr class="d-flex table-secondary text-muted">
                <td class="col-8"><del><?= htmlspecialchars($row['name']) ?></del></td>
                <td class="col-2"><del><?= htmlspecialchars($row['due_date']) ?></del></td>
                <td class="col-2"><del><?= htmlspecialchars($row['time_spent']) ?>h</del></td>
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
