<?php
// actions/registrar_venta.php
header('Content-Type: application/json');

require_once __DIR__ . '/../core/session.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../core/funciones.php';

verificarSesion();

$response = ['success' => false, 'mensaje' => 'Error desconocido.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $producto_id = filter_var($_POST['producto_id'], FILTER_VALIDATE_INT);
    $cantidad_venta = filter_var($_POST['cantidad'], FILTER_VALIDATE_INT);
    $usuario = $_SESSION['usuario'] ?? 'sistema';

    if ($producto_id === false || $cantidad_venta === false || $cantidad_venta <= 0) {
        $response['mensaje'] = 'Datos de venta inválidos.';
        echo json_encode($response);
        exit;
    }

    $conn->begin_transaction();

    try {
        // 1. Obtener producto y bloquear la fila para la transacción
        $stmt_prod = $conn->prepare("SELECT nombre, precio, cantidad FROM productos WHERE id = ? FOR UPDATE");
        $stmt_prod->bind_param("i", $producto_id);
        $stmt_prod->execute();
        $result_prod = $stmt_prod->get_result();

        if ($result_prod->num_rows === 0) {
            throw new Exception("Producto no encontrado.");
        }
        
        $producto = $result_prod->fetch_assoc();
        $stock_actual = (int)$producto['cantidad'];
        $precio_unitario = (float)$producto['precio'];
        $nombre_producto = $producto['nombre'];
        $stmt_prod->close();

        // 2. Verificar stock
        if ($cantidad_venta > $stock_actual) {
            throw new Exception("Stock insuficiente. Solo hay $stock_actual unidades disponibles.");
        }

        // 3. Calcular nuevo stock y precio total
        $nuevo_stock = $stock_actual - $cantidad_venta;
        $precio_total_venta = $cantidad_venta * $precio_unitario;

        // 4. Actualizar stock en 'productos'
        $stmt_update = $conn->prepare("UPDATE productos SET cantidad = ? WHERE id = ?");
        $stmt_update->bind_param("ii", $nuevo_stock, $producto_id);
        if (!$stmt_update->execute()) {
            throw new Exception("Error al actualizar el stock.");
        }
        $stmt_update->close();

        // 5. Insertar en 'ventas'
        $stmt_venta = $conn->prepare("INSERT INTO ventas (producto_id, cantidad, precio_total, usuario, fecha) VALUES (?, ?, ?, ?, NOW())");
        $stmt_venta->bind_param("iids", $producto_id, $cantidad_venta, $precio_total_venta, $usuario);
        if (!$stmt_venta->execute()) {
            throw new Exception("Error al registrar la venta.");
        }
        $stmt_venta->close();

        // 6. Registrar en 'movimientos'
        $comentario_mov = "Venta de $cantidad_venta unidades de $nombre_producto.";
        registrarMovimiento($conn, $producto_id, 'salida', $cantidad_venta, $comentario_mov);
        
        // 7. Confirmar transacción
        $conn->commit();

        $response['success'] = true;
        $response['mensaje'] = 'Venta registrada con éxito.';
        $response['producto_id'] = $producto_id;
        $response['nuevo_stock'] = $nuevo_stock;
        unset($response['error']);

    } catch (Exception $e) {
        $conn->rollback();
        $response['mensaje'] = $e->getMessage();
    }

} else {
    $response['mensaje'] = 'Método no permitido.';
}

echo json_encode($response);
?>