<?php
// includes/scripts.php

// CAMBIO: Usamos time() para forzar la recarga del script en CADA re-carga de página.
// Esto es agresivo, pero elimina 100% los problemas de caché.
$script_version = time();
?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js?v=<?= $script_version ?>"></script>

<script src="assets/js/app.js?v=<?= $script_version ?>"></script>