<?php
// public/ventas.php
// CAMBIO: Usar archivo central de inicialización
require_once __DIR__ . '/../core/bootstrap.php';
$csrf = generarCsrfToken();

// Cargar productos para el dropdown
// Solo productos con stock y activos
$productos_q = $conn->query("SELECT id, nombre, precio, cantidad FROM productos WHERE cantidad > 0 AND activo = 1 ORDER BY nombre ASC");

// Cargar ventas recientes
// CAMBIO: Se obtiene el nombre de usuario (u.usuario) para mostrar quién vendió
$ventas_q = $conn->query("
    SELECT v.id, p.nombre, v.cantidad, v.precio_total, v.fecha, u.usuario AS nombre_usuario
    FROM ventas v
    JOIN productos p ON v.producto_id = p.id
    LEFT JOIN usuarios u ON v.usuario = u.id
    ORDER BY v.fecha DESC
    LIMIT 10
");

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>
<div class="container-fluid p-4">
    <h2 class="mb-4">Registrar Venta</h2>
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <i class="bi bi-cart-plus-fill"></i> Nueva Venta
                </div>
                <div class="card-body">
                    <form id="formRegistrarVenta" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                        <div class="mb-3">
                            <label for="productoVenta" class="form-label">Producto</label>
                            <select class="form-select" id="productoVenta" name="producto_id" required>
                                <option value="" data-precio="0" data-stock="0">Seleccione un producto...</option>
                                <?php while($p = $productos_q->fetch_assoc()): ?>
                                    <option value="<?= $p['id'] ?>" data-precio="<?= $p['precio'] ?>" data-stock="<?= $p['cantidad'] ?>">
                                        <?= htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8') ?> (Stock: <?= $p['cantidad'] ?>)
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
                            <h4 class="mb-0 text-success" id="precioTotalVenta">$0,00</h4>
                        </div>
                        <button type="submit" class="btn btn-dark w-100">
                            <i class="bi bi-check-circle"></i> Registrar Venta
                        </button>
                    </form>
                </div>
            </div>
        </div>
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
                                    <th>Vendedor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($ventas_q->num_rows > 0): ?>
                                    <?php while($v = $ventas_q->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= (int)$v['id'] ?></td>
                                            <td><?= htmlspecialchars($v['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= (int)$v['cantidad'] ?></td>
                                            <td>$<?= number_format((float)$v['precio_total'], 2, ',', '.') ?></td>
                                            <td><?= htmlspecialchars($v['fecha'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($v['nombre_usuario'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center text-muted">No hay ventas recientes.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <form method="GET" action="exportar_csv.php" class="mt-3">
                        <input type="hidden" name="tipo" value="ventas">
                        <button class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-filetype-csv"></i> Exportar CSV Ventas
                        </button>
                    </form>
                </div>
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