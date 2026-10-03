<?php
/**
 * @var array<array-key, mixed> $datosDemostracion
 * @var array<array-key, mixed> $interfaz
 */ ?><section class="panel modulo-filtros">
    <label><i class="bi bi-search"></i><input type="search" placeholder="Buscar en <?= e($datosDemostracion['titulo_minuscula']) ?>..."></label>
    <button type="button"><i class="bi bi-funnel"></i> Todos los estados <i class="bi bi-chevron-down"></i></button>
    <button type="button"><i class="bi bi-calendar3"></i> Últimos 30 días <i class="bi bi-chevron-down"></i></button>
    <button class="btn btn-primary" type="button">Aplicar filtros</button>
</section>
<div class="modulo-con-detalle">
    <section class="panel tabla-mockup">

        <div class="titulo-panel">
            <div>
                <i class="bi <?= e($interfaz['icono']) ?>"></i>
                <h2>
                Listado de <?= e($datosDemostracion['titulo_minuscula']) ?>
                </h2>
            </div>
            <button type="button"><i class="bi bi-download"></i> Exportar
            </button>
        </div>
        <div class="table-responsive"><table class="table"><thead><tr><?php foreach ($datosDemostracion['tabla']['columnas'] as $columna) :
            ?><th><?= e($columna) ?></th><?php
                endforeach; ?><th>Acciones</th></tr></thead><tbody>
        <?php foreach ($datosDemostracion['tabla']['filas'] as $fila) :
            ?><tr>
            <?php foreach ($fila as $celda) : ?>
                        <td><span class="estado <?= e($celda['clase_estado']) ?>" <?= $celda['atributoEstadoOculto'] ?>><?= e($celda['valor']) ?></span><span <?= $celda['atributoValorOculto'] ?>><?= e($celda['valor']) ?></span></td>
            <?php endforeach; ?>
                        <td><button class="icon-btn" type="button" aria-label="Ver detalle"><i class="bi bi-three-dots"></i></button></td>
                    </tr>
        <?php endforeach; ?>
                </tbody></table></div>

        <div class="paginacion-mockup">
            <span>Mostrando 1 - 8 de 48 registros</span>
            <div>
                <button>‹
                </button>
                <button class="activo">1
                </button>
                <button>2
                </button>
                <button>3
                </button>
                <button>›
                </button>
            </div>
        </div>
    </section>
    <aside class="panel detalle-mockup">
        <div class="titulo-panel"><div><i class="bi bi-info-circle-fill"></i><h2>Detalle seleccionado</h2></div><button type="button"><i class="bi bi-x-lg"></i></button></div>
        <div class="identidad-detalle"><span>DA</span><div><b>Distribuidora Andina SAC</b><small>Registro activo · Lima</small></div></div>
        <div class="pestanas-mockup"><b>Información</b><span>Historial</span><span>Notas</span></div>

        <dl class="datos-detalle">
            <div>
                <dt>
                Responsable
                </dt>
                <dd>
                Juan Pérez García
                </dd>
            </div>
            <div>
                <dt>
                Correo
                </dt>
                <dd>
                juan.perez@empresa.com
                </dd>
            </div>
            <div>
                <dt>
                Teléfono
                </dt>
                <dd>
                +51 987 654 321
                </dd>
            </div>
            <div>
                <dt>
                Última actividad
                </dt>
                <dd>
                10 Jun 2025, 10:24
                </dd>
            </div>
        </dl>

        <div class="resumen-detalle">
            <article>
                <i class="bi bi-bag-check"></i><b>24</b><small>Operaciones</small>
            </article>
            <article>
                <i class="bi bi-cash-coin"></i><b>S/ 85,420</b><small>Acumulado</small>
            </article>
        </div>
        <button class="btn btn-primary full-width" type="button">Actualizar estado</button>
    </aside>
</div>
