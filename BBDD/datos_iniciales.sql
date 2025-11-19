INSERT INTO operaciones (operacion) VALUES
('VENTA'),
('ALQUILER'),
('TRASPASO');

INSERT INTO tipos_inmueble (tipo) VALUES
('PISO'),
('CHALET'),
('LOCAL'),
('OFICINA'),
('GARAJE');

INSERT INTO ccaa (comunidad) VALUES
('GALICIA');

INSERT INTO provincias (provincia, ccaa_id_ccaa) VALUES
('A CORUÑA', 1),
('LUGO', 1),
('OURENSE', 1),
('PONTEVEDRA', 1);

INSERT INTO localidades (localidad, provincias_id_provincia) VALUES
('A CORUÑA', 1),
('ARTEIXO', 1),
('OURENSE', 3),
('VIGO', 4);
