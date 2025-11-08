<?php
// actions/agregar_producto.php
header('Content-Type: application/json');

require_once __DIR__ . '/../core/session.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../core/funciones.php';

verificarSesion();

$response = ['success' => false, 'error' => 'Error desconocido.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $cantidad = filter_var($_POST['cantidad'], FILTER_VALIDATE_INT);
    $precio = filter_var($_POST['precio'], FILTER_VALIDATE_FLOAT);

    if (empty($nombre) || empty($categoria) || $cantidad === false || $precio === false || $cantidad < 0 || $precio < 0) {
        $response['error'] = 'Datos inválidos. Por favor, complete todos los campos con valores positivos.';
        echo json_encode($response);
        exit;
    }

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("INSERT INTO productos (nombre, categoria, cantidad, precio, fecha_ingreso) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssdd", $nombre, $categoria, $cantidad, $precio); // Usar 'd' (double) para precio
        
        if (!$stmt->execute()) {
            throw new Exception("Error al guardar el producto: " . $stmt->error);
        }
        
        $producto_id = $conn->insert_id;
        $stmt->close();

        // Registrar movimiento de agregado
        $tipo_mov = ($cantidad > 0) ? 'entrada' : 'edicion';
        $comentario_mov = "Producto agregado (ID: $producto_id). Cantidad inicial: $cantidad. Precio: $precio.";
        registrarMovimiento($conn, $producto_id, $tipo_mov, $cantidad, $comentario_mov);

        $conn->commit();

        // Obtener el producto recién creado para devolverlo
        $stmt_get = $conn->prepare("SELECT * FROM productos WHERE id = ?");
        $stmt_get->bind_param("i", $producto_id);
        $stmt_get->execute();
        $result = $stmt_get->get_result();
        $producto = $result->fetch_assoc();
        $stmt_get->close();

        if ($producto) {
            $producto['estado'] = calcular_estado_producto($producto['cantidad']);
            $producto['id'] = (int)$producto['id'];
            $producto['cantidad'] = (int)$producto['cantidad'];
            $producto['precio'] = (float)$producto['precio'];
            
            $response['success'] = true;
            $response['producto'] = $producto;
            unset($response['error']);
        } else {
            throw new Exception("No se pudo recuperar el producto insertado.");
        }

    } catch (Exception $e) {
        $conn->rollback();
        $response['error'] = $e->getMessage();
    }

} else {
    $response['error'] = 'Método no permitido.';
}

echo json_encode($response);
?>