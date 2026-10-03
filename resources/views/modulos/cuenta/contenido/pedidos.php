<header class="cuenta-titulo">
    <div><span><i class="bi bi-box-seam"></i></span>
        <div>
            <h1>Mis pedidos</h1>
            <p>Consulta y da seguimiento a todos tus pedidos.</p>
        </div>
    </div>
</header>
<section class="cuenta-filtros"><label>Estado del pedido<select>
            <option>Todos los estados</option>
            <option>En proceso</option>
            <option>Enviado</option>
            <option>Entregado</option>
        </select></label><label>Rango de fechas<input type="date"></label><a class="btn btn-primary" href="#lista-pedidos"><i class="bi bi-search"></i> Buscar pedidos</a></section>
<section class="cuenta-panel" id="lista-pedidos">
    <?php require dirname(__DIR__) . '/_parciales/lista-pedidos.php'; ?>
</section>