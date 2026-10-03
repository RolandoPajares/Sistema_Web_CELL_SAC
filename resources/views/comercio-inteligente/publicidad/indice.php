<?php
/**
 * @var array<int, array{icono:string,etiqueta:string,destino:string,activo:bool}> $opcionesMenuPublicidadInteligente
 * @var array<int, array{icono:string,etiqueta:string,valor:string,detalle:string}> $metricasPublicidad
 * @var array<int, array<string, mixed>> $campaniasDestacadas
 * @var string $rutaCampanias
 
 */
?>
<section class="publicidad-ia-fondo">
    <div class="container publicidad-ia-layout">
        <aside class="publicidad-ia-menu">
            <div><i class="bi bi-stars"></i><span><b>MD Ads inteligente</b><small>Impulsa tus ventas con IA</small></span></div>
            <?php foreach ($opcionesMenuPublicidadInteligente as $opcion): ?>
            <a
                class="<?= e($opcion['activo'] ? 'activo' : '') ?>"
                href="<?= e($opcion['destino']) ?>"><i class="bi <?= e($opcion['icono']) ?>"></i><?= e($opcion['etiqueta']) ?>
            </a>
            <?php endforeach; ?>
            <div class="publicidad-convierte"><i class="bi bi-graph-up-arrow"></i><b>Convierte más.<br>Vende inteligente.</b><small>IA + Datos + Resultados</small></div>
        </aside>
        <main class="publicidad-ia-contenido" id="vista-general">
            <header>
                <div><span>Inteligencia Artificial para tu negocio</span>
                    <h1>MD Ads inteligente<br><em>SmartCommerce</em></h1>
                    <p>Convierte clics en clientes. Analiza, optimiza y haz crecer tu negocio con el poder de la IA.</p>
                </div>

                <div class="publicidad-ia-acciones">
                    <a class="btn btn-primary" href="<?= e(url_interna($rutaCampanias)) ?>"><i class="bi bi-plus-lg"></i> Crear campaña
                    </a>
                    <button type="button" disabled title="Próxima iteración"><i class="bi bi-bar-chart"></i> Optimizar anuncios
                    </button>
                    <button type="button" disabled title="Próxima iteración"><i class="bi bi-file-text"></i> Generar reporte IA
                    </button>
                </div>
            </header>

            <div class="publicidad-metricas">
                <?php foreach ($metricasPublicidad as $metrica): ?>
                <article>
                <i class="bi <?= e($metrica['icono']) ?>"></i>
                    <div><span><?= e($metrica['etiqueta']) ?></span><b><?= e($metrica['valor']) ?></b><small><?= e($metrica['detalle']) ?></small></div>
                </article><?php endforeach; ?></div>
            <div class="publicidad-rejilla">
                <section class="panel">
                    <div class="titulo-panel">
                        <h2>Ventas atribuidas por día</h2><button>Ventas atribuidas <i class="bi bi-chevron-down"></i></button>
                    </div>
                    <p>Próxima iteración: se mostrará la serie diaria cuando exista historial de atribución.</p>
                </section>
                <section class="panel embudo-publicidad">
                    <h2>Conversión del embudo</h2><p>Próxima iteración: aún no existe atribución entre campañas, carrito y compras.</p>
                </section>
                <aside class="panel" id="recomendaciones"><span id="funciones-pendientes"></span>
                    <h2><i class="bi bi-stars"></i> Insights con IA</h2><p>Funcionalidad planificada para una siguiente etapa.</p>
                </aside>
            </div>
            <div class="publicidad-tablas" id="reportes">
                <section class="panel">
                    <h2>Productos destacados por ventas</h2>
                    <p>Próxima iteración: requiere atribución de ventas por campaña.</p>
                </section>
                <section class="panel">
                    <h2>Top campañas</h2>

                    <ol>
                <?php foreach ($campaniasDestacadas as $campania): ?>
                        <li>
                <?= e($campania['nombre']) ?> <b><?= (int) $campania['clics'] ?> clics</b>
                        </li>
                <?php endforeach; ?>
                    </ol>
                </section>
                <section class="panel">
                    <h2>Recomendaciones de la IA</h2>
                    <p>Funcionalidad planificada para una siguiente etapa.</p><button class="btn btn-primary" type="button" disabled>Próxima iteración</button>
                </section>
            </div>
        </main>
    </div>
</section>
