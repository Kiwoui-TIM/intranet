  <nav class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
    <!-- Lien qui retourne à l'accueil, contenant le titre du site et le nom de l'utilisateur connecté -->
    <a class="navbar-brand col-md-3 col-lg-2 mr-0 px-3 shadow" href="/">Kiwoui (<?= $_SESSION["username"] ?>)</a>

    <!-- Bouton pour le menu mobile -->
    <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-toggle="collapse" data-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Bouton de déconnexion faisant l'utilisation de la constante UTIL_LOGOUT -->
    <ul class="navbar-nav px-3">
      <li class="nav-item text-nowrap">
        <a class="nav-link" href="<?= UTIL_LOGOUT ?>">Déconnexion</a>
      </li>
    </ul>
  </nav>
