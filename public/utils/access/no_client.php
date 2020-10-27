<?php
  include( UTIL_CONNECT );

  try {
    $sql_query = "SELECT account_type
                  FROM   Users
                  WHERE  username = :username
                  LIMIT 1";
    $stmt = $connectedDB->prepare($sql_query);
    $stmt->execute([
      ':username' => $_SESSION['username']
    ]);
    $user = $stmt->fetch();

  } catch(PDOException $e) {
    echo 'Error: ' . $e->getMessage();
  }

  if ($user['account_type'] == 3) {
    header('location: /');
    exit;
  }

  $connectedDB = null;
?>
