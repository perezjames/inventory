<?php
// actions/eliminar_producto.php
header('Content-Type: application/json');

require_once __DIR__ . '/../core/session.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../core/funciones.php'; // Requerido para registrarMovimiento

verificarSesion();

$response = ['success' => false, 'mensaje' => 'Error desconocido.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    
    // 1. Limpieza y validación estricta del ID
    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);

    if ($id === false || $id <= 0) {
        $response['mensaje'] = 'ID de producto inválido o no numérico.';
        echo json_encode($response);
        exit;
    }

    $conn->begin_transaction();

    try {
        // 1. Obtener información del producto
        $stmt_info = $conn->prepare("SELECT nombre, cantidad FROM productos WHERE id = ?");
        $stmt_info->bind_param("i", $id);
        $stmt_info->execute();
        $result_info = $stmt_info->get_result();
        $producto_info = $result_info->fetch_assoc();
        $stmt_info->close();

        if (!$producto_info) {
             // Si el producto no se encuentra en la primera consulta, no puede ser eliminado.
            throw new Exception("No se encontró el producto (ID: $id).");
        }

        $nombre_producto = htmlspecialchars($producto_info['nombre']);
        $cantidad_eliminada = (int)$producto_info['cantidad'];

        // 2. Eliminar producto
        $stmt_delete = $conn->prepare("DELETE FROM productos WHERE id = ?");
        $stmt_delete->bind_param("i", $id);
        
        if (!$stmt_delete->execute()) {
            // Manejar errores de clave foránea (FK)
            if ($conn->errno == 1451) {
                throw new Exception('No se puede eliminar: el producto tiene historial o ventas asociadas.');
            }
            throw new Exception("Error al ejecutar la eliminación: " . $stmt_delete->error);
        }
        
        // 3. Verificar si se eliminó la fila (se comprueba después de execute)
        if ($stmt_delete->affected_rows > 0) {
            // 4. Registrar movimiento de eliminación
            $comentario_mov = "Producto '$nombre_producto' (ID: $id) eliminado. Stock al eliminar: $cantidad_eliminada.";
            registrarMovimiento($conn, $id, 'eliminacion', 0, $comentario_mov);

            $conn->commit();
            $response['success'] = true;
            unset($response['mensaje']);
        } else {
            // Esto se ejecuta si $stmt_delete->affected_rows == 0
            $conn->rollback();
            $response['mensaje'] = 'No se encontró el producto o ya había sido eliminado (filas afectadas: 0).';
        }
        $stmt_delete->close();

    } catch (Exception $e) {
        $conn->rollback();
        $response['mensaje'] = $e->getMessage();
    }

} else {
    $response['mensaje'] = 'Solicitud inválida.';
}

echo json_encode($response);
exit; // Asegura que no haya más salida
?>