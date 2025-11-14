<?php
// core/session.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verifica si el usuario ha iniciado sesión.
 * Si no, redirige a la página de login.
 * Ahora verifica la existencia de 'user_id' como identificador primario.
 */
function verificarSesion() {
    // Asumimos que login.php está en el mismo directorio (public)
    // que los archivos que llaman a esta función.
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}
?>