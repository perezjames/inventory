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

// Validar formato de fechas para prevenir inyección SQL
$use_date_filter = false;
$fecha_fin_ajustada = '';

if (!empty($fecha_inicio) && !empty($fecha_fin)) {
    // Validar que las fechas tengan el formato correcto (YYYY-MM-DD)
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_inicio) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_fin)) {
        // Validar que sean fechas válidas
        $fecha_inicio_obj = DateTime::createFromFormat('Y-m-d', $fecha_inicio);
        $fecha_fin_obj = DateTime::createFromFormat('Y-m-d', $fecha_fin);
        
        if ($fecha_inicio_obj && $fecha_fin_obj && 
            $fecha_inicio_obj->format('Y-m-d') === $fecha_inicio && 
            $fecha_fin_obj->format('Y-m-d') === $fecha_fin) {
            $use_date_filter = true;
            $fecha_fin_ajustada = $fecha_fin . ' 23:59:59';
        }
    }
}

// 1. Consulta de Ventas (Modificada para aceptar filtros)
$query_ventas = "SELECT p.nombre, SUM(v.cantidad) AS total_vendido 
                 FROM ventas v 
                 JOIN productos p ON v.producto_id = p.id";
$params_ventas = [];

if ($use_date_filter) {
    // El filtro de ventas usa la fecha de la venta (v.fecha)
    $query_ventas .= " WHERE v.fecha BETWEEN ? AND ?";
    $params_ventas = [$fecha_inicio, $fecha_fin_ajustada];
}

$query_ventas .= " GROUP BY v.producto_id ORDER BY total_vendido DESC LIMIT 10";

$stmt_ventas = $conn->prepare($query_ventas);
if ($use_date_filter) {
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


// 2. Consulta de Stock (Filtro por fecha de ingreso)
$query_stock = "SELECT nombre, cantidad 
                FROM productos";
$params_stock = [];

if ($use_date_filter) {
    // CAMBIO: Se aplica el filtro a fecha_ingreso para limitar los productos considerados
    $query_stock .= " WHERE fecha_ingreso BETWEEN ? AND ?";
    $params_stock = [$fecha_inicio, $fecha_fin_ajustada];
}

$query_stock .= " ORDER BY cantidad ASC LIMIT 20";

$stmt_stock = $conn->prepare($query_stock);
if ($use_date_filter) {
    $stmt_stock->bind_param("ss", $params_stock[0], $params_stock[1]);
}
$stmt_stock->execute();
$result_stock = $stmt_stock->get_result();

if ($result_stock && $result_stock->num_rows > 0) {
    while ($row = $result_stock->fetch_assoc()) {
        $data_stock[] = $row;
    }
}
$result_stock->close();
$stmt_stock->close();


// 3. Consulta de Valor (Filtro por fecha de ingreso)
$query_valor = "SELECT categoria, SUM(cantidad * precio) AS valor_total 
                FROM productos";
$params_valor = [];

if ($use_date_filter) {
    // CAMBIO: Se aplica el filtro a fecha_ingreso para limitar los productos considerados
    $query_valor .= " WHERE fecha_ingreso BETWEEN ? AND ?";
    $params_valor = [$fecha_inicio, $fecha_fin_ajustada];
}

$query_valor .= " GROUP BY categoria ORDER BY valor_total DESC";

$stmt_valor = $conn->prepare($query_valor);
if ($use_date_filter) {
    $stmt_valor->bind_param("ss", $params_valor[0], $params_valor[1]);
}
$stmt_valor->execute();
$result_valor = $stmt_valor->get_result();

if ($result_valor && $result_valor->num_rows > 0) {
    while ($row = $result_valor->fetch_assoc()) {
        $data_valor[] = $row;
    }
}
$result_valor->close();
$stmt_valor->close();

// --- RENDERIZACIÓN DE VISTA ---
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
                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?= htmlspecialchars($fecha_inicio) ?>">
                </div>
                <div class="col-md-4">
                    <label for="fecha_fin" class="form-label">Fecha Fin</label>
                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?= htmlspecialchars($fecha_fin) ?>">
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
                    <td><?= htmlspecialchars($row['nombre']) ?></td>
                    <td><?= $row['total_vendido'] ?></td>
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
                    <td><?= htmlspecialchars($row['nombre']) ?></td>
                    <td><?= $row['cantidad'] ?></td>
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
                    <td><?= htmlspecialchars($row['categoria']) ?></td>
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
// Inicialización de DataTables y Lógica de PDF (Ajustada para html2pdf.js)
$(document).ready(function(){
    
    // 1. Inicializar DataTables
    const dataTableConfig = {
        paging: true,
        ordering: true,
        responsive: true,
        searching: false,
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
    };
    
    // Se usa 'let' porque estas variables se reasignarán después de la destrucción/recreación
    let tablaVentas = $('#tablaVentas').DataTable(dataTableConfig);
    let tablaStock = $('#tablaStock').DataTable(dataTableConfig);
    let tablaValor = $('#tablaValor').DataTable(dataTableConfig);
    

    // 2. Handler para exportar PDF (Usando html2pdf.js)
    $('#btnExportarPDF').on('click', function() {
        
        const activeTabButton = $('#reportesTab .nav-link.active');
        const tableId = activeTabButton.attr('data-bs-target'); // #ventas, #stock, #valor
        const reportTitle = activeTabButton.text().trim(); 
        const originalTableSelector = tableId === '#ventas' ? '#tablaVentas' : (tableId === '#stock' ? '#tablaStock' : '#tablaValor');

        let dataTableInstance;
        
        // Determinar la instancia de DataTables
        if (tableId === '#ventas') {
            dataTableInstance = tablaVentas;
        } else if (tableId === '#stock') {
            dataTableInstance = tablaStock;
        } else if (tableId === '#valor') {
            dataTableInstance = tablaValor;
        } else {
             alert("Error: No se encontró la tabla de reporte activa.");
             return;
        }

        // **Ajuste CRÍTICO: Destruir, Clonar (para exportación), y Recrear**
        
        // 1. Destruir la instancia actual para exponer todo el HTML (todas las filas)
        dataTableInstance.destroy();
        
        // 2. Clonar la tabla HTML (que ahora es simple HTML con todas las filas)
        const $table = $(originalTableSelector).clone();
        
        // 3. Re-inicializar DataTables inmediatamente en el elemento original
        const newInstance = $(originalTableSelector).DataTable(dataTableConfig); 

        // 4. Actualizar la variable de instancia global (SOLUCIÓN AL ERROR)
        if (tableId === '#ventas') {
            tablaVentas = newInstance;
        } else if (tableId === '#stock') {
            tablaStock = newInstance;
        } else if (tableId === '#valor') {
            tablaValor = newInstance;
        }
        
        const tableToExport = $table[0]; 

        // --- Construir el contenido a exportar ---
        
        // Usamos estilos inline básicos para que html2pdf.js los reconozca.
        let htmlContent = `
            <div style="padding: 20px; font-family: Arial, sans-serif;">
                <h1 style="color: #212529; font-size: 24px; margin-bottom: 5px;">Reporte de Inventario</h1>
        `;

        // Subtítulo con fechas (APLICA A TODOS)
        const fechaInicio = $('#fecha_inicio').val();
        const fechaFin = $('#fecha_fin').val();
        let subTitle = `Reporte de: ${reportTitle}`;
        
        // Si hay fechas, se añaden al subtítulo
        if (fechaInicio && fechaFin) {
            subTitle += ` (Desde ${fechaInicio} hasta ${fechaFin})`;
        } else {
            subTitle += ` (Sin filtros de fecha)`;
        }
        
        htmlContent += `
                <p style="color: #6c757d; font-size: 14px; margin-bottom: 20px;">${subTitle}</p>
        `;
        
        // Agregar la tabla al contenido.
        htmlContent += tableToExport.outerHTML;

        htmlContent += `</div>`;


        // 4. Configuración y llamada a html2pdf
        
        // Crear un elemento temporal y añadirle el contenido HTML
        const element = document.createElement('div');
        element.innerHTML = htmlContent;

        const fechaHoy = new Date().toISOString().slice(0, 10);
        const filename = `reporte_${reportTitle.toLowerCase().replace(' ', '_')}_${fechaHoy}.pdf`;

        // **Ajustes de Configuración para html2pdf**
        const opt = {
          margin: 10,
          filename: filename,
          image: { type: 'jpeg', quality: 0.98 },
          html2canvas: { 
              scale: 2, // Aumentar la escala para mejor calidad de imagen
              logging: false, 
              // Ignorar selectores de DataTables para asegurar que no se exporten elementos de navegación.
              ignoreElements: (element) => {
                  return element.classList.contains('dataTables_length') || 
                         element.classList.contains('dataTables_filter') || 
                         element.classList.contains('dataTables_info') || 
                         element.classList.contains('dataTables_paginate');
              }
          },
          jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };

        try {
            // Usar la función global html2pdf que se carga desde el CDN
            html2pdf().set(opt).from(element).save();
            
        } catch (e) {
            console.error("Error al generar el PDF con html2pdf:", e);
            alert("Error al generar el PDF. Verifique la consola.");
        }
    });
    
    // Restaurar los botones de DataTables si el usuario cambia de pestaña
    $('#reportesTab button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        // Redraw DataTables on tab switch to fix possible layout issues
        tablaVentas.columns.adjust().responsive.recalc();
        tablaStock.columns.adjust().responsive.recalc();
        tablaValor.columns.adjust().responsive.recalc();
    });
});
</script>

<?php 
require_once __DIR__ . '/../includes/footer.php'; 
?>
</body>
</html>