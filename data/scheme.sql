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
    id VARCHAR(20) PRIMARY KEY NOT NULL,
    valor VARCHAR(255)
);