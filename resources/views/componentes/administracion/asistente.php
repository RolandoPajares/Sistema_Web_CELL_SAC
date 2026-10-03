<?php
/**
 * @var array<array-key, mixed> $consultasRapidas
 */ ?>
<div
    class="admin-assistant"
    data-admin-assistant
    data-endpoint="<?= e(url_interna('admin/assistant')) ?>"
>
    <!-- Panel principal del asistente -->
    <section
        class="admin-assistant-panel"
        data-admin-assistant-panel
        hidden
        aria-label="MD Assistant"
    >
        <!-- Cabecera del panel -->
        <header>
            <span class="admin-assistant-avatar">
                <i class="bi bi-robot"></i>
            </span>

            <div>
                <strong>MD Assistant</strong>
                <small>Consulta datos reales del negocio</small>
            </div>

            <button
                type="button"
                data-admin-assistant-close
                aria-label="Cerrar asistente"
            >
                <i class="bi bi-x-lg"></i>
            </button>
        </header>

        <!-- Contenedor del historial de mensajes -->
        <div
            class="admin-assistant-messages"
            data-admin-assistant-messages
        >
            <article>
                <span>
                <i class="bi bi-robot"></i>
                </span>
                <p>
                Hola. Puedo resumir ventas, productos, inventario, stock bajo, pedidos y reportes con la información disponible en MySQL.
                </p>
            </article>
        </div>

        <!-- Botones de consultas rápidas -->
        <div class="admin-assistant-prompts">
            <?php foreach ($consultasRapidas as $consultaRapida): ?>
            <button
                type="button"
                data-admin-assistant-prompt="<?= e($consultaRapida) ?>"
                >
                <?= e($consultaRapida) ?>
            </button>
            <?php endforeach; ?>
        </div>

        <!-- Formulario de entrada de texto -->
        <form data-admin-assistant-form>
            <input
                type="text"
                maxlength="300"
                placeholder="Escribe tu consulta..."
                aria-label="Consulta para MD Assistant"
            >
            <button
                type="submit"
                aria-label="Enviar consulta"
            >
                <i class="bi bi-send-fill"></i>
            </button>
        </form>
    </section>

    <!-- Botón lanzador flotante -->
    <button
        class="admin-assistant-launcher"
        type="button"
        data-admin-assistant-toggle
        aria-label="Abrir MD Assistant"
    >
        <i class="bi bi-robot"></i>
        <span></span>
    </button>
</div>
