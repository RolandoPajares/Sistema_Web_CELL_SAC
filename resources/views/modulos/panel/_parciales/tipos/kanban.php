<?php
/**
 * @var array<array-key, mixed> $datosDemostracion
 */ ?><section class="panel modulo-filtros">
    <button type="button"><i class="bi bi-geo-alt"></i> Todas las regiones
    </button>
    <button type="button"><i class="bi bi-sort-down"></i> Fecha límite
    </button>
    <span class="vista-botones">
    <button class="activo"><i class="bi bi-grid-3x3-gap"></i>
    </button>
    <button><i class="bi bi-list"></i>
    </button>
    </span>
</section>
<div class="tablero-kanban">
    <?php foreach ($datosDemostracion['kanban'] as $columna) :
                ?>
    <section class="columna-kanban <?= e($columna['color']) ?>">
        <header>
            <div>
                <i></i><b><?= e($columna['etapa']) ?></b>
            </div>
            <span><?= $columna['cantidad'] ?></span><strong>S/ <?= e($columna['total']) ?></strong>
        </header>
        <?php foreach ($columna['oportunidades'] as $oportunidad) :
                ?>
        <article>
            <div>
                <span class="logo-empresa"><?= e($oportunidad['iniciales']) ?></span>
                <button><i class="bi bi-three-dots-vertical"></i>
                </button>
            </div>
            <b><?= e($oportunidad['empresa']) ?></b><strong>S/ <?= e($oportunidad['total']) ?></strong><small><i class="bi bi-person"></i> <?= e($oportunidad['responsable']) ?></small><small><i class="bi bi-calendar3"></i> <?= e($oportunidad['fecha']) ?></small>
        </article>
            <?php
        endforeach; ?>
        <button class="agregar-kanban" type="button"><i class="bi bi-plus-lg"></i> Agregar oportunidad</button>
    </section>
    <?php endforeach; ?>
</div>
