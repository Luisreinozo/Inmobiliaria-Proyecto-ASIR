DROP DATABASE IF EXISTS inmobiliaria;
CREATE DATABASE inmobiliaria
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_spanish_ci;

USE inmobiliaria;

-- =========================================================
-- 1) CCAA
-- =========================================================
DROP TABLE IF EXISTS ccaa;
CREATE TABLE ccaa (
  id_ccaa   SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  comunidad VARCHAR(50)       NOT NULL,    
  PRIMARY KEY (id_ccaa),
  UNIQUE KEY ccaa_comunidad_UN (comunidad)
) 

-- =========================================================
-- 2) Provincias
-- =========================================================
DROP TABLE IF EXISTS provincias;
CREATE TABLE provincias (
  id_provincia SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  provincia    VARCHAR(50)       NOT NULL,
  ccaa_id_ccaa SMALLINT UNSIGNED NOT NULL,
  PRIMARY KEY (id_provincia),
  UNIQUE KEY provincias_provincia_UN (provincia),
  CONSTRAINT provincias_ccaa_FK
    FOREIGN KEY (ccaa_id_ccaa)
    REFERENCES ccaa (id_ccaa)
    ON DELETE RESTRICT ON UPDATE RESTRICT
) 

-- =========================================================
-- 3) Localidades
-- =========================================================
DROP TABLE IF EXISTS localidades;
CREATE TABLE localidades (
  id_localidades          SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  localidad               VARCHAR(50)       NOT NULL,
  provincias_id_provincia SMALLINT UNSIGNED NOT NULL,
  PRIMARY KEY (id_localidades),
  UNIQUE KEY localidades_UN (localidad, provincias_id_provincia),
  CONSTRAINT localidades_provincias_FK
    FOREIGN KEY (provincias_id_provincia)
    REFERENCES provincias (id_provincia)
    ON DELETE RESTRICT ON UPDATE RESTRICT
) 

-- =========================================================
-- 4) Operaciones (venta, alquiler, etc.)
-- =========================================================
DROP TABLE IF EXISTS operaciones;
CREATE TABLE operaciones (
  id_operaciones SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  operacion      VARCHAR(20)       NOT NULL,
  PRIMARY KEY (id_operaciones),
  UNIQUE KEY operaciones_operacion_UN (operacion)
) 

-- =========================================================
-- 5) Tipos de inmueble (normalización de inmuebles.tipo)
-- =========================================================
DROP TABLE IF EXISTS tipos_inmueble;
CREATE TABLE tipos_inmueble (
  id_tipo SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tipo    VARCHAR(50)       NOT NULL,
  PRIMARY KEY (id_tipo),
  UNIQUE KEY tipos_inmueble_tipo_UN (tipo)
) 

-- =========================================================
-- 6) Empleados (negocio, sin tablas de AD)
-- =========================================================
DROP TABLE IF EXISTS empleados;
CREATE TABLE empleados (
  id_empleado SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre      VARCHAR(50)       NOT NULL,
  apellido1   VARCHAR(50)       NOT NULL,
  apellido2   VARCHAR(50)       NULL,
  nif         VARCHAR(15)       NOT NULL,
  telefono    VARCHAR(20)       NOT NULL,
  email       VARCHAR(190)      NOT NULL,
  cargo       VARCHAR(50)       NULL,
  fecha_creacion     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_modificacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_empleado),
  UNIQUE KEY empleados_nif_UN    (nif),
  UNIQUE KEY empleados_tel_UN    (telefono),
  UNIQUE KEY empleados_email_UN  (email)
) 

-- =========================================================
-- 7) Clientes
-- =========================================================
DROP TABLE IF EXISTS clientes;
CREATE TABLE clientes (
  id_cliente                 INT UNSIGNED      NOT NULL AUTO_INCREMENT,
  nombre                     VARCHAR(50)       NOT NULL,
  apellido1                  VARCHAR(50)       NOT NULL,
  apellido2                  VARCHAR(50)       NULL,
  nif                        VARCHAR(15)       NOT NULL,
  telefono                   VARCHAR(20)       NOT NULL,
  email                      VARCHAR(190)      NOT NULL,
  direccion                  VARCHAR(100)      NOT NULL,
  localidades_id_localidades SMALLINT UNSIGNED NOT NULL,
  fecha_alta                 DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_creacion             DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_modificacion         DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  usuario_creacion           SMALLINT UNSIGNED NOT NULL,
  usuario_modificacion       SMALLINT UNSIGNED NULL,
  PRIMARY KEY (id_cliente),
  UNIQUE KEY clientes_nif_UN    (nif),
  UNIQUE KEY clientes_tel_UN    (telefono),
  UNIQUE KEY clientes_email_UN  (email),
  CONSTRAINT clientes_localidades_FK
    FOREIGN KEY (localidades_id_localidades)
    REFERENCES localidades (id_localidades)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT clientes_empleados_creacion_FK
    FOREIGN KEY (usuario_creacion)
    REFERENCES empleados (id_empleado)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT clientes_empleados_modificacion_FK
    FOREIGN KEY (usuario_modificacion)
    REFERENCES empleados (id_empleado)
    ON DELETE SET NULL ON UPDATE RESTRICT
) 

-- =========================================================
-- 8) Inmuebles
-- =========================================================
DROP TABLE IF EXISTS inmuebles;
CREATE TABLE inmuebles (
  id_inmueble                INT UNSIGNED      NOT NULL AUTO_INCREMENT,
  titulo                     VARCHAR(100)      NOT NULL,
  descripcion                TEXT              NULL,
  zona                       VARCHAR(50)       NOT NULL,
  id_tipo                    SMALLINT UNSIGNED NOT NULL,
  metros                     DECIMAL(8,2)      NOT NULL,
  pvp                        DECIMAL(12,2)     NOT NULL,
  dormitorios                TINYINT UNSIGNED  NOT NULL DEFAULT 0,
  banos                      TINYINT UNSIGNED  NOT NULL,
  garaje                     TINYINT(1)        NULL,
  ascensor                   TINYINT(1)        NULL,
  trastero                   TINYINT(1)        NULL,
  publicado                  TINYINT(1)        NOT NULL DEFAULT 0
  clientes_id_propietario    INT UNSIGNED      NOT NULL,
  localidades_id_localidades SMALLINT UNSIGNED NOT NULL,
  operaciones_id_operaciones SMALLINT UNSIGNED NOT NULL,
  fecha_creacion             DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_modificacion         DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  usuario_creacion           SMALLINT UNSIGNED NOT NULL,
  usuario_modificacion       SMALLINT UNSIGNED NULL,
  PRIMARY KEY (id_inmueble),
  CONSTRAINT inmuebles_tipos_inmueble_FK
    FOREIGN KEY (id_tipo)
    REFERENCES tipos_inmueble (id_tipo)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT inmuebles_localidades_FK
    FOREIGN KEY (localidades_id_localidades)
    REFERENCES localidades (id_localidades)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT inmuebles_operaciones_FK
    FOREIGN KEY (operaciones_id_operaciones)
    REFERENCES operaciones (id_operaciones)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT inmuebles_clientes_propietario_FK
    FOREIGN KEY (clientes_id_propietario)
    REFERENCES clientes (id_cliente)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT inmuebles_empleados_creacion_FK
    FOREIGN KEY (usuario_creacion)
    REFERENCES empleados (id_empleado)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT inmuebles_empleados_modificacion_FK
    FOREIGN KEY (usuario_modificacion)
    REFERENCES empleados (id_empleado)
    ON DELETE SET NULL ON UPDATE RESTRICT
) 

-- =========================================================
-- 9) Ventas
-- =========================================================
DROP TABLE IF EXISTS ventas;
CREATE TABLE ventas (
  id_venta              SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  fecha_venta           DATE              NOT NULL,
  clientes_id_comprador INT UNSIGNED      NOT NULL,
  empleados_id_empleado SMALLINT UNSIGNED NOT NULL,
  precio_total          DECIMAL(12,2)     NOT NULL,
  fecha_creacion        DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_modificacion    DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  usuario_creacion      SMALLINT UNSIGNED NOT NULL,
  usuario_modificacion  SMALLINT UNSIGNED NULL,
  PRIMARY KEY (id_venta),
  CONSTRAINT ventas_clientes_comprador_FK
    FOREIGN KEY (clientes_id_comprador)
    REFERENCES clientes (id_cliente)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT ventas_empleados_FK
    FOREIGN KEY (empleados_id_empleado)
    REFERENCES empleados (id_empleado)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT ventas_empleados_creacion_FK
    FOREIGN KEY (usuario_creacion)
    REFERENCES empleados (id_empleado)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT ventas_empleados_modificacion_FK
    FOREIGN KEY (usuario_modificacion)
    REFERENCES empleados (id_empleado)
    ON DELETE SET NULL ON UPDATE RESTRICT
) 

-- =========================================================
-- 10) Detalle_venta 
-- =========================================================
DROP TABLE IF EXISTS detalle_venta;
CREATE TABLE detalle_venta (
  ventas_id_venta       SMALLINT UNSIGNED NOT NULL,
  inmuebles_id_inmueble INT UNSIGNED      NOT NULL,
  pvp_individual        DECIMAL(12,2)     NOT NULL,
  PRIMARY KEY (ventas_id_venta, inmuebles_id_inmueble),
  CONSTRAINT detalle_venta_ventas_FK
    FOREIGN KEY (ventas_id_venta)
    REFERENCES ventas (id_venta)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT detalle_venta_inmuebles_FK
    FOREIGN KEY (inmuebles_id_inmueble)
    REFERENCES inmuebles (id_inmueble)
    ON DELETE RESTRICT ON UPDATE RESTRICT
) 

-- =========================================================
-- 11) Fotos de inmuebles
-- =========================================================
DROP TABLE IF EXISTS fotos;
CREATE TABLE fotos (
  id_fotos              SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  inmuebles_id_inmueble INT UNSIGNED      NOT NULL,
  ruta                  VARCHAR(512)      NOT NULL,
  orden                 INT UNSIGNED      NOT NULL DEFAULT 1,
  fecha_creacion        DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_modificacion    DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP  ON UPDATE CURRENT_TIMESTAMP,
  usuario_creacion      SMALLINT UNSIGNED NOT NULL,
  usuario_modificacion  SMALLINT UNSIGNED NULL,
  PRIMARY KEY (id_fotos),
  UNIQUE KEY fotos_inmueble_ruta_UN (inmuebles_id_inmueble, ruta),
  CONSTRAINT fotos_inmuebles_FK
    FOREIGN KEY (inmuebles_id_inmueble)
    REFERENCES inmuebles (id_inmueble)
    ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT fotos_empleados_creacion_FK
    FOREIGN KEY (usuario_creacion)
    REFERENCES empleados (id_empleado)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT fotos_empleados_modificacion_FK
    FOREIGN KEY (usuario_modificacion)
    REFERENCES empleados (id_empleado)
    ON DELETE SET NULL ON UPDATE RESTRICT
) 

-- =========================================================
-- 12) Tareas para integración con Nextcloud
-- =========================================================
DROP TABLE IF EXISTS tareas_nextcloud;
CREATE TABLE tareas_nextcloud (
  id_tarea        INT UNSIGNED      NOT NULL AUTO_INCREMENT,
  id_inmueble     INT UNSIGNED      NOT NULL,
  accion          ENUM('CREAR_CARPETA','BORRAR_CARPETA') NOT NULL,
  estado          ENUM('PENDIENTE','OK','ERROR') NOT NULL DEFAULT 'PENDIENTE',
  detalle_error   VARCHAR(255)      NULL,
  fecha_creacion  DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_ejecucion DATETIME          NULL,
  PRIMARY KEY (id_tarea),
  CONSTRAINT tareas_nextcloud_inmuebles_FK
    FOREIGN KEY (id_inmueble)
    REFERENCES inmuebles (id_inmueble)
    ON DELETE CASCADE ON UPDATE RESTRICT
) 

