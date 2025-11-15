<?php
// public/historial.php
require_once __DIR__ . '/../core/bootstrap.php';

// Consultar movimientos
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
            <?php if ($movimientos instanceof mysqli_result && $movimientos->num_rows > 0): ?>
              <?php while ($row = $movimientos->fetch_assoc()): ?>
                <tr>
                  <td><?= (int)$row['id'] ?></td>
                  <td><?= htmlspecialchars($row['producto'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td>
                    <?php
                      $tipo = (string)$row['tipo'];
                      $map = [
                        'entrada'     => ['badge' => 'bg-success',   'texto' => 'Entrada'],
                        'salida'      => ['badge' => 'bg-danger',    'texto' => 'Salida Manual'],
                        'venta'       => ['badge' => 'bg-danger',    'texto' => 'Venta'],
                        'eliminacion' => ['badge' => 'bg-secondary', 'texto' => 'Eliminación'],
                        'edicion'     => ['badge' => 'bg-secondary', 'texto' => 'Edición'],
                      ];
                      $badge_class = $map[$tipo]['badge'] ?? 'bg-secondary';
                      $texto = $map[$tipo]['texto'] ?? ucwords($tipo);
                    ?>
                    <span class="badge <?= $badge_class ?>"><?= $texto ?></span>
                  </td>
                  <td><?= (int)$row['cantidad'] ?></td>
                  <td><?= htmlspecialchars($row['fecha'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($row['nombre_usuario'] ?? 'Sistema', ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($row['comentario'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="7" class="text-center">No hay movimientos para mostrar.</td></tr>
            <?php endif; ?>
            <?php if ($movimientos instanceof mysqli_result) { $movimientos->free(); } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php 
require_once __DIR__ . '/../includes/scripts.php'; 
require_once __DIR__ . '/../includes/footer.php'; 
?>
</body>
</html>