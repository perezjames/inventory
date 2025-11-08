/inventory/
├── config/
│   └── conexion.php                        # Solo la conexión a la BD
├── core/
│   ├── funciones.php                       # Funciones de ayuda (ej. calcular_estado)
│   └── session.php                         # Manejo de inicio de sesión y verificación
├── includes/
│   ├── header.php                          # Cabecera HTML, CSS links
│   ├── navbar.php                          # Barra de navegación
│   ├── footer.php                          # Pie de página
│   ├── scripts.php                         # Links JS (jQuery, Bootstrap, DataTables)
│   └── modals.php                          # Todos los modales (Agregar, Editar, Eliminar)
├── public/                                 # Carpeta principal (document root)
│   ├── index.php                           # Dashboard principal
│   ├── historial.php
│   ├── login.php
│   ├── logout.php
│   ├── reportes.php
│   └── assets/
│       └── js/
│           └── app.js                      # JavaScript personalizado
└── actions/                                # Lógica de backend (CRUD)
    ├── agregar_producto.php
    ├── editar_producto_form.php
    ├── editar_producto_guardar.php
    └── eliminar_producto.php