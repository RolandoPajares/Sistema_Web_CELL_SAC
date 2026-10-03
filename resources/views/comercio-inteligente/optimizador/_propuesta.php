<?php
/**
 * Propuesta formateada por PresentadorComercioInteligente.
 * @var array{
 *     metricas:array<int, array{etiqueta:string,valor:string}>,
 *     articulos:array<int, array{producto:string,cantidad:int,precio:string,subtotal:string}>
 * } $propuestaVista
 
 * @var array<array-key, mixed> $propuestaVista
 */
?>
<div class="panel proposal">
    <div class="proposal-kpis">
        <?php foreach ($propuestaVista['metricas'] as $metrica): ?>
            <div><span><?= e($metrica['etiqueta']) ?></span><strong><?= e($metrica['valor']) ?></strong></div>
        <?php endforeach; ?>
    </div>
    <table class="table">
        <thead>
            <tr><th>Producto</th><th>Cant.</th><th>Precio</th><th>Subtotal</th></tr>
        </thead>
        <tbody>
        <?php foreach ($propuestaVista['articulos'] as $linea): ?>
            <tr>
                <td><?= e($linea['producto']) ?></td>
                <td><?= $linea['cantidad'] ?></td>
                <td><?= e($linea['precio']) ?></td>
                <td><?= e($linea['subtotal']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <p class="product-note">Margen referencial calculado para simulación académica; no constituye una garantía de rentabilidad.</p>
</div>
