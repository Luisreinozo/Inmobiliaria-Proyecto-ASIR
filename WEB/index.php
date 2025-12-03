<?php
require __DIR__ . '/config.php';

// Sólo inmuebles publicados
$sql = "
    SELECT
        i.id_inmueble,
        i.titulo,
        i.zona,
        i.metros,
        i.pvp,
        t.tipo,
        o.operacion
    FROM inmuebles i
    JOIN tipos_inmueble t ON i.id_tipo = t.id_tipo
    JOIN operaciones o ON i.operaciones_id_operaciones = o.id_operaciones
    WHERE i.publicado = 1
    ORDER BY i.fecha_creacion DESC
";

$inmuebles = $pdo->query($sql)->fetchAll();
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inmobiliaria Centro - Inmuebles</title>
</head>
<body>
<h1>Inmuebles disponibles</h1>

<?php if (!$inmuebles): ?>
    <p>No hay inmuebles publicados.</p>
<?php else: ?>
    <ul>
        <?php foreach ($inmuebles as $inm): ?>
            <li>
                <strong><?= htmlspecialchars($inm['titulo']) ?></strong>
                (<?= htmlspecialchars($inm['tipo']) ?>, <?= htmlspecialchars($inm['operacion']) ?>) -
                Zona: <?= htmlspecialchars($inm['zona']) ?> -
                <?= number_format($inm['pvp'], 2, ',', '.') ?> €
                [<a href="inmueble.php?id=<?= (int)$inm['id_inmueble'] ?>">Ver ficha</a>]
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

</body>
</html>
