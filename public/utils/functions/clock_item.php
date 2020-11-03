<?php
  if (isset($_POST['clock_item']) || isset($_SESSION['postdata']['clock_item'])) {

    // Si la requête est faite via POST, mettre les variables POST dans un array dans SESSION
    // puis retourner à la page qui a fait la requête.
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $_SESSION['postdata'] = $_POST;
      $_POST = array();
      header('Location: ' . $_SERVER['REQUEST_URI'],true,303);
      exit;

      // Si l'array "postdata" existe, changer les variables pour les valeurs entrée par l'utilisateur
    } elseif (array_key_exists('postdata', $_SESSION)) {
      // Définir les variables et les mettre vides
      $id = $tbl = '';

      include( UTIL_CONNECT );
      $id = trim($_SESSION['postdata']['id']);

      // Vérifier quelle liste doit être modifiée
      switch($_SESSION['postdata']['clock_item']) {
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

      // Vérifier l'état de punch
      try {
        $sql_query = "SELECT time_spent,
                             clock
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

      // Temps présent (epoch)
      $cur_timestamp = time();

      // Si on est clocked, mettre clocked à NULL, sinon mettre $cur_timestamp
      if ($task['clock']) {
        $sql_query = "UPDATE $tbl
                      SET    clock = NULL
                      WHERE  id = :id";
      } else {
        $sql_query = "UPDATE $tbl
                      SET    clock = $cur_timestamp
                      WHERE  id = :id";
      }

      try {
        $stmt = $connectedDB->prepare($sql_query);
        $stmt->execute([
          ':id' => $id
        ]);

      } catch(PDOException $e) {
        echo 'Error: ' . $e->getMessage();
      }

      // Si on est clocked, calculer le temps passé
      if ($task['clock']) {
        $prev_timestamp = $task['clock'];

        // Temps passé = présent - temps de clock / 3600s (1h) + le temps déjà passé
        $time_spent = ($cur_timestamp - $prev_timestamp) / 3600 + $task['time_spent'];

        try {
          $sql_query = "UPDATE $tbl
                        SET    time_spent = :time_spent
                        WHERE  id = :id";
          $stmt = $connectedDB->prepare($sql_query);
          $stmt->execute([
            ':id' => $id,
            ':time_spent' => $time_spent
          ]);

        } catch(PDOException $e) {
          echo 'Error: ' . $e->getMessage();
        }
      }

      // Déconnecter la base de données, détruire les variables
      unset($_SESSION['postdata']);
      $connectedDB = null;
    }
  }
?>
