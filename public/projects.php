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
  $page_title = 'Liste des projets';
  $projects = 'active';

  // Actions des formulaires/boutons dans le tableau

  // Ajouter les projets
  if (isset($_POST['add_project']) || isset($_SESSION['postdata']['add_project'])) {
    // Définir les variables et les mettre vides
    $name = $client = '';

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
      $client = trim($_SESSION['postdata']['client']);
      $sql_query = 'INSERT INTO Projects (name, client)
                    VALUES (:name, :client)';
      $stmt = $connectedDB->prepare($sql_query);
      $stmt->execute([
        ':name' => $name,
        ':client' => $client
      ]);
      unset($_SESSION['postdata']);
      $connectedDB = null;
    }
  }

  // Supprimer les projets
  if (isset($_POST['delete_project']) || isset($_SESSION['postdata']['delete_project'])) {
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
      $sql_query = 'DELETE FROM Projects WHERE id = :id';
      $stmt = $connectedDB->prepare($sql_query);
      $stmt->execute([
        ':id' => $id
      ]);
      unset($_SESSION['postdata']);
      $connectedDB = null;
    }
  }

  // Compléter les projets
  if (isset($_POST['project_completion']) || isset($_SESSION['postdata']['project_completion'])) {
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

      $sql_query = 'SELECT completed FROM Projects WHERE id = :id';
      $stmt = $connectedDB->prepare($sql_query);
      $stmt->execute([
        ':id' => $id
      ]);
      $project = $stmt->fetch();

      if ($project['completed'] == 0) {
        $sql_query = 'UPDATE Projects SET completed = \'1\' WHERE id = :id';
        $stmt = $connectedDB->prepare($sql_query);
        $stmt->execute([
          ':id' => $id
        ]);
      } else {
        $sql_query = 'UPDATE Projects SET completed = \'0\' WHERE id = :id';
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
            <div class="form-row">
              <div class="form-group col-md-9">
                <label class="h6" for="name">Nom du projet</label>
                <input type="text" class="form-control" id="name" name="name" required>
              </div>
              <div class="form-group col-md-3">
                <label class="h6" for="client">Client</label>
                <select class="form-control" name="client" id="client" required>
                  <option value="" disabled selected>Sélectionner un client...</option>
<?php
  include( 'connect.php' );
  $sql_query = 'SELECT id, username FROM Users
                WHERE account_type = 3
                ORDER BY id ASC';
  $stmt = $connectedDB->prepare($sql_query);
  $stmt->execute();
  foreach($stmt as $row) {
?>
                    <option value="<?= htmlspecialchars($row['id']) ?>"><?= htmlspecialchars($row['username']) ?></option>
<?php
  }
?>
                </select>
              </div>
            </div>
            <button class="btn btn-lg btn-outline-primary btn-block" type="submit" name="add_project">Créer un projet</button>
          </form>
          <h2>Projets</h2>
          <table class="table table-bordered table-hover table-sm">
            <thead class="thead-dark">
              <tr class="d-flex">
                <th class="col-8">Nom</th>
                <th class="col-2">Client</th>
                <th class="col-1 text-center">Complétion</th>
                <th class="col-1 text-center">Supprimer</th>
              </tr>
            </thead>
            <tbody>
<?php
  $sql_query = 'SELECT Projects.id, Projects.name, Users.username FROM Projects
                INNER JOIN Users ON Projects.client = Users.id
                WHERE completed = 0 ORDER BY Projects.id ASC';
  $stmt = $connectedDB->prepare($sql_query);
  $stmt->execute();
  foreach($stmt as $row) {
?>
              <tr class="d-flex">
                <td class="col-8"><?= htmlspecialchars($row['1']) ?></td>
                <td class="col-2"><?= htmlspecialchars($row['2']) ?></td>
                <td class="col-1 text-center">
                  <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <button class="btn btn-sm btn-danger" type="submit" name="project_completion">
                      <span data-feather="x"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $row['0'] ?>">
                  </form>
                </td>
                <td class="col-1 text-center">
                  <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <button class="btn btn-sm btn-outline-danger" type="submit" name="delete_project">
                      <span data-feather="trash-2"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $row['0'] ?>">
                  </form>
                </td>
              </tr>
<?php
  }
  $sql_query = 'SELECT Projects.id, Projects.name, Users.username FROM Projects
                INNER JOIN Users ON Projects.client = Users.id
                WHERE completed = 1 ORDER BY Projects.id ASC';
  $stmt = $connectedDB->prepare($sql_query);
  $stmt->execute();
  foreach($stmt as $row) {
?>
              <tr class="d-flex table-secondary text-muted">
                <td class="col-8"><del><?= htmlspecialchars($row['name']) ?></del></td>
                <td class="col-2"><del><?= htmlspecialchars($row['username']) ?></del></td>
                <td class="col-1 text-center">
                  <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <button class="btn btn-sm btn-success" type="submit" name="project_completion">
                      <span data-feather="check"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                  </form>
                </td>
                <td class="col-1 text-center">
                  <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <button class="btn btn-sm btn-outline-danger" type="submit" name="delete_project">
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
            </tbody>
          </table>
        </div>
      </main>
<!-- START INCLUDE FOOTER -->
<?php
include( VIEW_FOOTER );
?>
<!-- END INCLUDE FOOTER -->
</body>
</html>
