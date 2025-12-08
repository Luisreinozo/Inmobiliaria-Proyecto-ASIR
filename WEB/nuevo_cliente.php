<?php
//root@Web:~# cat /var/www/inmoweb/admin/nuevo_cliente.php 

require __DIR__ . '/config.php';
require __DIR__ . '/ldap_empleado.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre      = trim($_POST['nombre'] ?? '');
    $apellido1   = trim($_POST['apellido1'] ?? '');
    $apellido2   = trim($_POST['apellido2'] ?? '');
    $nif         = trim($_POST['nif'] ?? '');
    $telefono    = trim($_POST['telefono'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $direccion   = trim($_POST['direccion'] ?? '');
    $localidadId = (int)($_POST['localidad_id'] ?? 0);

    try {
        $sql = "INSERT INTO clientes
                   (nombre, apellido1, apellido2, nif, telefono, email,
                    direccion, localidades_id_localidades,
                    usuario_creacion)
                VALUES
                   (:nombre, :apellido1, :apellido2, :nif, :telefono, :email,
                    :direccion, :localidad_id,
                    :usuario_creacion)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nombre'           => $nombre,
            ':apellido1'        => $apellido1,
            ':apellido2'        => $apellido2 !== '' ? $apellido2 : null,
            ':nif'              => $nif,
            ':telefono'         => $telefono,
            ':email'            => $email,
            ':direccion'        => $direccion,
            ':localidad_id'     => $localidadId,
            ':usuario_creacion' => $idEmpleadoActual,
        ]);

        echo 'Cliente creado correctamente';
        exit;
    } catch (PDOException $e) {
        echo 'Error al crear cliente: ' . htmlspecialchars($e->getMessage());
        exit;
    }
}

// Carga de localidades para el select
$localidades = $pdo->query("
    SELECT id_localidades, localidad
    FROM localidades
    ORDER BY localidad
")->fetchAll();
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Nuevo cliente</title>
</head>

<body>

    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="admin-forms.css">

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
                <h1>Nuevo cliente</h1>
            </div>

            <form method="post">


                <div class="form-row">
                    <label class="required">
                        <span>Nombre:</span>
                        <input type="text" name="nombre" required>
                    </label>
                    <label class="required">
                        <span>Primer apellido:</span>
                        <input type="text" name="apellido1" required>
                    </label>
                </div>



                <div class="form-row">
                    <label>
                        <span>Segundo apellido:</span>
                        <input type="text" name="apellido2">
                    </label>
                    <label class="required">
                        <span>NIF:</span>
                        <input type="text" name="nif" required>
                    </label>
                </div>



                <div class="form-row">
                    <label class="required">
                        <span>Teléfono:</span>
                        <input type="text" name="telefono" required>
                    </label>
                    <label class="required">
                        <span>Email:</span>
                        <input type="email" name="email" required>
                    </label>
                </div>



                <label class="required">
                    <span>Dirección:</span>
                    <input type="text" name="direccion" required>
                </label>


                <label class="required">
                    <span>Localidad:<span>
                            <select name="localidad_id" required>
                                <option value="">-- Selecciona una localidad --</option>
                                <?php foreach ($localidades as $loc): ?>
                                    <option value="<?= (int)$loc['id_localidades'] ?>">
                                        <?= htmlspecialchars($loc['localidad']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                </label><br>



                <div class="form-actions">
                    <button type="submit">Guardar cliente</button>
                    <a href="index_admin.php" class="btn-secondary">Cancelar</a>
                </div>

            </form>
        </div>
    </div>
</body>

</html>
