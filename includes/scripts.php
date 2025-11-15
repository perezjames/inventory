<?php
// includes/scripts.php

// CAMBIO: Cache-busting menos agresivo. Usa APP_VERSION (si existe) o filemtime del JS.
$script_version = defined('APP_VERSION')
    ? APP_VERSION
    : (file_exists(__DIR__ . '/../public/assets/js/app.js') ? filemtime(__DIR__ . '/../public/assets/js/app.js') : '1');
?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js?v=<?= htmlspecialchars((string)$script_version, ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="assets/js/app.js?v=<?= htmlspecialchars((string)$script_version, ENT_QUOTES, 'UTF-8') ?>"></script>