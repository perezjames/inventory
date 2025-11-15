<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../core/bootstrap.php';

$response = ['success' => false, 'error' => 'Error desconocido.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarCsrfToken($_POST['csrf'] ?? null)) {
        $response['error'] = 'Token CSRF inválido.';
        echo json_encode($response);
        exit;
    }

    $nombre = trim($_POST['nombre'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $cantidad = filter_var($_POST['cantidad'], FILTER_VALIDATE_INT);
    $precio = filter_var($_POST['precio'], FILTER_VALIDATE_FLOAT);

    if ($nombre === '' || $categoria === '' || $cantidad === false || $precio === false || $cantidad < 0 || $precio < 0) {
        $response['error'] = 'Datos inválidos.';
        echo json_encode($response);
        exit;
    }

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO productos (nombre, categoria, cantidad, precio, fecha_ingreso) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssdd", $nombre, $categoria, $cantidad, $precio);
        if (!$stmt->execute()) {
            throw new Exception("Error al guardar producto.");
        }
        $producto_id = $conn->insert_id;
        $stmt->close();

        registrarMovimiento($conn, $producto_id, $cantidad > 0 ? 'entrada' : 'edicion', $cantidad, "Producto agregado ID $producto_id");
        $conn->commit();

        $stmt_get = $conn->prepare("SELECT id,nombre,categoria,cantidad,precio,fecha_ingreso FROM productos WHERE id = ?");
        $stmt_get->bind_param("i", $producto_id);
        $stmt_get->execute();
        $producto = $stmt_get->get_result()->fetch_assoc();
        $stmt_get->close();

        if ($producto) {
            $producto['estado'] = calcular_estado_producto($producto['cantidad']);
            $producto['id'] = (int)$producto['id'];
            $producto['cantidad'] = (int)$producto['cantidad'];
            $producto['precio'] = (float)$producto['precio'];
            $response = ['success' => true, 'producto' => $producto];
        } else {
            throw new Exception("No se pudo recuperar el producto.");
        }
    } catch (Exception $e) {
        $conn->rollback();
        $response['error'] = $e->getMessage();
    }
} else {
    $response['error'] = 'Método no permitido.';
}

echo json_encode($response);