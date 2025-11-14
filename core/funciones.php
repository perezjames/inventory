<?php
// core/funciones.php

/**
 * Calcula el badge de estado de un producto basado en la cantidad.
 * @param int $cantidad
 * @return string HTML del badge
 */
function calcular_estado_producto($cantidad) {
    $cantidad = (int)$cantidad;
    if ($cantidad == 0) {
        return '<span class="badge bg-danger rounded-pill">Agotado</span>';
    }
    if ($cantidad < 10) {
        return '<span class="badge bg-warning text-dark rounded-pill">Bajo stock</span>';
    }
    return '<span class="badge bg-success rounded-pill">Disponible</span>';
}

/**
 * Registra un movimiento de inventario.
 * @param mysqli $conn La conexión a la base de datos.
 * @param int $producto_id El ID del producto.
 * @param string $tipo Tipo de movimiento ('entrada', 'salida', 'edicion', 'eliminacion', 'venta').
 * @param int $cantidad La cantidad afectada (solo para 'entrada'/'salida'/venta).
 * @param string $comentario Descripción del movimiento.
 * @return bool
 */
function registrarMovimiento($conn, $producto_id, $tipo, $cantidad, $comentario) {
    // La cantidad solo se registra si es una operación de stock (entrada/salida/venta).
    $cantidad_a_registrar = in_array($tipo, ['entrada', 'salida', 'venta']) ? $cantidad : 0;
    
    // CAMBIO: Usar user_id de la sesión como identificador único
    $user_id = $_SESSION['user_id'] ?? 0; // Usar ID 0 o 'sistema' si la sesión no está definida.
    
    $stmt = $conn->prepare("INSERT INTO movimientos (producto_id, tipo, cantidad, fecha, usuario_id, comentario) VALUES (?, ?, ?, NOW(), ?, ?)");
    
    if (!$stmt) {
        error_log("Error al preparar el movimiento: " . $conn->error);
        return false;
    }
    
    // CAMBIO: Bind con 'i' para usuario_id (asumiendo que es INT en DB)
    $stmt->bind_param("isiss", $producto_id, $tipo, $cantidad_a_registrar, $user_id, $comentario);
    $result = $stmt->execute();
    
    if (!$result) {
        error_log("Error al ejecutar el movimiento: " . $stmt->error);
    }
    
    $stmt->close();
    return $result;
}

/**
 * Genera la condición SQL WHERE para filtrar por períodos de tiempo.
 * @param string $filtro El período de tiempo ('todos', 'dia', 'semana', 'mes', 'anio').
 * @param string $campo_fecha El nombre del campo de la fecha en la tabla.
 * @return string La condición SQL.
 */
function obtener_condicion_fecha($filtro, $campo_fecha) {
    if (!$filtro || $filtro === 'todos') return "1"; 
    
    switch ($filtro) {
        case 'dia':
            return "DATE($campo_fecha) = CURDATE()";
        case 'semana':
            // Asume Lunes como inicio de semana (modo 1)
            return "YEARWEEK($campo_fecha, 1) = YEARWEEK(CURDATE(), 1)";
        case 'mes':
            return "YEAR($campo_fecha) = YEAR(CURDATE()) AND MONTH($campo_fecha) = MONTH(CURDATE())";
        case 'anio':
            return "YEAR($campo_fecha) = YEAR(CURDATE())";
        default:
            return "1";
    }
}
?>