<?php
require_once __DIR__ . '/../core/bootstrap.php';

// Resumen en una sola consulta para reducir round-trips a la base de datos
$totales = ['productos' => 0, 'stock' => 0, 'valor' => 0.0];
$summarySql = "SELECT COUNT(*) AS productos, COALESCE(SUM(cantidad),0) AS stock, COALESCE(SUM(cantidad * precio),0) AS valor FROM productos";
if ($res = $conn->query($summarySql)) {
    $row = $res->fetch_assoc();
    $totales['productos'] = (int)($row['productos'] ?? 0);
    $totales['stock']     = (int)($row['stock'] ?? 0);
    $totales['valor']     = (float)($row['valor'] ?? 0.0);
    $res->free();
}

// Incluir UI
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

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
          <h2>$<?= number_format((float)$totales['valor'], 2, ',', '.') ?></h2>
        </div>
      </div>
    </div>
  </div>

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
          // Seleccionar solo columnas necesarias
          $sql = "SELECT id, nombre, categoria, cantidad, precio, fecha_ingreso FROM productos ORDER BY id DESC";
          if ($result = $conn->query($sql)):
              while ($row = $result->fetch_assoc()):
                  $cantidad = (int)$row['cantidad'];
                  $precio = (float)$row['precio'];
                  $valor = $cantidad * $precio;
                  $estado = calcular_estado_producto($cantidad);
          ?>
            <tr>
              <td><?= (int)$row['id'] ?></td>
              <td><?= htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($row['categoria'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= $cantidad ?></td>
              <td>$<?= number_format($precio, 2, ',', '.') ?></td>
              <td>$<?= number_format($valor, 2, ',', '.') ?></td>
              <td><?= $estado ?></td>
              <td><?= htmlspecialchars($row['fecha_ingreso'], ENT_QUOTES, 'UTF-8') ?></td>
              <td>
                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                  <button class="btn btn-outline-dark btn-sm" data-bs-toggle="modal" data-bs-target="#editarModal" data-id="<?= (int)$row['id'] ?>">Editar</button>
                  <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#eliminarModal" data-id="<?= (int)$row['id'] ?>">Eliminar</button>
                </div>
              </td>
            </tr>
          <?php
              endwhile;
              $result->free();
          endif;
          ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php
require_once __DIR__ . '/../includes/modals.php';
require_once __DIR__ . '/../includes/scripts.php';
require_once __DIR__ . '/../includes/footer.php';
?>
</body>
</html>