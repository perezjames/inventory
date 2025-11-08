// actions/exportar_reporte.php - NUEVO ARCHIVO
<?php
// actions/exportar_reporte.php
header('Content-Type: application/json');

require_once __DIR__ . '/../core/session.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../core/funciones.php';

verificarSesion();

// Se incluye la función auxiliar para obtener la condición SQL de filtrado
function obtener_condicion_fecha($filtro, $campo_fecha) {
    if (!$filtro || $filtro === 'todos') return "1"; 
    
    switch ($filtro) {
        case 'dia':
            return "DATE($campo_fecha) = CURDATE()";
        case 'semana':
            return "YEARWEEK($campo_fecha, 1) = YEARWEEK(CURDATE(), 1)";
        case 'mes':
            return "YEAR($campo_fecha) = YEAR(CURDATE()) AND MONTH($campo_fecha) = MONTH(CURDATE())";
        case 'anio':
            return "YEAR($campo_fecha) = YEAR(CURDATE())";
        default:
            return "1";
    }
}


$response = ['success' => false, 'mensaje' => 'Solicitud inválida.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reporte_tipo'])) {
    
    $reporte_tipo = $_POST['reporte_tipo']; // ventas, stock, valor
    $filtro_periodo = $_POST['filtro_periodo'] ?? 'todos'; // dia, semana, mes, anio
    
    $query = "";
    $titulo = "";
    $data = [];

    switch ($reporte_tipo) {
        case 'ventas':
            $titulo = "Reporte de Ventas por Producto";
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
        // con $response['titulo'] y $response['data'].
        
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