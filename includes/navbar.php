<?php
// includes/navbar.php
// Se asume que session_start() ya fue llamado por el archivo principal.
// CAMBIO: user_id es el primario, pero 'usuario' sigue siendo el nombre a mostrar.
$nombreUsuario = isset($_SESSION['usuario'])
    ? htmlspecialchars($_SESSION['usuario'], ENT_QUOTES, 'UTF-8')
    : 'Invitado';
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">
      <i class="bi bi-box-seam"></i> Inventario
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" href="index.php">Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="ventas.php">Ventas</a>
        </li>  
        <li class="nav-item">
          <a class="nav-link" href="historial.php">Historial</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="reportes.php">Reportes</a>
        </li>
      </ul>
      <ul class="navbar-nav ms-auto">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle"></i> <?= $nombreUsuario ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="logout.php">Cerrar sesión</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>