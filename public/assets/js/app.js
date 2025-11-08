// public/assets/js/app.js

// Esperar a que el DOM esté listo
$(function() {

    // URL base para las acciones
    const actionsUrl = '../actions/'; // Ruta desde public/ a actions/

    // Configuración global de idioma para DataTables
    const dataTableLang = {
        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
    };

    // --- INICIALIZACIÓN DE DATATABLES ---

    // Solo inicializar la tabla si existe en la página actual
    if ($('#tablaProductos').length > 0) {
        const tablaProductos = $('#tablaProductos').DataTable({
            language: dataTableLang,
            pageLength: 10,
            responsive: true,
            // Definir columnas para el renderizado de datos
            columnDefs: [
                { "targets": [0, 3, 7], "width": "1%" }, // ID, Cantidad, Ingreso
                { "targets": [8], "orderable": false, "width": "10%" } // Acciones
            ],
        });

        // --- MANEJADORES DE EVENTOS PARA index.php ---

        // 1. AGREGAR PRODUCTO (Fetch API)
        const formAgregar = document.getElementById("formAgregar");
        if (formAgregar) {
            formAgregar.addEventListener("submit", function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                fetch(actionsUrl + "agregar_producto.php", {
                        method: "POST",
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            const producto = data.producto;
                            const modal = bootstrap.Modal.getInstance(document.getElementById('modalAgregar'));

                            modal.hide();
                            formAgregar.reset();

                            const precioFormateado = `$${Number(producto.precio).toLocaleString('es-CO', { minimumFractionDigits: 0 })}`;
                            const valorTotalFormateado = `$${(producto.cantidad * producto.precio).toLocaleString('es-CO', { minimumFractionDigits: 0 })}`;

                            // Agregar fila al DataTable
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
                            ]).draw(false); // 'false' para no resetear la paginación

                            mostrarAlerta('Producto agregado correctamente', 'success');
                        } else {
                            mostrarAlerta("Error: " + data.error, 'danger');
                        }
                    })
                    .catch(error => {
                        console.error("Error al agregar:", error);
                        mostrarAlerta("Error en la conexión o formato de respuesta.", 'danger');
                    });
            });
        }

        // 2. EDITAR PRODUCTO (CARGAR MODAL)
        const modalEditar = document.getElementById('editarModal');
        if (modalEditar) {
            modalEditar.addEventListener('show.bs.modal', function(e) {
                const id = e.relatedTarget.dataset.id;
                const contenidoModal = document.getElementById('contenidoEditar');

                const spinner = `<div class="text-center py-5">
                                  <div class="spinner-border text-dark" role="status">
                                      <span class="visually-hidden">Cargando...</span>
                                  </div>
                                  <p class="mt-2">Cargando información...</p>
                              </div>`;
                contenidoModal.innerHTML = spinner;

                $.post(actionsUrl + 'editar_producto_form.php', { id: id }, function(data) {
                    contenidoModal.innerHTML = data;
                }).fail(function() {
                    contenidoModal.innerHTML = '<div class="alert alert-danger">Error al cargar los datos del producto.</div>';
                });
            });
        }

        // 3. GUARDAR CAMBIOS (EDICIÓN) (Delegación de evento)
        $(document).on('submit', '#formEditar', function(e) {
            e.preventDefault();
            const formData = $(this).serialize();

            $.post(actionsUrl + 'editar_producto_guardar.php', formData, function(respuesta) {
                try {
                    const data = JSON.parse(respuesta);
                    if (data.success) {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('editarModal'));
                        modal.hide();

                        // Encontrar la fila en DataTable
                        const fila = tablaProductos.row($(`button[data-id="${data.id}"]`).closest('tr'));

                        if (fila.length) {
                            const precioFormateado = `$${Number(data.precio).toLocaleString('es-CO', { minimumFractionDigits: 0 })}`;
                            const valorTotalFormateado = `$${(data.precio * data.cantidad).toLocaleString('es-CO', { minimumFractionDigits: 0 })}`;

                            // Actualizar datos de la fila
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
                        mostrarAlerta('Error: ' + data.mensaje, 'danger');
                    }
                } catch (e) {
                    console.error("Error al parsear JSON:", respuesta);
                    mostrarAlerta('Error inesperado al guardar.', 'danger');
                }
            }).fail(function() {
                mostrarAlerta('Error de conexión al guardar.', 'danger');
            });
        });

        // 4. ELIMINAR PRODUCTO (CARGAR MODAL)
        const modalEliminar = document.getElementById('eliminarModal');
        if (modalEliminar) {
            modalEliminar.addEventListener('show.bs.modal', function(e) {
                const id = e.relatedTarget.dataset.id;
                document.getElementById('producto_id_eliminar').value = id;
            });
        }

        // 5. CONFIRMAR ELIMINACIÓN
        const formEliminar = document.getElementById('formEliminar');
        if (formEliminar) {
            formEliminar.addEventListener('submit', function(e) {
                e.preventDefault();
                const id = document.getElementById('producto_id_eliminar').value;

                $.post(actionsUrl + 'eliminar_producto.php', { id: id }, function(respuesta) {
                    try {
                        const data = JSON.parse(respuesta);
                        if (data.success) {
                            const modal = bootstrap.Modal.getInstance(document.getElementById('eliminarModal'));
                            modal.hide();

                            // Eliminar fila de DataTable
                            const fila = tablaProductos.row($(`button[data-id="${id}"]`).closest('tr'));
                            if (fila.length) {
                                fila.remove().draw(false);
                            }

                            mostrarAlerta('Producto eliminado correctamente', 'success');
                        } else {
                            mostrarAlerta('No se pudo eliminar: ' + data.mensaje, 'danger');
                        }
                    } catch (e) {
                        console.error("Error al parsear JSON:", respuesta);
                        mostrarAlerta('Error inesperado al eliminar.', 'danger');
                    }
                }).fail(function() {
                    mostrarAlerta('Error de conexión al eliminar.', 'danger');
                });
            });
        }
    }

    // --- FUNCIÓN DE ALERTA GLOBAL ---
    function mostrarAlerta(mensaje, tipo = 'success') {
        const alerta = document.createElement("div");
        alerta.className = `alert alert-${tipo} alert-dismissible fade show position-fixed top-0 end-0 m-3 shadow`;
        alerta.role = "alert";
        alerta.style.zIndex = "9999";
        alerta.innerHTML = `
          ${mensaje}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      `;
        document.body.appendChild(alerta);

        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alerta);
            bsAlert.close();
        }, 4000);
    }

});