<?php
/**
 * @var mixed $contextoPublicidad
 * @var mixed $claveFrecuenciaPublicidad
 * @var string $perfilPublicidad
 * @var string $urlSeguimiento
 * @var array<array-key, mixed> $ofertaEntrada
 * @var array<array-key, mixed> $campaniaPrincipal
 * @var string $atributoPrecioOfertaOculto
 * @var string $atributoPrecioAnteriorOculto
 * @var mixed $precioAnteriorPublicidadVista
 * @var mixed $precioOfertaPublicidadVista
 * @var string $atributoBeneficiosOculto
 * @var string $urlAccionPrincipal
 * @var array<array-key, mixed> $ofertaContextual
 * @var string $urlAccionContextual
 */ ?><div
    class="publicidad-dinamica publicidad-<?= e($contextoPublicidad) ?>"
    id="publicidadDinamica"
    data-contexto="<?= e($claveFrecuenciaPublicidad) ?>"
    data-perfil="<?= e($perfilPublicidad) ?>"
    data-limite="4"
    data-espera="12000">
    <div class="publicidad-modal-fondo js-publicidad-modal" hidden>
        <section class="publicidad-modal" role="dialog" aria-modal="true" aria-labelledby="publicidadTitulo"
            <?= $urlSeguimiento ? 'data-track-url="' . e($urlSeguimiento) . '"' : '' ?>>
            <button class="publicidad-cerrar js-publicidad-cerrar" type="button" aria-label="Cerrar promoción"><i class="bi bi-x-lg"></i></button>
            <div class="publicidad-modal-arte">
                <span><i class="bi <?= e($ofertaEntrada['icono']) ?>"></i></span>
                <b>MD</b><strong>SMART DEALS</strong>
                <small>Una mejor compra empieza con una buena recomendación.</small>
            </div>
            <div class="publicidad-modal-contenido">
                <span class="publicidad-etiqueta"><i class="bi bi-lightning-charge-fill"></i> <?= e($ofertaEntrada['etiqueta']) ?></span>
                <h2 id="publicidadTitulo"><?= e($campaniaPrincipal['titulo'] ?? $ofertaEntrada['titulo']) ?></h2>
                <p><?= e($campaniaPrincipal['descripcion'] ?? $ofertaEntrada['descripcion']) ?></p>
                <div class="publicidad-precio" <?= $atributoPrecioOfertaOculto ?>>
                <del <?= $atributoPrecioAnteriorOculto ?>><?= e($precioAnteriorPublicidadVista) ?></del><strong><?= e($precioOfertaPublicidadVista) ?></strong>
                </div>
                <div class="publicidad-beneficios" <?= $atributoBeneficiosOculto ?>>
                <span><i class="bi bi-check-circle-fill"></i> Fácil de usar</span><span><i class="bi bi-check-circle-fill"></i> Sin compromiso</span>
                </div>

                <a
                class="btn btn-primary js-publicidad-accion"
                href="<?= e($urlAccionPrincipal) ?>"><?= e($campaniaPrincipal['texto_boton'] ?? $ofertaEntrada['texto_boton']) ?> <i class="bi bi-arrow-right"></i>
                </a>
                <button class="publicidad-despues js-publicidad-cerrar" type="button">Seguir navegando</button>
            </div>
        </section>
    </div>

    <aside class="publicidad-contextual js-publicidad-contextual" aria-live="polite" hidden>
        <button class="publicidad-cerrar js-contextual-cerrar" type="button" aria-label="Cerrar promoción"><i class="bi bi-x-lg"></i></button>
        <span class="publicidad-contextual-icono"><i class="bi <?= e($ofertaContextual['icono']) ?>"></i></span>
        <div><small><?= e($ofertaContextual['etiqueta']) ?></small><b><?= e($ofertaContextual['titulo']) ?></b>
            <p><?= e($ofertaContextual['descripcion']) ?></p>
        </div>
        <a class="btn btn-primary" href="<?= e($urlAccionContextual) ?>"><?= e($ofertaContextual['texto_boton']) ?></a>
    </aside>

    <button class="publicidad-lanzador js-publicidad-lanzador" type="button" aria-label="Ver oferta disponible" title="Ver oferta">
        <i class="bi bi-gift-fill"></i><span>Oferta</span>
    </button>
</div>
