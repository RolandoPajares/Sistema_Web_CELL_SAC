<?php $etapas = ['Nuevo lead', 'Contactado', 'Propuesta enviada', 'Negociación', 'Cierre'];
$colores = ['azul', 'verde', 'ambar', 'violeta', 'verde']; ?>
<section class="panel modulo-filtros"><button type="button"><i class="bi bi-geo-alt"></i> Todas las regiones</button><button type="button"><i class="bi bi-sort-down"></i> Fecha límite</button><span class="vista-botones"><button class="activo"><i class="bi bi-grid-3x3-gap"></i></button><button><i class="bi bi-list"></i></button></span></section>
<div class="tablero-kanban">
    <?php foreach ($etapas as $indiceEtapa => $etapa) :
        ?><section class="columna-kanban <?= e($colores[$indiceEtapa]) ?>"><header><div><i></i><b><?= e($etapa) ?></b></div><span><?= 5 + $indiceEtapa ?></span><strong>S/ <?= number_format(84200 + ($indiceEtapa * 138000)) ?></strong></header>
        <?php foreach (array_slice(['Distribuidora Andina SAC','Supermercados Sol','Comercial del Sur','MegaRetail SAC','Grupo Horizonte','Inversiones Valle SAC'], 0, 4) as $indice => $empresa) :
            ?><article><div><span class="logo-empresa"><?= e(mb_substr($empresa, 0, 2)) ?></span><button><i class="bi bi-three-dots-vertical"></i></button></div><b><?= e($empresa) ?></b><strong>S/ <?= number_format(180000 + ($indice * 42000) + ($indiceEtapa * 10000)) ?></strong><small><i class="bi bi-person"></i> <?= e(['Laura Torres','Carlos Mendoza','Andrea Ruiz'][$indice % 3]) ?></small><small><i class="bi bi-calendar3"></i> <?= 12 + $indice ?> Jun 2025</small></article><?php
        endforeach; ?>
        <button class="agregar-kanban" type="button"><i class="bi bi-plus-lg"></i> Agregar oportunidad</button>
    </section>
    <?php endforeach; ?>
</div>
