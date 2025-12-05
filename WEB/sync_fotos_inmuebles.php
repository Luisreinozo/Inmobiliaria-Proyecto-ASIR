#!/usr/bin/php
<?php
// sync_fotos_inmuebles.php
// Descarga fotos desde Nextcloud a la web y rellena la tabla `fotos`
// Versión segura: lee credenciales desde archivo protegido

// Cargar configuración desde archivo protegido
$configFile = '/var/www/inmoweb/admin/acceso_dmz.conf';

if (!file_exists($configFile)) {
    fwrite(STDERR, "Error: Archivo de configuración no encontrado: $configFile\n");
    exit(1);
}

if (!is_readable($configFile)) {
    fwrite(STDERR, "Error: No se puede leer el archivo de configuración: $configFile\n");
    exit(1);
}

$config = parse_ini_file($configFile);

if (!$config) {
    fwrite(STDERR, "Error: No se pudo parsear la configuración\n");
    exit(1);
}

// Validar que existan todas las variables necesarias
$requiredKeys = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'NC_BASE_URL', 'NC_USER_LOGIN', 'NC_USER_ID', 'NC_PASSWORD'];
foreach ($requiredKeys as $key) {
    if (!isset($config[$key])) {
        fwrite(STDERR, "Error: Falta la variable $key en la configuración\n");
        exit(1);
    }
}

// Asignar variables desde configuración
$dbHost = $config['DB_HOST'];
$dbName = $config['DB_NAME'];
$dbUser = $config['DB_USER'];
$dbPass = $config['DB_PASS'];

$ncBaseUrl   = $config['NC_BASE_URL'];
$ncUserLogin = $config['NC_USER_LOGIN'];
$ncUserId    = $config['NC_USER_ID'];
$ncPassword  = $config['NC_PASSWORD'];

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

// 1) Obtener inmuebles publicados
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

/**
 * Sincroniza las fotos de un inmueble:
 * - lista archivos en Nextcloud: Inmuebles/{id_inmueble}/
 * - descarga a media/inmuebles/{id_inmueble}/
 * - rellena tabla `fotos`
 */
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
        // usuario_creacion lo dejamos en 1 (sistema) como placeholder
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

    if ($httpCode !== 207) {
        return null;
    }

    return $response;
}

/**
 * Extrae nombres de archivo de la respuesta PROPFIND
 */
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
