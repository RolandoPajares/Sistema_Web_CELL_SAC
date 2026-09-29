<?php $rutaCampanias = is_admin() ? 'admin/campaigns' : 'panel/campanias'; ?>
<section class="publicidad-ia-fondo">
    <div class="container publicidad-ia-layout">
        <aside class="publicidad-ia-menu">
            <div><i class="bi bi-stars"></i><span><b>MD Ads inteligente</b><small>Impulsa tus ventas con IA</small></span></div>
            <?php foreach ([['bi-grid', 'Vista general'], ['bi-clock-history', 'Mis campañas'], ['bi-bar-chart', 'Analítica de ventas'], ['bi-people', 'Audiencia'], ['bi-box-seam', 'Productos'], ['bi-stars', 'Recomendaciones IA'], ['bi-file-earmark-bar-graph', 'Reportes'], ['bi-plugin', 'Integraciones'], ['bi-gear', 'Configuración']] as $indice => $opcion): ?><a class="<?= $indice === 0 ? 'activo' : '' ?>" href="<?= e($indice === 1 ? url($rutaCampanias) : '#seccion-' . $indice) ?>"><i class="bi <?= e($opcion[0]) ?>"></i><?= e($opcion[1]) ?></a><?php endforeach; ?>
            <div class="publicidad-convierte"><i class="bi bi-graph-up-arrow"></i><b>Convierte más.<br>Vende inteligente.</b><small>IA + Datos + Resultados</small></div>
        </aside>
        <main class="publicidad-ia-contenido">
            <header>
                <div><span>Inteligencia Artificial para tu negocio</span>
                    <h1>MD Ads inteligente<br><em>SmartCommerce</em></h1>
                    <p>Convierte clics en clientes. Analiza, optimiza y haz crecer tu negocio con el poder de la IA.</p>
                </div>
                <div class="publicidad-ia-acciones"><a class="btn btn-primary" href="<?= e(url($rutaCampanias)) ?>"><i class="bi bi-plus-lg"></i> Crear campaña</a><button type="button" disabled title="Próxima iteración"><i class="bi bi-bar-chart"></i> Optimizar anuncios</button><button type="button" disabled title="Próxima iteración"><i class="bi bi-file-text"></i> Generar reporte IA</button></div>
            </header>
            <div class="publicidad-metricas"><?php foreach ([['bi-megaphone', 'Campañas activas', (string) $resumen['activas'], 'Datos reales'], ['bi-eye', 'Impresiones', number_format((int) $resumen['vistas']), 'Datos reales'], ['bi-mouse', 'Clics', number_format((int) $resumen['clics']), 'Datos reales'], ['bi-bar-chart', 'CTR', number_format((float) $resumen['ctr'], 1) . '%', 'Calculado']] as $metrica): ?><article><i class="bi <?= e($metrica[0]) ?>"></i>
                        <div><span><?= e($metrica[1]) ?></span><b><?= e($metrica[2]) ?></b><small><?= e($metrica[3]) ?></small></div>
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
                <aside class="panel" id="recomendaciones">
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
                    <ol><?php foreach (array_slice($campanias, 0, 4) as $campania): ?><li><?= e($campania['nombre']) ?> <b><?= (int) $campania['clics'] ?> clics</b></li><?php endforeach; ?></ol>
                </section>
                <section class="panel">
                    <h2>Recomendaciones de la IA</h2>
                    <p>Funcionalidad planificada para una siguiente etapa.</p><button class="btn btn-primary" type="button" disabled>Próxima iteración</button>
                </section>
            </div>
        </main>
    </div>
</section>
