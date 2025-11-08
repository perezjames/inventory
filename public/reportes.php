<?php
// public/reportes.php
require_once __DIR__ . '/../core/session.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../core/funciones.php';

verificarSesion();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container-fluid p-4">
 <h2 class="mb-4">Reportes</h2>  
    
    <ul class="nav nav-tabs" id="reportesTab" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="ventas-tab" data-bs-toggle="tab" data-bs-target="#ventas" type="button">Ventas</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="stock-tab" data-bs-toggle="tab" data-bs-target="#stock" type="button">Stock</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="valor-tab" data-bs-toggle="tab" data-bs-target="#valor" type="button">Valor</button>
      </li>
    </ul>
    
    <div class="tab-content mt-3" id="reportesTabContent">

      <!-- Reporte de Ventas -->
      <div class="tab-pane fade show active" id="ventas" role="tabpanel">
          <?php
          // Nota: Asumiendo que tienes una tabla 'ventas'. Si no, esta consulta fallará.
          // La mantengo como estaba en tu archivo original.
          $ventas = $conn->query("SELECT p.nombre, SUM(v.cantidad) AS total_vendido 
                                  FROM ventas v 
                                  JOIN productos p ON v.producto_id = p.id
                                  GROUP BY v.producto_id
                                  ORDER BY total_vendido DESC
                                  LIMIT 10");
          ?>
          <table class="table table-striped table-bordered" id="tablaVentas">
            <thead class="table-dark">
              <tr><th>Producto</th><th>Total Vendido</th></tr>
            </thead>
            <tbody>
              <?php if ($ventas) while($row = $ventas->fetch_assoc()): ?>
                <tr>
                  <td><?= htmlspecialchars($row['nombre']) ?></td>
                  <td><?= $row['total_vendido'] ?></td>
                </tr>
              <?php endwhile; if ($ventas) $ventas->close(); ?>
            </tbody>
          </table>
      </div>

      <!-- Reporte de Stock -->
      <div class="tab-pane fade" id="stock" role="tabpanel">
          <?php
          $stock = $conn->query("SELECT nombre, cantidad 
                                 FROM productos
                                 ORDER BY cantidad ASC
                                 LIMIT 20");
          ?>
          <table class="table table-striped table-bordered" id="tablaStock">
            <thead class="table-dark">
              <tr><th>Producto</th><th>Cantidad</th></tr>
            </thead>
            <tbody>
              <?php while($row = $stock->fetch_assoc()): ?>
                <tr>
                  <td><?= htmlspecialchars($row['nombre']) ?></td>
                  <td><?= $row['cantidad'] ?></td>
                </tr>
              <?php endwhile; $stock->close(); ?>
            </tbody>
          </table>
      </div>

      <!-- Reporte de Valor -->
      <div class="tab-pane fade" id="valor" role="tabpanel">
          <?php
          $valor = $conn->query("SELECT categoria, SUM(cantidad * precio) AS valor_total 
                                 FROM productos
                                 GROUP BY categoria
                                 ORDER BY valor_total DESC");
          ?>
          <table class="table table-striped table-bordered" id="tablaValor">
            <thead class="table-dark">
              <tr><th>Categoría</th><th>Valor Total</th></tr>
            </thead>
            <tbody>
              <?php while($row = $valor->fetch_assoc()): ?>
                <tr>
                  <td><?= htmlspecialchars($row['categoria']) ?></td>
                  <td>$<?= number_format($row['valor_total'], 2) ?></td>
                </tr>
              <?php endwhile; $valor->close(); ?>
            </tbody>
          </table>
      </div>

    </div>
</div>

<?php 
require_once __DIR__ . '/../includes/scripts.php'; 
?>
<script>
// Inicialización de DataTables específica para esta página
$(document).ready(function(){
    $('#tablaVentas, #tablaStock, #tablaValor').DataTable({
        paging: true,
        searching: true,
        ordering: true,
        responsive: true,
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
    });
});
</script>

<?php 
require_once __DIR__ . '/../includes/footer.php'; 
?>
</body>
</html>