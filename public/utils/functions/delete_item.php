<?php
  if (isset($_POST['delete_item']) || isset($_SESSION['postdata']['delete_item'])) {

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
      $id = $tbl = '';

      include( UTIL_CONNECT );
      $id = trim($_SESSION['postdata']['id']);

      // Vérifier quelle liste doit être modifiée
      switch($_SESSION['postdata']['delete_item']) {
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

      // Supprimer l'item
      try {
        $sql_query = "DELETE FROM $tbl
                      WHERE  id = :id";
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
?>
