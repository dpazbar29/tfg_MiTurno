<template>
    <footer class="footer" role="contentinfo">
        <div class="footer__container">
            <div class="footer__brand-section">
                <RouterLink class="footer__brand" to="/">
                    Mi Turno
                </RouterLink>

                <p class="footer__tagline">
                    Tu peluquería de confianza en San Fernando
                </p>
            </div>

            <div class="footer__info-grid">
                <div class="footer__info-block">
                    <h3 class="footer__block-title">Horario</h3>
                    <p>Lunes a viernes: 09:30 - 19:30</p>
                    <p>Sábado: 10:00 - 14:00</p>
                </div>

                <div class="footer__info-block">
                    <h3 class="footer__block-title">Ubicación</h3>
                    <p>Calle Ejemplo, 12</p>
                    <p>San Fernando, Cádiz</p>
                </div>

                <div class="footer__info-block">
                    <h3 class="footer__block-title">Contacto</h3>
                    <p>600 000 000</p>
                    <p>contacto@peluqueria.es</p>
                </div>
            </div>

            <div class="footer__bottom">
                <p class="footer__copyright">
                    © 2026 Mi Turno. Peluquería en San Fernando.
                </p>

                <nav class="footer__nav" aria-label="Enlaces secundarios">
                    <RouterLink to="/privacidad" class="footer__link">Privacidad</RouterLink>
                    <RouterLink to="/cookies" class="footer__link">Cookies</RouterLink>
                    <RouterLink to="/terminos" class="footer__link">Términos</RouterLink>
                </nav>
            </div>
        </div>
    </footer>
</template>