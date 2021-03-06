  <div class="container-fluid">
    <div class="row">
      <!-- Menu de navigation, utilisable en mobile avec le bouton dans le header -->
      <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-white sidebar collapse shadow">
        <div class="sidebar-sticky pt-3">
          <ul class="nav flex-column">
            <li class="nav-item">
              <!-- Si le titre de la page est celui de la page d'accueil,
                   mettre le lien comme actif. Les autres liens fonctionnent
                   de la même manière -->
              <a class="nav-link <?= $page_title == HOME_TITLE ? 'active' : null ?>" href="/">
                <span data-feather="home"></span>
                Tableau de bord
              </a>
            </li>
<?php
  // Se connecte à la base de données et récupère le niveau d'accès de l'utilisateur
  include( UTIL_CONNECT );
  try {
    $sql_query = "SELECT account_type
                  FROM   Users
                  WHERE  username = :username
                  LIMIT  1";
    $stmt = $connectedDB->prepare($sql_query);
    $stmt->execute([
      ':username' => $_SESSION['username']
    ]);
    $user = $stmt->fetch();

  } catch(PDOException $e) {
    echo 'Error: ' . $e->getMessage();
  }

  $connectedDB = null;

  // Si ce n'est pas un client
  if ($user['account_type'] != 3) {
?>
            <li class="nav-item">
              <a class="nav-link <?= $page_title == TASKS_TITLE ? 'active' : null ?>" href="tasks.php">
                <span data-feather="check-square"></span>
                Tâches
              </a>
            </li>
<?php
    // Si c'est au moins un chef d'équipe
    if ($user['account_type'] <= 1) {
?>
            <li class="nav-item">
              <a class="nav-link <?= $page_title == MILESTONES_TITLE ? 'active' : null ?>" href="milestones.php">
                <span data-feather="flag"></span>
                Jalons
              </a>
            </li>
<?php
      // Si c'est un admin seulement
      if ($user['account_type'] == 0) {
?>
            <li class="nav-item">
              <a class="nav-link <?= $page_title == PROJECTS_TITLE ? 'active' : null ?>" href="projects.php">
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
              <a class="nav-link <?= $page_title == CHANGE_PWD_TITLE ? 'active' : null ?>" href="change-password.php">
                <span data-feather="lock"></span>
                Changer de mot de passe
              </a>
            </li>
          </ul>

<?php
  // Si c'est un admin seulement
  if ($user['account_type'] == 0) {
?>
          <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            Administration
          </h6>
          <ul class="nav flex-column mb-2">
            <li class="nav-item">
              <a class="nav-link <?= $page_title == ALL_TASKS_TITLE ? 'active' : null ?>" href="all-tasks.php">
                <span data-feather="list"></span>
                Toutes les tâches
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= $page_title == CREATE_ACC_TITLE ? 'active' : null ?>" href="create-account.php">
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
