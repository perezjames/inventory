<?php
// public/login.php
require_once __DIR__ . '/../config/conexion.php'; 
session_start();

// Si el usuario ya está logueado, redirigir al index
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error = null;
$usuario_input = ''; // Inicializamos para evitar notices

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario_input = trim($_POST['usuario'] ?? '');
    $clave_input = $_POST['clave'] ?? '';

    if (empty($usuario_input) || empty($clave_input)) {
        $error = "Por favor, ingrese usuario y contraseña.";
    } else {
        // --- Lógica de login principal (obtener ID, usuario y clave) ---
        // CAMBIO: Se obtiene el ID (que asumimos es INT) junto con la clave
        $stmt = $conn->prepare("SELECT id, usuario, clave FROM usuarios WHERE usuario = ?");
        $stmt->bind_param("s", $usuario_input);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $fila = $result->fetch_assoc();
            $clave_hash = $fila['clave'];

            if (password_verify($clave_input, $clave_hash)) {
                // Autenticación exitosa
                // CAMBIO: Almacenar ID y nombre
                $_SESSION['user_id'] = $fila['id'];
                $_SESSION['usuario'] = $fila['usuario'];
                $stmt->close();
                header('Location: index.php');
                exit();
            } else {
                $error = "Usuario o contraseña incorrectos";
            }
        } else {
            // No se encontró el usuario, o la contraseña es incorrecta
            $error = "Usuario o contraseña incorrectos";
        }
        $stmt->close();
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
        
        <?php if ($error): ?>
          <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
          <div class="mb-3">
            <label class="form-label">Usuario</label>
            <input type="text" name="usuario" class="form-control" required value="<?= htmlspecialchars($usuario_input) ?>" placeholder="admin">
          </div>
          <div class="mb-3">
            <label class="form-label">Contraseña</label>
            <input type="password" name="clave" class="form-control" required placeholder="12345">
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