<?php

/** Pie de página público reutilizable y único. */
?>
<footer>
    <div class="container footer-grid">
        <div>
            <h3>MD Technology Digital Cell</h3>
            <p>Celulares y audífonos originales. Tecnología, confianza y atención cercana en Bagua.</p>
        </div>
        <div>
            <h4>Enlaces</h4>
            <a href="<?= e(url('catalog')) ?>">Catálogo</a>
            <a href="<?= e(url('smart/recommend')) ?>">SmartMatch</a>
            <a href="<?= e(url('smart/compare')) ?>">Comparar</a>
            <a href="<?= e(url('mayorista')) ?>">Mayorista</a>
            <a href="<?= e(url('smart/assistant')) ?>">Asistente IA</a>
            <a href="<?= e(url('about')) ?>">Nosotros</a>
            <a href="<?= e(url('contact')) ?>">Contacto</a>
        </div>
        <div>
            <h4>Ubicación</h4>
            <p><?= e(config('app.address')) ?><br>Cerca de Plásticos Jireh</p>
        </div>
        <div>
            <h4>Horario</h4>
            <p>Lun-Sáb<br>Horario comercial</p>
        </div>
    </div>
    <div class="footer-bottom">&copy; 2026 MD Technology Digital Cell S.A.C. &middot; Proyecto académico</div>
</footer>