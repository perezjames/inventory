<?php
require_once __DIR__ . '/../config/conexion.php';
header('Content-Type: application/json');
$status = ['db' => 'ok', 'time' => date('c'), 'version' => '1.0.0'];
try {
    $conn->query('SELECT 1');
} catch (Throwable $e) {
    $status['db'] = 'fail';
    $status['error'] = $e->getMessage();
}
echo json_encode($status);