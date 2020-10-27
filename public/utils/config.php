<?php
  define('DIR_VIEWS',         'views/');
  define('VIEW_META',         DIR_VIEWS . 'meta.php');
  define('VIEW_HEADER',       DIR_VIEWS . 'header.php');
  define('VIEW_NAVIGATION',   DIR_VIEWS . 'navigation.php');
  define('VIEW_FOOTER',       DIR_VIEWS . 'footer.php');

  define('DIR_UTILS',         'utils/');
  define('UTIL_CONNECT',      DIR_UTILS . 'connect.php');
  define('UTIL_LOGOUT',       'logout.php');

  define('DIR_ACCESS',        'access/');
  define('ACCESS_ADMIN_ONLY', DIR_UTILS . DIR_ACCESS . 'admin.php');
  define('ACCESS_NO_STUDENT', DIR_UTILS . DIR_ACCESS . 'no_student.php');
  define('ACCESS_NO_CLIENT',  DIR_UTILS . DIR_ACCESS . 'no_client.php');

  define('DIR_FUNCTIONS',     'functions/');
  define('FUNCTION_CREATE',   DIR_UTILS . DIR_FUNCTIONS . 'create_item.php');
  define('FUNCTION_DELETE',   DIR_UTILS . DIR_FUNCTIONS . 'delete_item.php');
  define('FUNCTION_CLOCK',    DIR_UTILS . DIR_FUNCTIONS . 'clock_item.php');
  define('FUNCTION_COMPLETE', DIR_UTILS . DIR_FUNCTIONS . 'complete_item.php');
  define('FUNCTION_NEW_ACC',  DIR_UTILS . DIR_FUNCTIONS . 'create_account.php');
  define('FUNCTION_NEW_PWD',  DIR_UTILS . DIR_FUNCTIONS . 'change_password.php');
  define('FUNCTION_LOGIN',    DIR_UTILS . DIR_FUNCTIONS . 'login.php');

  define('HOME_TITLE',        'Tableau de bord');
  define('TASKS_TITLE',       'Liste des tâches');
  define('MILESTONES_TITLE',  'Liste des jalons');
  define('PROJECTS_TITLE',    'Liste des projets');
  define('CHANGE_PWD_TITLE',  'Changer de mot de passe');
  define('ALL_TASKS_TITLE',   'Liste de toutes les tâches');
  define('CREATE_ACC_TITLE',  'Créer un compte');
?>
