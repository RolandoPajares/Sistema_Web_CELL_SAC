<?php
$usuario = current_user();
$campanias = active_campaigns();
$campaniaEmergente = $campanias['emergente'] ?? null;
$rolActual = user_role();
$rutaActual = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$estilosContextuales = \App\Soporte\Presentacion\CatalogoEstilos::para(
    $rutaActual,
    'aplicacion',
    $rolActual,
    (string) ($modulo ?? '')
);
$clasesCuerpo = \App\Soporte\Presentacion\CatalogoEstilos::clasesCuerpo(
    $rutaActual,
    $rolActual,
    (string) ($modulo ?? '')
);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($tituloPagina ?? config('app.name')) ?> | <?= e(config('app.name')) ?></title>
    <meta name="description" content="Celulares y audífonos originales en Bagua. Catálogo, stock y atención de MD Technology Digital Cell.">
    <link rel="stylesheet" href="<?= e(asset('assets/css/estilos.css?v=20260927-8')) ?>">
    <?php foreach ($estilosContextuales as $archivoCss): ?>
        <?php
        $versionCss = match ($archivoCss) {
            'assets/css/publico/inicio.css' => '20260928-8',
            'assets/css/estructura/sitio.css' => '20260928-1',
            'assets/css/publico/catalogo.css' => '20260929-3',
            'assets/css/publico/producto.css' => '20260929-2',
            default => '20260927-8',
        };
        ?>
        <link rel="stylesheet" href="<?= e(asset($archivoCss . '?v=' . $versionCss)) ?>">
    <?php endforeach; ?>
    <link rel="stylesheet" href="<?= e(asset('assets/vendor/bootstrap-icons/bootstrap-icons.min.css')) ?>">
</head>
<body class="<?= e($clasesCuerpo) ?>">
<?php require dirname(__DIR__) . '/componentes/encabezados/publico.php'; ?>
<main>
    <?= $contenido ?>
</main>
<?php require dirname(__DIR__) . '/componentes/pies/pie-pagina.php'; ?>
<?php require dirname(__DIR__) . '/componentes/publicidad-dinamica.php'; ?>
<button id="toTop" class="to-top" aria-label="Subir"><i class="bi bi-arrow-up" aria-hidden="true"></i></button>
<script src="<?= e(asset('assets/js/aplicacion.js?v=20260927-5')) ?>"></script>
<?php if (isset($producto) && str_contains($contenido, 'data-product-detail')): ?>
<script src="<?= e(asset('assets/js/david/producto.js?v=20260929-2')) ?>"></script>
<?php endif; ?>
</body>
</html>
