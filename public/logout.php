<?php
  session_start();
  ob_start( );

  // Vider toutes les variables de la session
  $_SESSION = array();

  // Si la session utilise des cookies, les supprime aussi
  if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
      $params['path'], $params['domain'],
      $params['secure'], $params['httponly']
    );
  }
  // Finalement, détruire la session et retourner au login
  session_destroy();
  header('Location: /login.php');
  exit;
?>
