<?php
// public/login.php
require_once __DIR__ . '/../config/conexion.php'; 
session_start();

// Si el usuario ya está logueado, redirigir al index
if (isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit();
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'];
    $clave = $_POST['clave'];

    // usuario demo (Manteniendo tu lógica original)
    if ($usuario === 'admin' && $clave === '12345') {
        $_SESSION['usuario'] = $usuario;
        header('Location: index.php');
        exit();
    } else {
        $error = "Usuario o contraseña incorrectos";
    }
}

// Incluir header de HTML (no el navbar de la app)
require_once __DIR__ . '/../includes/header.php';
?>
<body class="bg-light d-flex align-items-center" style="height:100vh;">
  <div class="container">
    <div class="card shadow mx-auto" style="max-width:400px;">
      <div class="card-header bg-dark text-white text-center">
        <h4 class="bi bi-box-seam me-2"> Inventario</h4>
      </div>
      <div class="card-body p-4">
        <!-- Alerta con datos de prueba -->
        <div class="alert alert-info text-center" role="alert">
          <i class="bi bi-info-circle"></i>
          <strong>Datos de prueba:</strong> <br> Usuario: <em>admin</em> <br> Contraseña: <em>12345</em>
        </div>

        <?php if ($error): ?>
          <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
          <div class="mb-3">
            <label class="form-label">Usuario</label>
            <input type="text" name="usuario" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Contraseña</label>
            <input type="password" name="clave" class="form-control" required>
          </div>
          <button class="btn btn-dark w-100">Ingresar</button>
        </form>
      </div>
    </div>
  </div>
<?php 
// Incluir solo los scripts necesarios (jQuery y Bootstrap)
?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>