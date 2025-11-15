<?php
require_once __DIR__ . '/../core/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $stmt = $conn->prepare("SELECT id,nombre,categoria,cantidad,precio FROM productos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $p = $result->fetch_assoc();
        $stmt->close();
        $csrf = generarCsrfToken();
?>
<form id="formEditar">
  <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
  <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
  <div class="mb-3">
    <label class="form-label">Nombre</label>
    <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8') ?>" required maxlength="100">
  </div>
  <div class="mb-3">
    <label class="form-label">Categoría</label>
    <input type="text" name="categoria" class="form-control" value="<?= htmlspecialchars($p['categoria'], ENT_QUOTES, 'UTF-8') ?>" required maxlength="100">
  </div>
  <div class="mb-3">
    <label class="form-label">Cantidad</label>
    <input type="number" name="cantidad" class="form-control" value="<?= (int)$p['cantidad'] ?>" required min="0" max="999999">
  </div>
  <div class="mb-3">
    <label class="form-label">Precio</label>
    <input type="number" name="precio" class="form-control" value="<?= number_format((float)$p['precio'], 2, '.', '') ?>" required min="0" step="0.01">
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