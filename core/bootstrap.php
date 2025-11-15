<?php
// core/bootstrap.php
require_once __DIR__ . '/session.php';

// Cargar variables de entorno desde .env si existe
$envFile = dirname(__DIR__) . '/.env';
if (is_readable($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        [$k, $v] = array_map('trim', explode('=', $line, 2));
        if ($k !== '') {
            $_ENV[$k] = $v;
            putenv("$k=$v");
        }
    }
}

define('APP_VERSION', '1.0.0');

// Seguridad de cabeceras (ajusta CSP según necesites)
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
header("Content-Security-Policy: default-src 'self' https:; script-src 'self' https: 'unsafe-inline'; style-src 'self' https: 'unsafe-inline'; img-src 'self' https: data:; font-src 'self' https: data:; connect-src 'self' https:;");

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/funciones.php';

verificarSesion();