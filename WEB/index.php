<?php
//root@Web:~# cat /var/www/inmoweb/index.php 
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
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inmobiliaria Centro - Inmuebles</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Barra de menú -->
    <nav>
        <ul>
            <li><a href="index.php" class="active">Inmuebles</a></li>
            <li><a href="https://inmocentro.es/vender-casa/">Vender Casa</a></li>
            <li><a href="https://inmocentro.es/trabajo/">Trabaja con Nosotros</a></li>
            <li><a href="https://inmocentro.es/contacto/">Contacto</a></li>
            <li><a href="admin/index.php" class="admin-link">Login</a></li>
        </ul>
    </nav>

    <!-- Contenido principal -->
    <div class="container">
        <h1>Inmuebles disponibles</h1>
        
        <?php if (!$inmuebles): ?>
            <p>No hay inmuebles publicados.</p>
        <?php else: ?>
            <ul class="inmuebles-list">
                <?php foreach ($inmuebles as $inm): ?>
                    <li>
                        <strong><?= htmlspecialchars($inm['titulo']) ?></strong>
                        <br>
                        <small>
                            <?= htmlspecialchars($inm['tipo']) ?> · 
                            <?= htmlspecialchars($inm['operacion']) ?> · 
                            Zona: <?= htmlspecialchars($inm['zona']) ?> · 
                            <?= number_format($inm['metros'], 0) ?> m²
                        </small>
                        <br>
                        <span class="precio"><?= number_format($inm['pvp'], 2, ',', '.') ?> €</span>
                        <a href="inmueble.php?id=<?= (int)$inm['id_inmueble'] ?>">Ver ficha completa →</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</body>
</html>
