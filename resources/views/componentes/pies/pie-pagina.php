<?php

/** Pie de página público reutilizable y único. 
 * @var string $direccionEmpresa
 */
?>
<footer class="site-footer">
    <div class="container footer-grid">
        <section class="footer-brand">
            <a class="footer-brand-link" href="<?= e(url_interna()) ?>" aria-label="MD Technology Digital Cell, inicio">
                <span class="footer-brand-md" aria-hidden="true">M<span>D</span></span>
                <span class="footer-wordmark"><strong>Technology</strong><small>DIGITAL CELL</small></span>
            </a>
            <p>Celulares y accesorios originales con atención cercana en Bagua.</p>
            <div class="footer-social-links" aria-label="Redes sociales">

                <a
                class="footer-social-link"
                href="<?= e('https://www.facebook.com/profile.php?id=61593115485111&mibextid=wwXIfr&rdid=st8rrEn9SrNp9jtb&share_url=https%3A%2F%2Fwww.facebook.com%2Fshare%2F1F8PUAV59g%2F%3Fmibextid%3DwwXIfr#') ?>"
                target="_blank"
                rel="noopener noreferrer"
                aria-label="Facebook">
                <i class="bi bi-facebook" aria-hidden="true"></i>
                </a>

                <a
                class="footer-social-link"
                href="<?= e('https://www.tiktok.com/@technology.cell.d?_r=1&_t=ZS-9A7GAGdYiph') ?>"
                target="_blank"
                rel="noopener noreferrer"
                aria-label="TikTok">
                <i class="bi bi-tiktok" aria-hidden="true"></i>
                </a>

                <a
                class="footer-social-link"
                href="<?= e('https://www.instagram.com/celltechonology?stkn=MTVhbXdraG1pNHRiaQ%3D%3D&utm_source=qr') ?>"
                target="_blank"
                rel="noopener noreferrer"
                aria-label="Instagram">
                <i class="bi bi-instagram" aria-hidden="true"></i>
                </a>
            </div>
        </section>
        <nav class="footer-column" aria-label="Explora">
            <h2>Explora</h2>
            <a href="<?= e(url_interna('catalog')) ?>"><i class="bi bi-bag" aria-hidden="true"></i> Catálogo</a>
            <a href="<?= e(url_interna('smart/recommend')) ?>"><i class="bi bi-stars" aria-hidden="true"></i> SmartMatch</a>
            <a href="<?= e(url_interna('smart/compare')) ?>"><i class="bi bi-sliders" aria-hidden="true"></i> Comparador</a>
            <a href="<?= e(url_interna('about')) ?>"><i class="bi bi-people" aria-hidden="true"></i> Nosotros</a>
        </nav>
        <nav class="footer-column" aria-label="Ayuda">
            <h2>Ayuda</h2>
            <a href="<?= e(url_interna('contact')) ?>"><i class="bi bi-envelope" aria-hidden="true"></i> Contacto</a>
            <a href="<?= e(url_interna('contact') . '#contact-name') ?>"><i class="bi bi-journal-text" aria-hidden="true"></i> Libro de reclamaciones</a>
            <a href="<?= e(url_interna('smart/assistant')) ?>"><i class="bi bi-robot" aria-hidden="true"></i> Asistente IA</a>
            <a href="<?= e(url_interna('mayorista')) ?>"><i class="bi bi-truck" aria-hidden="true"></i> Compras mayoristas</a>
        </nav>
        <section class="footer-column footer-visit">
            <h2>Visítanos</h2>
            <p><i class="bi bi-geo-alt" aria-hidden="true"></i><span><?= e($direccionEmpresa) ?><br>Cerca de Plásticos Jireh</span></p>
            <p><i class="bi bi-clock" aria-hidden="true"></i><span>Lun-Sáb<br>Horario comercial</span></p>

            <a
                class="footer-map-link"
                href="https://www.google.com/maps/search/?api=1&amp;query=<?= e(rawurlencode($direccionEmpresa)) ?>"
                target="_blank"
                rel="noopener noreferrer">
                <i class="bi bi-geo-alt" aria-hidden="true"></i> Cómo llegar <i class="bi bi-arrow-right" aria-hidden="true"></i>
            </a>
        </section>
    </div>
    <div class="footer-bottom">
        <div class="container footer-bottom-inner">
            <span>&copy; 2026 MD Technology Digital Cell S.A.C.</span>
            <span>Bagua, Amazonas</span>
        </div>
    </div>
</footer>
