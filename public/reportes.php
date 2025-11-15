<?php
// public/reportes.php
require_once __DIR__ . '/../core/bootstrap.php';

// --- LÓGICA DE DATOS ---
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';

$data_ventas = [];
$data_stock = [];
$data_valor = [];

$fecha_fin_ajustada = '';
$use_date_filter = !empty($fecha_inicio) && !empty($fecha_fin);
if ($use_date_filter) {
    $fecha_fin_ajustada = $fecha_fin . ' 23:59:59';
}

// 1. Ventas (con filtros opcionales)
$query_ventas = "SELECT p.nombre, SUM(v.cantidad) AS total_vendido 
                 FROM ventas v 
                 JOIN productos p ON v.producto_id = p.id";
if ($use_date_filter) {
    $query_ventas .= " WHERE v.fecha BETWEEN ? AND ?";
}
$query_ventas .= " GROUP BY v.producto_id ORDER BY total_vendido DESC LIMIT 10";
$stmt_ventas = $conn->prepare($query_ventas);
if ($use_date_filter) { $stmt_ventas->bind_param("ss", $fecha_inicio, $fecha_fin_ajustada); }
$stmt_ventas->execute();
if ($result_ventas = $stmt_ventas->get_result()) {
    while ($row = $result_ventas->fetch_assoc()) { $data_ventas[] = $row; }
    $result_ventas->free();
}
$stmt_ventas->close();

// 2. Stock (snapshot; opcional por fecha_ingreso)
$query_stock = "SELECT nombre, cantidad FROM productos";
if ($use_date_filter) { $query_stock .= " WHERE fecha_ingreso BETWEEN ? AND ?"; }
$query_stock .= " ORDER BY cantidad ASC LIMIT 20";
$stmt_stock = $conn->prepare($query_stock);
if ($use_date_filter) { $stmt_stock->bind_param("ss", $fecha_inicio, $fecha_fin_ajustada); }
$stmt_stock->execute();
if ($result_stock = $stmt_stock->get_result()) {
    while ($row = $result_stock->fetch_assoc()) { $data_stock[] = $row; }
    $result_stock->free();
}
$stmt_stock->close();

// 3. Valor (snapshot; opcional por fecha_ingreso)
$query_valor = "SELECT categoria, SUM(cantidad * precio) AS valor_total FROM productos";
if ($use_date_filter) { $query_valor .= " WHERE fecha_ingreso BETWEEN ? AND ?"; }
$query_valor .= " GROUP BY categoria ORDER BY valor_total DESC";
$stmt_valor = $conn->prepare($query_valor);
if ($use_date_filter) { $stmt_valor->bind_param("ss", $fecha_inicio, $fecha_fin_ajustada); }
$stmt_valor->execute();
if ($result_valor = $stmt_valor->get_result()) {
    while ($row = $result_valor->fetch_assoc()) { $data_valor[] = $row; }
    $result_valor->free();
}
$stmt_valor->close();

// --- RENDERIZACIÓN ---
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php'; 
?>

<div class="container-fluid p-4">
 <h2 class="mb-4">Reportes</h2>  

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-secondary text-white">
            <i class="bi bi-filter"></i> Filtros de Reporte
        </div>
        <div class="card-body">
            <form method="GET" action="reportes.php" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?= htmlspecialchars($fecha_inicio, ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="col-md-4">
                    <label for="fecha_fin" class="form-label">Fecha Fin</label>
                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?= htmlspecialchars($fecha_fin, ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-dark w-100">
                        <i class="bi bi-search"></i> Aplicar Filtros
                    </button>
                    <button type="button" class="btn btn-danger w-100" id="btnExportarPDF">
                        <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
                    </button>
                </div>
            </form>
        </div>
    </div>
    
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

      <div class="tab-pane fade show active" id="ventas" role="tabpanel">
          <table class="table table-striped table-bordered" id="tablaVentas">
            <thead class="table-dark">
              <tr><th>Producto</th><th>Total Vendido</th></tr>
            </thead>
            <tbody>
              <?php if (empty($data_ventas)): ?>
                <tr><td colspan="2" class="text-center">No hay datos de ventas (Verifique el rango de fechas).</td></tr>
              <?php else: ?>
                <?php foreach ($data_ventas as $row): ?>
                  <tr>
                    <td><?= htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= (int)$row['total_vendido'] ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
      </div>

      <div class="tab-pane fade" id="stock" role="tabpanel">
          <table class="table table-striped table-bordered" id="tablaStock">
            <thead class="table-dark">
              <tr><th>Producto</th><th>Cantidad</th></tr>
            </thead>
            <tbody>
              <?php if (empty($data_stock)): ?>
                <tr><td colspan="2" class="text-center">No hay productos en stock (Verifique el rango de fechas de ingreso).</td></tr>
              <?php else: ?>
                <?php foreach ($data_stock as $row): ?>
                  <tr>
                    <td><?= htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= (int)$row['cantidad'] ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
      </div>

      <div class="tab-pane fade" id="valor" role="tabpanel">
          <table class="table table-striped table-bordered" id="tablaValor">
            <thead class="table-dark">
              <tr><th>Categoría</th><th>Valor Total</th></tr>
            </thead>
            <tbody>
              <?php if (empty($data_valor)): ?>
                <tr><td colspan="2" class="text-center">No hay datos de valor de inventario (Verifique el rango de fechas de ingreso).</td></tr>
              <?php else: ?>
                <?php foreach ($data_valor as $row): ?>
                  <tr>
                    <td><?= htmlspecialchars($row['categoria'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td>$<?= number_format((float)$row['valor_total'], 2, ',', '.') ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
      </div>

    </div>
</div>

<?php 
require_once __DIR__ . '/../includes/scripts.php'; 
require_once __DIR__ . '/../includes/footer.php'; 
?>
</body>
</html>