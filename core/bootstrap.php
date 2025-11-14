<?php
// core/bootstrap.php

/**
 * Archivo central de inicialización (Bootstrap)
 * Incluye archivos esenciales y verifica la autenticación.
 */

// 1. Incluir el manejo de sesión (session_start() y verificarSesion())
require_once __DIR__ . '/session.php';

// 2. Incluir la conexión a la base de datos
require_once __DIR__ . '/../config/conexion.php';

// 3. Incluir las funciones de utilidad (calcular_estado_producto, registrarMovimiento, etc.)
require_once __DIR__ . '/funciones.php';

// 4. Verificar la sesión y redirigir al login si es necesario.
// Esto se aplica a TODAS las páginas que requieran autenticación.
verificarSesion();

// Nota: La variable $conn (conexión DB) está disponible globalmente a través de la inclusión.

?>