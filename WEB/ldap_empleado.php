<?php
//root@Web:~# cat /var/www/inmoweb/admin/ldap_empleado.php 
//Obtener el sAMAccountName del usuario autenticado por Apache/LDAP
$nombreUsuarioAD = $_SERVER['REMOTE_USER'] ?? null;

if (!$nombreUsuarioAD) {
    //Si la variable no existe o no se usuario LDAP.
    http_response_code(401);
    die('Acceso no autorizado: Identidad LDAP no recibida.');
}

//Buscar el ID interno del empleado usando el sAMAccountName desde la BBDD usando el PDO de config.php
try {
    $stmt = $pdo->prepare("SELECT id_empleado FROM empleados WHERE nombre_usuario_ad = :nombre_usuario");
    $stmt->execute([':nombre_usuario' => $nombreUsuarioAD]);
    $empleado = $stmt->fetch();

    if (!$empleado) {
        http_response_code(403);
        //Si el usuario existe en AD pero no esta en la tabla, deniega el acceso.
        die("Acceso denegado: Usuario '{$nombreUsuarioAD}' existe en dominio pero no en la base de datos.");
    }
    
    // Asignar el ID de empleado real
    $idEmpleadoActual = (int)$empleado['id_empleado'];

} catch (PDOException $e) {
    error_log("Error al buscar ID de empleado: " . $e->getMessage());
    http_response_code(500);
    die('Error interno del servidor al verificar identidad.');
}
?>
