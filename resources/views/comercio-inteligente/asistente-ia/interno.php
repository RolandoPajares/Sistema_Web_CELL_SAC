<header class="admin-page-head asistente-b2b-cabecera">
    <div><span class="eyebrow">Ventas Mayoristas</span>
        <h1>MD Assistant B2B comercial <small>IA</small></h1>
        <p>Asistente con IA para responder, cotizar y recomendar soluciones empresariales.</p>
    </div>
    <div class="beneficios-asistente"><span><i class="bi bi-shield-check"></i><b>Respuestas confiables</b></span><span><i class="bi bi-lightning-charge"></i><b>Ahorra tiempo</b></span><span><i class="bi bi-graph-up"></i><b>Crece tu negocio</b></span></div>
</header>
<div class="asistente-b2b-layout">
    <aside class="panel conversaciones-b2b">
        <div class="titulo-panel">
            <h2>Conversaciones</h2><button><i class="bi bi-plus-lg"></i> Nueva</button>
        </div><label><i class="bi bi-search"></i><input placeholder="Buscar conversaciones..."></label><?php foreach ([['Recomendación laptops empresa', 'Necesito laptops para una empresa...'], ['Cotización celulares corporativos', 'Hola, necesito una cotización de 50...'], ['Stock iPhone 15 para distribuidor', '¿Tienen stock del iPhone 15 en volumen?'], ['Propuesta para licitación estatal', 'Revisa estas especificaciones...'], ['Auriculares para call center', '¿Qué opciones tienen con micrófono?'], ['Simulador de margen', 'Ayúdame a calcular un margen...']] as $i => $conversacion): ?><a class="<?= $i === 0 ? 'activo' : '' ?>" href="#conversacion"><i class="bi bi-chat-left-text"></i><span><b><?= e($conversacion[0]) ?></b><small><?= e($conversacion[1]) ?></small></span></a><?php endforeach; ?><h3>Prompts rápidos</h3><?php foreach (['Buscar productos por volumen', 'Armar una cotización', 'Comparar productos', 'Ver stock disponible', 'Sugerir productos por rubro', 'Redactar mensaje para cliente'] as $prompt): ?><button class="prompt-b2b"><i class="bi bi-stars"></i><?= e($prompt) ?></button><?php endforeach; ?>
    </aside>
    <main class="panel conversacion-b2b" id="conversacion">
        <div class="mensaje-b2b usuario"><span class="admin-avatar">CM</span>
            <p>Necesito laptops para una empresa de 50 colaboradores. Busco equipos confiables para trabajo de oficina, videollamadas y navegación. ¿Qué me recomiendas y cuál es el precio por volumen?</p>
        </div>
        <div class="mensaje-b2b bot"><span><i class="bi bi-robot"></i></span>
            <p>¡Listo! Te comparto 3 opciones de laptops ideales para entornos empresariales, con stock disponible y precios especiales por volumen.</p>
        </div>
        <div class="opciones-laptop"><?php foreach ([['Lenovo ThinkPad E14 Gen 5', 'Rendimiento y confiabilidad', '120', 'S/ 2,299.00'], ['HP ProBook 450 G10', 'Equilibrio perfecto para empresas', '85', 'S/ 2,199.00'], ['Dell Latitude 3440', 'Seguridad y soporte empresarial', '60', 'S/ 2,249.00']] as $opcion): ?><article>
                    <h3><?= e($opcion[0]) ?></h3><small><?= e($opcion[1]) ?></small><i class="bi bi-laptop"></i>
                    <ul>
                        <li>Intel Core i5-1335U</li>
                        <li>16 GB RAM</li>
                        <li>512 GB SSD</li>
                        <li>Windows 11 Pro</li>
                    </ul><span>Stock: <?= e($opcion[2]) ?> und.</span>
                    <p>50+ und. <b><?= e($opcion[3]) ?></b></p>
                </article><?php endforeach; ?></div>
        <div class="comparativa-b2b">
            <h3><i class="bi bi-columns-gap"></i> Comparativa rápida</h3>
            <table>
                <tr>
                    <th>Característica</th>
                    <th>Lenovo ThinkPad</th>
                    <th>HP ProBook</th>
                    <th>Dell Latitude</th>
                </tr>
                <tr>
                    <td>Procesador</td>
                    <td>Intel Core i5</td>
                    <td>Intel Core i5</td>
                    <td>Intel Core i5</td>
                </tr>
                <tr>
                    <td>Memoria RAM</td>
                    <td>16 GB</td>
                    <td>16 GB</td>
                    <td>16 GB</td>
                </tr>
                <tr>
                    <td>Precio 50+ und.</td>
                    <td>S/ 2,299</td>
                    <td>S/ 2,199</td>
                    <td>S/ 2,249</td>
                </tr>
            </table>
        </div>
        <div class="acciones-recomendacion">
            <div><i class="bi bi-stars"></i><span><b>Mi recomendación</b>El HP ProBook ofrece el mejor equilibrio entre precio, pantalla y garantía.</span></div><a class="btn btn-primary" href="<?= e(url('panel/cotizaciones')) ?>"><i class="bi bi-file-earmark-text"></i> Generar cotización</a><a class="btn btn-ghost" href="<?= e(url('smart/compare')) ?>">Comparar opciones</a>
        </div>
        <form class="entrada-asistente" data-assistant-form data-endpoint="<?= e(url('smart/assistant/reply')) ?>"><input name="message" placeholder="Escribe tu mensaje al asistente..."><button><i class="bi bi-send-fill"></i></button></form>
    </main>
</div>