CREATE TABLE IF NOT EXISTS proveedores (
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    razonSocial VARCHAR(120) NOT NULL,
    rut VARCHAR(16) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS facturas_compra (
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    id_proveedor INT NOT NULL,
    fecha DATE NOT NULL,
    fechaIngresoSII DATETIME NOT NULL,
    folio INT NOT NULL UNIQUE,
    montoTotal DECIMAL(20, 2) NOT NULL,
    iva DECIMAL(20, 2) NOT NULL,
    montoNeto DECIMAL(20, 2) NOT NULL,
    FOREIGN KEY (id_proveedor) REFERENCES proveedores(id)
);

CREATE TABLE IF NOT EXISTS facturas_compra_pagos (
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    id_factura_compra INT NOT NULL,
    fecha DATETIME NOT NULL,
    monto DECIMAL(20, 2) NOT NULL,
    comentario VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (id_factura_compra) REFERENCES facturas_compra(id)
);

CREATE TABLE IF NOT EXISTS config (
    id VARCHAR(100) PRIMARY KEY NOT NULL,
    valor VARCHAR(255)
);


CREATE TABLE `boletas` (
  `rowid` int(11) NOT NULL AUTO_INCREMENT,
  `folio` int(11) NOT NULL,
  `fecha` datetime DEFAULT NULL,
  `id_cotizacion` int(11) DEFAULT NULL,
  `caf` int(11) DEFAULT NULL,
  `track_id` varchar(30) DEFAULT NULL,
  `data` text DEFAULT NULL,
  `estado` varchar(30) DEFAULT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `id_cotizacion_directa` int(11) DEFAULT NULL,
  `id_guia_despacho` int(11) DEFAULT NULL,
  PRIMARY KEY (`rowid`),
  UNIQUE KEY `folio` (`folio`),
  KEY `caf` (`caf`),
  KEY `id_cotizacion` (`id_cotizacion`),
  KEY `fk_id_vendedor` (`id_usuario`),
  KEY `fk_id_coti_directa` (`id_cotizacion_directa`),
  KEY `fk_id_gd1` (`id_guia_despacho`),
  CONSTRAINT `boletas_ibfk_1` FOREIGN KEY (`caf`) REFERENCES `folios_caf` (`id`),
  CONSTRAINT `boletas_ibfk_2` FOREIGN KEY (`id_cotizacion`) REFERENCES `cotizaciones` (`id`),
  CONSTRAINT `fk_id_coti_directa_boleta` FOREIGN KEY (`id_cotizacion_directa`) REFERENCES `cotizaciones_directas` (`id`),
  CONSTRAINT `fk_id_gd1_boleta` FOREIGN KEY (`id_guia_despacho`) REFERENCES `guias_despacho` (`rowid`),
  CONSTRAINT `fk_id_vendedor_boleta` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `boletas_pagos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comentario` varchar(100) DEFAULT NULL,
  `monto` int(11) DEFAULT NULL,
  `fecha` datetime DEFAULT NULL,
  `rowid_boleta` int(11) DEFAULT NULL,
  `comprobante` longblob DEFAULT NULL,
  `rowid_cotizacion` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rowid_boleta` (`rowid_boleta`),
  KEY `rowid_cotizacion` (`rowid_cotizacion`),
  CONSTRAINT `boletas_pagos_ibfk_1` FOREIGN KEY (`rowid_boleta`) REFERENCES `boletas` (`rowid`),
  CONSTRAINT `boletas_pagos_ibfk_2` FOREIGN KEY (`rowid_cotizacion`) REFERENCES `cotizaciones` (`id`)
);

ALTER TABLE `notas_credito` ADD `id_boleta` INT NULL DEFAULT NULL AFTER `id_factura`;

ALTER TABLE notas_credito
ADD CONSTRAINT fk_id_boleta
FOREIGN KEY (id_boleta) 
REFERENCES boletas(rowid);

ALTER TABLE `guias_despacho` ADD `id_boleta` INT NULL DEFAULT NULL AFTER `id_factura`;

ALTER TABLE guias_despacho
ADD CONSTRAINT fk_id_boleta2
FOREIGN KEY (id_boleta) 
REFERENCES boletas(rowid);