<?php
/**
 * @var array<array-key, mixed> $datosDemostracion
 */ ?><section class="panel modulo-filtros">
    <label><i class="bi bi-search"></i><input type="search" placeholder="Buscar productos o direcciones..."></label>
    <button><i class="bi bi-funnel"></i> Todas las categorías
    </button>
    <button><i class="bi bi-sort-down"></i> Más relevantes
    </button>
</section>
<div class="rejilla-productos-panel">
    <?php foreach ($datosDemostracion['productos_tarjetas'] as $producto) :
        ?>
    <article>
        <span class="etiqueta-producto"><?= e($producto['etiqueta']) ?></span>
        <div class="producto-ilustrado">
            <i class="bi <?= e($producto['icono']) ?>"></i>
        </div>
        <small><?= e($producto['marca']) ?></small>
        <h3>
            <?= e($producto['nombre']) ?>
        </h3>
        <div class="valoracion">
            <span aria-hidden="true"><i class="bi bi-star-fill"></i> <i class="bi bi-star-fill"></i> <i class="bi bi-star-fill"></i> <i class="bi bi-star-fill"></i> <i class="bi bi-star-fill"></i></span> <span><?= e($producto['valoracion']) ?></span>
        </div>
        <strong>S/ <?= e($producto['precio']) ?></strong>
        <button class="btn btn-primary"><i class="bi bi-cart-plus"></i> Agregar
        </button>
    </article>
    <?php
                endforeach; ?></div>
