<!-- ===== 1. Presentación de SmartMatch ===== -->
<section class="smartmatch-hero">
    <div class="container smartmatch-hero-grid">
        <div>
            <span class="smartmatch-etiqueta"><i class="bi bi-stars"></i> MD SmartCommerce</span>
            <h1>Encuentra tu <span>celular ideal</span> con SmartMatch</h1>
            <p>Indica tu presupuesto, para qué usarás el equipo y qué es lo más importante para ti. SmartMatch revisa el catálogo y te muestra los celulares que mejor encajan contigo.</p>
            <div class="smartmatch-pasos-mini">
                <span><b>1</b> Presupuesto</span>
                <span><b>2</b> Uso</span>
                <span><b>3</b> Prioridad</span>
            </div>
        </div>
        <div class="smartmatch-hero-imagen">
            <img src="<?= e(asset('assets/img/publico/inicio/secciones/hero-devices.png')) ?>" alt="Celulares y accesorios">
        </div>
    </div>
</section>

<section class="smart-page">
    <div class="container">
        <form class="smart-form panel" method="get">
            <label>
                Presupuesto máximo
                <input class="input" type="number" name="budget" min="300" step="50" value="<?= e($presupuesto ?: '') ?>" placeholder="Ej. 1500">
            </label>
            <label>
                Uso principal
                <select name="use">
                    <option value="study" <?= $uso === 'study' ? 'selected' : '' ?>>Estudio</option>
                    <option value="gaming" <?= $uso === 'gaming' ? 'selected' : '' ?>>Gaming</option>
                    <option value="camera" <?= $uso === 'camera' ? 'selected' : '' ?>>Fotografía</option>
                    <option value="work" <?= $uso === 'work' ? 'selected' : '' ?>>Trabajo</option>
                    <option value="social" <?= $uso === 'social' ? 'selected' : '' ?>>Redes sociales</option>
                </select>
            </label>
            <label>
                Prioridad
                <select name="priority">
                    <option value="valor" <?= $prioridad === 'valor' ? 'selected' : '' ?>>Calidad/precio</option>
                    <option value="rendimiento" <?= $prioridad === 'rendimiento' ? 'selected' : '' ?>>Rendimiento</option>
                    <option value="bateria" <?= $prioridad === 'bateria' ? 'selected' : '' ?>>Batería</option>
                    <option value="camara" <?= $prioridad === 'camara' ? 'selected' : '' ?>>Cámara</option>
                </select>
            </label>
            <button class="btn btn-primary">Encontrar mi celular</button>
        </form>

        <?php if ($resultados): ?>
            <div class="smart-results">
                <?php foreach ($resultados as $indice => $producto): ?>
                    <article class="smart-card panel">
                        <div class="smart-rank">#<?= $indice + 1 ?></div>
                        <div>
                            <span class="eyebrow"><?= e($producto['marca']) ?></span>
                            <h3><?= e($producto['nombre']) ?></h3>
                            <div class="match-ring"><?= (int) $producto['coincidencia'] ?>%</div>
                            <p class="price"><?= money($producto['precio']) ?></p>
                            <ul>
                                <?php foreach ($producto['razones'] as $razon): ?>
                                    <li>✓ <?= e($razon) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <div class="smart-actions">
                                <a class="btn btn-primary" href="<?= e(url('products/' . (int) $producto['id'])) ?>">Ver equipo</a>
                                <a class="btn btn-ghost" href="<?= e(url('smart/compare?ids=' . (int) $producto['id'])) ?>">Comparar</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
