<header class="admin-page-head">
    <div><span class="eyebrow">Dashboard · Pedidos</span>
        <h1>Pedidos</h1>
        <p>Gestiona y da seguimiento a todos los pedidos de MD Technology Cell.</p>
    </div><button class="boton-fecha"><i class="bi bi-calendar3"></i> Hoy, <?= e(date('d M Y')) ?></button>
</header>
<div class="metricas-mockup">
    <article class="metrica-mockup azul">
        <div class="metrica-icono"><i class="bi bi-cart"></i></div>
        <div><span>Pedidos nuevos</span><strong>24</strong><small><b>↑ 33%</b> vs. ayer</small></div>
    </article>
    <article class="metrica-mockup ambar">
        <div class="metrica-icono"><i class="bi bi-gear"></i></div>
        <div><span>En proceso</span><strong>18</strong><small><b>↑ 12%</b> vs. ayer</small></div>
    </article>
    <article class="metrica-mockup violeta">
        <div class="metrica-icono"><i class="bi bi-truck"></i></div>
        <div><span>Enviados</span><strong>32</strong><small><b>↑ 28%</b> vs. ayer</small></div>
    </article>
    <article class="metrica-mockup verde">
        <div class="metrica-icono"><i class="bi bi-check-circle"></i></div>
        <div><span>Entregados</span><strong>156</strong><small><b>↑ 19%</b> vs. ayer</small></div>
    </article>
</div>
<section class="panel modulo-filtros"><label><i class="bi bi-search"></i><input type="search" placeholder="Buscar pedido, cliente o producto" data-table-search></label><button>Todos los estados <i class="bi bi-chevron-down"></i></button><button>Todos los canales <i class="bi bi-chevron-down"></i></button><button><i class="bi bi-calendar3"></i> Últimos 30 días</button></section>
<div class="modulo-con-detalle">
    <section class="panel tabla-mockup">
        <div class="titulo-panel">
            <div><i class="bi bi-cart"></i>
                <h2>Pedidos (<?= count($pedidos) ?>)</h2>
            </div><button><i class="bi bi-download"></i> Exportar</button>
        </div>
        <div class="table-responsive">
            <table class="table data-table">
                <thead>
                    <tr>
                        <th>Pedido</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Total</th>
                        <th>Pago</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody><?php foreach ($pedidos as $pedido): ?><tr>
                            <td><b>#<?= str_pad((string) $pedido['id'], 6, '0', STR_PAD_LEFT) ?></b></td>
                            <td><?= e($pedido['nombre']) ?><small><?= e($pedido['correo']) ?></small></td>
                            <td><?= e(date('d M Y', strtotime((string) $pedido['creado_en']))) ?></td>
                            <td><?= money($pedido['total']) ?></td>
                            <td><span class="estado estado--verde">Pagado</span></td>
                            <td><span class="estado estado--ambar"><?= e($pedido['status']) ?></span></td>
                            <td><button class="icon-btn"><i class="bi bi-three-dots"></i></button></td>
                        </tr><?php endforeach; ?></tbody>
            </table>
        </div><?php if (!$pedidos): ?><div class="empty">Todavía no hay pedidos.</div><?php endif; ?>
    </section>
    <aside class="panel detalle-mockup">
        <div class="titulo-panel">
            <div><i class="bi bi-receipt"></i>
                <h2>Detalle del pedido</h2>
            </div><button><i class="bi bi-x-lg"></i></button>
        </div>
        <h3>#001244 <span class="estado estado--ambar">Enviado</span></h3>
        <p><b>Cliente</b><br>Distribuidora Tech S.A.<br><small>RUC: 20567890123</small></p>
        <hr>
        <h3>Productos (3)</h3>
        <div class="producto-detalle-pedido"><i class="bi bi-phone"></i><span>iPhone 15 128GB<small>2 unidades</small></span><b>S/ 4,598</b></div>
        <div class="producto-detalle-pedido"><i class="bi bi-headphones"></i><span>Audífonos JBL<small>2 unidades</small></span><b>S/ 399</b></div>
        <div class="total-pedido"><span>Total del pedido</span><b>S/ 5,680.00</b></div>
        <h3>Envío y seguimiento</h3>
        <div class="linea-seguimiento"><i class="activo"></i><i class="activo"></i><i class="activo"></i><i></i></div><small>Confirmado · En preparación · En tránsito · Entregado</small>
    </aside>
</div>
