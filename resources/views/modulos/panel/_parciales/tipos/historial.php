<?php
/**
 * @var array<array-key, mixed> $datosDemostracion
 */ ?><section class="panel modulo-filtros">
    <label><i class="bi bi-search"></i><input type="search" placeholder="Buscar por producto o número de pedido"></label>
    <button><i class="bi bi-calendar3"></i> Últimos 12 meses
    </button>
    <button><i class="bi bi-funnel"></i> Todos los estados
    </button>
    <button class="btn btn-primary">Aplicar filtros
    </button>
</section>
<section class="panel tabla-mockup">
    <div class="titulo-panel">
        <div>
            <i class="bi bi-bag-check"></i>
            <h2>
                Tus compras (12)
            </h2>
        </div>
        <button>Más recientes <i class="bi bi-chevron-down"></i>
        </button>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>
                N.º de pedido
                </th>
                <th>
                Fecha
                </th>
                <th>
                Productos
                </th>
                <th>
                Total
                </th>
                <th>
                Estado
                </th>
                <th>
                Comprobante
                </th>
                <th>
                Acciones
                </th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($datosDemostracion['historial'] as $pedido) :
                ?>
            <tr>
                <td>
                <b><?= e($pedido['pedido']) ?></b>
                </td>
                <td>
                <?= e($pedido['fecha']) ?>
                </td>
                <td>
                <span class="producto-chico"><i class="bi bi-phone"></i></span><span class="producto-chico"><i class="bi bi-earbuds"></i></span> +<?= $pedido['cantidad_adicional'] ?>
                </td>
                <td>
                <b>S/ <?= e($pedido['total']) ?></b>
                </td>
                <td>
                <span class="estado estado--verde">Entregado</span>
                </td>
                <td>
                <i class="bi bi-file-earmark-text"></i> Boleta 001-<?= e($pedido['comprobante']) ?>
                </td>
                <td>
                    <button class="btn btn-outline">Ver detalles
                    </button>
                </td>
            </tr>
            <?php
                endforeach; ?>
        </tbody>
    </table>
</section>
<div class="rejilla-listados recomendaciones-compra">
    <section class="panel">
        <div class="titulo-panel">
            <h2>
                Compra de nuevo tus favoritos
            </h2>
            <a href="<?= e(url_interna('catalog')) ?>">Ver todos
            </a>
        </div>
        <div class="productos-horizontales">
            <?php foreach ($datosDemostracion['favoritos'] as $producto) :
                ?>
            <article>
                <i class="bi <?= e($producto['icono']) ?>"></i><b><?= e($producto['nombre']) ?></b><strong>S/ <?= $producto['precio'] ?></strong>
                <a class="btn btn-primary" href="<?= e(url_interna('catalog')) ?>">Comprar de nuevo
                </a>
            </article>
            <?php
                endforeach; ?>
        </div>
    </section>
    <section class="panel">
        <div class="titulo-panel">
            <h2>
                Recomendado para ti
            </h2>
        </div>
        <div class="productos-horizontales">
                <?php foreach ($datosDemostracion['recomendados'] as $producto) :
                ?>
            <article>
                <i class="bi <?= e($producto['icono']) ?>"></i><b><?= e($producto['nombre']) ?></b><strong>S/ <?= $producto['precio'] ?></strong>
                <a class="btn btn-primary" href="<?= e(url_interna('catalog')) ?>">Ver producto
                </a>
            </article>
            <?php
                endforeach; ?>
        </div>
    </section>
</div>
