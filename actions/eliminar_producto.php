<?php
// actions/eliminar_producto.php
header('Content-Type: application/json');

require_once __DIR__ . '/../core/session.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../core/funciones.php';

verificarSesion();

$response = ['success' => false, 'mensaje' => 'Error desconocido.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    
    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);

    if ($id === false || $id <= 0) {
        $response['mensaje'] = 'ID de producto inválido.';
        echo json_encode($response);
        exit;
    }

    $conn->begin_transaction();

    try {
        // 1. Obtener nombre y cantidad para el log antes de eliminar
        $stmt_info = $conn->prepare("SELECT nombre, cantidad FROM productos WHERE id = ?");
        $stmt_info->bind_param("i", $id);
        $stmt_info->execute();
        $result_info = $stmt_info->get_result();
        $producto_info = $result_info->fetch_assoc();
        $stmt_info->close();

        if (!$producto_info) {
            throw new Exception("No se encontró el producto.");
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
            throw new Exception("Error al eliminar el producto: " . $stmt_delete->error);
        }
        $stmt_delete->close();
        
        if ($conn->affected_rows > 0) {
            // 3. Registrar movimiento de eliminación
            $comentario_mov = "Producto '$nombre_producto' (ID: $id) eliminado. Stock al eliminar: $cantidad_eliminada.";
            // El tipo 'eliminacion' no registra cantidad.
            registrarMovimiento($conn, $id, 'eliminacion', 0, $comentario_mov);

            $conn->commit();
            $response['success'] = true;
            unset($response['mensaje']);
        } else {
            $conn->rollback();
            $response['mensaje'] = 'No se encontró el producto o ya había sido eliminado.';
        }
    } catch (Exception $e) {
        $conn->rollback();
        $response['mensaje'] = $e->getMessage();
    }

} else {
    $response['mensaje'] = 'Solicitud inválida.';
}

echo json_encode($response);
?>