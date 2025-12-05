#!/usr/bin/php
<?php

//Este escript se ejecuta desde nextcloud, lo dejo en este repositorio porque es parte de la web

$dbHost = '10.0.2.22';
$dbName = 'inmobiliaria';
$dbUser = 'webapp';
$dbPass = 'Webapp1234.';

// Nextcloud WebDAV
$ncBaseUrl   = 'http://nextcloud.inmobiliaria.local';
$ncUserLogin = 'Ramon';
$ncUserId    = '6E089838-1326-4B40-9998-8E8230BC256A';
$ncPassword  = '84odR-swpgp-zniXK-AE6Mm-z28xB';

// Ruta base local donde guardar fotos
$mediaBaseDir = '/var/www/inmoweb/media/inmuebles';

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

//Obtener inmuebles publicados
$sqlInm = "
    SELECT id_inmueble
    FROM inmuebles
    WHERE publicado = 1
";
$inmuebles = $pdo->query($sqlInm)->fetchAll();

if (!$inmuebles) {
    exit(0);
}

foreach ($inmuebles as $row) {
    $idInmueble = (int)$row['id_inmueble'];
    syncFotosDeInmueble($pdo, $idInmueble, $ncBaseUrl, $ncUserLogin, $ncUserId, $ncPassword, $mediaBaseDir);
}

//Sincroniza las fotos de un inmueble:

function syncFotosDeInmueble(PDO $pdo, int $idInmueble, string $baseUrl, string $login, string $userId, string $pass, string $mediaBaseDir): void
{
    $remotePath = "/remote.php/dav/files/{$userId}/Inmuebles/{$idInmueble}/";
    $listUrl    = rtrim($baseUrl, '/') . $remotePath;

    // Hacemos PROPFIND para listar ficheros
    $xml = webdavPropfind($listUrl, $login, $pass);

    if ($xml === null) {
        fwrite(STDERR, "No se pudo listar fotos para inmueble {$idInmueble}\n");
        return;
    }

    // Parsear respuestas DAV y extraer nombres de archivo
    $files = parseDavFileList($xml, $remotePath);

    // Carpeta local
    $localDir = rtrim($mediaBaseDir, '/') . '/' . $idInmueble;
    if (!is_dir($localDir) && !mkdir($localDir, 0755, true) && !is_dir($localDir)) {
        fwrite(STDERR, "No se pudo crear dir local {$localDir}\n");
        return;
    }

    // Limpiamos registros previos de fotos de ese inmueble
    $stmtDel = $pdo->prepare("DELETE FROM fotos WHERE inmuebles_id_inmueble = :id");
    $stmtDel->execute([':id' => $idInmueble]);

    $orden = 1;

    foreach ($files as $fileName) {
        // Ignorar directorios y cosas raras
        if ($fileName === '' || $fileName === '.' || $fileName === '..') {
            continue;
        }

        // Extensiones simples
        if (!preg_match('/\.(jpe?g|png|gif|webp)$/i', $fileName)) {
            continue;
        }

        $remoteFileUrl = $listUrl . rawurlencode($fileName);
        $localFilePath = $localDir . '/' . $fileName;

        if (!descargarArchivo($remoteFileUrl, $login, $pass, $localFilePath)) {
            fwrite(STDERR, "Error descargando {$remoteFileUrl}\n");
            continue;
        }

        // Ruta relativa que usará la web
        $rutaRelativa = "media/inmuebles/{$idInmueble}/{$fileName}";

        $sqlIns = "
            INSERT INTO fotos (inmuebles_id_inmueble, ruta, orden, usuario_creacion)
            VALUES (:id_inmueble, :ruta, :orden, :usuario_creacion)
        ";
        $stmtIns = $pdo->prepare($sqlIns);
        // usuario_creacion lo dejamos en 1 (sistema) como placeholder?
        $stmtIns->execute([
            ':id_inmueble'      => $idInmueble,
            ':ruta'             => $rutaRelativa,
            ':orden'            => $orden++,
            ':usuario_creacion' => 1,
        ]);
    }
}

function webdavPropfind(string $url, string $login, string $pass): ?string
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PROPFIND');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $login . ':' . $pass);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Depth: 1',
    ]);

    $response = curl_exec($ch);
    if ($response === false) {
        curl_close($ch);
        return null;
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 207 && $httpCode !== 207) {
        return null;
    }

    return $response;
}

// Extrae nombres de archivo de la respuesta PROPFIND
 
function parseDavFileList(string $xml, string $remotePath): array
{
    $files = [];

    $dom = new DOMDocument();
    if (!@$dom->loadXML($xml)) {
        return $files;
    }

    $xpath = new DOMXPath($dom);
    $xpath->registerNamespace('d', 'DAV:');

    foreach ($xpath->query('//d:response/d:href') as $hrefNode) {
        $href = $hrefNode->textContent;

        // Ejemplo de href: /remote.php/dav/files/USERID/Inmuebles/1/archivo.jpg
        $decoded = urldecode($href);

        // Nos quedamos solo con la parte después de la carpeta del inmueble
        $pos = strpos($decoded, $remotePath);
        if ($pos === false) {
            continue;
        }

        $relative = substr($decoded, $pos + strlen($remotePath));

        // relative vacío -> es la carpeta en sí, la ignoramos
        if ($relative === '') {
            continue;
        }

        // Si contiene '/', es un subdirectorio -> de momento ignoramos subdirs
        if (strpos($relative, '/') !== false) {
            continue;
        }

        $files[] = $relative;
    }

    return $files;
}

function descargarArchivo(string $url, string $login, string $pass, string $localPath): bool
{
    $fp = fopen($localPath, 'w');
    if ($fp === false) {
        return false;
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_USERPWD, $login . ':' . $pass);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $ok = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);
    fclose($fp);

    if ($ok === false || $httpCode >= 400) {
        @unlink($localPath);
        return false;
    }

    return true;
}
