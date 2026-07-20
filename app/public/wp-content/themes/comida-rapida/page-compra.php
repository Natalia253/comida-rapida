<?php
/**
 * Template Name: Página Compra
 * Description: Vista del checkout y confirmación de pedido.
 */

// Detectar si es página de confirmación (viene del redirect de WooCommerce)
$es_confirmacion = isset($_GET['confirmado']) && $_GET['confirmado'] === '1' && isset($_GET['order_id']);

// Validar la order_key si es confirmación para mayor seguridad
if ($es_confirmacion) {
    $order_id  = intval($_GET['order_id']);
    $order_key = isset($_GET['order_key']) ? sanitize_text_field($_GET['order_key']) : '';
    $order     = wc_get_order($order_id);
    if (!$order || $order->get_order_key() !== $order_key) {
        $es_confirmacion = false;
    }
}

// Redirigir al login solo si no está logueado Y no es confirmación
if (!$es_confirmacion && !comida_rapida_is_logged_in()) {
    wp_safe_redirect(add_query_arg(array('view' => 'login', 'notice' => 'compra_requiere_login'), home_url('/')));
    exit;
}
?>

<!-- SECCIÓN DE COMPRA / CONFIRMACIÓN -->
<section class="section-padding order-section" id="hacer-pedido">
    <div class="container" style="max-width: 900px;">

        <?php if ($es_confirmacion && isset($order) && $order) : ?>
            <!-- ──── VISTA DE CONFIRMACIÓN ──── -->
            <div class="section-header" style="text-align: center; padding-bottom: 12px;">
                <div style="font-size: 4rem; color: var(--color-green); margin-bottom: 12px;">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <span class="section-subtitle" style="background: rgba(16, 185, 129, 0.08); color: var(--color-green);">¡Éxito!</span>
                <h2 class="section-title">¡Pedido Confirmado!</h2>
                <p class="section-desc">Tu orden <strong>#<?php echo $order->get_id(); ?></strong> ha sido registrada correctamente.<br>¡Gracias por tu compra en <strong>La Barra</strong>!</p>
            </div>

            <!-- Resumen del pedido -->
            <div style="background: var(--bg-primary); border: var(--border-main); border-radius: 16px; padding: 28px; margin-top: 24px; box-shadow: var(--shadow-sm);">
                <h3 style="margin: 0 0 20px; font-size: 1rem; color: var(--text-primary); font-weight: 700;">
                    <i class="fa-solid fa-receipt" style="color: var(--color-amber); margin-right: 8px;"></i>Resumen del Pedido
                </h3>
                <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-main);">
                            <th style="text-align: left; padding: 8px 4px; color: var(--text-secondary); font-weight: 600;">Producto</th>
                            <th style="text-align: center; padding: 8px 4px; color: var(--text-secondary); font-weight: 600;">Cant.</th>
                            <th style="text-align: right; padding: 8px 4px; color: var(--text-secondary); font-weight: 600;">Precio</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order->get_items() as $item) : ?>
                        <tr style="border-bottom: 1px solid var(--border-main);">
                            <td style="padding: 12px 4px; color: var(--text-primary); font-weight: 500;"><?php echo esc_html($item->get_name()); ?></td>
                            <td style="padding: 12px 4px; text-align: center; color: var(--text-secondary);">x<?php echo $item->get_quantity(); ?></td>
                            <td style="padding: 12px 4px; text-align: right; color: var(--text-primary); font-weight: 600;">$<?php echo number_format($item->get_total(), 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" style="padding: 16px 4px 0; font-weight: 700; color: var(--text-primary); font-size: 1.05rem;">Total</td>
                            <td style="padding: 16px 4px 0; text-align: right; font-weight: 700; color: var(--color-amber); font-size: 1.2rem;">$<?php echo number_format($order->get_total(), 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Botón volver al menú -->
            <div style="text-align: center; margin-top: 32px;">
                <a href="<?php echo esc_url(add_query_arg('view', 'menu', home_url('/'))); ?>" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-utensils"></i> Seguir Comprando
                </a>
                <a href="<?php echo esc_url(add_query_arg('view', 'login', home_url('/'))); ?>" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; margin-left: 12px;">
                    <i class="fa-solid fa-user"></i> Ver Mi Perfil
                </a>
            </div>

        <?php else : ?>
            <!-- ──── VISTA DE CHECKOUT ──── -->
            <div class="section-header">
                <span class="section-subtitle">Realiza tu Compra</span>
                <h2 class="section-title">Finalizar Pedido</h2>
                <p class="section-desc">Personaliza tus datos de contacto y completa tu pago.</p>
            </div>

            <div class="wc-checkout-wrapper" style="background: transparent; border: none; padding: 0; box-shadow: none;">
                <?php
                if (class_exists('WooCommerce')) {
                    echo do_shortcode('[woocommerce_checkout]');
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

        <?php endif; ?>

    </div>
</section>
