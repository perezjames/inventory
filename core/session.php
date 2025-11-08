<?php
// core/session.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verifica si el usuario ha iniciado sesión.
 * Si no, redirige a la página de login.
 */
function verificarSesion() {
    // Asumimos que login.php está en el mismo directorio (public)
    // que los archivos que llaman a esta función.
    if (!isset($_SESSION['usuario'])) {
        header('Location: login.php');
        exit();
    }
}
?>