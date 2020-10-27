<?php
  session_start();
  ob_start();

  // Importer les constantes et changer le titre de la page
  require( 'utils/config.php' );
  $page_title = HOME_TITLE;

  // S'il n'y a pas d'utilisateur connecté, inclure le script de déconnexion
  if (!$_SESSION['username']) {
    include( UTIL_LOGOUT );
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
<?php
  if (!$_SESSION['already_seen']) {
?>
  <div class="position-absolute d-flex align-items-center justify-content-center" id="spinner-container">
    <div id="spinner" class="spinner-border text-secondary" role="status">
      <span class="sr-only">Chargement...</span>
    </div>
  </div>
<?php
  }
?>
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

  if ($user['account_type'] == 3) {
    try {
      $sql_query = 'SELECT id, name FROM Projects
                    WHERE client = :client';
      $stmt = $connectedDB->prepare($sql_query);
      $stmt->execute([
        ':client' => $_SESSION['id']
      ]);
    } catch(PDOException $e) {
      echo 'Error: ' . $e->getMessage();
    }

  } elseif ($user['account_type'] == 0) {
    try {
      $sql_query = 'SELECT DISTINCT Projects.id, Projects.name FROM Projects
                    INNER JOIN Milestones ON Projects.id = Milestones.project
                    ORDER BY Projects.id ASC';
      $stmt = $connectedDB->prepare($sql_query);
      $stmt->execute();
    } catch(PDOException $e) {
      echo 'Error: ' . $e->getMessage();
    }

  } else {
    try {
      $sql_query = 'SELECT DISTINCT Projects.id, Projects.name FROM Projects
                    INNER JOIN Milestones ON Projects.id = Milestones.project
                    WHERE Milestones.team = :team
                    ORDER BY Projects.id ASC';
      $stmt = $connectedDB->prepare($sql_query);
      $stmt->execute([
        ':team' => $_SESSION['team']
      ]);
    } catch(PDOException $e) {
      echo 'Error: ' . $e->getMessage();
    }
  }

  foreach($stmt as $project_row) {
    try {
      $sql_query = 'SELECT id, name, due_date, completed FROM Milestones
                    WHERE project = :project
                    ORDER BY due_date ASC';
      $stmt = $connectedDB->prepare($sql_query);
      $stmt->execute([
        ':project' => $project_row['id']
      ]);
    } catch(PDOException $e) {
      echo 'Error: ' . $e->getMessage();
    }

    $completed = $total = 0;

    foreach($stmt as $row) {
      if ($row['completed']) {
        $completed++;
      }
      $total++;
    }

    $percentage = $completed / $total * 100;
?>
          <div class="card my-4 border-0 shadow">
            <div class="card-header bg-white">
              <h2><?= $project_row['name'] ?></h2>
            </div>
            <div class="card-body">
              <div class="progress mx-2 mb-4 shadow-sm" style="height: 25px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated <?= $percentage == 100 ? 'bg-success' : 'bg-info' ?>" role="progressbar" aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= $percentage ?>%">
                  <strong>
                    <?= $percentage == 100 ? 'Complété' : round($percentage) ?>%
                  </strong>
                </div>
              </div>
              <div class="m-2 overflow-auto rounded shadow-sm">
                <table class="mb-0 table">
                  <thead class="thead-dark">
                    <tr class="d-flex">
                      <th class="col-10">Jalon</th>
                      <th class="col-2">Date d'échéance</th>
                    </tr>
                  </thead>
                  <tbody>
<?php
    try {
      $sql_query = 'SELECT id, name, due_date, completed FROM Milestones
                    WHERE project = :project
                    ORDER BY due_date ASC';
      $stmt = $connectedDB->prepare($sql_query);
      $stmt->execute([
        ':project' => $project_row['id']
      ]);
    } catch(PDOException $e) {
      echo 'Error: ' . $e->getMessage();
    }

    foreach($stmt as $row) {
?>
                    <tr class="d-flex <?=$row['completed'] ? 'table-success' : 'table-danger' ?>">
                      <td class="col-10"><?= htmlspecialchars($row['name']) ?></td>
                      <td class="col-2"><?= htmlspecialchars($row['due_date']) ?></td>
                    </tr>

<?php
    }
?>

                  </tbody>
                </table>
              </div>
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
<?php
  if (!$_SESSION['already_seen']) {
?>
<script>
  setTimeout(function () {
    document.getElementById('spinner-container').style.opacity='0';
    setTimeout(function () {
      document.getElementById('spinner-container').remove();
    }, 750);
  }, 1250);
</script>
<?php
  }

  $_SESSION['already_seen'] = true;
?>
</body>
</html>
