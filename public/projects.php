<?php
  session_start();
  ob_start();

  // Importer les constantes et changer le titre de la page
  require( 'utils/config.php' );
  $page_title = PROJECTS_TITLE;

  // S'il n'y a pas d'utilisateur connecté, inclure le script de déconnexion
  if (!$_SESSION['username']) {
    include( UTIL_LOGOUT );
  }
  // Vérifier le niveau d'accès
  include( ACCESS_ADMIN_ONLY );

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
      include( UTIL_CONNECT );
      $name = trim($_SESSION['postdata']['name']);
      $client = trim($_SESSION['postdata']['client']);

      try {
        $sql_query = 'INSERT INTO Projects (name, client)
                      VALUES (:name, :client)';
        $stmt = $connectedDB->prepare($sql_query);
        $stmt->execute([
          ':name' => $name,
          ':client' => $client
        ]);
      } catch(PDOException $e) {
        echo 'Error: ' . $e->getMessage();
      }

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
      include( UTIL_CONNECT );
      $id = trim($_SESSION['postdata']['id']);

      try {
        $sql_query = 'DELETE FROM Projects WHERE id = :id';
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
      include( UTIL_CONNECT );
      $id = trim($_SESSION['postdata']['id']);

      try {
        $sql_query = 'SELECT completed FROM Projects WHERE id = :id';
        $stmt = $connectedDB->prepare($sql_query);
        $stmt->execute([
          ':id' => $id
        ]);
      } catch(PDOException $e) {
        echo 'Error: ' . $e->getMessage();
      }

      $project = $stmt->fetch();

      if ($project['completed'] == 0) {
        try {
          $sql_query = 'UPDATE Projects SET completed = \'1\' WHERE id = :id';
          $stmt = $connectedDB->prepare($sql_query);
          $stmt->execute([
            ':id' => $id
          ]);
        } catch(PDOException $e) {
          echo 'Error: ' . $e->getMessage();
        }
      } else {
        try {
          $sql_query = 'UPDATE Projects SET completed = \'0\' WHERE id = :id';
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
                  <input type="text" class="form-control" id="name" name="name" placeholder="Nom du projet" required>
                  <select class="custom-select" name="client" id="client" required>
                    <option value="" disabled selected>Choisir un client...</option>
<?php
  include( UTIL_CONNECT );

  try {
    $sql_query = 'SELECT id, username
                  FROM Users
                  WHERE account_type = 3
                  ORDER BY id ASC';
    $stmt = $connectedDB->prepare($sql_query);
    $stmt->execute();
  } catch(PDOException $e) {
    echo 'Error: ' . $e->getMessage();
  }

  foreach($stmt as $client) {
?>
                      <option value="<?= htmlspecialchars($client['id']) ?>"><?= htmlspecialchars($client['username']) ?></option>
<?php
  }
?>
                  </select>
                  <div class="input-group-append">
                  	<button class="btn btn-outline-primary" type="submit" name="add_project">Créer un projet</button>
                  </div>
                </div>
              </form>
            </div>
          </div>

          <div class="card my-4 border-0 shadow">
            <div class="card-header bg-white">
              <h3 class="h4">Projets</h3>
            </div>
            <div class="card-body">
              <div class="m-2 p-3 bg-light rounded shadow-sm">
<?php
  try {
    $sql_query = 'SELECT Projects.id,
                         Projects.name,
                         Users.username AS client
                  FROM Projects
                  INNER JOIN Users ON Projects.client = Users.id
                  WHERE completed = 0 ORDER BY Projects.id ASC';
    $stmt = $connectedDB->prepare($sql_query);
    $stmt->execute();
  } catch(PDOException $e) {
    echo 'Error: ' . $e->getMessage();
  }

  foreach($stmt as $project) {
?>
                <div class="media pt-3 border-bottom border-gray">
                  <form class="mr-2" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <button class="btn btn-sm btn-square btn-danger" type="submit" name="project_completion">
                      <span data-feather="x"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $project['id'] ?>">
                  </form>
                  <div class="media-body pb-3 mb-0 small lh-125">
                    <div class="d-flex justify-content-between align-items-center w-100">
                      <strong><?= htmlspecialchars($project['name']) ?></strong>
                    </div>
                    <span class="d-block"><?= htmlspecialchars($project['client']) ?></span>
                  </div>
                  <form class="ml-2" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <button class="btn btn-sm btn-square btn-secondary" type="submit" name="edit_project">
                      <span data-feather="edit"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $project['id'] ?>">
                  </form>
                  <form class="ml-2" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <button class="btn btn-sm btn-square btn-outline-danger" type="submit" name="delete_project">
                      <span data-feather="trash-2"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $project['id'] ?>">
                  </form>
                </div>
<?php
  }

  try {
    $sql_query = 'SELECT Projects.id,
                         Projects.name,
                         Users.username AS client
                  FROM Projects
                  INNER JOIN Users ON Projects.client = Users.id
                  WHERE completed = 1 ORDER BY Projects.id ASC';
    $stmt = $connectedDB->prepare($sql_query);
    $stmt->execute();
  } catch(PDOException $e) {
    echo 'Error: ' . $e->getMessage();
  }

  foreach($stmt as $project) {
?>
                <div class="media text-muted pt-3 border-bottom border-gray">
                  <form class="mr-2" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <button class="btn btn-sm btn-square btn-success" type="submit" name="project_completion">
                      <span data-feather="check"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $project['id'] ?>">
                  </form>
                  <div class="media-body pb-3 mb-0 small lh-125">
                    <div class="d-flex justify-content-between align-items-center w-100">
                      <strong><del><?= htmlspecialchars($project['name']) ?></del></strong>
                    </div>
                    <span class="d-block"><?= htmlspecialchars($project['client']) ?></span>
                  </div>
                  <form class="ml-2" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <button class="btn btn-sm btn-square btn-secondary" type="submit" name="edit_project" disabled>
                      <span data-feather="edit"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $project['id'] ?>">
                  </form>
                  <form class="ml-2" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <button class="btn btn-sm btn-square btn-outline-danger" type="submit" name="delete_project">
                      <span data-feather="trash-2"></span>
                    </button>
                    <input type="hidden" name="id" value="<?= $project['id'] ?>">
                  </form>
                </div>
<?php
  }

  $connectedDB = null;
?>
              </div>
            </div>
          </div>
        </div>
      </main>
<!-- START INCLUDE FOOTER -->
<?php
  include( VIEW_FOOTER );
?>
<!-- END INCLUDE FOOTER -->
</body>
</html>
