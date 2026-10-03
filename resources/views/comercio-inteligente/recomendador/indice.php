<?php
/**
 * Datos preparados por ComercioInteligenteController y su presentador.
 * @var array<int, array<string, mixed>> $resultados
 * @var float $presupuesto
 * @var string $uso
 * @var string $prioridad
 
 * @var string $atributoResultadosOculto
 * @var string $atributoSinResultadosOculto
 */
?>
<!-- ===== 1. Presentación de SmartMatch ===== -->
<section class="smartmatch-hero">
    <div class="container smartmatch-hero-grid">
        <div>
            <span class="smartmatch-etiqueta"><i class="bi bi-stars"></i> MD SmartCommerce</span>
            <h1>Encuentra tu <span>celular ideal</span> con SmartMatch</h1>

            <p>
                Indica tu presupuesto, para qué usarás el equipo y qué es lo más importante para ti. SmartMatch revisa el catálogo y te muestra los celulares que mejor encajan contigo.
            </p>
            <div class="smartmatch-pasos-mini">
                <span><b>1</b> Presupuesto</span>
                <span><b>2</b> Uso</span>
                <span><b>3</b> Prioridad</span>
            </div>
        </div>
        <div class="smartmatch-hero-imagen">
            <img src="<?= e(url_recurso_estatico('assets/img/publico/inicio/secciones/hero-devices.png')) ?>" alt="Celulares y accesorios">
        </div>
    </div>
</section>

<section class="container smartmatch-contenido">
    <!-- ===== 2. Formulario de búsqueda (se envía por GET porque solo consulta, no guarda nada) ===== -->
    <form class="smartmatch-form" method="get" id="buscar">
        <div class="smartmatch-form-titulo">
            <i class="bi bi-sliders"></i>
            <div>
                <h2>Cuéntanos qué buscas</h2>
                <p>Completa los tres datos y presiona el botón.</p>
            </div>
        </div>
        <div class="smartmatch-campos">
            <label>
                <span><i class="bi bi-cash-coin"></i> Presupuesto máximo (S/)</span>
                <input class="input" type="number" name="budget" min="300" step="50" value="<?= e($presupuesto ?: '') ?>" placeholder="Ej. 1500">
            </label>
            <label>
                <span><i class="bi bi-phone"></i> Uso principal</span>
                <select name="use">
                <option value="study" <?= $uso === 'study' ? 'selected' : '' ?>>Estudio</option>
                <option value="gaming" <?= $uso === 'gaming' ? 'selected' : '' ?>>Gaming</option>
                <option value="camera" <?= $uso === 'camera' ? 'selected' : '' ?>>Fotografía</option>
                <option value="work" <?= $uso === 'work' ? 'selected' : '' ?>>Trabajo</option>
                <option value="social" <?= $uso === 'social' ? 'selected' : '' ?>>Redes sociales</option>
                </select>
            </label>
            <label>
                <span><i class="bi bi-star"></i> Prioridad</span>
                <select name="priority">
                <option value="valor" <?= $prioridad === 'valor' ? 'selected' : '' ?>>Calidad/precio</option>
                <option value="rendimiento" <?= $prioridad === 'rendimiento' ? 'selected' : '' ?>>Rendimiento</option>
                <option value="bateria" <?= $prioridad === 'bateria' ? 'selected' : '' ?>>Batería</option>
                <option value="camara" <?= $prioridad === 'camara' ? 'selected' : '' ?>>Cámara</option>
                </select>
            </label>
            <button class="btn btn-primary"><i class="bi bi-search"></i> Encontrar mi celular</button>
        </div>
    </form>

    <!-- ===== 3. Cómo funciona ===== -->
    <div class="smartmatch-como">
        <article>
            <i class="bi bi-wallet2"></i>
            <h3>1. Tu presupuesto</h3>
            <p>Solo se muestran equipos que no pasan el monto que indicas.</p>
        </article>
        <article>
            <i class="bi bi-controller"></i>
            <h3>2. Tu forma de usarlo</h3>
            <p>Estudio, juegos, fotos, trabajo o redes: cada uso tiene sus puntos fuertes.</p>
        </article>
        <article>
            <i class="bi bi-graph-up-arrow"></i>
            <h3>3. Tu coincidencia</h3>
            <p>Cada celular recibe un porcentaje y las razones de por qué te conviene.</p>
        </article>
    </div>

    <!-- ===== 4. Resultados de la búsqueda ===== -->
    <div class="smartmatch-titulo-resultados" <?= $atributoResultadosOculto ?>>
        <h2>Te recomendamos estos equipos</h2>
        <p>Ordenados de mayor a menor coincidencia.</p>
    </div>
    <div class="smartmatch-resultados" <?= $atributoResultadosOculto ?>>
            <?php foreach ($resultados as $producto): ?>
        <article class="smartmatch-tarjeta">
                <span class="smartmatch-puesto">#<?= (int) $producto['puesto_vista'] ?></span>
                <img <?= $producto['atributoImagenOculta'] ?>
                class="smartmatch-product-photo"
                src="<?= e($producto['imagen_recomendada_vista']) ?>"
                alt="<?= e($producto['texto_alternativo_vista']) ?>"
                loading="lazy">
            <div class="smartmatch-product-photo smartmatch-product-placeholder" <?= $producto['atributoIconoOculto'] ?> aria-hidden="true">
                <i
                class="bi <?= e($producto['icono_recomendado_vista']) ?>"></i>
            </div>
            <div class="smartmatch-tarjeta-arriba">
                <div>
                <small><?= e($producto['marca']) ?></small>
                    <h3><?= e($producto['nombre']) ?></h3>
                    <p class="smartmatch-precio"><?= formatear_dinero($producto['precio']) ?></p>
                </div>
                <div class="smartmatch-porcentaje">
                <b><?= (int) $producto['coincidencia'] ?>%</b>
                <small>coincide</small>
                </div>
            </div>
            <ul>
                <?php foreach ($producto['razones'] as $razon): ?>
                <li><i class="bi bi-check-circle-fill"></i> <?= e($razon) ?></li>
                <?php endforeach; ?>
            </ul>
            <div class="smartmatch-botones">
                <a class="btn btn-primary" href="<?= e(url_interna('products/' . (int) $producto['id'])) ?>">Ver equipo</a>
                <a class="btn btn-ghost" href="<?= e(url_interna('smart/compare?ids=' . (int) $producto['id'])) ?>">Comparar</a>
            </div>
        </article>
            <?php endforeach; ?>
    </div>
    
        <!-- si buscó pero no hubo resultados, se muestra un mensaje en vez de dejar la página vacía -->
    <div class="smartmatch-vacio" <?= $atributoSinResultadosOculto ?>>
            <i class="bi bi-emoji-neutral"></i>
        <h2>No encontramos equipos para esa búsqueda</h2>
        <p>Prueba con un presupuesto un poco más alto o cambia la prioridad. También puedes revisar todo el catálogo.</p>
        <a class="btn btn-primary" href="<?= e(url_interna('catalog')) ?>">Ver catálogo</a>
    </div>
</section>
