<script setup>
import { RouterLink } from 'vue-router'
</script>

<template>
    <main class="home">
        <section class="home__hero">
            <div class="home__container home__hero-grid">
                <div class="home__hero-content">
                    <p class="home__eyebrow"></p>
                    <h1 class="home__title">
                        Reserva tu cita en una peluquería pensada para que salgas como quieres verte
                    </h1>
                    <p class="home__subtitle">
                        Corte, color, peinado y tratamientos con atención cercana, resultados cuidados
                        y reserva online rápida.
                    </p>

                    <div class="home__actions">
                        <RouterLink :to="{ name: 'register' }" class="home__button home__button--primary">
                            Reservar cita
                        </RouterLink>

                        <RouterLink :to="{ name: 'servicios' }" class="home__button home__button--secondary">
                            Ver servicios
                        </RouterLink>
                    </div>
                </div>

                <aside class="home__hero-card">
                    <div class="home__hero-card-inner">
                        <p class="home__card-label">Reserva online</p>
                        <h2 class="home__card-title">Elige servicio, día y hora en pocos pasos</h2>
                        <ul class="home__card-list">
                            <li>Corte y peinado</li>
                            <li>Coloración y mechas</li>
                            <li>Tratamientos capilares</li>
                            <li>Consulta de disponibilidad</li>
                        </ul>
                    </div>
                </aside>
            </div>
        </section>

        <section class="home__section">
            <div class="home__container">
                <header class="home__section-heading">
                    <p class="home__eyebrow">Servicios destacados</p>
                    <h2 class="home__section-title">Lo que más reservan nuestras clientas</h2>
                </header>

                <div class="home__services">
                    <article class="home__service-card">
                        <h3 class="home__service-title">Corte</h3>
                        <p class="home__service-text">
                            Corte adaptado a tu estilo y al cuidado natural de tu cabello.
                        </p>
                    </article>

                    <article class="home__service-card">
                        <h3 class="home__service-title">Coloración</h3>
                        <p class="home__service-text">
                            Color, matiz y acabado profesional con asesoramiento personalizado.
                        </p>
                    </article>

                    <article class="home__service-card">
                        <h3 class="home__service-title">Barbas</h3>
                        <p class="home__service-text">
                            Recortes y peinados para un resultado mas limpio.
                        </p>
                    </article>

                    <article class="home__service-card">
                        <h3 class="home__service-title">Tratamientos</h3>
                        <p class="home__service-text">
                            Hidratación, reparación y cuidado para mejorar la salud del cabello.
                        </p>
                    </article>
                </div>
            </div>
        </section>

        <section class="home__section">
            <div class="home__container">
                <header class="home__section-heading">
                    <p class="home__eyebrow">Por qué elegirnos</p>
                    <h2 class="home__section-title">Una experiencia cercana y cuidada</h2>
                </header>

                <div class="home__benefits">
                    <article class="home__benefit">
                        <h3 class="home__benefit-title">Atención personalizada</h3>
                        <p class="home__benefit-text">
                            Cada cita se adapta al tipo de cabello, al estilo y al resultado que buscas.
                        </p>
                    </article>

                    <article class="home__benefit">
                        <h3 class="home__benefit-title">Reserva sencilla</h3>
                        <p class="home__benefit-text">
                            Consulta disponibilidad y reserva tu cita online de forma rápida.
                        </p>
                    </article>

                    <article class="home__benefit">
                        <h3 class="home__benefit-title">Resultados cuidados</h3>
                        <p class="home__benefit-text">
                            Trabajos pensados para que el acabado sea bonito, favorecedor y duradero.
                        </p>
                    </article>
                </div>
            </div>
        </section>

        <section class="home__section">
            <div class="home__container">
                <header class="home__section-heading">
                    <p class="home__eyebrow">Inspírate</p>
                    <h2 class="home__section-title">Algunos estilos y acabados</h2>
                </header>

                <div class="home__gallery">
                    <div class="home__gallery-item">
                        <img 
                            class="home__gallery-image"
                            src="../assets/media/fade.webp"
                            alt="Corte fade realizado en la peluqería"
                        >
                    </div>
                    <div class="home__gallery-item">
                        <img 
                            class="home__gallery-image"
                            src="../assets/media/barba.jpg"
                            alt="Corte fade realizado en la peluqería"
                        >
                    </div>
                    <div class="home__gallery-item">
                        <img 
                            class="home__gallery-image"
                            src="../assets/media/peinado.jpg"
                            alt="Corte fade realizado en la peluqería"
                        >
                    </div>
                    <div class="home__gallery-item">
                        <img 
                            class="home__gallery-image"
                            src="../assets/media/peinado2.jpg"
                            alt="Corte fade realizado en la peluqería"
                        >
                    </div>
                </div>
            </div>
        </section>

        <section class="home__section">
            <div class="home__container home__info-grid">
                <div class="home__info-block">
                    <p class="home__eyebrow">Información</p>
                    <h2 class="home__section-title">Todo lo que necesitas antes de venir</h2>
                    <p class="home__info-text">
                        Consulta el horario, la ubicación y reserva tu cita cuando mejor te venga.
                    </p>
                </div>

                <div class="home__info-card">
                    <h3 class="home__info-title">Horario</h3>
                    <p>Lunes a viernes: 09:30 - 19:30</p>
                    <p>Sábado: 10:00 - 14:00</p>
                </div>

                <div class="home__info-card">
                    <h3 class="home__info-title">Ubicación</h3>
                    <p>Calle Ejemplo, 12</p>
                    <p>San Fernando, Cádiz</p>
                </div>

                <div class="home__info-card">
                    <h3 class="home__info-title">Contacto</h3>
                    <p>600 000 000</p>
                    <p>contacto@peluqueria.es</p>
                </div>
            </div>
        </section>

        <section class="home__cta">
            <div class="home__container home__cta-box">
                <div>
                    <h2 class="home__section-title">Reserva online en pocos pasos</h2>
                    <p class="home__subtitle">
                        Elige el servicio que necesitas y encuentra el hueco que mejor te encaje.
                    </p>
                </div>

                <RouterLink :to="{ name: 'register' }" class="home__button home__button--primary">
                    Reservar ahora
                </RouterLink>
            </div>
        </section>
    </main>
</template>