<?php
// actions/eliminar_producto.php
header('Content-Type: application/json');
require_once __DIR__ . '/../core/bootstrap.php';

$response = ['success' => false, 'mensaje' => 'Error desconocido.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarCsrfToken($_POST['csrf'] ?? null)) {
        $response['mensaje'] = 'Token CSRF inválido.';
        echo json_encode($response);
        exit;
    }

    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    if (!$id || $id <= 0) {
        $response['mensaje'] = 'ID inválido.';
        echo json_encode($response);
        exit;
    }

    $conn->begin_transaction();
    try {
        // 1. Obtener información del producto
        $stmt_info = $conn->prepare("SELECT nombre, cantidad FROM productos WHERE id = ?");
        $stmt_info->bind_param("i", $id);
        $stmt_info->execute();
        $info = $stmt_info->get_result()->fetch_assoc();
        $stmt_info->close();
        if (!$info) throw new Exception('Producto no encontrado.');

        // 2. Eliminar producto
        $stmt_delete = $conn->prepare("DELETE FROM productos WHERE id = ?");
        $stmt_delete->bind_param("i", $id);
        $stmt_delete->execute();
        if ($stmt_delete->affected_rows < 1) {
            throw new Exception('No se eliminó el producto.');
        }
        $stmt_delete->close();

        // 3. Registrar movimiento de eliminación
        registrarMovimiento($conn, $id, 'eliminacion', 0, "Producto '{$info['nombre']}' eliminado.");
        $conn->commit();

        $response = ['success' => true];
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