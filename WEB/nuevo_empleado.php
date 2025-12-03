<?php

require __DIR__ . '/config.php'; 
require __DIR__ . '/ldap_empleado.php';

// Inicializar cargos
$cargos_opciones = ['Administrativo', 'Comercial'];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre           = trim($_POST['nombre'] ?? '');
    $apellido1        = trim($_POST['apellido1'] ?? '');
    $apellido2        = trim($_POST['apellido2'] ?? '');
    $nif              = trim($_POST['nif'] ?? '');
    $telefono         = trim($_POST['telefono'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $cargo            = trim($_POST['cargo'] ?? ''); 
    $nombreUsuarioAD  = trim($_POST['nombre_usuario_ad'] ?? ''); 

    if (empty($nombre) || empty($apellido1) || empty($nif) || empty($telefono) || empty($email) || empty($cargo) || empty($nombreUsuarioAD)) {
        die('Error: Todos los campos requeridos deben ser completados.');
    }

    try {
        $sql = "INSERT INTO empleados
                  (nombre, apellido1, apellido2, nif, telefono, email,
                   cargo, nombre_usuario_ad)
                VALUES
                  (:nombre, :apellido1, :apellido2, :nif, :telefono, :email,
                   :cargo, :nombre_usuario_ad)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nombre'           => $nombre,
            ':apellido1'        => $apellido1,
            ':apellido2'        => $apellido2 ?: null,
            ':nif'              => $nif,
            ':telefono'         => $telefono,
            ':email'            => $email,
            ':cargo'            => $cargo,
            ':nombre_usuario_ad' => $nombreUsuarioAD,
        ]);

        echo '✅ Empleado ' . htmlspecialchars($nombre) . ' creado y mapeado con exito.';
        exit;

    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            echo 'Error: Ya existe un empleado con el NIF, Telefono, Email o nombre de usuario de AD proporcionado.';
        } else {
            error_log("Error al crear empleado: " . $e->getMessage());
            echo 'Error interno al registrar el empleado.';
        }
        exit;
    }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Nuevo Empleado</title></head>
<body>
<h1>Nuevo Empleado</h1>

<form method="post">
  <label>Nombre: <input type="text" name="nombre" required></label><br>
  <label>Primer apellido: <input type="text" name="apellido1" required></label><br>
  <label>Segundo apellido: <input type="text" name="apellido2"></label><br>
  <label>NIF: <input type="text" name="nif" required></label><br>
  <label>Teléfono: <input type="text" name="telefono" required></label><br>
  <label>Email: <input type="email" name="email" required></label><br>
  
  <label>Cargo:
    <select name="cargo" required>
      <option value="">-- Selecciona --</option>
      <?php foreach ($cargos_opciones as $c): ?>
          <option value="<?= htmlspecialchars($c) ?>">
            <?= htmlspecialchars($c) ?>
          </option>
      <?php endforeach; ?>
    </select>
  </label><br>
  
  <hr>
  <label>**Usuario AD (sAMAccountName):** <input type="text" name="nombre_usuario_ad" required></label><br>
  <hr>
  
  <button type="submit">Guardar Empleado</button>
</form>
</body>
</html>
