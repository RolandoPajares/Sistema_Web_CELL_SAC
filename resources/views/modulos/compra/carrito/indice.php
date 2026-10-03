<?php

/**
 * @var string $atributoCarritoVacioOculto
 * @var string $atributoCarritoConArticulosOculto
 * @var array<array-key, mixed> $resumen
 * @var string $atributoSugerenciasOculto
 * @var array<array-key, mixed> $sugerencias
 */ ?><section>
    <div class="container">
        <h1>Tu carrito</h1>
        <div class="empty panel" <?= $atributoCarritoVacioOculto ?>>
            Tu carrito está vacío.<br><br>
            <a class="btn btn-primary" href="<?= e(url_interna('catalog')) ?>"><i class="bi bi-bag" aria-hidden="true"></i> Explorar productos</a>
        </div>
        <div class="panel table-wrap" <?= $atributoCarritoConArticulosOculto ?>>
            <table class="table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resumen['articulos'] as $producto): ?>
                        <tr>

                            <td class="cart-product-cell">
                                <img <?= $producto['atributoImagenOculta'] ?> src="<?= e($producto['imagen_carrito_vista']) ?>" alt="" loading="lazy">
                                <span class="cart-product-placeholder" <?= $producto['atributoIconoOculto'] ?> aria-hidden="true">
                                    <i class="bi <?= e($producto['icono_carrito_vista']) ?>"></i>
                                </span>
                                <b><?= e($producto['marca'] . ' ' . $producto['nombre']) ?></b>
                            </td>
                            <td><?= formatear_dinero($producto['precio']) ?></td>
                            <td><?= (int) $producto['cantidad'] ?></td>
                            <td><?= formatear_dinero($producto['subtotal']) ?></td>
                            <td>
                                <form method="post" action="<?= e(url_interna('cart')) ?>">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-ghost" name="remove" value="<?= (int) $producto['id'] ?>">
                                        <i class="bi bi-trash3" aria-hidden="true"></i> Quitar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="cart-summary">
                <form method="post" action="<?= e(url_interna('cart')) ?>">
                    <?= csrf_field() ?>
                    <button class="btn btn-ghost" name="clear" value="1"><i class="bi bi-x-circle" aria-hidden="true"></i> Vaciar</button>
                </form>
                <div>
                    <b>Total: <span class="price"><?= formatear_dinero($resumen['total']) ?></span></b>
                    <a class="btn btn-primary" href="<?= e(url_interna('checkout')) ?>"><i class="bi bi-arrow-right" aria-hidden="true"></i> Continuar</a>
                </div>
            </div>
        </div>
        <section class="smart-cart panel" <?= $atributoSugerenciasOculto ?>>
            <div class="section-head">
                <div><span class="eyebrow">Carrito inteligente</span>
                    <h2><i class="bi bi-cart-check" aria-hidden="true"></i> Completa tu compra</h2>
                    <p>Recomendaciones compatibles o complementarias según lo que ya agregaste.</p>
                </div>
            </div>
            <div class="smart-cart-grid">
                <?php foreach ($sugerencias as $sugerencia): ?>
                    <article class="smart-cart-item">
                        <img <?= $sugerencia['atributoImagenOculta'] ?>
                            class="smart-cart-photo"
                            src="<?= e($sugerencia['imagen_sugerencia_vista']) ?>"
                            alt="<?= e($sugerencia['texto_alternativo_vista']) ?>"
                            loading="lazy">
                        <span class="smart-cart-photo smart-cart-photo-placeholder" <?= $sugerencia['atributoIconoOculto'] ?> aria-hidden="true">
                            <i class="bi <?= e($sugerencia['icono_sugerencia_vista']) ?>"></i>
                        </span>
                        <div><b><?= e($sugerencia['marca'] . ' ' . $sugerencia['nombre']) ?></b><small><?= e($sugerencia['compatibilidad']) ?></small></div>
                        <span><?= formatear_dinero($sugerencia['precio']) ?></span>

                        <form method="post" action="<?= e(url_interna('cart')) ?>">
                            <?= csrf_field() ?>
                            <button class="btn btn-ghost" name="add" value="<?= (int)$sugerencia['id'] ?>">+ Agregar
                            </button>
                        </form>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="compatibility-note"><i class="bi bi-shield-check"></i>
                <div><b>Control de compatibilidad</b>
                    <p>El sistema prioriza accesorios de la misma marca y marca como “universal / verificar modelo” aquellos que requieren validación adicional.</p>
                </div>
            </div>
        </section>
    </div>
</section>