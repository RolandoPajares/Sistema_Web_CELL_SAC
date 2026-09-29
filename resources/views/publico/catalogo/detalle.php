<?php
$variantes = $producto['variantes'] ?? [];
$caracteristicas = $producto['caracteristicas'] ?? [];
$imagenInicial = (string) ($producto['imagen_referencia'] ?? $producto['imagen'] ?? '');
foreach ($variantes as $v) { if (!empty($v['imagenes'][0]['ruta_imagen'])) { $imagenInicial = (string)$v['imagenes'][0]['ruta_imagen']; break; } }
$imagenDe = static fn(array $p): string => (string)($p['imagen_referencia'] ?? $p['imagen'] ?? '');
$valorComparacion = static function(array $item, string $label): string { $map=['Marca'=>'marca','Categoría'=>'categoria','Capacidad'=>'almacenamiento','Color'=>'color','Stock'=>'existencias']; if(isset($map[$label])) return (string)($item[$map[$label]] ?? '—'); foreach(($item['caracteristicas'] ?? []) as $c){ if(($c['nombre'] ?? '')===$label) return (string)($c['valor'] ?? '—'); } return '—'; };
?>
<section class="product-page-light" data-product-detail>
<div class="container product-detail-modern">
  <div class="detail-gallery">
    <div class="gallery-thumbs" data-gallery-thumbs>
      <?php $primera=true; foreach ($variantes as $variante): foreach (($variante['imagenes'] ?? []) as $img): ?>
        <button type="button" class="gallery-thumb <?= $primera?'active':'' ?>" <?= ((int)$variante['id'] !== (int)($variantes[0]['id'] ?? 0)) ? 'hidden' : '' ?> data-variant="<?= (int)$variante['id'] ?>" data-src="<?= e(asset($img['ruta_imagen'])) ?>"><img src="<?= e(asset($img['ruta_imagen'])) ?>" alt="<?= e($producto['nombre']) ?>"></button>
      <?php $primera=false; endforeach; endforeach; ?>
    </div>
    <div class="detail-main-image">
      <?php if ($imagenInicial): ?><img data-main-product-image src="<?= e(asset($imagenInicial)) ?>" alt="<?= e($producto['nombre']) ?>"><?php else: ?><div class="phone-shape" data-image-placeholder><?= e(product_visual((string)$producto['marca'])) ?></div><?php endif; ?>
      <?php if ($variantes !== []): ?><button class="gallery-arrow prev" type="button" data-gallery-prev aria-label="Anterior"><i class="bi bi-chevron-left"></i></button><button class="gallery-arrow next" type="button" data-gallery-next aria-label="Siguiente"><i class="bi bi-chevron-right"></i></button><?php endif; ?>
    </div>
  </div>
  <div class="detail-info">
    <span class="eyebrow"><?= e($producto['marca'] ?? '') ?></span><h1><?= e($producto['nombre']) ?></h1><p><?= e($producto['descripcion'] ?? '') ?></p><div class="price"><?= money($producto['precio']) ?></div>
    <h3>Color</h3><div class="variant-colors" data-variant-colors>
      <?php foreach ($variantes as $i=>$variante): $hex=$variante['codigo_color'] ?: '#d8dee9'; ?>
      <button type="button" class="color-option <?= $i===0?'active':'' ?>" title="<?= e($variante['nombre_color']) ?>" data-variant-id="<?= (int)$variante['id'] ?>" data-color-name="<?= e($variante['nombre_color']) ?>" data-stock="<?= (int)$variante['stock'] ?>" data-images="<?= e(json_encode(array_values(array_map(static fn($img) => asset((string)$img['ruta_imagen']), $variante['imagenes'] ?? [])), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)) ?>" style="--swatch:<?= e($hex) ?>"><span></span></button>
      <?php endforeach; ?>
    </div><div class="selected-color" data-selected-color><?= e($variantes[0]['nombre_color'] ?? $producto['color'] ?? '') ?></div>
    <div class="stock">Stock disponible: <b data-variant-stock><?= (int)($variantes[0]['stock'] ?? $producto['existencias']) ?></b></div>
    <form action="<?= e(url('cart')) ?>" method="post"><?= csrf_field() ?><input type="hidden" name="add" value="<?= (int)$producto['id'] ?>"><button class="btn btn-primary"><i class="bi bi-cart-plus"></i> Comprar</button></form>
  </div>
</div>
<?php if (!empty($complementos)): ?><div class="container recommendation-block"><h2>Complementa tu compra</h2><div class="complement-grid">
<?php foreach (($complementos ?? []) as $item): ?><article class="complement-card"><div class="mini-product-image"><?php if ($imagenDe($item)): ?><img src="<?= e(asset($imagenDe($item))) ?>" alt=""><?php else: ?><div class="phone-shape"><?= e(product_visual((string)$item['marca'])) ?></div><?php endif; ?></div><div><small><?= e($item['marca']??'') ?></small><h3><?= e($item['nombre'] ?? '') ?></h3><strong><?= money($item['precio'] ?? 0) ?></strong></div><form action="<?= e(url('cart')) ?>" method="post"><?= csrf_field() ?><input type="hidden" name="add" value="<?= (int)$item['id'] ?>"><button class="btn btn-outline"><i class="bi bi-cart-plus"></i> Añadir al carrito</button></form></article><?php endforeach; ?>
</div></div>
<?php endif; ?><?php if (!empty($similares)): ?><div class="container comparison-block"><h2>Compara productos similares</h2><div class="comparison-scroll"><table class="comparison-table"><thead><tr><th>Característica</th><th class="current">Estás viendo<br><b><?= e($producto['nombre']) ?></b></th><?php foreach (($similares??[]) as $item): ?><th><div class="compare-image"><?php if ($imagenDe($item)): ?><img src="<?= e(asset($imagenDe($item))) ?>" alt=""><?php else: ?><div class="phone-shape"><?= e(product_visual((string)$item['marca'])) ?></div><?php endif; ?></div><small><?= e($item['marca']) ?></small><b><?= e($item['nombre']) ?></b><span><?= money($item['precio']) ?></span><form action="<?= e(url('cart')) ?>" method="post"><?= csrf_field() ?><input type="hidden" name="add" value="<?= (int)$item['id'] ?>"><button class="compare-add">AGREGAR</button></form></th><?php endforeach; ?></tr></thead><tbody>
<?php $specActual=[]; foreach($caracteristicas as $c){$specActual[$c['nombre']]=$c['valor'];} $filas=['Memoria RAM'=>$specActual['Memoria RAM']??'—','Memoria Interna'=>$specActual['Memoria Interna']??($producto['almacenamiento']??'—'),'Batería'=>$specActual['Batería']??'—','Procesador y generación'=>$specActual['Procesador y generación']??'—']; foreach($filas as $label=>$valor): ?><tr><th><?= e($label) ?></th><td class="current"><?= e($valor) ?></td><?php foreach(($similares??[]) as $item): ?><td><?= e($valorComparacion($item, $label)) ?></td><?php endforeach; ?></tr><?php endforeach; ?>
</tbody></table></div></div><?php endif; ?></section>
