<?php
/**
 * Template Name: Página Carrito
 * Description: Vista dedicada para revisar el carrito de compras y proceder al pago.
 */
?>

<!-- SECCIÓN DEL CARRITO INDEPENDIENTE -->
<section class="section-padding" id="seccion-carrito">
    <div class="container" style="max-width: 700px;">
        <div class="section-header">
            <span class="section-subtitle">Tu Selección</span>
            <h2 class="section-title">Tu Carrito de Pedidos</h2>
            <p class="section-desc">Revisa y modifica los productos agregados antes de proceder a la compra.</p>
        </div>
        
        <div class="cart-card">
            <h3 class="cart-title">
                <span><i class="fa-solid fa-shopping-basket" style="color: var(--color-amber);"></i> Resumen de Pedido</span>
            </h3>
            
            <!-- Contenedor de items en el carrito -->
            <div class="cart-items" id="cart-items-container">
                <?php
                if ( class_exists( 'WooCommerce' ) && WC()->cart ) {
                    $cart_items = WC()->cart->get_cart();
                    if ( empty( $cart_items ) ) {
                        ?>
                        <div class="empty-cart-message">
                            <i class="fa-solid fa-cart-arrow-down" style="font-size: 2.5rem; opacity: 0.3;"></i>
                            <p>Tu carrito está vacío. Agrega algunos combos y comidas del menú.</p>
                        </div>
                        <?php
                    } else {
                        foreach ( $cart_items as $cart_item_key => $cart_item ) {
                            $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                            $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

                            if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
                                $product_name      = $_product->get_name();
                                $product_price     = $_product->get_price();
                                $demo_image        = get_post_meta($product_id, '_image_demo_file', true);
                                
                                if (has_post_thumbnail($product_id)) {
                                    $image_url = get_the_post_thumbnail_url($product_id, 'thumbnail');
                                } elseif ($demo_image) {
                                    $image_url = get_template_directory_uri() . '/assets/images/' . $demo_image;
                                } else {
                                    $image_url = get_template_directory_uri() . '/assets/images/burger_gourmet.png';
                                }
                                ?>
                                <div class="cart-item" data-key="<?php echo esc_attr($cart_item_key); ?>">
                                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($product_name); ?>" class="cart-item-img">
                                    <div class="cart-item-info">
                                        <h4 class="cart-item-name"><?php echo esc_html($product_name); ?></h4>
                                        <span class="cart-item-price">$<?php echo number_format((float)$product_price, 2); ?> c/u</span>
                                    </div>
                                    <div class="cart-qty-controls">
                                        <button type="button" class="qty-btn dec-qty-wc" data-key="<?php echo esc_attr($cart_item_key); ?>" data-qty="<?php echo esc_attr($cart_item['quantity']); ?>">-</button>
                                        <span class="qty-val"><?php echo esc_html($cart_item['quantity']); ?></span>
                                        <button type="button" class="qty-btn inc-qty-wc" data-key="<?php echo esc_attr($cart_item_key); ?>" data-qty="<?php echo esc_attr($cart_item['quantity']); ?>">+</button>
                                    </div>
                                    <i class="fa-solid fa-trash-can cart-item-remove-wc" data-key="<?php echo esc_attr($cart_item_key); ?>"></i>
                                </div>
                                <?php
                            }
                        }
                    }
                }
                ?>
            </div>
            
            <!-- Resumen de Costos -->
            <div class="cart-summary" style="<?php echo (class_exists('WooCommerce') && WC()->cart && WC()->cart->is_empty()) ? 'display: none;' : ''; ?>">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span id="summary-subtotal">$<?php echo class_exists('WooCommerce') && WC()->cart ? number_format((float)WC()->cart->get_subtotal(), 2) : '0.00'; ?></span>
                </div>
                <div class="summary-row total">
                    <span>Total Estimado:</span>
                    <span id="summary-total">$<?php echo class_exists('WooCommerce') && WC()->cart ? number_format((float)WC()->cart->total, 2) : '0.00'; ?></span>
                </div>
            </div>

            <!-- Botón para Proceder a la Compra -->
            <div class="cart-actions-checkout" style="margin-top: 24px; <?php echo (class_exists('WooCommerce') && WC()->cart && WC()->cart->is_empty()) ? 'display: none;' : ''; ?>">
                <a href="<?php echo esc_url(add_query_arg('view', 'compra', home_url('/'))); ?>" class="btn btn-primary" id="btn-proceder-compra" style="width: 100%;">
                    Proceder al Pago <i class="fa-solid fa-credit-card"></i>
                </a>
            </div>
        </div>
    </div>
</section>
