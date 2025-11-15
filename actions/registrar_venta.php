<?php
// actions/registrar_venta.php
header('Content-Type: application/json');
require_once __DIR__ . '/../core/bootstrap.php';

$response = ['success' => false, 'mensaje' => 'Error desconocido.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!validarCsrfToken($_POST['csrf'] ?? null)) {
        $response['mensaje'] = 'Token CSRF inválido.';
        echo json_encode($response);
        exit;
    }

    $producto_id = filter_var($_POST['producto_id'], FILTER_VALIDATE_INT);
    $cantidad_venta = filter_var($_POST['cantidad'], FILTER_VALIDATE_INT);
    $user_id = $_SESSION['user_id'] ?? 0;

    if ($producto_id === false || $cantidad_venta === false || $cantidad_venta <= 0) {
        $response['mensaje'] = 'Datos de venta inválidos.';
        echo json_encode($response);
        exit;
    }

    $conn->begin_transaction();
    try {
        $stmt_prod = $conn->prepare("SELECT nombre, precio, cantidad FROM productos WHERE id = ? AND activo = 1 FOR UPDATE");
        $stmt_prod->bind_param("i", $producto_id);
        $stmt_prod->execute();
        $result_prod = $stmt_prod->get_result();

        if ($result_prod->num_rows === 0) {
            throw new Exception("Producto no encontrado.");
        }
        $producto = $result_prod->fetch_assoc();
        $stmt_prod->close();

        $stock_actual = (int)$producto['cantidad'];
        $precio_unitario = (float)$producto['precio'];
        if ($cantidad_venta > $stock_actual) {
            throw new Exception("Stock insuficiente. Disponible: $stock_actual");
        }

        $nuevo_stock = $stock_actual - $cantidad_venta;
        $precio_total_venta = $cantidad_venta * $precio_unitario;

        $stmt_update = $conn->prepare("UPDATE productos SET cantidad = ? WHERE id = ?");
        $stmt_update->bind_param("ii", $nuevo_stock, $producto_id);
        $stmt_update->execute();
        $stmt_update->close();

        $stmt_venta = $conn->prepare("INSERT INTO ventas (producto_id, cantidad, precio_total, usuario, fecha) VALUES (?, ?, ?, ?, NOW())");
        $stmt_venta->bind_param("iidi", $producto_id, $cantidad_venta, $precio_total_venta, $user_id);
        $stmt_venta->execute();
        $stmt_venta->close();

        $comentario = "Venta de $cantidad_venta x {$producto['nombre']}. Total: " . number_format($precio_total_venta, 2, ',', '.');
        registrarMovimiento($conn, $producto_id, 'venta', $cantidad_venta, $comentario);

        $conn->commit();
        $response = [
            'success' => true,
            'mensaje' => 'Venta registrada con éxito.',
            'nuevo_stock' => $nuevo_stock,
            'producto_id' => $producto_id
        ];

    } catch (Exception $e) {
        $conn->rollback();
        $response['mensaje'] = $e->getMessage();
    }
} else {
    $response['mensaje'] = 'Método no permitido.';
}

echo json_encode($response);
?>