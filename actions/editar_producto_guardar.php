<?php
// actions/editar_producto_guardar.php
header('Content-Type: application/json');

require_once __DIR__ . '/../core/session.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../core/funciones.php';

verificarSesion();

$response = ['success' => false, 'mensaje' => 'Error desconocido.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
    $nombre = trim($_POST['nombre'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $cantidad = filter_var($_POST['cantidad'], FILTER_VALIDATE_INT);
    $precio = filter_var($_POST['precio'], FILTER_VALIDATE_FLOAT);

    if ($id === false || empty($nombre) || empty($categoria) || $cantidad === false || $precio === false) {
        $response['mensaje'] = 'Datos inválidos. Por favor, complete todos los campos.';
        echo json_encode($response);
        exit;
    }

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("UPDATE productos SET nombre = ?, categoria = ?, cantidad = ?, precio = ? WHERE id = ?");
        $stmt->bind_param("ssddi", $nombre, $categoria, $cantidad, $precio, $id); // 'd' para precio, 'i' para cantidad
        
        if (!$stmt->execute()) {
            throw new Exception("Error al actualizar el producto: " . $stmt->error);
        }
        $stmt->close();
        
        $conn->commit();

        // Devolver los datos actualizados para la fila de la tabla
        $response['success'] = true;
        $response['id'] = $id;
        $response['nombre'] = $nombre;
        $response['categoria'] = $categoria;
        $response['cantidad'] = $cantidad;
        $response['precio'] = $precio;
        $response['estado'] = calcular_estado_producto($cantidad);
        
        $stmt_fecha = $conn->prepare("SELECT fecha_ingreso FROM productos WHERE id = ?");
        $stmt_fecha->bind_param("i", $id);
        $stmt_fecha->execute();
        $response['fecha_ingreso'] = $stmt_fecha->get_result()->fetch_assoc()['fecha_ingreso'];
        $stmt_fecha->close();

        unset($response['mensaje']);

    } catch (Exception $e) {
        $conn->rollback();
        $response['mensaje'] = $e->getMessage();
    }

} else {
    $response['mensaje'] = 'Método no permitido.';
}

echo json_encode($response);
?>