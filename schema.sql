-- schema.sql
-- Entorno de desarrollo: recrea totalmente la base de datos inventario
-- ADVERTENCIA: Este script elimina la base existente (solo usar en DEV)

DROP DATABASE IF EXISTS inventario;
CREATE DATABASE inventario CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE inventario;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Tablas
CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario VARCHAR(100) NOT NULL UNIQUE,
  clave VARCHAR(255) NOT NULL,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE productos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL,
  categoria VARCHAR(100) NOT NULL,
  cantidad INT NOT NULL DEFAULT 0,
  precio DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  fecha_ingreso DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  INDEX idx_categoria (categoria),
  INDEX idx_fecha_ingreso (fecha_ingreso)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE movimientos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  producto_id INT NOT NULL,
  tipo ENUM('entrada','salida','venta','eliminacion','edicion') NOT NULL,
  cantidad INT NOT NULL DEFAULT 0,
  fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  usuario_id INT NULL DEFAULT NULL,
  comentario VARCHAR(255),
  INDEX idx_mov_producto (producto_id),
  INDEX idx_mov_fecha (fecha),
  INDEX idx_mov_usuario (usuario_id),
  CONSTRAINT fk_mov_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
  CONSTRAINT fk_mov_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ventas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  producto_id INT NOT NULL,
  cantidad INT NOT NULL,
  precio_total DECIMAL(12,2) NOT NULL,
  usuario INT NULL DEFAULT NULL,
  fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_ventas_producto (producto_id),
  INDEX idx_ventas_fecha (fecha),
  INDEX idx_ventas_usuario (usuario),
  CONSTRAINT fk_ventas_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
  CONSTRAINT fk_ventas_usuario FOREIGN KEY (usuario) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- Usuarios (las claves se migrarán a hash al primer login)
INSERT INTO usuarios (usuario, clave) VALUES
('admin','admin'),
('vendedor1','123456'),
('vendedor2','123456'),
('operador','123456'),
('auditor','123456'),
('almacen','123456'),
('soporte','123456'),
('compras','123456'),
('ventas','123456'),
('invitado','123456');

-- Productos base y adicionales (cantidad inicial = 0, luego se ajusta vía movimientos)
INSERT INTO productos (nombre,categoria,cantidad,precio,activo,fecha_ingreso) VALUES
('Monitor 24"','Hardware',0,650000,1,NOW()-INTERVAL 50 DAY),
('Teclado Mecánico','Hardware',0,280000,1,NOW()-INTERVAL 35 DAY),
('Silla Ergonómica','Mobiliario',0,1250000,1,NOW()-INTERVAL 40 DAY),
('Mouse Inalámbrico','Periféricos',0,95000,1,NOW()-INTERVAL 15 DAY),
('Disco SSD 1TB','Almacenamiento',0,420000,1,NOW()-INTERVAL 12 DAY),
('Router Gigabit','Redes',0,310000,1,NOW()-INTERVAL 18 DAY),
('Licencia Office Std','Software',0,580000,1,NOW()-INTERVAL 10 DAY),
('Cartucho Tóner Negro','Consumibles',0,180000,1,NOW()-INTERVAL 25 DAY),
('Impresora Láser','Impresoras',0,980000,1,NOW()-INTERVAL 27 DAY),
('Regleta 8 Tomas','Accesorios',0,45000,1,NOW()-INTERVAL 5 DAY),
('Soporte Monitor','Accesorios',0,78000,1,NOW()-INTERVAL 9 DAY),
('Switch 24 Puertos','Redes',0,1250000,1,NOW()-INTERVAL 55 DAY),
('HDD 4TB','Almacenamiento',0,390000,1,NOW()-INTERVAL 60 DAY),
('UPS 1500VA','Hardware',0,890000,1,NOW()-INTERVAL 42 DAY),
('Alfombrilla XL','Accesorios',0,32000,1,NOW()-INTERVAL 3 DAY),
('Dock USB-C','Periféricos',0,210000,1,NOW()-INTERVAL 22 DAY),
('Cable HDMI 2m','Accesorios',0,18000,1,NOW()-INTERVAL 13 DAY),
('Servidor Torre','Hardware',0,6500000,1,NOW()-INTERVAL 70 DAY),
('Rack 42U','Mobiliario',0,3500000,1,NOW()-INTERVAL 90 DAY),
('Firewall Hardware','Redes',0,2750000,1,NOW()-INTERVAL 66 DAY),
('Licencia Antivirus','Software',0,150000,1,NOW()-INTERVAL 30 DAY),
('Memoria RAM 16GB','Hardware',0,210000,1,NOW()-INTERVAL 28 DAY),
('Memoria RAM 32GB','Hardware',0,380000,1,NOW()-INTERVAL 25 DAY),
('HDD 1TB','Almacenamiento',0,210000,1,NOW()-INTERVAL 45 DAY),
('SSD 512GB','Almacenamiento',0,250000,1,NOW()-INTERVAL 33 DAY),
('Switch PoE 16P','Redes',0,980000,1,NOW()-INTERVAL 41 DAY),
('Patch Cord Cat6','Redes',0,12000,1,NOW()-INTERVAL 11 DAY),
('Cámara IP','Redes',0,350000,1,NOW()-INTERVAL 36 DAY),
('Brazo Monitor','Accesorios',0,110000,1,NOW()-INTERVAL 14 DAY),
('Kit Herramientas','Accesorios',0,95000,1,NOW()-INTERVAL 20 DAY),
('Scanner Código Barras','Periféricos',0,230000,1,NOW()-INTERVAL 29 DAY),
('Etiqueta Térmica 1000u','Consumibles',0,75000,1,NOW()-INTERVAL 17 DAY),
('Multifuncional Color','Impresoras',0,1650000,1,NOW()-INTERVAL 58 DAY),
('Panel Parcheo 24P','Redes',0,140000,1,NOW()-INTERVAL 24 DAY),
('Regulador Voltaje','Hardware',0,260000,1,NOW()-INTERVAL 38 DAY),
('Fuente 750W','Hardware',0,480000,1,NOW()-INTERVAL 44 DAY),
('Disco Externo 2TB','Almacenamiento',0,310000,1,NOW()-INTERVAL 19 DAY),
('Lector Huella','Periféricos',0,145000,1,NOW()-INTERVAL 21 DAY),
('Pizarra Acrílica','Mobiliario',0,180000,1,NOW()-INTERVAL 54 DAY),
('Silla Visita','Mobiliario',0,210000,1,NOW()-INTERVAL 59 DAY),
('Kit Limpieza PC','Consumibles',0,25000,1,NOW()-INTERVAL 7 DAY),
('Cartucho Tinta Color','Consumibles',0,120000,1,NOW()-INTERVAL 26 DAY),
('Monitor 27"','Hardware',0,1250000,1,NOW()-INTERVAL 34 DAY),
('Cable USB-C','Accesorios',0,15000,1,NOW()-INTERVAL 6 DAY),
('Base Refrigerante','Accesorios',0,95000,1,NOW()-INTERVAL 48 DAY),
('Laptop 14"','Hardware',0,2850000,1,NOW()-INTERVAL 43 DAY),
('Laptop 15"','Hardware',0,3250000,1,NOW()-INTERVAL 37 DAY),
('Mini PC','Hardware',0,1750000,1,NOW()-INTERVAL 52 DAY),
('Servidor Rack 2U','Hardware',0,12500000,1,NOW()-INTERVAL 85 DAY),
('Cinta Etiquetadora','Consumibles',0,55000,1,NOW()-INTERVAL 31 DAY),
('Cable Fibra SFP','Redes',0,98000,1,NOW()-INTERVAL 47 DAY);

-- Movimientos de entrada (stock inicial)
INSERT INTO movimientos (producto_id,tipo,cantidad,fecha,usuario_id,comentario) VALUES
(1,'entrada',25,NOW()-INTERVAL 30 DAY,1,'Carga inicial'),
(2,'entrada',15,NOW()-INTERVAL 20 DAY,1,'Carga inicial'),
(3,'entrada',8,NOW()-INTERVAL 18 DAY,1,'Carga inicial'),
(4,'entrada',40,NOW()-INTERVAL 10 DAY,1,'Carga inicial'),
(5,'entrada',22,NOW()-INTERVAL 9 DAY,1,'Carga inicial'),
(6,'entrada',18,NOW()-INTERVAL 15 DAY,1,'Carga inicial'),
(7,'entrada',35,NOW()-INTERVAL 8 DAY,1,'Carga inicial'),
(8,'entrada',60,NOW()-INTERVAL 12 DAY,1,'Carga inicial'),
(9,'entrada',10,NOW()-INTERVAL 14 DAY,1,'Carga inicial'),
(10,'entrada',55,NOW()-INTERVAL 5 DAY,1,'Carga inicial'),
(11,'entrada',25,NOW()-INTERVAL 7 DAY,1,'Carga inicial'),
(12,'entrada',6,NOW()-INTERVAL 40 DAY,1,'Carga inicial'),
(13,'entrada',14,NOW()-INTERVAL 45 DAY,1,'Carga inicial'),
(14,'entrada',9,NOW()-INTERVAL 25 DAY,1,'Carga inicial'),
(15,'entrada',50,NOW()-INTERVAL 3 DAY,1,'Carga inicial'),
(16,'entrada',20,NOW()-INTERVAL 22 DAY,1,'Carga inicial'),
(17,'entrada',80,NOW()-INTERVAL 13 DAY,1,'Carga inicial'),
(18,'entrada',3,NOW()-INTERVAL 70 DAY,1,'Carga inicial'),
(19,'entrada',2,NOW()-INTERVAL 90 DAY,1,'Carga inicial'),
(20,'entrada',4,NOW()-INTERVAL 66 DAY,1,'Carga inicial'),
(21,'entrada',40,NOW()-INTERVAL 30 DAY,1,'Carga inicial'),
(22,'entrada',35,NOW()-INTERVAL 28 DAY,1,'Carga inicial'),
(23,'entrada',25,NOW()-INTERVAL 25 DAY,1,'Carga inicial'),
(24,'entrada',30,NOW()-INTERVAL 45 DAY,1,'Carga inicial'),
(25,'entrada',28,NOW()-INTERVAL 33 DAY,1,'Carga inicial'),
(26,'entrada',7,NOW()-INTERVAL 41 DAY,1,'Carga inicial'),
(27,'entrada',120,NOW()-INTERVAL 11 DAY,1,'Carga inicial'),
(28,'entrada',18,NOW()-INTERVAL 36 DAY,1,'Carga inicial'),
(29,'entrada',22,NOW()-INTERVAL 14 DAY,1,'Carga inicial'),
(30,'entrada',26,NOW()-INTERVAL 20 DAY,1,'Carga inicial'),
(31,'entrada',12,NOW()-INTERVAL 29 DAY,1,'Carga inicial'),
(32,'entrada',40,NOW()-INTERVAL 17 DAY,1,'Carga inicial'),
(33,'entrada',4,NOW()-INTERVAL 58 DAY,1,'Carga inicial'),
(34,'entrada',18,NOW()-INTERVAL 24 DAY,1,'Carga inicial'),
(35,'entrada',15,NOW()-INTERVAL 38 DAY,1,'Carga inicial'),
(36,'entrada',20,NOW()-INTERVAL 44 DAY,1,'Carga inicial'),
(37,'entrada',30,NOW()-INTERVAL 19 DAY,1,'Carga inicial'),
(38,'entrada',16,NOW()-INTERVAL 21 DAY,1,'Carga inicial'),
(39,'entrada',5,NOW()-INTERVAL 54 DAY,1,'Carga inicial'),
(40,'entrada',12,NOW()-INTERVAL 59 DAY,1,'Carga inicial'),
(41,'entrada',90,NOW()-INTERVAL 7 DAY,1,'Carga inicial'),
(42,'entrada',35,NOW()-INTERVAL 26 DAY,1,'Carga inicial'),
(43,'entrada',14,NOW()-INTERVAL 34 DAY,1,'Carga inicial'),
(44,'entrada',22,NOW()-INTERVAL 6 DAY,1,'Carga inicial'),
(45,'entrada',14,NOW()-INTERVAL 48 DAY,1,'Carga inicial'),
(46,'entrada',10,NOW()-INTERVAL 43 DAY,1,'Carga inicial'),
(47,'entrada',8,NOW()-INTERVAL 37 DAY,1,'Carga inicial'),
(48,'entrada',5,NOW()-INTERVAL 52 DAY,1,'Carga inicial'),
(49,'entrada',1,NOW()-INTERVAL 85 DAY,1,'Carga inicial'),
(50,'entrada',60,NOW()-INTERVAL 31 DAY,1,'Carga inicial'),
(51,'entrada',25,NOW()-INTERVAL 47 DAY,1,'Carga inicial');

-- Ventas (movimiento + tabla ventas)
-- Selección simple: reducimos stock en algunos productos
INSERT INTO ventas (producto_id,cantidad,precio_total,usuario,fecha) VALUES
(1,5,650000*5,2,NOW()-INTERVAL 5 DAY),
(2,3,280000*3,2,NOW()-INTERVAL 4 DAY),
(4,10,95000*10,2,NOW()-INTERVAL 2 DAY),
(8,15,180000*15,3,NOW()-INTERVAL 3 DAY),
(10,12,45000*12,3,NOW()-INTERVAL 1 DAY),
(15,20,32000*20,2,NOW()-INTERVAL 1 DAY),
(21,8,150000*8,2,NOW()-INTERVAL 6 DAY),
(27,30,12000*30,3,NOW()-INTERVAL 2 DAY),
(41,25,25000*25,3,NOW()-INTERVAL 1 DAY);

INSERT INTO movimientos (producto_id,tipo,cantidad,fecha,usuario_id,comentario) VALUES
(1,'venta',5,NOW()-INTERVAL 5 DAY,2,'Venta referenciada'),
(2,'venta',3,NOW()-INTERVAL 4 DAY,2,'Venta referenciada'),
(4,'venta',10,NOW()-INTERVAL 2 DAY,2,'Venta referenciada'),
(8,'venta',15,NOW()-INTERVAL 3 DAY,3,'Venta referenciada'),
(10,'venta',12,NOW()-INTERVAL 1 DAY,3,'Venta referenciada'),
(15,'venta',20,NOW()-INTERVAL 1 DAY,2,'Venta referenciada'),
(21,'venta',8,NOW()-INTERVAL 6 DAY,2,'Venta referenciada'),
(27,'venta',30,NOW()-INTERVAL 2 DAY,3,'Venta referenciada'),
(41,'venta',25,NOW()-INTERVAL 1 DAY,3,'Venta referenciada');

-- Salidas (mermas / ajustes)
INSERT INTO movimientos (producto_id,tipo,cantidad,fecha,usuario_id,comentario) VALUES
(4,'salida',2,NOW()-INTERVAL 1 DAY,1,'Merma embalaje dañado'),
(8,'salida',3,NOW()-INTERVAL 2 DAY,1,'Ajuste inventario'),
(10,'salida',1,NOW()-INTERVAL 1 DAY,1,'Merma'),
(27,'salida',5,NOW()-INTERVAL 1 DAY,1,'Ajuste físico');

-- Ediciones / eliminaciones (logs informativos)
INSERT INTO movimientos (producto_id,tipo,cantidad,fecha,usuario_id,comentario) VALUES
(5,'edicion',0,NOW()-INTERVAL 2 DAY,4,'Actualización precio'),
(18,'eliminacion',0,NOW()-INTERVAL 10 DAY,4,'Desactivado temporal'),
(33,'edicion',0,NOW()-INTERVAL 3 DAY,4,'Corrección nombre'),
(49,'eliminacion',0,NOW()-INTERVAL 1 DAY,4,'Retirado catálogo');

-- Sincronizar cantidades finales
UPDATE productos p
LEFT JOIN (
  SELECT producto_id,
         SUM(CASE tipo
               WHEN 'entrada' THEN cantidad
               WHEN 'salida' THEN -cantidad
               WHEN 'venta' THEN -cantidad
               ELSE 0 END) AS stock
  FROM movimientos
  GROUP BY producto_id
) s ON s.producto_id = p.id
SET p.cantidad = IFNULL(s.stock,0);

-- Verificación rápida (opcional, puede comentarse si se importará en hosting con restricciones)
-- SELECT p.id,p.nombre,p.cantidad FROM productos p ORDER BY p.id;

-- Fin del script de semilla de desarrollo