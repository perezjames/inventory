<?php
// actions/editar_producto_form.php
// Este archivo devuelve SOLO HTML

// CAMBIO: Usar archivo central de inicialización
require_once __DIR__ . '/../core/bootstrap.php';
// Nota: verificarSesion() ya se llama en bootstrap.php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    
    $stmt = $conn->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $producto = $result->fetch_assoc();
        $stmt->close();
?>
        <!-- Este es el HTML que se inyecta en el modal -->
        <form id="formEditar">
          <input type="hidden" name="id" value="<?= $producto['id'] ?>">
          <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($producto['nombre']) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Categoría</label>
            <input type="text" name="categoria" class="form-control" value="<?= htmlspecialchars($producto['categoria']) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Cantidad</label>
            <input type="number" name="cantidad" class="form-control" value="<?= $producto['cantidad'] ?>" required min="0">
          </div>
          <div class="mb-3">
            <label class="form-label">Precio</label>
            <!-- CAMBIO: Se ajusta el valor para que sea un float, asegurando que el navegador lo muestre correctamente con decimales -->
            <input type="number" name="precio" class="form-control" value="<?= number_format($producto['precio'], 2, '.', '') ?>" required min="0" step="0.01">
          </div>
          <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-dark">Guardar Cambios</button>
          </div>
        </form>
<?php
    } else {
        $stmt->close();
        echo '<div class="alert alert-danger">Producto no encontrado.</div>';
    }
} else {
    echo '<div class="alert alert-danger">Solicitud inválida.</div>';
}
?>