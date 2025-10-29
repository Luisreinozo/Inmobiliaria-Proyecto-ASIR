INSERT INTO ad_recursos (codigo, recurso) VALUES
  ('INMUEBLES','Inmuebles'),
  ('CLIENTES','Clientes'),
  ('PROPIETARIOS','Propietarios'),
  ('EMPLEADOS','Empleados')
ON DUPLICATE KEY UPDATE recurso=VALUES(recurso);

INSERT INTO grupos (grupo, ruta) VALUES
  ('comerciales','CN=comerciales,OU=Grupos,DC=inmobiliaria,DC=local'),
  ('administrativos','CN=administrativos,OU=Grupos,DC=inmobiliaria,DC=local')
ON DUPLICATE KEY UPDATE ruta=VALUES(ruta);

-- permisos: comerciales (CRUD en inmuebles/clientes/propietarios; lectura en empleados)
REPLACE INTO ad_permisos (ad_recursos_codigo, grupos_id_grupo, crear, leer, actualizar, borrar)
SELECT r.codigo, g.id_grupo,
       (r.codigo IN ('INMUEBLES','CLIENTES','PROPIETARIOS')) AS crear,
       1 AS leer,
       (r.codigo IN ('INMUEBLES','CLIENTES','PROPIETARIOS')) AS actualizar,
       (r.codigo IN ('INMUEBLES','CLIENTES','PROPIETARIOS')) AS borrar
FROM ad_recursos r CROSS JOIN grupos g
WHERE g.grupo='comerciales';

-- permisos: administrativos = comerciales + CRUD empleados
REPLACE INTO ad_permisos (ad_recursos_codigo, grupos_id_grupo, crear, leer, actualizar, borrar)
SELECT r.codigo, g.id_grupo,
       (r.codigo IN ('INMUEBLES','CLIENTES','PROPIETARIOS','EMPLEADOS')) AS crear,
       1 AS leer,
       (r.codigo IN ('INMUEBLES','CLIENTES','PROPIETARIOS','EMPLEADOS')) AS actualizar,
       (r.codigo IN ('INMUEBLES','CLIENTES','PROPIETARIOS','EMPLEADOS')) AS borrar
FROM ad_recursos r CROSS JOIN grupos g
WHERE g.grupo='administrativos';
