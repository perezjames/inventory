<?php
// public/historial.php
require_once __DIR__ . '/../core/session.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../core/funciones.php';

verificarSesion();

// Ejecutar la consulta de movimientos
$query = "
    SELECT m.id, p.nombre AS producto, m.tipo, m.cantidad, m.fecha, m.usuario, m.comentario
    FROM movimientos m
    JOIN productos p ON m.producto_id = p.id
    ORDER BY m.fecha DESC
";
$movimientos = $conn->query($query);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container-fluid p-4">
 <h2 class="mb-4">Historial de movimientos</h2>

<!-- TABLA DE MOVIMIENTOS -->
  <div class="card shadow-sm">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
      <span>Lista de movimientos</span>
    </div>
    <div class="card-body table-container">
      <div class="table-responsive">
        <table id="tablaMovimientos" class="table table-bordered table-hover">
          <thead class="table-dark">
            <tr>
              <th>ID</th>
              <th>Producto</th>
              <th>Tipo</th>
              <th>Cantidad</th>
              <th>Fecha</th>
              <th>Usuario</th>
              <th>Comentario</th>
            </tr>
          </thead>
          <tbody>  
            <?php while($row = $movimientos->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['producto']) ?></td>
                <td>
                    <?php if($row['tipo'] == 'entrada'): ?>
                        <span class="badge bg-success">Entrada</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Salida</span>
                    <?php endif; ?>
                </td>
                <td><?= $row['cantidad'] ?></td>
                <td><?= $row['fecha'] ?></td>
                <td><?= htmlspecialchars($row['usuario']) ?></td>
                <td><?= htmlspecialchars($row['comentario']) ?></td>
            </tr>
            <?php endwhile; ?>
            <?php $movimientos->close(); ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php 
require_once __DIR__ . '/../includes/scripts.php'; 
?>
<script>
    // Inicialización de DataTable específica para esta página
    $(document).ready(function(){
        $('#tablaMovimientos').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            pageLength: 10,
            responsive: true,
            order: [[ 4, 'desc' ]] // Ordenar por fecha descendente
        });
    });
</script>

<?php 
require_once __DIR__ . '/../includes/footer.php'; 
?>
</body>
</html>