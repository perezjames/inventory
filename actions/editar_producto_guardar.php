<?php
// actions/editar_producto_guardar.php
header('Content-Type: application/json');
require_once __DIR__ . '/../core/bootstrap.php';

$response = ['success' => false, 'mensaje' => 'Error desconocido.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarCsrfToken($_POST['csrf'] ?? null)) {
        $response['mensaje'] = 'Token CSRF inválido.';
        echo json_encode($response);
        exit;
    }

    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
    $nombre = trim($_POST['nombre'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $cantidad = filter_var($_POST['cantidad'], FILTER_VALIDATE_INT);
    $precio = filter_var($_POST['precio'], FILTER_VALIDATE_FLOAT);

    if ($id === false || $nombre === '' || $categoria === '' || $cantidad === false || $precio === false || $cantidad < 0 || $precio < 0) {
        $response['mensaje'] = 'Datos inválidos.';
        echo json_encode($response);
        exit;
    }

    $conn->begin_transaction();
    try {
        $stmt_old = $conn->prepare("SELECT cantidad FROM productos WHERE id = ?");
        $stmt_old->bind_param("i", $id);
        $stmt_old->execute();
        $old = $stmt_old->get_result()->fetch_assoc();
        $stmt_old->close();
        if (!$old) throw new Exception('Producto no encontrado.');

        $cantidad_anterior = (int)$old['cantidad'];
        $diferencia = $cantidad - $cantidad_anterior;

        $stmt = $conn->prepare("UPDATE productos SET nombre=?, categoria=?, cantidad=?, precio=? WHERE id=?");
        $stmt->bind_param("ssddi", $nombre, $categoria, $cantidad, $precio, $id);
        $stmt->execute();
        $stmt->close();

        $tipo_mov = 'edicion';
        $cantidad_mov = 0;
        $comentario = 'Edición sin cambio de stock.';
        if ($diferencia > 0) {
            $tipo_mov = 'entrada';
            $cantidad_mov = $diferencia;
            $comentario = "Aumento de $diferencia unidades.";
        } elseif ($diferencia < 0) {
            $tipo_mov = 'salida';
            $cantidad_mov = abs($diferencia);
            $comentario = "Reducción de " . abs($diferencia) . " unidades.";
        }
        registrarMovimiento($conn, $id, $tipo_mov, $cantidad_mov, $comentario);

        $conn->commit();

        $stmt_fecha = $conn->prepare("SELECT fecha_ingreso FROM productos WHERE id = ?");
        $stmt_fecha->bind_param("i", $id);
        $stmt_fecha->execute();
        $fecha_ingreso = $stmt_fecha->get_result()->fetch_assoc()['fecha_ingreso'] ?? '';
        $stmt_fecha->close();

        $response = [
            'success' => true,
            'id' => $id,
            'nombre' => $nombre,
            'categoria' => $categoria,
            'cantidad' => $cantidad,
            'precio' => $precio,
            'estado' => calcular_estado_producto($cantidad),
            'fecha_ingreso' => $fecha_ingreso
        ];
    } catch (Exception $e) {
        $conn->rollback();
        $response['mensaje'] = $e->getMessage();
    }
} else {
    $response['mensaje'] = 'Método no permitido.';
}

echo json_encode($response);