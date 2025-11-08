<?php
// public/ventas.php
require_once __DIR__ . '/../core/session.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../core/funciones.php';

verificarSesion();

// Cargar productos para el dropdown
// Solo productos con stock
$productos_q = $conn->query("SELECT id, nombre, precio, cantidad FROM productos WHERE cantidad > 0 ORDER BY nombre ASC");

// Cargar ventas recientes
$ventas_q = $conn->query("
    SELECT v.id, p.nombre, v.cantidad, v.precio_total, v.fecha 
    FROM ventas v
    JOIN productos p ON v.producto_id = p.id
    ORDER BY v.fecha DESC
    LIMIT 10
");

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container-fluid p-4">
    <h2 class="mb-4">Registrar Venta</h2>

    <div class="row g-4">
        <!-- Columna del formulario de venta -->
        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <i class="bi bi-cart-plus-fill"></i> Nueva Venta
                </div>
                <div class="card-body">
                    <form id="formRegistrarVenta" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="productoVenta" class="form-label">Producto</label>
                            <select class="form-select" id="productoVenta" name="producto_id" required>
                                <option value="" data-precio="0" data-stock="0">Seleccione un producto...</option>
                                <?php while($p = $productos_q->fetch_assoc()): ?>
                                    <option value="<?= $p['id'] ?>" data-precio="<?= $p['precio'] ?>" data-stock="<?= $p['cantidad'] ?>">
                                        <?= htmlspecialchars($p['nombre']) ?> (Stock: <?= $p['cantidad'] ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <div class="invalid-feedback">
                                Por favor, seleccione un producto.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="cantidadVenta" class="form-label">Cantidad</label>
                            <input type="number" class="form-control" id="cantidadVenta" name="cantidad" min="1" required>
                            <div class="invalid-feedback" id="stockFeedback">
                                Por favor, ingrese una cantidad válida.
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Total:</h5>
                            <h4 class="mb-0 text-success" id="precioTotalVenta">$0</h4>
                        </div>

                        <button type="submit" class="btn btn-dark w-100">
                            <i class="bi bi-check-circle"></i> Registrar Venta
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Columna de ventas recientes -->
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <i class="bi bi-clock-history"></i> Ventas Recientes
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="tablaVentasRecientes">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Total</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($ventas_q->num_rows > 0): ?>
                                    <?php while($v = $ventas_q->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $v['id'] ?></td>
                                            <td><?= htmlspecialchars($v['nombre']) ?></td>
                                            <td><?= $v['cantidad'] ?></td>
                                            <td>$<?= number_format($v['precio_total'], 0) ?></td>
                                            <td><?= $v['fecha'] ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No hay ventas recientes.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
require_once __DIR__ . '/../includes/scripts.php'; 
// El JS para esta página está en app.js
require_once __DIR__ . '/../includes/footer.php'; 
?>
</body>
</html>