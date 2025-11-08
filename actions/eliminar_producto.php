<?php
// actions/eliminar_producto.php
header('Content-Type: application/json');

require_once __DIR__ . '/../core/session.php';
require_once __DIR__ . '/../config/conexion.php';

verificarSesion();

$response = ['success' => false, 'mensaje' => 'Error desconocido.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    
    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);

    if ($id === false || $id <= 0) {
        $response['mensaje'] = 'ID de producto inválido.';
        echo json_encode($response);
        exit;
    }

    // Usar sentencias preparadas para prevenir Inyección SQL
    $stmt = $conn->prepare("DELETE FROM productos WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $response['success'] = true;
            unset($response['mensaje']);
        } else {
            $response['mensaje'] = 'No se encontró el producto o ya había sido eliminado.';
        }
    } else {
        // Manejar errores de clave foránea (FK)
        if ($conn->errno == 1451) {
            $response['mensaje'] = 'No se puede eliminar: el producto tiene movimientos de historial asociados.';
        } else {
            $response['mensaje'] = 'Error al eliminar el producto: ' . $stmt->error;
        }
    }
    
    $stmt->close();

} else {
    $response['mensaje'] = 'Solicitud inválida.';
}

echo json_encode($response);
?>