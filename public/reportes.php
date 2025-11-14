<?php
// public/reportes.php
// CAMBIO: Usar archivo central de inicialización
require_once __DIR__ . '/../core/bootstrap.php';

// --- LÓGICA DE DATOS ---
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';

$data_ventas = [];
$data_stock = [];
$data_valor = [];

// 1. Consulta de Ventas (Modificada para aceptar filtros)
$params_ventas = [];
$query_ventas = "SELECT p.nombre, SUM(v.cantidad) AS total_vendido 
                 FROM ventas v 
                 JOIN productos p ON v.producto_id = p.id";

// Aplicar filtro de fecha SOLO si ambas fechas están presentes
if (!empty($fecha_inicio) && !empty($fecha_fin)) {
    $query_ventas .= " WHERE v.fecha BETWEEN ? AND ?";
    // Ajustar fecha_fin para que incluya todo el día
    $fecha_fin_ajustada = $fecha_fin . ' 23:59:59';
    $params_ventas = [$fecha_inicio, $fecha_fin_ajustada];
}

$query_ventas .= " GROUP BY v.producto_id ORDER BY total_vendido DESC LIMIT 10";

$stmt_ventas = $conn->prepare($query_ventas);
if (!empty($params_ventas)) {
    // 'ss' para dos strings (fecha_inicio, fecha_fin_ajustada)
    $stmt_ventas->bind_param("ss", $params_ventas[0], $params_ventas[1]);
}
$stmt_ventas->execute();
$result_ventas = $stmt_ventas->get_result();

if ($result_ventas && $result_ventas->num_rows > 0) {
    while ($row = $result_ventas->fetch_assoc()) {
        $data_ventas[] = $row;
    }
}
$result_ventas->close();
$stmt_ventas->close();


// 2. Consulta de Stock (Sin cambios, es un snapshot actual)
$query_stock = "SELECT nombre, cantidad 
                FROM productos
                ORDER BY cantidad ASC
                LIMIT 20";
$result_stock = $conn->query($query_stock);
if ($result_stock && $result_stock->num_rows > 0) {
    while ($row = $result_stock->fetch_assoc()) {
        $data_stock[] = $row;
    }
    $result_stock->close();
}

// 3. Consulta de Valor (Sin cambios, es un snapshot actual)
$query_valor = "SELECT categoria, SUM(cantidad * precio) AS valor_total 
                FROM productos
                GROUP BY categoria
                ORDER BY valor_total DESC";
$result_valor = $conn->query($query_valor);
if ($result_valor && $result_valor->num_rows > 0) {
    while ($row = $result_valor->fetch_assoc()) {
        $data_valor[] = $row;
    }
    $result_valor->close();
}

// --- RENDERIZACIÓN DE VISTA ---
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php'; 
?>

<div class="container-fluid p-4">
 <h2 class="mb-4">Reportes</h2>  

    <!-- Formulario de Filtros -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-secondary text-white">
            <i class="bi bi-filter"></i> Filtros de Reporte
        </div>
        <div class="card-body">
            <!-- Usamos GET para que las fechas se mantengan en la URL al recargar -->
            <form method="GET" action="reportes.php" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="fecha_inicio" class="form-label">Fecha Inicio (Solo Ventas)</label>
                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?= htmlspecialchars($fecha_inicio) ?>">
                </div>
                <div class="col-md-4">
                    <label for="fecha_fin" class="form-label">Fecha Fin (Solo Ventas)</label>
                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?= htmlspecialchars($fecha_fin) ?>">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-dark w-100">
                        <i class="bi bi-search"></i> Aplicar Filtros
                    </button>
                    <!-- Este botón NO es type="submit", es manejado por JS -->
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

      <!-- Reporte de Ventas -->
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
                    <td><?= htmlspecialchars($row['nombre']) ?></td>
                    <td><?= $row['total_vendido'] ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
      </div>

      <!-- Reporte de Stock -->
      <div class="tab-pane fade" id="stock" role="tabpanel">
          <table class="table table-striped table-bordered" id="tablaStock">
            <thead class="table-dark">
              <tr><th>Producto</th><th>Cantidad</th></tr>
            </thead>
            <tbody>
              <?php if (empty($data_stock)): ?>
                <tr><td colspan="2" class="text-center">No hay productos en stock.</td></tr>
              <?php else: ?>
                <?php foreach ($data_stock as $row): ?>
                  <tr>
                    <td><?= htmlspecialchars($row['nombre']) ?></td>
                    <td><?= $row['cantidad'] ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
      </div>

      <!-- Reporte de Valor -->
      <div class="tab-pane fade" id="valor" role="tabpanel">
          <table class="table table-striped table-bordered" id="tablaValor">
            <thead class="table-dark">
              <tr><th>Categoría</th><th>Valor Total</th></tr>
            </thead>
            <tbody>
              <?php if (empty($data_valor)): ?>
                <tr><td colspan="2" class="text-center">No hay datos de valor de inventario.</td></tr>
              <?php else: ?>
                <?php foreach ($data_valor as $row): ?>
                  <tr>
                    <td><?= htmlspecialchars($row['categoria']) ?></td>
                    <!-- CAMBIO: Formato monetario estandarizado a 2 decimales -->
                    <td>$<?= number_format($row['valor_total'], 2, ',', '.') ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
      </div>

    </div>
</div>

<?php 
// Esta línea incluye 'scripts.php' (que ya tiene la versión ?v=1.0.2)
require_once __DIR__ . '/../includes/scripts.php'; 
?>
<script>
// Inicialización de DataTables y Lógica de PDF (Ajustada para v1.5.3)
$(document).ready(function(){
    
    // 1. Inicializar DataTables
    const dataTableConfig = {
        paging: true,
        ordering: true,
        responsive: true,
        searching: false,
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
    };
    
    $('#tablaVentas').DataTable(dataTableConfig);
    $('#tablaStock').DataTable(dataTableConfig);
    $('#tablaValor').DataTable(dataTableConfig);

    // 2. Handler para exportar PDF
    $('#btnExportarPDF').on('click', function() {
        try {
            // 1. Verificar jsPDF (v1.5.3 se carga como window.jsPDF)
            if (typeof window.jsPDF === 'undefined') {
                alert("Error: La librería jsPDF (núcleo) no se cargó correctamente.");
                console.error("window.jsPDF no está definido.");
                return;
            }

            // 2. Instanciar jsPDF (v1.5.3)
            const doc = new window.jsPDF(); 
            
            // 3. Verificar autoTable (v1.5.3 se adhiere a la instancia doc)
            if (typeof doc.autoTable === 'undefined') {
                 alert("Error: La librería jspdf-autotable no se cargó correctamente.");
                 console.error("doc.autoTable no está definido.");
                 return;
            }
            
            // Título del documento
            doc.setFontSize(18);
            doc.text("Reporte de Inventario", 14, 22);
            doc.setFontSize(11);
            doc.setTextColor(100);

            // Determinar qué tabla está activa
            const activeTabButton = $('#reportesTab .nav-link.active');
            const tableId = activeTabButton.attr('data-bs-target');
            const tableElement = $(tableId).find('table'); 
            const reportTitle = activeTabButton.text().trim(); 

            // Añadir subtítulo con fechas
            const fechaInicio = $('#fecha_inicio').val();
            const fechaFin = $('#fecha_fin').val();
            let subTitle = `Reporte de: ${reportTitle}`;
            if (reportTitle === 'Ventas' && fechaInicio && fechaFin) {
                subTitle += ` (Desde ${fechaInicio} hasta ${fechaFin})`;
            }
            
            doc.text(subTitle, 14, 30);

            // 4. LLAMAR A doc.autoTable (Sintaxis para v1.5.3)
            doc.autoTable({
                html: tableElement[0],
                startY: 35,
                theme: 'grid',
                headStyles: { fillColor: [41, 45, 50] }
            });

            // Guardar el PDF
            const fechaHoy = new Date().toISOString().slice(0, 10);
            doc.save(`reporte_${reportTitle.toLowerCase().replace(' ', '_')}_${fechaHoy}.pdf`);

        } catch (e) {
            console.error("Error al generar el PDF:", e);
            alert("Error al generar el PDF. Verifique la consola.");
        }
    });
});
</script>

<?php 
require_once __DIR__ . '/../includes/footer.php'; 
?>
</body>
</html>