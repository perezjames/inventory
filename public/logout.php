<?php
// public/logout.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Destruir todos los datos de la sesión
$_SESSION = array();

// Borrar la cookie de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destruir la sesión
session_destroy();

// Redirigir a login
header('Location: login.php');
exit();
?>