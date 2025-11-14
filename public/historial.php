<?php
// public/historial.php
// CAMBIO: Usar archivo central de inicialización
require_once __DIR__ . '/../core/bootstrap.php';

// Ejecutar la consulta de movimientos
// CAMBIO: Se obtiene el nombre de usuario (u.usuario) usando el user_id
$query = "
    SELECT 
        m.id, 
        p.nombre AS producto, 
        m.tipo, 
        m.cantidad, 
        m.fecha, 
        u.usuario AS nombre_usuario, 
        m.comentario
    FROM movimientos m
    JOIN productos p ON m.producto_id = p.id
    LEFT JOIN usuarios u ON m.usuario_id = u.id
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
                    <?php 
                    $tipo = $row['tipo'];
                    $badge_class = 'bg-secondary'; // Default: Edición, Eliminación
                    $texto = ucwords($tipo);

                    if ($tipo === 'entrada') {
                        $badge_class = 'bg-success';
                        $texto = 'Entrada';
                    } elseif ($tipo === 'salida') {
                        $badge_class = 'bg-danger';
                        $texto = 'Salida Manual';
                    } elseif ($tipo === 'venta') {
                        $badge_class = 'bg-danger';
                        $texto = 'Venta';
                    } elseif ($tipo === 'eliminacion') {
                        $texto = 'Eliminación';
                    } elseif ($tipo === 'edicion') {
                        $texto = 'Edición';
                    }
                    ?>
                    <span class="badge <?= $badge_class ?>"><?= $texto ?></span>
                </td>
                <td><?= $row['cantidad'] ?></td>
                <td><?= $row['fecha'] ?></td>
                <!-- CAMBIO: Mostrar nombre de usuario (viene de la tabla usuarios) -->
                <td><?= htmlspecialchars($row['nombre_usuario'] ?? 'Sistema') ?></td>
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