<?php
// includes/header.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistema de Inventario</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml, <svg xmlns='http://www.w3.org/2000/svg' width='32' height='32' fill='white' class='bi bi-box-seam' viewBox='0 0 16 16'><path d='M8.186 1.113a.5.5 0 0 0-.372 0l-6.5 2.5A.5.5 0 0 0 1 4v8a.5.5 0 0 0 .314.464l6.5 2.5a.5.5 0 0 0 .372 0l6.5-2.5A.5.5 0 0 0 15 12V4a.5.5 0 0 0-.314-.387l-6.5-2.5zM8 2.06l5.79 2.23L8 6.06 2.21 4.29 8 2.06zM2 5.383l5.5 2.115v6.543L2 11.926V5.383zm6.5 8.658V7.498L14 5.383v6.543l-5.5 2.115z'/></svg>">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <!-- Estilos personalizados -->
    <style>
        body {
            background-color: #f8f9fa;
        }
    .table-container {
        max-height: 60vh;
        overflow-y: auto;
    }
    .card-header {
        position: sticky;
        top: 0;
        z-index: 10;
    }
</style>
</head>
<body>