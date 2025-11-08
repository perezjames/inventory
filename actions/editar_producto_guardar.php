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

    if ($id === false || empty($nombre) || empty($categoria) || $cantidad === false || $precio === false || $cantidad < 0 || $precio < 0) {
        $response['mensaje'] = 'Datos inválidos. Por favor, complete todos los campos con valores válidos.';
        echo json_encode($response);
        exit;
    }

    $conn->begin_transaction();

    try {
        // 1. Obtener datos antiguos para registrar movimiento
        $stmt_old = $conn->prepare("SELECT nombre, cantidad FROM productos WHERE id = ?");
        $stmt_old->bind_param("i", $id);
        $stmt_old->execute();
        $result_old = $stmt_old->get_result();
        $producto_old = $result_old->fetch_assoc();
        $stmt_old->close();

        if (!$producto_old) {
            throw new Exception("Producto a editar no encontrado.");
        }

        $cantidad_anterior = (int)$producto_old['cantidad'];
        $diferencia = $cantidad - $cantidad_anterior;

        // 2. Actualizar producto
        $stmt = $conn->prepare("UPDATE productos SET nombre = ?, categoria = ?, cantidad = ?, precio = ? WHERE id = ?");
        $stmt->bind_param("ssddi", $nombre, $categoria, $cantidad, $precio, $id); // 'd' para precio, 'i' para cantidad
        
        if (!$stmt->execute()) {
            throw new Exception("Error al actualizar el producto: " . $stmt->error);
        }
        $stmt->close();
        
        // 3. Registrar movimiento
        $comentario_mov = "";
        $tipo_mov = "";
        $cantidad_mov = abs($diferencia);
        
        if ($diferencia > 0) {
            $tipo_mov = 'entrada';
            $comentario_mov = "Aumento de stock en $diferencia unidades. Nuevo stock: $cantidad.";
        } elseif ($diferencia < 0) {
            $tipo_mov = 'salida';
            $comentario_mov = "Reducción de stock en " . abs($diferencia) . " unidades (Edición manual). Nuevo stock: $cantidad.";
        } else {
            $tipo_mov = 'edicion';
            $comentario_mov = "Edición de metadatos (Nombre/Categoría/Precio). Stock sin cambio.";
            $cantidad_mov = 0; // No es un movimiento de stock
        }
        
        registrarMovimiento($conn, $id, $tipo_mov, $cantidad_mov, $comentario_mov);

        $conn->commit();

        // 4. Devolver los datos actualizados para la fila de la tabla
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