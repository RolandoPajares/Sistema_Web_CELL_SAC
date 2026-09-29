<?php
$clave=(string)($interfaz['clave']??'');
$esProductos=$clave==='productos';
$esMovimientos=$clave==='movimientos-stock';
$esAlertas=$clave==='alertas-stock';
$filas=$registros ?? [];
if($esProductos){$columnas=['sku'=>'SKU','producto'=>'Producto','categoria'=>'Categoría','precio'=>'Precio','stock'=>'Stock','estado'=>'Estado'];}
elseif($esMovimientos){$columnas=['codigo'=>'Código','producto'=>'Producto','movimiento'=>'Movimiento','cantidad'=>'Cantidad','responsable'=>'Responsable','motivo'=>'Motivo','fecha'=>'Fecha'];}
elseif($esAlertas){$columnas=['sku'=>'SKU','producto'=>'Producto','categoria'=>'Categoría','stock_actual'=>'Stock actual','stock_minimo'=>'Stock mínimo'];}
else{$columnas=[]; if($filas){foreach(array_keys($filas[0]) as $c){if($c!=='id')$columnas[$c]=ucfirst(str_replace('_',' ',$c));}}}
?>
<?php if($esProductos): ?>
<section class="panel modulo-filtros" data-module-filters>
 <label><i class="bi bi-search"></i><input type="search" placeholder="Buscar en gestión de productos..." data-filter-search></label>
 <select class="module-filter-select" data-filter-status><option value="">Todos los estados</option><option>Publicado</option><option>Stock bajo</option><option>Borrador</option></select>
 <button class="btn btn-primary" type="button" data-apply-filters>Aplicar filtros</button>
</section>
<?php endif; ?>
<div class="modulo-listado-real modulo-claro" data-filter-table>
<section class="panel tabla-mockup">
 <div class="titulo-panel"><div><i class="bi <?= e($interfaz['icono']??'bi-list') ?>"></i><h2><?= $esProductos?'Gestión de productos':($esMovimientos?'Movimientos reales de stock':($esAlertas?'Productos con stock menor a 3':'Listado')) ?></h2></div><button type="button" data-export-table><i class="bi bi-download"></i> Exportar</button></div>
 <div class="table-responsive"><table class="table"><thead><tr><?php foreach($columnas as $label):?><th><?=e($label)?></th><?php endforeach;?><?php if($esAlertas):?><th>Actualizar stock</th><th>Proveedor</th><?php endif;?></tr></thead><tbody>
 <?php if(!$filas):?><tr><td colspan="<?=count($columnas)+($esAlertas?2:0)?>">No hay registros disponibles.</td></tr><?php endif;?>
 <?php foreach($filas as $r):?><tr data-filter-row data-record='<?=e(json_encode($r,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES))?>'>
  <?php foreach($columnas as $key=>$label): $v=$r[$key]??'—';?><td><?php if(in_array($key,['estado','movimiento'],true)):?><span class="estado <?= in_array(mb_strtolower((string)$v),['publicado','entrada'],true)?'estado--verde':'estado--ambar' ?>"><?=e((string)$v)?></span><?php elseif($key==='precio'):?><?=money($v)?><?php else:?><?=e((string)$v)?><?php endif;?></td><?php endforeach;?>
  <?php if($esAlertas):?><td><a class="btn btn-primary btn-sm" href="<?=e(url('panel/inventario'))?>#editor"><i class="bi bi-box-arrow-in-down"></i> Gestionar stock</a></td><td><button class="btn btn-outline btn-sm" type="button" data-provider-detail><i class="bi bi-person-lines-fill"></i> Ver contacto</button></td><?php endif;?>
 </tr><?php endforeach;?>
 <tr data-filter-empty hidden><td colspan="<?=count($columnas)+($esAlertas?2:0)?>">No hay registros que coincidan con los filtros.</td></tr>
 </tbody></table></div>
</section>
<?php if($esAlertas):?><aside class="panel detalle-mockup" data-provider-panel hidden><div class="titulo-panel"><div><i class="bi bi-truck"></i><h2>Contacto del proveedor</h2></div><button type="button" data-close-provider><i class="bi bi-x-lg"></i></button></div><dl class="datos-detalle"><div><dt>Producto</dt><dd data-p-product>—</dd></div><div><dt>Proveedor</dt><dd data-p-name>—</dd></div><div><dt>Correo</dt><dd data-p-email>—</dd></div><div><dt>Teléfono</dt><dd data-p-phone>—</dd></div></dl><small>Si aparece “Sin proveedor asignado”, primero debe vincularse un proveedor al producto.</small></aside><?php endif;?>
</div>
