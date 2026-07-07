<?php
/**
 * Template Name: Página Contacto
 * Description: Vista para mostrar información de contacto de la sucursal y mapa interactivo simulación.
 */
?>

<!-- 6. CONTACTO & MAPA -->
<section class="section-padding" id="contacto" style="background-color: #fbf9f6; border-top: 1px solid rgba(120,113,108,0.08);">
    <div class="container">
        <div class="section-header">
            <span class="section-subtitle">Ubicación</span>
            <h2 class="section-title">Visítanos o Contáctanos</h2>
            <p class="section-desc">Estamos ubicados en una zona accesible de la ciudad. También puedes llamarnos para hacer tus reservas físicas.</p>
        </div>
        
        <div class="contact-grid">
            <!-- Información de Contacto -->
            <div>
                <ul class="contact-info-list">
                    <li class="contact-info-item">
                        <div class="contact-info-icon"><i class="fa-solid fa-location-dot"></i></div>
                        <div>
                            <h3 class="contact-info-title">Nuestra Sucursal</h3>
                            <p class="contact-info-text">Vía España, Edificio Central, Local 4, Ciudad de Panamá</p>
                        </div>
                    </li>
                    <li class="contact-info-item">
                        <div class="contact-info-icon"><i class="fa-solid fa-phone"></i></div>
                        <div>
                            <h3 class="contact-info-title">Línea Telefónica</h3>
                            <p class="contact-info-text">+507 334-5566 / +507 6677-8899</p>
                        </div>
                    </li>
                    <li class="contact-info-item">
                        <div class="contact-info-icon"><i class="fa-solid fa-envelope"></i></div>
                        <div>
                            <h3 class="contact-info-title">Correo Electrónico</h3>
                            <p class="contact-info-text">contacto@comidarapidaproyecto.com</p>
                        </div>
                    </li>
                </ul>
                
                <div class="product-card" style="padding: 24px; border: 1px dashed var(--glass-border); border-radius: 16px;">
                    <h4 style="font-weight: 700; margin-bottom: 8px; color: var(--color-amber);"><i class="fa-solid fa-circle-exclamation"></i> Información Académica</h4>
                    <p style="font-size: 0.88rem; color: var(--text-secondary); line-height: 1.5;">Este sitio web es un proyecto escolar simulado para la asignatura <strong>Comercio Electrónico</strong> en el Cuarto Año en Gestión de Desarrollo de Software. No se realizan cobros ni despachos reales de comida.</p>
                </div>
            </div>
            
            <!-- Mapa Interactivo Simulado (Offline-Friendly y Ultra Estético) -->
            <div class="map-container">
                <div class="simulated-map">
                    <!-- Carreteras Simuladas en Gris Oscuro -->
                    <div class="simulated-map-road road-h"></div>
                    <div class="simulated-map-road road-v"></div>
                    
                    <!-- Marcador del Local -->
                    <div class="map-marker">
                        <i class="fa-solid fa-location-dot"></i>
                        <div class="map-marker-pulse"></div>
                    </div>
                    
                    <!-- Caja de Popup del Local -->
                    <div class="map-card-popup">
                        <h4 class="map-popup-title"><i class="fa-solid fa-utensils"></i> Comida Rápida</h4>
                        <p class="map-popup-desc">Vía España. Local N° 4.<br>¡Estamos frente a la estación de metro!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
