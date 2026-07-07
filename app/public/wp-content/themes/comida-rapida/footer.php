<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <!-- Brand Column -->
            <div class="footer-brand">
                <h3><i class="fa-solid fa-burger" style="color: var(--color-amber);"></i> Comida<span style="color: var(--color-amber);">Rápida</span></h3>
                <p>Las mejores hamburguesas, pizzas y combos de la ciudad elaborados con ingredientes 100% frescos y de calidad premium. ¡Haz tu pedido ahora!</p>
                <div class="social-links">
                    <a href="#" class="social-link" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" class="social-link" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#" class="social-link" aria-label="Twitter"><i class="fa-brands fa-x-twitter"></i></a>
                    <a href="#" class="social-link" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
                </div>
            </div>
            
            <!-- Hours & Location Column -->
            <div>
                <h4 class="footer-title">Horarios y Ubicación</h4>
                <ul class="footer-links" style="color: var(--text-secondary); font-size: 0.95rem;">
                    <li style="margin-bottom: 12px;"><i class="fa-solid fa-clock" style="color: var(--color-amber); margin-right: 8px;"></i> Lunes a Sábado:<br><span style="padding-left: 24px;">11:00 AM - 11:00 PM</span></li>
                    <li style="margin-bottom: 12px;"><i class="fa-solid fa-clock" style="color: var(--color-amber); margin-right: 8px;"></i> Domingos:<br><span style="padding-left: 24px;">12:00 PM - 10:00 PM</span></li>
                    <li><i class="fa-solid fa-location-dot" style="color: var(--color-amber); margin-right: 8px;"></i> Vía España, Edificio Central, Local 4, Ciudad de Panamá</li>
                </ul>
            </div>
            
            <!-- Quick Links Column -->
            <div>
                <h4 class="footer-title">Enlaces Rápidos</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo esc_url(home_url('/')); ?>">Inicio</a></li>
                    <li><a href="<?php echo esc_url(add_query_arg('view', 'menu', home_url('/'))); ?>">Menú de Comidas</a></li>
                    <li><a href="<?php echo esc_url(home_url('/#combos')); ?>">Combos Especiales</a></li>
                    <li><a href="<?php echo esc_url(home_url('/#promociones')); ?>">Promociones</a></li>
                    <li><a href="<?php echo esc_url(add_query_arg('view', 'compra', home_url('/'))); ?>">Hacer Pedido en Línea</a></li>
                    <li><a href="<?php echo esc_url(add_query_arg('view', 'contacto', home_url('/'))); ?>">Contacto & Ubicación</a></li>
                </ul>
            </div>
        </div>
        
        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div class="footer-copy">
                &copy; <?php echo date('Y'); ?> Comida Rápida. Proyecto 2 - Comercio Electrónico.
            </div>
            <div class="footer-authors">
                Desarrollado por: <span style="color: var(--color-amber);">Grupo de 2 Estudiantes (4° GDS)</span>
            </div>
        </div>
    </div>
</footer>

<!-- Success Modal Overlay -->
<div class="success-overlay" id="order-success-modal">
    <div class="success-card">
        <div class="success-icon-wrapper">
            <i class="fa-solid fa-check"></i>
        </div>
        <h3 class="success-title">¡Pedido Recibido!</h3>
        <p class="success-desc">Tu pedido ficticio ha sido registrado exitosamente en el panel de WordPress para revisión del docente.</p>
        
        <div class="success-details">
            <div class="success-detail-row">
                <span>ID del Pedido:</span>
                <span id="modal-order-id" style="font-weight:700;">#0000</span>
            </div>
            <div class="success-detail-row">
                <span>Subtotal:</span>
                <span id="modal-order-subtotal">$0.00</span>
            </div>
            <div class="success-detail-row">
                <span>Costo de Envío:</span>
                <span id="modal-order-delivery">$0.00</span>
            </div>
            <div class="success-detail-row">
                <span>Total General:</span>
                <span id="modal-order-total" style="color: var(--color-green); font-weight:700;">$0.00</span>
            </div>
        </div>
        
        <button class="btn btn-primary" id="close-success-modal-btn" style="width:100%;">Aceptar y Volver</button>
    </div>
</div>

<?php wp_footer(); ?>
</body>
</html>
