<?php
require __DIR__ . '/config.php';

$idEmpleadoActual = $_SESSION['id_empleado']

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre      = trim($_POST['nombre'] ?? '');
    $apellido1   = trim($_POST['apellido1'] ?? '');
    $apellido2   = trim($_POST['apellido2'] ?? '');
    $nif         = trim($_POST['nif'] ?? '');
    $telefono    = trim($_POST['telefono'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $direccion   = trim($_POST['direccion'] ?? '');
    $localidadId = (int)($_POST['localidad_id'] ?? 0);

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
        ':apellido2'        => $apellido2 ?: null,
        ':nif'              => $nif,
        ':telefono'         => $telefono,
        ':email'            => $email,
        ':direccion'        => $direccion,
        ':localidad_id'     => $localidadId,
        ':usuario_creacion' => $idEmpleadoActual,
    ]);

    echo 'Cliente creado';
    exit;
}

// Carga de localidades para el select
$localidades = $pdo->query("SELECT id_localidades, localidad FROM localidades ORDER BY localidad")->fetchAll();
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Nuevo cliente</title></head>
<body>
<h1>Nuevo cliente</h1>
<form method="post">
  <label>Nombre: <input type="text" name="nombre" required></label><br>
  <label>Primer apellido: <input type="text" name="apellido1" required></label><br>
  <label>Segundo apellido: <input type="text" name="apellido2"></label><br>
  <label>NIF: <input type="text" name="nif" required></label><br>
  <label>Teléfono: <input type="text" name="telefono" required></label><br>
  <label>Email: <input type="email" name="email" required></label><br>
  <label>Dirección: <input type="text" name="direccion" required></label><br>
  <label>Localidad:
    <select name="localidad_id" required>
      <option value="">-- Selecciona --</option>
      <?php foreach ($localidades as $loc): ?>
        <option value="<?= htmlspecialchars($loc['id_localidades']) ?>">
          <?= htmlspecialchars($loc['localidad']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </label><br>
  <button type="submit">Guardar</button>
</form>
</body>
</html>
