-- Agregar campo link_roelplant a la tabla productos
ALTER TABLE `productos`
ADD COLUMN `link_roelplant` INT NULL DEFAULT NULL AFTER `imagen`,
ADD KEY `fk_link_roelplant_producto` (`link_roelplant`),
ADD CONSTRAINT `fk_link_roelplant_producto` FOREIGN KEY (`link_roelplant`) REFERENCES `variedades_producto` (`id`) ON DELETE SET NULL;

-- Agregar campo link_roelplant a la tabla servicios
ALTER TABLE `servicios`
ADD COLUMN `link_roelplant` INT NULL DEFAULT NULL AFTER `imagen`,
ADD KEY `fk_link_roelplant_servicio` (`link_roelplant`),
ADD CONSTRAINT `fk_link_roelplant_servicio` FOREIGN KEY (`link_roelplant`) REFERENCES `variedades_producto` (`id`) ON DELETE SET NULL;