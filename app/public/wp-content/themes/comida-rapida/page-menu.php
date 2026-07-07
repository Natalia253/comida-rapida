<?php
/**
 * Template Name: Página Menú
 * Description: Vista para mostrar el menú completo y combos en un mismo lugar.
 */
?>

<!-- 1. SECCIÓN DE COMBOS DESTACADOS -->
<section class="section-padding" id="combos" style="background-color: var(--bg-secondary); border-bottom: 1px solid #cbd5e1;">
    <div class="container">
        <div class="section-header">
            <span class="section-subtitle">Combos Recomendados</span>
            <h2 class="section-title">¡Combos Especiales!</h2>
            <p class="section-desc">Diseñados para compartir o saciar el hambre más feroz al mejor precio. Perfectos para grupos o antojos dobles.</p>
        </div>
        
        <div class="combos-grid">
            <?php
            // Query para obtener productos de la categoría Combos
            $combo_args = array(
                'post_type' => 'product',
                'posts_per_page' => 4,
                'tax_query' => array(
                    array(
                        'taxonomy' => 'product_cat',
                        'field'    => 'slug',
                        'terms'    => 'combos',
                    ),
                ),
            );
            $combo_query = new WP_Query($combo_args);
            
            if ($combo_query->have_posts()) :
                while ($combo_query->have_posts()) : $combo_query->the_post();
                    $precio_reg = '';
                    $precio_comb = '';
                    
                    $product = wc_get_product(get_the_ID());
                    if ($product) {
                        $precio_reg = $product->get_regular_price();
                        $precio_comb = $product->get_sale_price();
                        if (empty($precio_comb)) {
                            $precio_comb = $product->get_price();
                        }
                    }
                    $demo_image = get_post_meta(get_the_ID(), '_image_demo_file', true);
                    
                    if (has_post_thumbnail()) {
                        $image_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
                    } elseif ($demo_image) {
                        $image_url = get_template_directory_uri() . '/assets/images/' . $demo_image;
                    } else {
                        $image_url = get_template_directory_uri() . '/assets/images/combo_pack.png';
                    }
                    ?>
                    <div class="combo-card">
                        <div class="combo-img-wrapper">
                            <img src="<?php echo esc_url($image_url); ?>" alt="<?php the_title_attribute(); ?>" class="combo-img" loading="lazy">
                            <?php if (!empty($precio_reg) && $precio_reg > $precio_comb) : 
                                $porcentaje = round((($precio_reg - $precio_comb) / $precio_reg) * 100);
                            ?>
                                <span class="combo-discount-badge">-<?php echo $porcentaje; ?>%</span>
                            <?php endif; ?>
                        </div>
                        <div class="combo-details">
                            <h3 class="combo-title"><?php the_title(); ?></h3>
                            <div class="product-meta combo-meta">
                                <span class="meta-item popular"><i class="fa-solid fa-fire"></i> Popular</span>
                                <span class="meta-divider">•</span>
                                <span class="meta-item rating"><i class="fa-solid fa-star"></i> 4.9</span>
                                <span class="meta-divider">•</span>
                                <span class="meta-item delivery"><i class="fa-solid fa-truck"></i> Envío Gratis</span>
                            </div>
                            <p class="combo-desc"><?php the_content(); ?></p>
                            <div class="combo-footer-row">
                                <div class="combo-price-block">
                                    <?php if (!empty($precio_reg) && $precio_reg > $precio_comb) : 
                                        $ahorro = $precio_reg - $precio_comb;
                                    ?>
                                        <div class="price-old-wrapper">
                                            <span class="combo-price-old">$<?php echo number_format((float)$precio_reg, 2); ?></span>
                                            <span class="combo-save-pill">Ahorra $<?php echo number_format((float)$ahorro, 2); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <span class="combo-price-new">$<?php echo number_format((float)$precio_comb, 2); ?></span>
                                </div>
                                <a href="?add-to-cart=<?php the_ID(); ?>" 
                                   class="btn btn-primary add-to-cart-btn ajax_add_to_cart add_to_cart_button combo-add-btn" 
                                   data-product_id="<?php the_ID(); ?>"
                                   aria-label="Añadir al Carrito">
                                    <span>Agregar</span> <i class="fa-solid fa-cart-plus"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php
                endwhile;
                wp_reset_postdata();
            else :
                echo '<p style="grid-column: 1/-1; text-align: center; color: var(--text-secondary);">No hay combos activos en este momento.</p>';
            endif;
            ?>
        </div>
    </div>
</section>

<!-- 2. MENÚ DE PRODUCTOS -->
<section class="section-padding" id="menu">
    <div class="container">
        <div class="section-header">
            <span class="section-subtitle">Categorías</span>
            <h2 class="section-title">Elige tus Antojos Favoritos</h2>
            <p class="section-desc">Selecciona de nuestra gran variedad de hamburguesas gourmet, pizzas artesanales crujientes y bebidas refrescantes.</p>
        </div>
        
        <!-- Filtros de Categorías -->
        <div class="menu-filters">
            <button class="filter-btn active" data-filter="all">Todos</button>
            <?php
            $terms = get_terms(array(
                'taxonomy' => 'product_cat',
                'hide_empty' => true,
            ));
            if (!is_wp_error($terms) && !empty($terms)) {
                foreach ($terms as $term) {
                    echo '<button class="filter-btn" data-filter="' . esc_attr($term->slug) . '">' . esc_html($term->name) . '</button>';
                }
            }
            ?>
        </div>
        
        <!-- Grid de Productos -->
        <div class="menu-grid" id="menu-items-grid">
            <?php
            $args = array(
                'post_type' => 'product',
                'posts_per_page' => -1,
                'orderby' => 'menu_order',
                'order' => 'ASC'
            );
            $query = new WP_Query($args);
            
            if ($query->have_posts()) :
                while ($query->have_posts()) : $query->the_post();
                    // Obtener precio y meta
                    $product = wc_get_product(get_the_ID());
                    if ($product) {
                        $precio = $product->get_regular_price();
                        $es_promo = $product->is_on_sale() ? '1' : '0';
                        $precio_promo = $product->get_sale_price();
                        if (empty($precio)) {
                            $precio = $product->get_price();
                        }
                    } else {
                        $precio = '';
                        $es_promo = '0';
                        $precio_promo = '';
                    }
                    $demo_image = get_post_meta(get_the_ID(), '_image_demo_file', true);
                    
                    // Obtener los slugs de categorías para filtrar en JS
                    $item_terms = get_the_terms(get_the_ID(), 'product_cat');
                    $filter_classes = array();
                    $primary_cat = '';
                    if ($item_terms && !is_wp_error($item_terms)) {
                        foreach ($item_terms as $t) {
                            $filter_classes[] = $t->slug;
                        }
                        $primary_cat = $item_terms[0]->slug;
                    }
                    $filter_class_str = implode(' ', $filter_classes);

                    // Determinar la imagen a usar
                    if (has_post_thumbnail()) {
                        $image_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
                    } elseif ($demo_image) {
                        $image_url = get_template_directory_uri() . '/assets/images/' . $demo_image;
                    } else {
                        if ($primary_cat === 'bebidas') {
                            $image_url = get_template_directory_uri() . '/assets/images/bebida_refresh.png';
                        } elseif ($primary_cat === 'pizzas') {
                            $image_url = get_template_directory_uri() . '/assets/images/pizza_pepperoni.png';
                        } elseif ($primary_cat === 'combos') {
                            $image_url = get_template_directory_uri() . '/assets/images/combo_pack.png';
                        } else {
                            $image_url = get_template_directory_uri() . '/assets/images/burger_gourmet.png';
                        }
                    }
                    
                    // Si es promo, usar precio promocional en la visualización
                    $precio_mostrado = ($es_promo == '1' && !empty($precio_promo)) ? $precio_promo : $precio;
                    ?>
                    <article class="product-card <?php echo esc_attr($filter_class_str); ?>">
                        <div class="product-img-wrapper">
                            <img src="<?php echo esc_url($image_url); ?>" alt="<?php the_title_attribute(); ?>" class="product-img" loading="lazy">
                            <?php if ($es_promo == '1') : ?>
                                <span class="product-tag">¡Promo!</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3 class="product-title"><?php the_title(); ?></h3>
                            <div class="product-meta" style="display: flex; gap: 8px; align-items: center; margin-bottom: 8px; font-size: 0.82rem; color: var(--text-secondary); font-weight: 500;">
                                <span><i class="fa-solid fa-star" style="color: #eab308;"></i> 4.8</span>
                                <span>•</span>
                                <span>20-30 min</span>
                                <span>•</span>
                                <span style="color: var(--color-green);"><i class="fa-solid fa-truck"></i> Gratis</span>
                            </div>
                            <div class="product-desc"><?php the_content(); ?></div>
                            <div class="product-footer">
                                <span class="product-price">$<?php echo number_format((float)$precio_mostrado, 2); ?></span>
                                <a href="?add-to-cart=<?php the_ID(); ?>" 
                                   class="add-to-cart-btn ajax_add_to_cart add_to_cart_button" 
                                   data-product_id="<?php the_ID(); ?>"
                                   aria-label="Agregar al pedido">
                                    <i class="fa-solid fa-plus"></i>
                                </a>
                            </div>
                        </div>
                    </article>
                    <?php
                endwhile;
                wp_reset_postdata();
            else :
                echo '<p style="grid-column: 1/-1; text-align: center; color: var(--text-secondary);">No se encontraron productos en el menú. Active el tema de nuevo para importar la demo.</p>';
            endif;
            ?>
        </div>
    </div>
</section>
