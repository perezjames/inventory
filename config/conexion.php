<?php
// config/conexion.php
declare(strict_types=1);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host     = $_ENV['DB_HOST']     ?? 'localhost';
$user     = $_ENV['DB_USER']     ?? 'root';
$pass     = $_ENV['DB_PASS']     ?? '';
$name     = $_ENV['DB_NAME']     ?? 'inventario';
$charset  = 'utf8mb4';

try {
  $conn = new mysqli($host, $user, $pass, $name);
  $conn->set_charset($charset);
} catch (mysqli_sql_exception $e) {
  error_log('[DB] ' . $e->getMessage());
  http_response_code(500);
  exit('Error de conexión a la base de datos.');
}
?>