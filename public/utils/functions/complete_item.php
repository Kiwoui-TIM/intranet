<?php
  if (isset($_POST['complete_item']) || isset($_SESSION['postdata']['complete_item'])) {

    // Si la requête est faite via POST, mettre les variables POST dans un array dans SESSION
    // puis retourner à la page qui a fait la requête.
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $_SESSION['postdata'] = $_POST;
      $_POST = array();
      header('Location: ' . $_SERVER['REQUEST_URI'], true, 303);
      exit;

      // Si l'array "postdata" existe
    } elseif (array_key_exists('postdata', $_SESSION)) {
      // Définir les variables et les mettre vides
      $id = $completion = $tbl = '';

      include( UTIL_CONNECT );
      $id = trim($_SESSION['postdata']['id']);

      // Vérifier quelle liste doit être modifiée
      switch($_SESSION['postdata']['complete_item']) {
        case 'Tasks':
          $tbl = 'Tasks';
          break;

        case 'Milestones':
          $tbl = 'Milestones';
          break;

        case 'Projects':
          $tbl = 'Projects';
          break;
      }

      // Vérifier l'état de complétion
      try {
        $sql_query = "SELECT completed
                      FROM   $tbl
                      WHERE  id = :id";
        $stmt = $connectedDB->prepare($sql_query);
        $stmt->execute([
          ':id' => $id
        ]);
        $task = $stmt->fetch();

      } catch(PDOException $e) {
        echo 'Error: ' . $e->getMessage();
      }

      // Completed est vrai ? assigner 0, sinon assigner 1
      $completion = $task['completed'] == 1 ? 0 : 1;

      // Changer l'état de complétion
      try {
        $sql_query = "UPDATE $tbl
                      SET    completed = :completion
                      WHERE  id = :id";
        $stmt = $connectedDB->prepare($sql_query);
        $stmt->execute([
          ':completion' => $completion,
          ':id' => $id
        ]);
      } catch(PDOException $e) {
        echo 'Error: ' . $e->getMessage();
      }

      $connectedDB = null;
      unset($_SESSION['postdata']);
    }
  }
?>
