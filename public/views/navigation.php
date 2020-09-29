  <div class="container-fluid">
    <div class="row">
      <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
        <div class="sidebar-sticky pt-3">
          <ul class="nav flex-column">
            <li class="nav-item">
              <a class="nav-link <?= $home ?>" href="./">
                <span data-feather="home"></span>
                Tableau de bord
              </a>
            </li>
<?php
  include( 'connect.php' );
  try {
    $query_sql = 'SELECT account_type FROM Users WHERE username = :username LIMIT 1';
    $stmt = $connectedDB->prepare($query_sql);
    $stmt->execute([
      ':username' => $_SESSION['username']
    ]);
    $user = $stmt->fetch();
    $connectedDB = null;
  } catch(PDOException $e) {
    echo 'Error: ' . $e->getMessage();
  }
  if ($user['account_type'] != 3) {
?>
            <li class="nav-item">
              <a class="nav-link <?= $tasks ?>" href="tasks.php">
                <span data-feather="check-square"></span>
                Tâches
              </a>
            </li>
<?php
    if ($user['account_type'] <= 1) {
?>
            <li class="nav-item">
              <a class="nav-link <?= $milestones ?>" href="milestones.php">
                <span data-feather="flag"></span>
                Jalons
              </a>
            </li>
<?php
      if ($user['account_type'] == 0) {
?>
            <li class="nav-item">
              <a class="nav-link <?= $projects ?>" href="projects.php">
                <span data-feather="briefcase"></span>
                Projets
              </a>
            </li>
<?php
      }
    }
  }
?>
          </ul>

          <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            Gestion de compte
          </h6>
          <ul class="nav flex-column mb-2">
            <li class="nav-item">
              <a class="nav-link <?= $change_password ?>" href="change-password.php">
                <span data-feather="lock"></span>
                Changer de mot de passe
              </a>
            </li>
          </ul>

<?php
  if ($user['account_type'] == 0) {
?>
          <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            Administration
          </h6>
          <ul class="nav flex-column mb-2">
            <li class="nav-item">
              <a class="nav-link <?= $all_tasks ?>" href="all-tasks.php">
                <span data-feather="list"></span>
                Toutes les tâches
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= $create_account ?>" href="create-account.php">
                <span data-feather="user-plus"></span>
                Créer un compte
              </a>
            </li>
          </ul>
<?php
  }
?>

          <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            Support
          </h6>
          <ul class="nav flex-column mb-2">
            <li class="nav-item">
              <a class="nav-link" href="https://github.com/Kiwoui-TIM/intranet/issues/new?assignees=JustinVallee&labels=bug&template=rapport-de-bug.md&title=%5BBUG%5D">
                <span data-feather="life-buoy"></span>
                Rapport de bug
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="https://github.com/Kiwoui-TIM/intranet/issues/new?assignees=jakobbouchard&labels=enhancement&template=demande-de-fonctionnalit-.md&title=%5BFEATURE%5D">
                <span data-feather="clipboard"></span>
                Demande de fonctionnalité
              </a>
            </li>
          </ul>
        </div>
      </nav>
