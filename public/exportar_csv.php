<?php
require_once __DIR__ . '/../core/bootstrap.php';

$tipo = $_GET['tipo'] ?? '';
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';
$use_date_filter = ($fecha_inicio !== '' && $fecha_fin !== '');
$fecha_fin_ajustada = $use_date_filter ? $fecha_fin . ' 23:59:59' : '';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="reporte_' . $tipo . '_' . date('Ymd') . '.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['Reporte', ucfirst($tipo), 'Generado', date('c')]);

switch ($tipo) {
    case 'ventas':
        $sql = "SELECT v.id, p.nombre, v.cantidad, v.precio_total, v.fecha 
                FROM ventas v JOIN productos p ON v.producto_id = p.id";
        if ($use_date_filter) $sql .= " WHERE v.fecha BETWEEN ? AND ?";
        $stmt = $conn->prepare($sql);
        if ($use_date_filter) $stmt->bind_param("ss", $fecha_inicio, $fecha_fin_ajustada);
        $stmt->execute();
        $res = $stmt->get_result();
        fputcsv($out, ['ID','Producto','Cantidad','Total','Fecha']);
        while ($r = $res->fetch_assoc()) {
            fputcsv($out, [$r['id'], $r['nombre'], $r['cantidad'], $r['precio_total'], $r['fecha']]);
        }
        $stmt->close();
        break;

    case 'stock':
        $sql = "SELECT id, nombre, categoria, cantidad, precio FROM productos";
        if ($use_date_filter) $sql .= " WHERE fecha_ingreso BETWEEN ? AND ?";
        $stmt = $conn->prepare($sql);
        if ($use_date_filter) $stmt->bind_param("ss", $fecha_inicio, $fecha_fin_ajustada);
        $stmt->execute();
        $res = $stmt->get_result();
        fputcsv($out, ['ID','Nombre','Categoría','Cantidad','Precio']);
        while ($r = $res->fetch_assoc()) {
            fputcsv($out, [$r['id'], $r['nombre'], $r['categoria'], $r['cantidad'], $r['precio']]);
        }
        $stmt->close();
        break;

    case 'valor':
        $sql = "SELECT categoria, SUM(cantidad * precio) AS valor_total FROM productos";
        if ($use_date_filter) $sql .= " WHERE fecha_ingreso BETWEEN ? AND ?";
        $sql .= " GROUP BY categoria";
        $stmt = $conn->prepare($sql);
        if ($use_date_filter) $stmt->bind_param("ss", $fecha_inicio, $fecha_fin_ajustada);
        $stmt->execute();
        $res = $stmt->get_result();
        fputcsv($out, ['Categoría','Valor Total']);
        while ($r = $res->fetch_assoc()) {
            fputcsv($out, [$r['categoria'], $r['valor_total']]);
        }
        $stmt->close();
        break;

    default:
        fputcsv($out, ['Tipo de reporte no soportado.']);
}

fclose($out);
exit;