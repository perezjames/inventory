<?php
// core/funciones.php

/**
 * Calcula el badge de estado de un producto basado en la cantidad.
 * @param int $cantidad
 * @return string HTML del badge
 */
function calcular_estado_producto($cantidad) {
    $cantidad = (int)$cantidad;
    if ($cantidad == 0) {
        return '<span class="badge bg-danger rounded-pill">Agotado</span>';
    }
    if ($cantidad < 10) {
        return '<span class="badge bg-warning text-dark rounded-pill">Bajo stock</span>';
    }
    return '<span class="badge bg-success rounded-pill">Disponible</span>';
}
?>