<?php

/**
 * @var array<array-key, mixed> $datosDemostracion
 */ ?><div class="rejilla-analitica">

    <section class="panel panel-grafico panel-grafico--ancho">
        <div class="titulo-panel">
            <div>
                <i class="bi bi-graph-up-arrow"></i>
                <h2>
                    Tráfico y conversiones
                </h2>
                <small>Real vs. predicho por IA</small>
            </div>
            <button>Últimos 6 meses
            </button>
        </div>
        <div class="grafico-barras grande">
            <?php foreach ($datosDemostracion['serie_analitica'] as $punto):
            ?><span style="--altura:<?= $punto['altura'] ?>px"><b><?= e($punto['mes']) ?></b></span><?php
                                                                                            endforeach; ?><svg viewBox="0 0 600 180" preserveAspectRatio="none">
                <polyline points="0,140 100,105 200,88 300,62 400,72 500,48 600,20" />
            </svg>
        </div>
    </section>

    <section class="panel panel-grafico">
        <div class="titulo-panel">
            <div>
                <i class="bi bi-pie-chart-fill"></i>
                <h2>
                    Segmentación de audiencia
                </h2>
            </div>
        </div>
        <div class="grafico-donut">
            <div>
                <b><?= e($datosDemostracion['audiencia_total']) ?></b><span>usuarios</span>
            </div>
        </div>
        <ul class="leyenda-donut">
            <?php foreach ($datosDemostracion['leyenda_audiencia'] as $audiencia): ?>
                <li><i class="<?= e($audiencia['color']) ?>"></i> <?= e($audiencia['etiqueta']) ?> <b><?= e($audiencia['porcentaje']) ?></b></li>
            <?php endforeach; ?>
        </ul>
    </section>
</div>
<section class="panel acciones-rapidas acciones-ia">
    <div class="titulo-panel">
        <div>
            <i class="bi bi-stars"></i>
            <h2>
                Acciones rápidas con IA
            </h2>
        </div>
    </div>
    <div>
        <?php foreach ($datosDemostracion['acciones_ia'] as $accion) :
        ?><button><i class="bi bi-stars"></i><?= e($accion) ?><i class="bi bi-chevron-right"></i></button><?php
                                                                                                    endforeach; ?>
    </div>
</section>
<div class="rejilla-tres">
    <section class="panel lista-recomendaciones">
        <h2>
            Recomendaciones de IA
        </h2>
        <?php foreach ($datosDemostracion['recomendaciones_ia'] as $recomendacion) :
        ?><p><b><?= $recomendacion['numero'] ?></b><?= e($recomendacion['texto']) ?><span>Alto impacto</span></p><?php
                                                                                                            endforeach; ?>
    </section>
    <section class="panel lista-alertas">
        <h2>
            Alertas y anomalías
        </h2>
        <?php foreach ($datosDemostracion['alertas_ia'] as $texto) :
        ?><a href="<?= e(url_interna('admin/campaigns')) ?>"><i class="punto rojo"></i><span><b><?= e($texto) ?></b><small>Detectado por el modelo</small></span></a><?php
                                                                                                                                                                endforeach; ?>
    </section>
    <section class="panel tabla-resumen">
        <h2>
            Rendimiento por canal
        </h2>
        <table class="table">
            <tbody>
                <?php foreach ($datosDemostracion['rendimiento_canales'] as $canal) :
                ?><tr>
                        <td><?= e($canal['nombre']) ?></td>
                        <td><?= e($canal['visitas']) ?></td>
                        <td><b class="positivo">+<?= $canal['variacion'] ?>%</b></td>
                    </tr><?php
                        endforeach; ?></tbody>
        </table>
    </section>
</div>