<?php
// core/session.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tiempo máximo de inactividad (segundos)
const SESSION_MAX_IDLE = 900; // 15 min

/**
 * Regenera el ID de sesión de forma segura.
 */
function regenerarSesionSegura(): void {
    if (!isset($_SESSION['__regenerado'])) {
        session_regenerate_id(true);
        $_SESSION['__regenerado'] = time();
    }
}

/**
 * Genera y devuelve el token CSRF (lo almacena en sesión).
 * @return string
 */
function generarCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valida el token CSRF enviado.
 * @param string|null $token
 * @return bool
 */
function validarCsrfToken(?string $token): bool {
    if (!$token || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Verifica si el usuario ha iniciado sesión y controla expiración por inactividad.
 */
function verificarSesion(): void {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
    $ahora = time();
    $ultima = $_SESSION['last_activity'] ?? $ahora;
    if (($ahora - $ultima) > SESSION_MAX_IDLE) {
        // Expirada
        session_unset();
        session_destroy();
        header('Location: login.php?exp=1');
        exit();
    }
    $_SESSION['last_activity'] = $ahora;
    regenerarSesionSegura();
}