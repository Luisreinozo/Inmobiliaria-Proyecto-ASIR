#!/usr/bin/php
<?php
// worker_nextcloud.php
// Ejecuta tareas_nextcloud: crea/borrra carpetas en GroupFolder "Inmuebles"

// Config BBDD
$dbHost = '10.0.2.22';
$dbName = 'inmobiliaria';
$dbUser = 'nc_worker';
$dbPass = 'Worker1234.';

// Config Nextcloud WebDAV (usuario que ve el GroupFolder "Inmuebles" en este caso es propiedad del jefe)
$ncBaseUrl  = 'http://nextcloud.inmobiliaria.local';
$ncUser     = 'Ramon';
$ncPassword = 'Z4Yo6-gX3DE-A74CR-8zpwR-s635Z';

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

// Seleccionar tareas pendientes (CREAR_CARPETA / BORRAR_CARPETA)
$sql = "SELECT id_tarea, id_inmueble, accion
        FROM tareas_nextcloud
        WHERE estado = 'PENDIENTE'
        ORDER BY fecha_creacion
        LIMIT 20";

$tareas = $pdo->query($sql)->fetchAll();

if (!$tareas) {
    exit(0); // nada que hacer
}

foreach ($tareas as $tarea) {
    $idTarea    = (int)$tarea['id_tarea'];
    $idInmueble = (int)$tarea['id_inmueble'];
    $accion     = $tarea['accion'];

    // IMPORTANTE: inicializar la variable antes de pasarla por referencia
    $detalleError = '';

    if ($accion === 'CREAR_CARPETA') {
        $ok = crearCarpetaInmueble($ncBaseUrl, $ncUser, $ncPassword, $idInmueble, $detalleError);
    } elseif ($accion === 'BORRAR_CARPETA') {
        $ok = borrarCarpetaInmueble($ncBaseUrl, $ncUser, $ncPassword, $idInmueble, $detalleError);
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
 * Crea carpeta Inmuebles/{id_inmueble}/ vía WebDAV (MKCOL)
 */
function crearCarpetaInmueble(string $baseUrl, string $user, string $pass, int $idInmueble, string &$detalleError): bool
{
    $detalleError = '';

    $path = "/remote.php/dav/files/{$user}/Inmuebles/{$idInmueble}/";
    $url  = rtrim($baseUrl, '/') . $path;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'MKCOL');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $user . ':' . $pass);
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

    // 201 Created → correcto
    // 405 Method Not Allowed → carpeta ya existe (lo tratamos como OK)
    if ($httpCode === 201 || $httpCode === 405) {
        return true;
    }

    $detalleError = 'HTTP code ' . $httpCode . ' al crear carpeta';
    return false;
}

/**
 * Borra carpeta Inmuebles/{id_inmueble}/ vía WebDAV (DELETE)
 */
function borrarCarpetaInmueble(string $baseUrl, string $user, string $pass, int $idInmueble, string &$detalleError): bool
{
    $detalleError = '';

    $path = "/remote.php/dav/files/{$user}/Inmuebles/{$idInmueble}/";
    $url  = rtrim($baseUrl, '/') . $path;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $user . ':' . $pass);
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

    // 204 No Content → borrado correcto
    // 404 Not Found → carpeta no existe; lo consideramos OK suave
    if ($httpCode === 204 || $httpCode === 404) {
        return true;
    }

    $detalleError = 'HTTP code ' . $httpCode . ' al borrar carpeta';
    return false;
}
