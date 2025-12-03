#!/usr/bin/php
<?php

// Ejecuta tareas_nextcloud: crea/borra carpetas en GroupFolder "Inmuebles"

// Config BBDD (MariaDB en LAN)
$dbHost = '10.0.2.22';
$dbName = 'inmobiliaria';
$dbUser = 'nc_worker';
$dbPass = 'Worker1234.';

// Nextcloud
$ncBaseUrl   = 'http://nextcloud.inmobiliaria.local';

// login qxon usuario jefe
$ncUserLogin = 'Ramon';

// user_id interno de occ
$ncUserId    = '6E089838-1326-4B40-9998-8E8230BC256A';

// contraseña de aplicación Webdav
$ncPassword  = '84odR-swpgp-zniXK-AE6Mm-z28xB';

// Conexión PDO
try {
    $pdo = new PDO(
        "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    fwrite(STDERR, "Error DB: " . $e->getMessage() . PHP_EOL);
    exit(1);
}

// Seleccionar tareas pendientes
$sql = "SELECT id_tarea, id_inmueble, accion
        FROM tareas_nextcloud
        WHERE estado = 'PENDIENTE'
        ORDER BY fecha_creacion
        LIMIT 20";

$tareas = $pdo->query($sql)->fetchAll();

if (!$tareas) {
    exit(0);
}

foreach ($tareas as $tarea) {
    $idTarea    = (int)$tarea['id_tarea'];
    $idInmueble = (int)$tarea['id_inmueble'];
    $accion     = $tarea['accion'];

    $detalleError = '';

    if ($accion === 'CREAR_CARPETA') {
        $ok = crearCarpetaInmueble($ncBaseUrl, $ncUserLogin, $ncUserId, $ncPassword, $idInmueble, $detalleError);
    } elseif ($accion === 'BORRAR_CARPETA') {
        $ok = borrarCarpetaInmueble($ncBaseUrl, $ncUserLogin, $ncUserId, $ncPassword, $idInmueble, $detalleError);
    } else {
        $ok = false;
        $detalleError = 'Acción no soportada: ' . $accion;
    }

    $sqlUpdate = "UPDATE tareas_nextcloud
                  SET estado = :estado,
                      detalle_error = :detalle_error,
                      fecha_ejecucion = NOW()
                  WHERE id_tarea = :id_tarea";

    $stmt = $pdo->prepare($sqlUpdate);
    $stmt->execute([
        ':estado'        => $ok ? 'OK' : 'ERROR',
        ':detalle_error' => $detalleError !== '' ? $detalleError : null,
        ':id_tarea'      => $idTarea,
    ]);
}

/**
 * Crea carpeta Inmuebles/{id_inmueble}/ vía WebDAV
 */
function crearCarpetaInmueble(string $baseUrl, string $login, string $userId, string $pass, int $idInmueble, string &$detalleError): bool
{
    $detalleError = '';
  
    $path = "/remote.php/dav/files/{$userId}/Inmuebles/{$idInmueble}/";
    $url  = rtrim($baseUrl, '/') . $path;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'MKCOL');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
  // En la autenticación usamos el login LDAP
    curl_setopt($ch, CURLOPT_USERPWD, $login . ':' . $pass);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_HEADER, true);

    $response = curl_exec($ch);
    if ($response === false) {
        $detalleError = 'cURL error: ' . curl_error($ch);
        curl_close($ch);
        return false;
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 201 || $httpCode === 405) {
        return true;
    }

    $detalleError = 'HTTP code ' . $httpCode . ' al crear carpeta';
    return false;
}

/**
 * Borra carpeta Inmuebles/{id_inmueble}/ vía WebDAV
 */
function borrarCarpetaInmueble(string $baseUrl, string $login, string $userId, string $pass, int $idInmueble, string &$detalleError): bool
{
    $detalleError = '';

    $path = "/remote.php/dav/files/{$userId}/Inmuebles/{$idInmueble}/";
    $url  = rtrim($baseUrl, '/') . $path;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $login . ':' . $pass);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_HEADER, true);

    $response = curl_exec($ch);
    if ($response === false) {
        $detalleError = 'cURL error: ' . curl_error($ch);
        curl_close($ch);
        return false;
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 204 || $httpCode === 404) {
        return true;
    }

    $detalleError = 'HTTP code ' . $httpCode . ' al borrar carpeta';
    return false;
}
