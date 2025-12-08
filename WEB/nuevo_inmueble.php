<?php
//root@Web:~# cat /var/www/inmoweb/admin/nuevo_inmueble.php 

require __DIR__ . '/config.php';
require __DIR__ . '/ldap_empleado.php';


// Cargar datos para los desplegables
$tipos = $pdo->query("SELECT id_tipo, tipo FROM tipos_inmueble ORDER BY tipo")->fetchAll();
$operaciones = $pdo->query("SELECT id_operaciones, operacion FROM operaciones ORDER BY operacion")->fetchAll();
$localidades = $pdo->query("SELECT id_localidades, localidad FROM localidades ORDER BY localidad")->fetchAll();
$clientes = $pdo->query("SELECT id_cliente, nombre, apellido1 FROM clientes ORDER BY nombre, apellido1")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger datos del formulario
    $titulo        = trim($_POST['titulo'] ?? '');
    $descripcion   = trim($_POST['descripcion'] ?? '');
    $zona          = trim($_POST['zona'] ?? '');
    $idTipo        = (int)($_POST['id_tipo'] ?? 0);
    $metros        = (float)($_POST['metros'] ?? 0);
    $pvp           = (float)($_POST['pvp'] ?? 0);
    $dormitorios   = (int)($_POST['dormitorios'] ?? 0);
    $banos         = (int)($_POST['banos'] ?? 0);
    $garaje        = isset($_POST['garaje']) ? 1 : 0;
    $ascensor      = isset($_POST['ascensor']) ? 1 : 0;
    $trastero      = isset($_POST['trastero']) ? 1 : 0;
    $publicado     = isset($_POST['publicado']) ? 1 : 0;
    $idPropietario = (int)($_POST['id_propietario'] ?? 0);
    $idLocalidad   = (int)($_POST['id_localidad'] ?? 0);
    $idOperacion   = (int)($_POST['id_operacion'] ?? 0);

    try {
        $pdo->beginTransaction();

        // Insertar inmueble
        $sqlInmueble = "
            INSERT INTO inmuebles
              (titulo, descripcion, zona, id_tipo, metros, pvp,
               dormitorios, banos, garaje, ascensor, trastero,
               publicado,
               clientes_id_propietario,
               localidades_id_localidades,
               operaciones_id_operaciones,
               usuario_creacion)
            VALUES
              (:titulo, :descripcion, :zona, :id_tipo, :metros, :pvp,
               :dormitorios, :banos, :garaje, :ascensor, :trastero,
               :publicado,
               :id_propietario,
               :id_localidad,
               :id_operacion,
               :usuario_creacion)
        ";

        $stmt = $pdo->prepare($sqlInmueble);
        $stmt->execute([
            ':titulo'           => $titulo,
            ':descripcion'      => $descripcion !== '' ? $descripcion : null,
            ':zona'             => $zona,
            ':id_tipo'          => $idTipo,
            ':metros'           => $metros,
            ':pvp'              => $pvp,
            ':dormitorios'      => $dormitorios,
            ':banos'            => $banos,
            ':garaje'           => $garaje ? 1 : 0,
            ':ascensor'         => $ascensor ? 1 : 0,
            ':trastero'         => $trastero ? 1 : 0,
            ':publicado'        => $publicado ? 1 : 0,
            ':id_propietario'   => $idPropietario,
            ':id_localidad'     => $idLocalidad,
            ':id_operacion'     => $idOperacion,
            ':usuario_creacion' => $idEmpleadoActual,
        ]);

        $idInmueble = (int)$pdo->lastInsertId();

        // Insertar tarea para Nextcloud (crear carpeta Inmuebles/{id_inmueble}/)
        $sqlTarea = "
            INSERT INTO tareas_nextcloud (id_inmueble, accion)
            VALUES (:id_inmueble, 'CREAR_CARPETA')
        ";
        $stmtTarea = $pdo->prepare($sqlTarea);
        $stmtTarea->execute([
            ':id_inmueble' => $idInmueble,
        ]);

        $pdo->commit();

        echo "Inmueble creado con ID {$idInmueble} y tarea Nextcloud registrada.";
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error al crear el inmueble: " . htmlspecialchars($e->getMessage());
        exit;
    }
}
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Nuevo inmueble</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="admin-forms.css">
</head>

<body>

    <nav>
        <ul>
            <li><a href="index_admin.php" class="active">Inmuebles</a></li>
            <li><a href="nuevo_cliente.php">Nuevo Cliente</a></li>
            <li><a href="nuevo_inmueble.php">Nuevo Inmueble</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="form-container">
            <div class="form-header">
                <h1>Nuevo inmueble</h1>
            </div>

            <form method="post">

                <div class="form-row">
                    <label class="required">
                        <span>Título:</span>
                        <input type="text" name="titulo" required>
                    </label>
                    <label class="required">
                        <span>Zona:</span>
                        <input type="text" name="zona" required>
                    </label>
                </div>

                <label>
                    <span>Descripción:</span>
                    <textarea name="descripcion" rows="4"></textarea>
                </label>

                <div class="form-row">
                    <label class="required">
                        <span>Tipo de inmueble:</span>
                        <select name="id_tipo" required>
                            <option value="">-- Selecciona --</option>
                            <?php foreach ($tipos as $t): ?>
                                <option value="<?= (int)$t['id_tipo'] ?>"><?= htmlspecialchars($t['tipo']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="required">
                        <span>Operación:</span>
                        <select name="id_operacion" required>
                            <option value="">-- Selecciona --</option>
                            <?php foreach ($operaciones as $op): ?>
                                <option value="<?= (int)$op['id_operaciones'] ?>">
                                    <?= htmlspecialchars($op['operacion']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>

                <div class="form-row">
                    <label class="required">
                        <span>Metros cuadrados:</span>
                        <input type="number" step="0.01" name="metros" required>
                    </label>
                    <label class="required">
                        <span>Precio PVP:</span>
                        <input type="number" step="0.01" name="pvp" required>
                    </label>
                </div>

                <div class="form-row">
                    <label class="required">
                        <span>Dormitorios:</span>
                        <input type="number" name="dormitorios" min="0" value="0" required>
                    </label>
                    <label class="required">
                        <span>Baños:</span>
                        <input type="number" name="banos" min="1" required>
                    </label>
                </div>

                <div class="form-row">
                    <label class="required">
                        <span>Propietario:</span>
                        <select name="id_propietario" required>
                            <option value="">-- Selecciona --</option>
                            <?php foreach ($clientes as $c): ?>
                                <option value="<?= (int)$c['id_cliente'] ?>">
                                    <?= htmlspecialchars($c['nombre'] . ' ' . $c['apellido1']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="required">
                        <span>Localidad:</span>
                        <select name="id_localidad" required>
                            <option value="">-- Selecciona --</option>
                            <?php foreach ($localidades as $loc): ?>
                                <option value="<?= (int)$loc['id_localidades'] ?>">
                                    <?= htmlspecialchars($loc['localidad']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Características:</label>
                        <div>
                            <label class="checkbox-inline"><input type="checkbox" name="garaje"> Garaje</label>
                            <label class="checkbox-inline"><input type="checkbox" name="ascensor"> Ascensor</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Opciones:</label>
                        <div>
                            <label class="checkbox-inline"><input type="checkbox" name="trastero"> Trastero</label>
                            <label class="checkbox-inline"><input type="checkbox" name="publicado" checked> Publicado en web</label>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit">Guardar inmueble</button>
                    <a href="index.php" class="btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>
