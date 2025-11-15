    // public/assets/js/app.js

// Esperar a que el DOM esté listo
$(function () {

    // URL base para las acciones
    const actionsUrl = '../actions/'; // Ruta desde public/ a actions/

    // --- FUNCIÓN DE UTILIDAD: FORMATEO DE MONEDA CONSISTENTE (2 decimales, punto miles, coma decimal) ---
    function formatNumberToCurrency(number) {
        if (typeof number === 'string') number = parseFloat(number);
        if (isNaN(number)) return '$0,00';

        const formatter = new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        let formatted = formatter.format(number);
        // Reemplaza cualquier símbolo COP/$ al inicio por un único $
        return '$' + formatted.replace(/[^0-9.,-]/g, '').trim();
    }

    // --- FUNCIÓN DE ALERTA GLOBAL ---
    function mostrarAlerta(mensaje, tipo = 'success', duracion = 4000) {
        const alerta = document.createElement('div');
        alerta.className = `alert alert-${tipo} alert-dismissible fade show position-fixed top-0 end-0 m-3 shadow`;
        alerta.role = 'alert';
        alerta.style.zIndex = '9999';
        alerta.innerHTML = `
          ${mensaje}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        document.body.appendChild(alerta);

        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alerta);
            bsAlert.close();
        }, duracion);
    }

    // Configuración global de idioma para DataTables
    const dataTableLang = { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' };

    // --- INICIALIZACIÓN DE DATATABLES EN PÁGINAS ESPECÍFICAS ---

    // index.php
    if ($('#tablaProductos').length > 0) {
        const tablaProductos = $('#tablaProductos').DataTable({
            language: dataTableLang,
            pageLength: 10,
            responsive: true,
            columnDefs: [
                { targets: [0, 3, 7], width: '1%' },
                { targets: [8], orderable: false, width: '10%' }
            ],
        });

        // --- MANEJADORES DE EVENTOS PARA index.php ---

        // 1. AGREGAR PRODUCTO (Fetch API)
        const formAgregar = document.getElementById('formAgregar');
        if (formAgregar) {
            formAgregar.addEventListener('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(this);

                fetch(actionsUrl + 'agregar_producto.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            const producto = data.producto;
                            const modal = bootstrap.Modal.getInstance(document.getElementById('modalAgregar'));

                            modal.hide();
                            formAgregar.reset();

                            const precioFormateado = formatNumberToCurrency(producto.precio);
                            const valorTotalFormateado = formatNumberToCurrency(producto.cantidad * producto.precio);

                            tablaProductos.row.add([
                                producto.id,
                                producto.nombre,
                                producto.categoria,
                                producto.cantidad,
                                precioFormateado,
                                valorTotalFormateado,
                                producto.estado,
                                producto.fecha_ingreso,
                                `<div class='d-grid gap-2 d-md-flex justify-content-md-center'>
                                    <button class='btn btn-outline-dark btn-sm' data-bs-toggle="modal" data-bs-target="#editarModal" data-id='${producto.id}'>Editar</button>
                                    <button class='btn btn-outline-danger btn-sm' data-bs-toggle="modal" data-bs-target="#eliminarModal" data-id='${producto.id}'>Eliminar</button>
                                </div>`
                            ]).draw(false);

                            mostrarAlerta('Producto agregado correctamente', 'success');
                        } else {
                            mostrarAlerta('Error: ' + (data.error || 'Solicitud inválida'), 'danger');
                        }
                    })
                    .catch(error => {
                        console.error('Error al agregar:', error);
                        mostrarAlerta('Error en la conexión o formato de respuesta.', 'danger');
                    });
            });
        }

        // 2. EDITAR PRODUCTO (CARGAR MODAL)
        const modalEditar = document.getElementById('editarModal');
        if (modalEditar) {
            modalEditar.addEventListener('show.bs.modal', function (e) {
                const id = e.relatedTarget.dataset.id;
                const contenidoModal = document.getElementById('contenidoEditar');

                const spinner = `<div class="text-center py-5">
                                  <div class="spinner-border text-dark" role="status">
                                      <span class="visually-hidden">Cargando...</span>
                                  </div>
                                  <p class="mt-2">Cargando información...</p>
                              </div>`;
                contenidoModal.innerHTML = spinner;

                $.post(actionsUrl + 'editar_producto_form.php', { id: id }, function (data) {
                    contenidoModal.innerHTML = data;
                }).fail(function () {
                    contenidoModal.innerHTML = '<div class="alert alert-danger">Error al cargar los datos del producto.</div>';
                });
            });
        }

        // 3. GUARDAR CAMBIOS (EDICIÓN)
        $(document).on('submit', '#formEditar', function (e) {
            e.preventDefault();
            const formData = $(this).serialize();

            $.post(actionsUrl + 'editar_producto_guardar.php', formData, function (data) {
                if (data.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editarModal'));
                    modal.hide();

                    const fila = tablaProductos.row($(`button[data-id="${data.id}"]`).closest('tr'));

                    if (fila.length) {
                        const precioFormateado = formatNumberToCurrency(data.precio);
                        const valorTotalFormateado = formatNumberToCurrency(data.precio * data.cantidad);

                        fila.data([
                            data.id,
                            data.nombre,
                            data.categoria,
                            data.cantidad,
                            precioFormateado,
                            valorTotalFormateado,
                            data.estado,
                            data.fecha_ingreso,
                            `<div class='d-grid gap-2 d-md-flex justify-content-md-center'>
                                <button class='btn btn-outline-dark btn-sm' data-bs-toggle="modal" data-bs-target="#editarModal" data-id='${data.id}'>Editar</button>
                                <button class='btn btn-outline-danger btn-sm' data-bs-toggle="modal" data-bs-target="#eliminarModal" data-id='${data.id}'>Eliminar</button>
                             </div>`
                        ]).draw(false);
                    }

                    mostrarAlerta('Producto actualizado correctamente', 'success');
                } else {
                    mostrarAlerta('Error: ' + (data.mensaje || 'No se pudo actualizar'), 'danger');
                }
            }, 'json').fail(function () {
                mostrarAlerta('Error de conexión o respuesta inesperada del servidor.', 'danger');
            });
        });

        // 4. ELIMINAR PRODUCTO (CARGAR MODAL)
        const modalEliminar = document.getElementById('eliminarModal');
        if (modalEliminar) {
            modalEliminar.addEventListener('show.bs.modal', function (e) {
                const id = e.relatedTarget.dataset.id;
                document.getElementById('producto_id_eliminar').value = id;
            });
        }

        // 5. CONFIRMAR ELIMINACIÓN
        const formEliminar = document.getElementById('formEliminar');
        if (formEliminar) {
            formEliminar.addEventListener('submit', function (e) {
                e.preventDefault();
                const id = document.getElementById('producto_id_eliminar').value;

                $.post(actionsUrl + 'eliminar_producto.php', { id: id }, function (data) {
                    if (data.success) {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('eliminarModal'));
                        modal.hide();

                        const fila = tablaProductos.row($(`button[data-id="${id}"]`).closest('tr'));
                        if (fila.length) {
                            fila.remove().draw(false);
                        }

                        mostrarAlerta('Producto eliminado correctamente', 'success');
                    } else {
                        mostrarAlerta('No se pudo eliminar: ' + (data.mensaje || 'Error desconocido'), 'danger');
                    }
                }, 'json').fail(function () {
                    mostrarAlerta('Error de conexión o respuesta inesperada del servidor.', 'danger');
                });
            });
        }
    }

    // historial.php
    if ($('#tablaMovimientos').length > 0) {
        $('#tablaMovimientos').DataTable({
            language: dataTableLang,
            pageLength: 10,
            responsive: true,
            order: [[4, 'desc']] // Fecha desc
        });
    }

    // reportes.php
    if ($('#reportesTab').length > 0) {
        const dataTableConfig = {
            paging: true,
            ordering: true,
            responsive: true,
            searching: false,
            language: dataTableLang
        };

        let tablaVentas = $('#tablaVentas').DataTable(dataTableConfig);
        let tablaStock = $('#tablaStock').DataTable(dataTableConfig);
        let tablaValor = $('#tablaValor').DataTable(dataTableConfig);

        $('#btnExportarPDF').on('click', function () {
            const activeTabButton = $('#reportesTab .nav-link.active');
            const tableId = activeTabButton.attr('data-bs-target'); // #ventas, #stock, #valor
            const reportTitle = activeTabButton.text().trim();
            const originalTableSelector = tableId === '#ventas'
                ? '#tablaVentas'
                : (tableId === '#stock' ? '#tablaStock' : '#tablaValor');

            let dataTableInstance;
            if (tableId === '#ventas') dataTableInstance = tablaVentas;
            else if (tableId === '#stock') dataTableInstance = tablaStock;
            else if (tableId === '#valor') dataTableInstance = tablaValor;
            else {
                mostrarAlerta('No se encontró la tabla activa del reporte.', 'danger');
                return;
            }

            // Destruir para exponer todo el HTML (todas las filas)
            dataTableInstance.destroy();

            // Clonar la tabla HTML expandida
            const $table = $(originalTableSelector).clone();

            // Re-inicializar DataTables inmediatamente
            const newInstance = $(originalTableSelector).DataTable(dataTableConfig);
            if (tableId === '#ventas') tablaVentas = newInstance;
            else if (tableId === '#stock') tablaStock = newInstance;
            else if (tableId === '#valor') tablaValor = newInstance;

            const tableToExport = $table[0];

            // Construcción HTML para el PDF
            const fechaInicio = $('#fecha_inicio').val();
            const fechaFin = $('#fecha_fin').val();
            let subTitle = `Reporte de: ${reportTitle}`;
            subTitle += (fechaInicio && fechaFin)
                ? ` (Desde ${fechaInicio} hasta ${fechaFin})`
                : ` (Sin filtros de fecha)`;

            const htmlContent = `
                <div style="padding: 20px; font-family: Arial, sans-serif;">
                    <h1 style="color: #212529; font-size: 24px; margin-bottom: 5px;">Reporte de Inventario</h1>
                    <p style="color: #6c757d; font-size: 14px; margin-bottom: 20px;">${subTitle}</p>
                    ${tableToExport.outerHTML}
                </div>
            `;

            // Generar PDF
            const element = document.createElement('div');
            element.innerHTML = htmlContent;
            const fechaHoy = new Date().toISOString().slice(0, 10);
            const filename = `reporte_${reportTitle.toLowerCase().replace(/\s+/g, '_')}_${fechaHoy}.pdf`;

            const opt = {
                margin: 10,
                filename: filename,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: {
                    scale: 2,
                    logging: false,
                    ignoreElements: (element) => {
                        return element.classList?.contains('dataTables_length') ||
                               element.classList?.contains('dataTables_filter') ||
                               element.classList?.contains('dataTables_info') ||
                               element.classList?.contains('dataTables_paginate');
                    }
                },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            try {
                html2pdf().set(opt).from(element).save();
            } catch (e) {
                console.error('Error al generar el PDF con html2pdf:', e);
                mostrarAlerta('Error al generar el PDF. Revise la consola.', 'danger');
            }
        });

        // Redibujo al cambiar de pestaña
        $('#reportesTab button[data-bs-toggle="tab"]').on('shown.bs.tab', function () {
            tablaVentas.columns.adjust().responsive.recalc();
            tablaStock.columns.adjust().responsive.recalc();
            tablaValor.columns.adjust().responsive.recalc();
        });
    }

    // --- MANEJADORES DE EVENTOS PARA ventas.php ---
    const formVenta = document.getElementById('formRegistrarVenta');
    if (formVenta) {
        const selectProducto = document.getElementById('productoVenta');
        const inputCantidad = document.getElementById('cantidadVenta');
        const totalDisplay = document.getElementById('precioTotalVenta');
        const stockFeedback = document.getElementById('stockFeedback');

        function actualizarTotal() {
            const selectedOption = selectProducto.options[selectProducto.selectedIndex];
            const precio = parseFloat(selectedOption?.dataset?.precio || 0);
            const cantidad = parseInt(inputCantidad.value || 0);
            const stockMax = parseInt(selectedOption?.dataset?.stock || 0);
            const productoSeleccionado = selectedOption?.value;

            const total = precio * cantidad;
            totalDisplay.textContent = formatNumberToCurrency(total);

            if (!productoSeleccionado) {
                inputCantidad.classList.add('is-invalid');
                stockFeedback.textContent = 'Por favor, seleccione un producto.';
            } else if (cantidad > stockMax) {
                inputCantidad.classList.add('is-invalid');
                stockFeedback.textContent = `Stock insuficiente. Solo hay ${stockMax} unidades disponibles.`;
            } else if (cantidad < 1) {
                inputCantidad.classList.add('is-invalid');
                stockFeedback.textContent = 'La cantidad debe ser mayor a cero.';
            } else {
                inputCantidad.classList.remove('is-invalid');
                stockFeedback.textContent = 'Por favor, ingrese una cantidad válida.';
            }
        }

        selectProducto.addEventListener('change', function () {
            const stockMax = parseInt(this.options[this.selectedIndex].dataset.stock || 0);
            inputCantidad.max = stockMax;

            if (parseInt(inputCantidad.value) > stockMax) {
                inputCantidad.value = stockMax;
            } else if (inputCantidad.value === '' && stockMax > 0) {
                inputCantidad.value = 1;
            }

            actualizarTotal();
        });

        inputCantidad.addEventListener('input', actualizarTotal);
        actualizarTotal();

        formVenta.addEventListener('submit', function (e) {
            e.preventDefault();

            actualizarTotal();
            if (inputCantidad.classList.contains('is-invalid')) {
                mostrarAlerta('Verifique el producto y la cantidad de venta.', 'danger');
                return;
            }

            const formData = new FormData(this);

            fetch(actionsUrl + 'registrar_venta.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        mostrarAlerta(data.mensaje, 'success');
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        mostrarAlerta('Error al registrar venta: ' + (data.mensaje || 'Error desconocido'), 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error al registrar venta:', error);
                    mostrarAlerta('Error en la conexión o formato de respuesta.', 'danger');
                });
        });
    }

});