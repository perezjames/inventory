<?php
// actions/exportar_reporte.php
header('Content-Type: application/json');

// CAMBIO: Usar archivo central de inicialización
require_once __DIR__ . '/../core/bootstrap.php';
// Nota: La función obtener_condicion_fecha() ya está disponible a través de bootstrap.php -> funciones.php

$response = ['success' => false, 'mensaje' => 'Solicitud inválida.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reporte_tipo'])) {
    
    $reporte_tipo = $_POST['reporte_tipo']; // ventas, stock, valor
    $filtro_periodo = $_POST['filtro_periodo'] ?? 'todos'; // dia, semana, mes, anio
    
    // Validar reporte_tipo para prevenir inyección
    $tipos_validos = ['ventas', 'stock', 'valor'];
    if (!in_array($reporte_tipo, $tipos_validos, true)) {
        $response['mensaje'] = 'Tipo de reporte no válido.';
        echo json_encode($response);
        exit;
    }
    
    // Validar filtro_periodo para prevenir inyección
    $periodos_validos = ['todos', 'dia', 'semana', 'mes', 'anio'];
    if (!in_array($filtro_periodo, $periodos_validos, true)) {
        $response['mensaje'] = 'Filtro de período no válido.';
        echo json_encode($response);
        exit;
    }
    
    $query = "";
    $titulo = "";
    $data = [];

    switch ($reporte_tipo) {
        case 'ventas':
            $titulo = "Reporte de Ventas por Producto";
            // USO DE FUNCIÓN CENTRALIZADA
            $condicion_ventas = obtener_condicion_fecha($filtro_periodo, 'v.fecha');
            
            $query = "SELECT p.nombre, SUM(v.cantidad) AS total_vendido, SUM(v.precio_total) AS total_valor
                      FROM ventas v 
                      JOIN productos p ON v.producto_id = p.id
                      WHERE $condicion_ventas
                      GROUP BY v.producto_id, p.nombre
                      ORDER BY total_vendido DESC";
            break;

        case 'stock':
            $titulo = "Reporte de Stock Bajo";
            // Reporte de stock es un snapshot, no se filtra por fecha.
            $query = "SELECT nombre, cantidad 
                      FROM productos
                      ORDER BY cantidad ASC
                      LIMIT 20";
            break;

        case 'valor':
            $titulo = "Reporte de Valor por Categoría";
            // Reporte de valor es un snapshot, no se filtra por fecha.
            $query = "SELECT categoria, SUM(cantidad * precio) AS valor_total 
                                 FROM productos
                                 GROUP BY categoria
                                 ORDER BY valor_total DESC";
            break;
            
        default:
            $response['mensaje'] = 'Tipo de reporte no reconocido.';
            echo json_encode($response);
            exit;
    }
    
    $result = $conn->query($query);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $result->close();

        // **Punto de Generación de PDF**
        // En este punto se usaría una librería (ej. FPDF) para construir el archivo PDF
        
        $response['success'] = true;
        $response['titulo'] = $titulo;
        $response['filtro'] = $filtro_periodo;
        // La respuesta real no devolvería los datos, sino el PDF. 
        // Aquí solo simulamos el éxito.

    } else {
        $response['mensaje'] = 'Error al ejecutar la consulta: ' . $conn->error;
    }

} else {
    $response['mensaje'] = 'Solicitud inválida o faltan parámetros.';
}

echo json_encode($response);
?>