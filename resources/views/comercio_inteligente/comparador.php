<section class="smart-page">
    <div class="container">
        <div class="smart-hero">
            <span class="eyebrow">MD SmartCommerce</span>
            <h1><i class="bi bi-columns-gap" aria-hidden="true"></i> Comparador inteligente</h1>
            <p>Selecciona hasta tres celulares y compara precio, disponibilidad y SmartScore por perfil.</p>
        </div>

        <form class="panel smart-form compare-picker" method="get" id="compareForm">
            <?php for ($posicion = 0; $posicion < 3; $posicion++): ?>
                <label>
                    Equipo <?= $posicion + 1 ?>
                    <select class="compare-select">
                        <?php if ($posicion > 0): ?>
                            <option value="">-- Opcional --</option>
                        <?php endif; ?>
                        <?php foreach ($todosLosProductos as $producto): ?>
                            <option value="<?= (int) $producto['id'] ?>" <?= ($idsProductos[$posicion] ?? 0) == $producto['id'] ? 'selected' : '' ?>>
                                <?= e($producto['marca'] . ' ' . $producto['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
            <?php endfor; ?>
            <input type="hidden" name="ids" id="compareIds">
            <button class="btn btn-primary">Comparar ahora</button>
        </form>

        <?php if ($productos): ?>
            <div class="comparison-grid">
                <?php foreach ($productos as $producto): ?>
                    <article class="panel comparison-card">
                        <span class="eyebrow"><?= e($producto['marca']) ?></span>
                        <h2><?= e($producto['nombre']) ?></h2>
                        <div class="price"><?= money($producto['precio']) ?></div>
                        <p><?= e($producto['almacenamiento'] ?? 'Consultar') ?> · Stock <?= (int) $producto['existencias'] ?></p>
                        <?php foreach ($producto['puntajes_inteligentes'] as $clave => $puntaje): ?>
                            <div class="score-row">
                                <span><?= e([
                                    'rendimiento' => 'Rendimiento',
                                    'camara' => 'Cámara',
                                    'bateria' => 'Batería',
                                    'valor' => 'Calidad/precio',
                                ][$clave]) ?></span>
                                <b><?= (int) $puntaje ?>/100</b>
                                <div class="scorebar"><i style="width:<?= (int) $puntaje ?>%"></i></div>
                            </div>
                        <?php endforeach; ?>
                        <a class="btn btn-ghost" href="<?= e(url('products/' . (int) $producto['id'])) ?>">Ver detalles</a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
