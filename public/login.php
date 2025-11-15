<?php
// public/login.php
require_once __DIR__ . '/../config/conexion.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../core/session.php'; // Para generar CSRF

$error = null;
$usuario_input = '';
$csrf = generarCsrfToken();

// Si ya está logueado
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarCsrfToken($_POST['csrf'] ?? null)) {
        $error = 'Token CSRF inválido.';
    } else {
        $usuario_input = trim($_POST['usuario'] ?? '');
        $clave_input   = (string)($_POST['clave'] ?? '');

        if ($usuario_input === '' || $clave_input === '') {
            $error = 'Por favor, ingrese usuario y contraseña.';
        } else {
            $stmt = $conn->prepare('SELECT id, usuario, clave FROM usuarios WHERE usuario = ? LIMIT 1');
            if ($stmt) {
                $stmt->bind_param('s', $usuario_input);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result && $result->num_rows === 1) {
                    $fila = $result->fetch_assoc();
                    $hashBD = (string)$fila['clave'];
                    $esHash = preg_match('/^\$2y\$|^\$argon2/i', $hashBD) === 1;
                    $ok = $esHash ? password_verify($clave_input, $hashBD) : hash_equals($hashBD, $clave_input);

                    if ($ok) {
                        session_regenerate_id(true);
                        $_SESSION['user_id'] = (int)$fila['id'];
                        $_SESSION['usuario'] = $fila['usuario'];
                        $_SESSION['last_activity'] = time();

                        // Migrar a hash si estaba plano
                        if (!$esHash) {
                            $nuevoHash = password_hash($clave_input, PASSWORD_DEFAULT);
                            $u = $conn->prepare('UPDATE usuarios SET clave = ? WHERE id = ?');
                            if ($u) { $u->bind_param('si', $nuevoHash, $fila['id']); $u->execute(); $u->close(); }
                        }

                        header('Location: index.php');
                        exit();
                    } else {
                        $error = 'Usuario o contraseña incorrectos';
                    }
                } else {
                    $error = 'Usuario o contraseña incorrectos';
                }

                if ($result instanceof mysqli_result) $result->free();
                $stmt->close();
            } else {
                $error = 'Error interno.';
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="bg-light d-flex align-items-center" style="min-height:100vh;">
  <div class="container">
    <div class="card shadow mx-auto" style="max-width:400px;">
      <div class="card-header bg-dark text-white text-center">
        <h4 class="bi bi-box-seam me-2"> Inventario</h4>
      </div>
      <div class="card-body p-4">
        <?php if ($error): ?>
          <div class="alert alert-danger text-center"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <form method="POST" action="login.php" autocomplete="off">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
          <div class="mb-3">
            <label class="form-label">Usuario</label>
            <input type="text" name="usuario" class="form-control" required value="<?= htmlspecialchars($usuario_input, ENT_QUOTES, 'UTF-8') ?>" autocomplete="username">
          </div>
          <div class="mb-3">
            <label class="form-label">Contraseña</label>
            <input type="password" name="clave" class="form-control" required autocomplete="current-password">
          </div>
          <button class="btn btn-dark w-100">Ingresar</button>
        </form>
      </div>
    </div>
  </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>