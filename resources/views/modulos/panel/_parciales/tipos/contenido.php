<?php
/**
 * @var array<array-key, mixed> $datosDemostracion
 */ ?><nav class="pestanas-contenido">
    <button class="activo"><i class="bi bi-grid"></i> Todos
    </button>
    <button><i class="bi bi-file-text"></i> Blog posts
    </button>
    <button><i class="bi bi-image"></i> Banners
    </button>
    <button><i class="bi bi-play-btn"></i> Videos
    </button>
    <button><i class="bi bi-tag"></i> Promociones
    </button>
    <button><i class="bi bi-share"></i> Redes sociales
    </button>
</nav>
<div class="modulo-con-detalle">
    <section class="panel tabla-mockup">
        <div class="titulo-panel">
            <div>
                <i class="bi bi-file-richtext"></i>
                <h2>
                Listado de contenido
                </h2>
            </div>
            <label class="busqueda-compacta"><i class="bi bi-search"></i><input placeholder="Buscar contenido..."></label>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>
                Título
                    </th>
                    <th>
                Tipo
                    </th>
                    <th>
                Estado
                    </th>
                    <th>
                Publicación
                    </th>
                    <th>
                Canal
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($datosDemostracion['contenido'] as $pieza) :
                ?>
                <tr>
                    <td>
                <span class="miniatura-contenido"><i class="bi bi-image"></i></span><b><?= e($pieza['titulo']) ?></b>
                    </td>
                    <td>
                <?= e($pieza['tipo']) ?>
                    </td>
                    <td>
                <span class="estado estado--<?= e($pieza['clase_estado']) ?>"><?= e($pieza['estado']) ?></span>
                    </td>
                    <td>
                <?= e($pieza['fecha']) ?>
                    </td>
                    <td>
                <i class="bi bi-instagram"></i> <?= e($pieza['canal']) ?>
                    </td>
                </tr>
                <?php
                endforeach; ?>
            </tbody>
        </table>
    </section>
    <aside class="panel vista-contenido">
        <h2>
                <i class="bi bi-eye"></i> Vista previa
        </h2>
        <div class="creatividad">
                <small>MD TECHNOLOGY CELL</small><strong>Innovación<br><em>que impulsa</em><br>tu negocio</strong><span>Tecnología • Resultados • Crecimiento</span>
        </div>
        <dl class="datos-detalle">
            <div>
                <dt>
                Título
                </dt>
                <dd>
                Innovación que impulsa tu negocio
                </dd>
            </div>
            <div>
                <dt>
                Tipo
                </dt>
                <dd>
                Banner
                </dd>
            </div>
            <div>
                <dt>
                Estado
                </dt>
                <dd>
                <span class="estado estado--verde">Publicado</span>
                </dd>
            </div>
            <div>
                <dt>
                Canal
                </dt>
                <dd>
                Instagram
                </dd>
            </div>
        </dl>
        <button class="btn btn-primary full-width"><i class="bi bi-pencil"></i> Editar contenido
        </button>
    </aside>
</div>
