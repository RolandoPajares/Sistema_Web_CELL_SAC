<h1><i class="bi bi-bar-chart" aria-hidden="true"></i> Dashboard empresarial inteligente</h1>
<p>Indicadores operativos, productos de mayor movimiento, stock crítico y estimación básica de agotamiento.</p>

<div class="dashboard-cards">
    <div class="dash-card"><span>Productos</span><strong><?= (int) $estadisticas['productos'] ?></strong></div>
    <div class="dash-card"><span>Unidades en stock</span><strong><?= (int) $estadisticas['existencias'] ?></strong></div>
    <div class="dash-card"><span>Pedidos</span><strong><?= (int) $estadisticas['pedidos'] ?></strong></div>
    <div class="dash-card"><span>Ventas del mes</span><strong><?= money($inteligencia['ingresos']) ?></strong></div>
</div>

<div class="bi-grid">
    <section class="panel">
        <span class="eyebrow">Rotación</span>
        <h2>Productos con mayor movimiento</h2>
        <div class="bi-list">
            <?php foreach ($inteligencia['destacados'] as $indice => $producto): ?>
                <div>
                    <span class="bi-rank"><?= $indice + 1 ?></span>
                    <p>
                        <b><?= e($producto['marca'] . ' ' . $producto['nombre']) ?></b>
                        <small><?= (int) $producto['unidades'] ?> unidades · <?= money($producto['ingresos']) ?></small>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="panel">
        <span class="eyebrow">Inventario</span>
        <h2>Semáforo de stock</h2>
        <div class="bi-list">
            <?php foreach ($inteligencia['existencias_bajas'] as $producto): ?>
                <?php $existencias = (int) $producto['existencias']; ?>
                <div>
                    <span class="stock-dot <?= $existencias <= 3 ? 'critical' : ($existencias <= 7 ? 'warning' : 'ok') ?>"></span>
                    <p>
                        <b><?= e($producto['marca'] . ' ' . $producto['nombre']) ?></b>
                        <small><?= $existencias ?> unidades · <?= $producto['dias_restantes'] ? '≈ ' . (int) $producto['dias_restantes'] . ' días restantes' : 'sin historial suficiente' ?></small>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<section class="panel bi-insight">
    <div>
        <span class="eyebrow">MD Business Intelligence</span>
        <h2>Lectura automática</h2>
        <p>El dashboard cruza ventas e inventario para destacar productos de alta rotación y anticipar reposición. La predicción usa velocidad histórica simple, por lo que se presenta como estimación y no como pronóstico garantizado.</p>
    </div>
    <a class="btn btn-primary" href="<?= e(url('admin/products')) ?>">Gestionar inventario</a>
</section>
