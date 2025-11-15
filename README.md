# Inventario

Sistema de gestión de inventario con control de productos, movimientos, ventas y reportes (PDF/CSV).

## Requisitos
- PHP >= 8.1
- MySQL/MariaDB
- Extensiones: mysqli, json
- Opcional: Composer (para PHPUnit)

## Instalación
1. Clonar repositorio:
   git clone https://github.com/perezjames/inventory.git
2. Copiar `.env.example` a `.env` y ajustar credenciales.
3. Importar el esquema:
   mysql -u root -p inventario < schema.sql
4. Acceder a `public/login.php` y entrar con usuario: admin / admin (se migrará a hash).
5. (Opcional) Instalar PHPUnit:
   composer require --dev phpunit/phpunit
   vendor/bin/phpunit

## Esquema principal
Tablas: `usuarios`, `productos`, `movimientos`, `ventas`.
- movimientos.tipo: entrada | salida | venta | eliminacion | edicion
- productos.activo permite soft-delete futuro.

## Características
- Dashboard con totales.
- CRUD seguro (CSRF + prepared statements).
- Registro de movimientos con usuario.
- Ventas con actualización de stock transaccional.
- Reportes filtrables (PDF y CSV).
- Control de sesión con expiración por inactividad.
- Headers de seguridad (CSP básica).
- Exportación CSV: ventas, stock, valor.
- Pruebas unitarias básicas (PHPUnit).

## Comandos útiles
Backup:
mysqldump -u root -p inventario > backup.sql

Restaurar:
mysql -u root -p inventario < backup.sql

## Scripts
- `scripts/healthcheck.php` verifica estado de la DB.

## Seguridad
Ver `SECURITY.md`.

## Mejoras futuras
- Paginación server-side.
- Roles y permisos (admin/operador).
- Registro histórico de precios.
- Eliminación sin 'unsafe-inline' en CSP (nonces).
- Tests de integración adicionales.

## Autor
James Pérez

## Licencia
MIT