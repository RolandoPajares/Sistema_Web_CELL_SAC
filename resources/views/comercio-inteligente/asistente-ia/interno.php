<?php
/**
 * @var array<int, array{titulo:string,resumen:string,clase_vista:string}> $conversacionesDemo
 * @var array<int, string> $promptsRapidos
 * @var array<int, array{nombre:string,descripcion:string,existencias:int,precio:float,caracteristicas:array<int,array{nombre:string,valor:string}>}> $opcionesLaptop
 * @var array{disponible:bool,nombre:string} $recomendacionLaptop
 * @var array<int, array{etiqueta:string,valores:array<int,string>}> $filasComparativaLaptop
 * @var string $mensajeProductosCatalogo
 
 * @var string $atributoSinPortatilesOculto
 * @var string $atributoComparativaOculta
 * @var string $atributoOpcionCatalogoOculta
 */
?>
<header class="admin-page-head asistente-b2b-cabecera">
    <div>
        <span class="eyebrow">Ventas Mayoristas</span>
        <h1>MD Assistant B2B comercial <small>IA</small></h1>
        <p>Asistente con IA para responder, cotizar y recomendar soluciones empresariales.</p>
    </div>

    <div class="beneficios-asistente">
        <span>
            <i class="bi bi-shield-check"></i>
            <b>Respuestas confiables</b>
        </span>
        <span>
            <i class="bi bi-lightning-charge"></i>
            <b>Ahorra tiempo</b>
        </span>
        <span>
            <i class="bi bi-graph-up"></i>
            <b>Crece tu negocio</b>
        </span>
    </div>
</header>

<div class="asistente-b2b-layout">
    <aside class="panel conversaciones-b2b">
        <div class="titulo-panel">
            <h2>Conversaciones</h2>
            <button>
                <i class="bi bi-plus-lg"></i>
                Nueva
            </button>
        </div>

        <label>
            <i class="bi bi-search"></i>
            <input placeholder="Buscar conversaciones...">
        </label>

        <?php foreach ($conversacionesDemo as $conversacion): ?>
            <a class="<?= e($conversacion['clase_vista']) ?>" href="#conversacion">
                <i class="bi bi-chat-left-text"></i>
                <span>
                    <b><?= e($conversacion['titulo']) ?></b>
                    <small><?= e($conversacion['resumen']) ?></small>
                </span>
            </a>
        <?php endforeach; ?>

        <h3>Prompts rápidos</h3>

        <?php foreach ($promptsRapidos as $prompt): ?>
            <button class="prompt-b2b">
                <i class="bi bi-stars"></i>
                <?= e($prompt) ?>
            </button>
        <?php endforeach; ?>
    </aside>

    <main class="panel conversacion-b2b" id="conversacion">
        <div class="mensaje-b2b usuario">
            <span class="admin-avatar">CM</span>
            <p>
                Necesito laptops para una empresa de 50 colaboradores. Busco equipos confiables para trabajo de oficina,
                videollamadas y navegación. ¿Qué me recomiendas y cuál es el precio por volumen?
            </p>
        </div>

        <div class="mensaje-b2b bot">
            <span><i class="bi bi-robot"></i></span>
            <p><?= e($mensajeProductosCatalogo) ?></p>
        </div>

        <div class="opciones-laptop">
            <p <?= $atributoSinPortatilesOculto ?>>No hay equipos portátiles activos para comparar.</p>
            <?php foreach ($opcionesLaptop as $opcion): ?>
                    <article>
                        <h3><?= e($opcion['nombre']) ?></h3>

                        <small <?= $opcion['atributoDescripcionOculta'] ?>><?= e($opcion['descripcion']) ?></small>

                        <i class="bi bi-laptop"></i>

                        <ul <?= $opcion['atributoCaracteristicasOculto'] ?>>
                            <?php foreach ($opcion['caracteristicas'] as $caracteristica): ?>
                                <li>
                                    <?= e($caracteristica['nombre']) ?>:
                                    <?= e($caracteristica['valor']) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <span>Stock: <?= e($opcion['existencias']) ?> und.</span>
                        <p>Precio registrado: <b><?= e(formatear_dinero($opcion['precio'])) ?></b></p>
                    </article>
            <?php endforeach; ?>
        </div>

            <div class="comparativa-b2b" <?= $atributoComparativaOculta ?>>
                <h3><i class="bi bi-columns-gap"></i> Comparativa rápida</h3>
                <table>
                    <thead>
                        <tr>
                            <th scope="col">Característica</th>
                            <?php foreach ($opcionesLaptop as $opcion): ?>
                                <th scope="col"><?= e($opcion['nombre']) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($filasComparativaLaptop as $fila): ?>
                            <tr>
                                <th scope="row"><?= e($fila['etiqueta']) ?></th>
                                <?php foreach ($fila['valores'] as $valor): ?>
                                    <td><?= e($valor) ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <small>
                    El catálogo muestra el precio registrado; consulta las condiciones para compras por volumen.
                </small>
            </div>

        <div class="acciones-recomendacion">
                <div <?= $atributoOpcionCatalogoOculta ?>>
                    <i class="bi bi-stars"></i>
                    <span>
                        <b>Opción del catálogo</b>
                        <?= e($recomendacionLaptop['nombre']) ?>
                    </span>
                </div>

            <a class="btn btn-primary" href="<?= e(url_interna('panel/cotizaciones')) ?>">
                <i class="bi bi-file-earmark-text"></i>
                Generar cotización
            </a>
            <a class="btn btn-ghost" href="<?= e(url_interna('smart/compare')) ?>">
                Comparar opciones
            </a>
        </div>

        <form
            class="entrada-asistente"
            data-assistant-form
            data-endpoint="<?= e(url_interna('smart/assistant/reply')) ?>"
        >
            <input name="message" placeholder="Escribe tu mensaje al asistente...">
            <button>
                <i class="bi bi-send-fill"></i>
            </button>
        </form>
    </main>
</div>
