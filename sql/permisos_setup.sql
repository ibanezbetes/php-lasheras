-- ============================================================
-- permisos_setup.sql
-- Script para adaptar la tabla menus y crear las tablas de permisos
-- ============================================================

USE `db_di25`;

SET FOREIGN_KEY_CHECKS=0;

-- ============================================================
-- PARTE 1: Adaptar tabla menus
-- Renombrar columnas para que coincidan con el CRUD de permisos
-- ============================================================

-- Renombrar etiqueta -> texto
ALTER TABLE `menus` CHANGE COLUMN `etiqueta` `texto` VARCHAR(100) COLLATE latin1_spanish_ci NOT NULL;

-- Renombrar posicion -> orden
ALTER TABLE `menus` CHANGE COLUMN `posicion` `orden` INT NOT NULL DEFAULT 0;

-- Añadir columna publica (S/N)
ALTER TABLE `menus` ADD COLUMN `publica` CHAR(1) COLLATE latin1_spanish_ci NOT NULL DEFAULT 'S' AFTER `activo`;

-- ============================================================
-- PARTE 2: Crear tablas de permisos, roles y relaciones
-- ============================================================

-- Tabla de permisos
CREATE TABLE IF NOT EXISTS `permisos` (
  `idPermiso` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `idOpcion` int(11) UNSIGNED DEFAULT NULL,
  `numPermiso` int(2) UNSIGNED DEFAULT NULL,
  `permiso` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`idPermiso`),
  KEY `fk_permisos_menus_idx` (`idOpcion`),
  CONSTRAINT `opcion_permiso` FOREIGN KEY (`idOpcion`) REFERENCES `menus` (`idOpcion`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

-- Tabla de roles
CREATE TABLE IF NOT EXISTS `roles` (
  `idRol` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `rol` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`idRol`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

-- Tabla intermedia permisos-rol
CREATE TABLE IF NOT EXISTS `permisosrol` (
  `idPermiso` int(11) UNSIGNED NOT NULL,
  `idRol` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`idPermiso`,`idRol`),
  KEY `permisosrol_rol` (`idRol`),
  CONSTRAINT `permiso_permisosrol` FOREIGN KEY (`idPermiso`) REFERENCES `permisos` (`idPermiso`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `permisosrol_rol` FOREIGN KEY (`idRol`) REFERENCES `roles` (`idRol`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

-- Tabla intermedia permisos-usuario
CREATE TABLE IF NOT EXISTS `permisosusuario` (
  `idPermiso` int(11) UNSIGNED NOT NULL,
  `idUsuario` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`idPermiso`,`idUsuario`),
  KEY `id_Usuario` (`idUsuario`),
  CONSTRAINT `permisos_permisosusuario` FOREIGN KEY (`idPermiso`) REFERENCES `permisos` (`idPermiso`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `permisosusuario_usuario` FOREIGN KEY (`idUsuario`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

-- Tabla intermedia roles-usuario
CREATE TABLE IF NOT EXISTS `rolesusuario` (
  `idRol` int(11) UNSIGNED NOT NULL,
  `idUsuario` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`idRol`,`idUsuario`),
  KEY `fk_rolesusuarios_roles1_idx` (`idRol`),
  KEY `fk_rolesusuarios_usuarios1_idx` (`idUsuario`),
  CONSTRAINT `rolesusuario_rol` FOREIGN KEY (`idRol`) REFERENCES `roles` (`idRol`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `rolesusuario_usuario` FOREIGN KEY (`idUsuario`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

-- ============================================================
-- PARTE 3: Datos iniciales
-- ============================================================

-- Crear rol Administrador
INSERT INTO `roles` (`rol`) VALUES ('Administrador');

-- Obtener el idOpcion de cada opcion del menu para crear permisos basicos
-- Crear permisos para cada opcion del menu (Consultar, Crear, Modificar, Eliminar)
INSERT INTO `permisos` (`idOpcion`, `numPermiso`, `permiso`)
SELECT idOpcion, 1, 'Consultar' FROM menus
UNION ALL
SELECT idOpcion, 2, 'Crear' FROM menus
UNION ALL
SELECT idOpcion, 3, 'Modificar' FROM menus
UNION ALL
SELECT idOpcion, 4, 'Eliminar' FROM menus;

-- Asignar TODOS los permisos al rol Administrador (idRol=1)
INSERT INTO `permisosrol` (`idPermiso`, `idRol`)
SELECT idPermiso, 1 FROM permisos;

-- Asignar el rol Administrador al usuario admin (buscar por login)
INSERT INTO `rolesusuario` (`idRol`, `idUsuario`)
SELECT 1, idUsuario FROM usuarios WHERE login='admin' LIMIT 1;

-- Añadir opción de Permisos al menú
-- Asumiendo que "Mtto.Datos" tiene idOpcion=4 (el padre del desplegable)
INSERT INTO `menus` (`texto`, `idPadre`, `orden`, `accion`, `activo`, `publica`) VALUES
('Permisos', 4, 4, "obtenerVista('Permisos','getVistaPermisosPrincipal','capaContenido')", 'S', 'N');

-- Crear permisos para la nueva opcion de Permisos
SET @idOpcionPermisos = LAST_INSERT_ID();
INSERT INTO `permisos` (`idOpcion`, `numPermiso`, `permiso`) VALUES
(@idOpcionPermisos, 1, 'Consultar'),
(@idOpcionPermisos, 2, 'Crear'),
(@idOpcionPermisos, 3, 'Modificar'),
(@idOpcionPermisos, 4, 'Eliminar');

-- Asignar los permisos de la opcion Permisos al Administrador
INSERT INTO `permisosrol` (`idPermiso`, `idRol`)
SELECT idPermiso, 1 FROM permisos WHERE idOpcion = @idOpcionPermisos;

SET FOREIGN_KEY_CHECKS=1;

SELECT 'Setup de permisos completado correctamente' AS Estado;
SELECT COUNT(*) AS TotalPermisos FROM permisos;
SELECT COUNT(*) AS TotalRoles FROM roles;
