<?php
require __DIR__ . '/config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo 'ID de inmueble no válido';
    exit;
}

// Datos del inmueble
$sql = "
    SELECT
        i.id_inmueble,
        i.titulo,
        i.descripcion,
        i.zona,
        i.metros,
        i.pvp,
        i.dormitorios,
        i.banos,
        i.garaje,
        i.ascensor,
        i.trastero,
        i.publicado,
        i.fecha_creacion,
        t.tipo            AS tipo_inmueble,
        o.operacion,
        l.localidad,
        p.provincia,
        c.nombre          AS propietario_nombre,
        c.apellido1       AS propietario_apellido1
    FROM inmuebles i
    JOIN tipos_inmueble t             ON i.id_tipo = t.id_tipo
    JOIN operaciones o                ON i.operaciones_id_operaciones = o.id_operaciones
    JOIN localidades l                ON i.localidades_id_localidades = l.id_localidades
    JOIN provincias p                 ON l.provincias_id_provincia = p.id_provincia
    JOIN clientes c                   ON i.clientes_id_propietario = c.id_cliente
    WHERE i.id_inmueble = :id
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id]);
$inmueble = $stmt->fetch();

if (!$inmueble) {
    http_response_code(404);
    echo 'Inmueble no encontrado';
    exit;
}

// Fotos del inmueble
$sqlFotos = "
    SELECT ruta, orden
    FROM fotos
    WHERE inmuebles_id_inmueble = :id
    ORDER BY orden, id_fotos
";
$stmtFotos = $pdo->prepare($sqlFotos);
$stmtFotos->execute([':id' => $id]);
$fotos = $stmtFotos->fetchAll();
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($inmueble['titulo']) ?> - Ficha inmueble</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>

    <div class="container">

        <h1><?= htmlspecialchars($inmueble['titulo']) ?></h1>

        <p class="inmueble-ref">Referencia: <strong><?= htmlspecialchars($inmueble['id_inmueble']) ?></strong></p>

        <p class="precio">
            <?= number_format($inmueble['pvp'], 2, ',', '.') ?> €
        </p>

        <hr>

        <div class="inmueble-details">
            <h2>Detalles Principales</h2>

            <p><strong>Operación:</strong> <?= htmlspecialchars($inmueble['operacion']) ?></p>
            <p><strong>Tipo:</strong> <?= htmlspecialchars($inmueble['tipo_inmueble']) ?></p>
            <p><strong>Localización:</strong> <?= htmlspecialchars($inmueble['zona']) ?>,
                <?= htmlspecialchars($inmueble['localidad']) ?> (<?= htmlspecialchars($inmueble['provincia']) ?>)
            </p>

            <hr>

            <h2>Características</h2>

            <p><strong>Superficie:</strong> **<?= (int)$inmueble['metros'] ?> m²**</p>
            <p><strong>Dormitorios:</strong> <?= (int)$inmueble['dormitorios'] ?></p>
            <p><strong>Baños:</strong> <?= (int)$inmueble['banos'] ?></p>

            <ul class="features-list">
                <li><?= $inmueble['garaje'] ? '**Garaje**' : 'Garaje: No' ?></li>
                <li><?= $inmueble['ascensor'] ? '**Ascensor**' : 'Ascensor: No' ?></li>
                <li><?= $inmueble['trastero'] ? '**Trastero**' : 'Trastero: No' ?></li>
            </ul>

        </div>

        <?php if (!empty($inmueble['descripcion'])): ?>
            <hr>
            <h2>Descripción</h2>
            <p><?= nl2br(htmlspecialchars($inmueble['descripcion'])) ?></p>
        <?php endif; ?>

        <?php if ($fotos): ?>
            <hr>
            <h2>Galería de Fotos</h2>
            <div class="photos-gallery" style="display: flex; flex-wrap: wrap; gap: 15px;">
                <?php foreach ($fotos as $foto): ?>
                    <?php
                    // En `fotos.ruta` tenemos: media/inmuebles/1/foto1.jpg
                    $src = '/' . ltrim($foto['ruta'], '/');
                    ?>
                    <div class="photo-item" style="border: 1px solid #ccc; padding: 5px; border-radius: 5px;">
                        <img src="<?= htmlspecialchars($src) ?>"
                            alt="Foto inmueble <?= (int)$inmueble['id_inmueble'] ?>"
                            style="max-width: 300px; height: auto; display: block;">
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <hr>
        <p><a href="index.php" class="btn-secondary">Volver al listado</a></p>

    </div>
</body>

</html>
