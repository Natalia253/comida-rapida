<?php
/**
 * Template Name: Página Compra
 * Description: Vista para ingresar datos del cliente, método de entrega y enviar el pedido ficticio.
 */

if (!comida_rapida_is_logged_in()) {
    wp_safe_redirect(add_query_arg(array('view' => 'login', 'notice' => 'compra_requiere_login'), home_url('/')));
    exit;
}
?>

<!-- SECCIÓN DE COMPRA / FORMULARIO -->
<section class="section-padding order-section" id="hacer-pedido">
    <div class="container" style="max-width: 900px;">
        <div class="section-header">
            <?php if (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('order-received')) : ?>
                <span class="section-subtitle" style="background: rgba(16, 185, 129, 0.08); color: var(--color-green);">¡Éxito!</span>
                <h2 class="section-title">Pedido Recibido</h2>
                <p class="section-desc">Tu orden ha sido registrada correctamente. ¡Gracias por tu compra!</p>
            <?php else : ?>
                <span class="section-subtitle">Realiza tu Compra</span>
                <h2 class="section-title">Finalizar Pedido</h2>
                <p class="section-desc">Personaliza tus datos de contacto y completa tu pago.</p>
            <?php endif; ?>
        </div>
        
        <div class="wc-checkout-wrapper" style="background: var(--bg-secondary); border: var(--border-main); padding: 30px; border-radius: 8px;">
            <?php
            if (class_exists('WooCommerce')) {
                echo do_shortcode('[woocommerce_checkout]');
            } else {
                echo '<p>El plugin WooCommerce no está activo.</p>';
            }
            ?>
        </div>

        <!-- NOTIFICACIÓN DE CUMPLIMIENTO PCI DSS -->
        <div class="pci-compliance-notice" style="margin-top: 20px; padding: 20px; border: var(--border-main); border-radius: 12px; background: var(--bg-primary); display: flex; align-items: flex-start; gap: 15px; box-shadow: var(--shadow-sm);">
            <div style="font-size: 2rem; color: var(--color-green); margin-top: 2px;">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div style="font-size: 0.88rem; color: var(--text-secondary); line-height: 1.5;">
                <strong style="color: var(--text-primary); display: block; margin-bottom: 4px; font-weight: 700;">Conexión Segura & Cumplimiento PCI DSS</strong>
                Este sitio web implementa los estándares de seguridad de datos de la industria de tarjetas de pago (<strong>PCI DSS</strong>). La transmisión de tus datos está cifrada con SSL/TLS. Toda transacción se realiza mediante pasarelas de pago externas tokenizadas; la información confidencial de tarjetas de crédito nunca se almacena en nuestros servidores.
            </div>
        </div>
    </div>
</section>
