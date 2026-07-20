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
                <h2 class="section-title">Pedido Confirmado</h2>
                <p class="section-desc">Tu orden ha sido registrada correctamente. ¡Gracias por tu compra en La Barra!</p>
            <?php else : ?>
                <span class="section-subtitle">Realiza tu Compra</span>
                <h2 class="section-title">Finalizar Pedido</h2>
                <p class="section-desc">Personaliza tus datos de contacto y completa tu pago.</p>
            <?php endif; ?>
        </div>
        
        <div class="wc-checkout-wrapper" style="background: transparent; border: none; padding: 0; box-shadow: none;">
            <?php
            if (class_exists('WooCommerce')) {
                if (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('order-received')) {
                    // Mostrar confirmación del pedido
                    echo do_shortcode('[woocommerce_order_review]');
                    the_content();
                } else {
                    echo do_shortcode('[woocommerce_checkout]');
                }
            } else {
                echo '<p>El plugin WooCommerce no está activo.</p>';
            }
            ?>
        </div>

        <!-- NOTIFICACIÓN DE CUMPLIMIENTO PCI DSS -->
        <br>
        <div style="max-width: 900px; margin: 0 auto;">
            <div class="pci-compliance-notice" style="margin-top: 4px; padding: 18px 22px; border: var(--border-main); border-radius: 12px; background: var(--bg-primary); display: flex; align-items: flex-start; gap: 15px; box-shadow: var(--shadow-sm);">
                <div style="font-size: 1.8rem; color: var(--color-green); margin-top: 2px; flex-shrink: 0;">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div style="font-size: 0.85rem; color: var(--text-secondary); line-height: 1.55;">
                    <strong style="color: var(--text-primary); display: block; margin-bottom: 4px; font-weight: 700;">Conexión Segura &amp; Cumplimiento PCI DSS</strong>
                    Este sitio implementa los estándares <strong>PCI DSS</strong>. La transmisión de datos está cifrada con SSL/TLS. Las transacciones se realizan mediante pasarelas tokenizadas; la información de tarjetas nunca se almacena en nuestros servidores.
                </div>
            </div>
        </div>
    </div>
</section>
