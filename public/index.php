<?php
// public/index.php
// CAMBIO: Usar archivo central de inicialización
require_once __DIR__ . '/../core/bootstrap.php';

// Consultas resumidas
$totales = [
    'productos' => 0,
    'stock' => 0,
    'valor' => 0
];

if ($stmt_prod = $conn->query("SELECT COUNT(*) AS total FROM productos")) {
    $totales['productos'] = $stmt_prod->fetch_assoc()['total'];
    $stmt_prod->close();
}
if ($stmt_stock = $conn->query("SELECT SUM(cantidad) AS total FROM productos")) {
    $totales['stock'] = $stmt_stock->fetch_assoc()['total'] ?? 0;
    $stmt_stock->close();
}
if ($stmt_valor = $conn->query("SELECT SUM(cantidad * precio) AS total FROM productos")) {
    $totales['valor'] = $stmt_valor->fetch_assoc()['total'] ?? 0;
    $stmt_valor->close();
}

// Incluir partes de la UI
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<!-- DASHBOARD -->
<div class="container-fluid p-4">
  <h2 class="mb-4">Dashboard</h2>
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="card shadow-sm border-0 text-center">
        <div class="card-body">
          <h5>Total productos</h5>
          <h2><?= $totales['productos'] ?></h2>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card shadow-sm border-0 text-center">
        <div class="card-body">
          <h5>Stock total</h5>
          <h2><?= $totales['stock'] ?></h2>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card shadow-sm border-0 text-center">
        <div class="card-body">
          <h5>Valor total</h5>
          <!-- CAMBIO: Formato monetario estandarizado a 2 decimales -->
          <h2>$<?= number_format($totales['valor'], 2, ',', '.') ?></h2>
        </div>
      </div>
    </div>
  </div>

  <!-- TABLA DE PRODUCTOS -->
  <div class="card shadow-sm">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
      <span>Lista de productos</span>
      <a href="#" class="btn btn-outline-light btn-sm" data-bs-toggle="modal" data-bs-target="#modalAgregar">Agregar producto</a>
    </div>
    <div class="card-body table-container">
      <div class="table-responsive">
        <table id="tablaProductos" class="table table-bordered table-hover">
          <thead class="table-dark">
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Categoría</th>
              <th>Cantidad</th>
              <th>Precio</th>
              <th>Valor Total</th>
              <th>Estado</th>
              <th>Ingreso</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>  
          <?php
          $result = $conn->query("SELECT * FROM productos ORDER BY id DESC");
          while ($row = $result->fetch_assoc()):
              $valor = $row['cantidad'] * $row['precio'];
              $estado = calcular_estado_producto($row['cantidad']);
          ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['nombre']) ?></td>
            <td><?= htmlspecialchars($row['categoria']) ?></td>
            <td><?= $row['cantidad'] ?></td>
            <!-- CAMBIO: Formato monetario estandarizado a 2 decimales -->
            <td>$<?= number_format($row['precio'], 2, ',', '.') ?></td>
            <td>$<?= number_format($valor, 2, ',', '.') ?></td>
            <td><?= $estado ?></td>
            <td><?= $row['fecha_ingreso'] ?></td>
            <td>
              <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                <button class="btn btn-outline-dark btn-sm" data-bs-toggle="modal" data-bs-target="#editarModal" data-id="<?= $row['id'] ?>">Editar</button>
                <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#eliminarModal" data-id="<?= $row['id'] ?>">Eliminar</button>
              </div>
            </td>
          </tr>
          <?php endwhile; ?>
          <?php $result->close(); ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php
// Incluir Modales y scripts
require_once __DIR__ . '/../includes/modals.php';
require_once __DIR__ . '/../includes/scripts.php';
require_once __DIR__ . '/../includes/footer.php';
?>
</body>
</html>