-- Script para añadir el campo profesor_sustituto_id a la tabla guardias
-- Este campo es necesario para asignar profesores sustitutos a las guardias

ALTER TABLE `guardias` 
ADD COLUMN `profesor_sustituto_id` INT(11) NULL AFTER `fecha`,
ADD KEY `profesor_sustituto_id` (`profesor_sustituto_id`),
ADD CONSTRAINT `guardias_ibfk_2` FOREIGN KEY (`profesor_sustituto_id`) REFERENCES `profesores` (`id`);

-- Verificar la estructura actualizada
DESCRIBE guardias;
