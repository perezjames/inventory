<?php
// public/reportes.php
require_once __DIR__ . '/../core/session.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../core/funciones.php'; // Incluye obtener_condicion_fecha()

verificarSesion();

// Obtener parámetros GET para filtros y pestaña activa
$filtro_actual = $_GET['filtro'] ?? 'todos';
$active_tab = $_GET['tab'] ?? 'ventas';

// Lógica para asegurar que solo una pestaña está activa al recargar
$is_ventas_active = ($active_tab === 'ventas') ? 'active' : '';
$is_stock_active = ($active_tab === 'stock') ? 'active' : '';
$is_valor_active = ($active_tab === 'valor') ? 'active' : '';

if (empty($is_stock_active) && empty($is_valor_active)) {
    $is_ventas_active = 'active'; // Por defecto, ventas
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container-fluid p-4">
 <h2 class="mb-4">Reportes</h2>  
    
    <ul class="nav nav-tabs" id="reportesTab" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link <?= $is_ventas_active ?>" id="ventas-tab" data-bs-toggle="tab" data-bs-target="#ventas" type="button">Ventas</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link <?= $is_stock_active ?>" id="stock-tab" data-bs-toggle="tab" data-bs-target="#stock" type="button">Stock</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link <?= $is_valor_active ?>" id="valor-tab" data-bs-toggle="tab" data-bs-target="#valor" type="button">Valor</button>
      </li>
    </ul>
    
    <div class="tab-content mt-3" id="reportesTabContent">

      <div class="tab-pane fade show <?= $is_ventas_active ? 'active' : '' ?>" id="ventas" role="tabpanel">
          <div class="d-flex justify-content-end mb-3">
              <form id="formFiltroVentas" class="d-flex align-items-center me-2" method="GET" action="reportes.php">
                  <input type="hidden" name="tab" value="ventas">
                  <select name="filtro" class="form-select form-select-sm me-2" onchange="this.form.submit()">
                      <option value="todos" <?= $filtro_actual == 'todos' ? 'selected' : '' ?>>Todos</option>
                      <option value="dia" <?= $filtro_actual == 'dia' ? 'selected' : '' ?>>Hoy</option>
                      <option value="semana" <?= $filtro_actual == 'semana' ? 'selected' : '' ?>>Esta Semana</option>
                      <option value="mes" <?= $filtro_actual == 'mes' ? 'selected' : '' ?>>Este Mes</option>
                      <option value="anio" <?= $filtro_actual == 'anio' ? 'selected' : '' ?>>Este Año</option>
                  </select>
              </form>
              <button class="btn btn-danger btn-sm" id="exportarVentas" data-reporte="ventas">
                  <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
              </button>
          </div>
          <?php
          $condicion_ventas = obtener_condicion_fecha($filtro_actual, 'v.fecha');
          
          $query_ventas = "SELECT p.nombre, SUM(v.cantidad) AS total_vendido, SUM(v.precio_total) AS total_valor
                          FROM ventas v 
                          JOIN productos p ON v.producto_id = p.id
                          WHERE $condicion_ventas
                          GROUP BY v.producto_id, p.nombre
                          ORDER BY total_vendido DESC
                          LIMIT 100"; 

          $ventas = $conn->query($query_ventas);
          ?>
          <table class="table table-striped table-bordered" id="tablaVentas">
            <thead class="table-dark">
              <tr><th>Producto</th><th>Total Vendido</th><th>Valor Total</th></tr>
            </thead>
            <tbody>
              <?php if ($ventas && $ventas->num_rows > 0) while($row = $ventas->fetch_assoc()): ?>
                <tr>
                  <td><?= htmlspecialchars($row['nombre']) ?></td>
                  <td><?= $row['total_vendido'] ?></td>
                  <td>$<?= number_format($row['total_valor'], 2, ',', '.') ?></td>
                </tr>
              <?php endwhile; if ($ventas) $ventas->close(); ?>
              <?php if (!$ventas || $ventas->num_rows == 0): ?>
                  <tr><td colspan="3" class="text-center text-muted">No hay ventas registradas para el período seleccionado.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
      </div>

      <div class="tab-pane fade show <?= $is_stock_active ? 'active' : '' ?>" id="stock" role="tabpanel">
          <div class="d-flex justify-content-end mb-3">
              <button class="btn btn-danger btn-sm" id="exportarStock" data-reporte="stock">
                  <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
              </button>
          </div>
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

      <div class="tab-pane fade show <?= $is_valor_active ? 'active' : '' ?>" id="valor" role="tabpanel">
          <div class="d-flex justify-content-end mb-3">
              <button class="btn btn-danger btn-sm" id="exportarValor" data-reporte="valor">
                  <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
              </button>
          </div>
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
    
    // Función para manejar la activación de pestañas después de la recarga
    const activeTab = '<?= $active_tab ?>';
    if(activeTab) {
        new bootstrap.Tab(document.getElementById(activeTab + '-tab')).show();
    }
    
    // --- Lógica de Exportación (Simulación de PDF) ---
    $('button[id^="exportar"]').on('click', function() {
        const reporteTipo = $(this).data('reporte');
        let filtroPeriodo = 'todos'; 
        
        // Obtener el filtro del dropdown de Ventas si es el caso
        if (reporteTipo === 'ventas') {
             filtroPeriodo = $('#formFiltroVentas select[name="filtro"]').val();
        }
        
        // Simulación de la exportación a PDF (usa la función mostrarAlerta de app.js)
        mostrarAlerta(`Solicitando exportación PDF del reporte de ${reporteTipo.toUpperCase()} con filtro: ${filtroPeriodo}.
                      <br>
                      <p class='small mt-2 mb-0'>La generación de PDF requiere librerías como FPDF o TCPDF en el servidor. La lógica de filtrado está implementada, pero el archivo PDF no se generará sin ellas.</p>`, 'info', 7000);
    });
});
</script>

<?php 
require_once __DIR__ . '/../includes/footer.php'; 
?>
</body>
</html>