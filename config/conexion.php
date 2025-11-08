<?php
// config/conexion.php
$servername = "localhost";
$username = "root";
$password = "";
$database = "inventario";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
  die("Error de conexión: " . $conn->connect_error);
}

// Establecer el charset a UTF-8
$conn->set_charset("utf8");
?>