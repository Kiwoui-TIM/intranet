<?php
  if (isset($_POST['create_item']) || isset($_SESSION['postdata']['create_item'])) {

    // Si la requête est faite via POST, mettre les variables POST dans un array dans SESSION
    // puis retourner à la page qui a fait la requête.
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $_SESSION['postdata'] = $_POST;
      $_POST = array();
      header('Location: ' . $_SERVER['REQUEST_URI'], true, 303);
      exit;

      // Si l'array "postdata" existe
    } elseif (array_key_exists('postdata', $_SESSION)) {

      include( UTIL_CONNECT );

      // Vérifier quelle liste doit être modifiée
      switch($_SESSION['postdata']['create_item']) {

        // Créer une tâche
        case 'Tasks':
          $tbl = 'Tasks';
          $name = trim($_SESSION['postdata']['name']);
          $milestone = trim($_SESSION['postdata']['milestone']);
          $due_date = trim($_SESSION['postdata']['due_date']);

          try {
            $sql_query = "INSERT INTO Tasks
                                      (name,
                                       student,
                                       milestone,
                                       creation_date,
                                       due_date)
                          VALUES      (:name,
                                       :student,
                                       :milestone,
                                       :creation_date,
                                       :due_date)";
            $stmt = $connectedDB->prepare($sql_query);
            $stmt->execute([
              ':name' => $name,
              ':student' => $_SESSION['id'],
              ':milestone' => $milestone,
              ':creation_date' => date('Y-m-d'),
              ':due_date' => $due_date
            ]);

          } catch(PDOException $e) {
            echo 'Error: ' . $e->getMessage();
          }

          break;

        // Créer un jalon
        case 'Milestones':
          $tbl = 'Milestones';
          $name = trim($_SESSION['postdata']['name']);
          $project = trim($_SESSION['postdata']['project']);
          $due_date = trim($_SESSION['postdata']['due_date']);
          $team = trim($_SESSION['postdata']['team']);

          try {
            $sql_query = "INSERT INTO Milestones
                                      (name,
                                       project,
                                       team,
                                       due_date)
                          VALUES      (:name,
                                       :project,
                                       :team,
                                       :due_date)";
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

          break;

        // Créer un projet
        case 'Projects':
          $tbl = 'Projects';
          $name = trim($_SESSION['postdata']['name']);
          $client = trim($_SESSION['postdata']['client']);

          try {
            $sql_query = "INSERT INTO Projects
                                      (name,
                                       client)
                          VALUES      (:name,
                                       :client)";
            $stmt = $connectedDB->prepare($sql_query);
            $stmt->execute([
              ':name' => $name,
              ':client' => $client
            ]);

          } catch(PDOException $e) {
            echo 'Error: ' . $e->getMessage();
          }
          
          break;
      }

      unset($_SESSION['postdata']);
      $connectedDB = null;
    }
  }
?>
