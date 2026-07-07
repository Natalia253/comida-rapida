<?php
/**
 * Funciones y definiciones del tema Comida Rápida
 */

// Iniciar sesión de PHP para el CAPTCHA matemático y verificar inactividad
add_action('init', 'comida_rapida_session_start', 1);
function comida_rapida_session_start() {
    if (!session_id() && !headers_sent()) {
        session_start();
    }

    // Control de inactividad de sesión (15 minutos para PCI DSS)
    if (isset($_SESSION['comida_rapida_cliente_id'])) {
        $now = time();
        $timeout = 900; // 15 minutos en segundos
        
        if (isset($_SESSION['comida_rapida_last_activity']) && ($now - $_SESSION['comida_rapida_last_activity']) > $timeout) {
            // Cerrar sesión
            unset($_SESSION['comida_rapida_cliente_id']);
            unset($_SESSION['comida_rapida_cliente_name']);
            unset($_SESSION['comida_rapida_cliente_email']);
            unset($_SESSION['comida_rapida_role']);
            unset($_SESSION['comida_rapida_last_activity']);
            
            if (!defined('DOING_AJAX') || !DOING_AJAX) {
                wp_safe_redirect(add_query_arg(array('view' => 'login', 'notice' => 'session_timeout'), home_url('/')));
                exit;
            }
        } else {
            $_SESSION['comida_rapida_last_activity'] = $now;
        }
    }
}

// Soporte básico del tema
add_action('after_setup_theme', 'comida_rapida_setup');
function comida_rapida_setup() {
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    add_theme_support('woocommerce');
    
    // Registrar menús de navegación
    register_nav_menus(array(
        'primary' => __('Menú Principal', 'comida-rapida'),
    ));
}

// Encolar estilos y scripts
add_action('wp_enqueue_scripts', 'comida_rapida_scripts');
function comida_rapida_scripts() {
    // Encolar FontAwesome para iconos
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', array(), '6.4.0');
    
    // Encolar hoja de estilos principal del tema
    wp_enqueue_style('comida-rapida-style', get_stylesheet_uri(), array(), '1.0.0');
    
    // Encolar script principal
    wp_enqueue_script('comida-rapida-js', get_template_directory_uri() . '/assets/js/main.js', array('jquery'), '1.0.0', true);
    
    // Generar un CAPTCHA aleatorio para la carga inicial
    if (!isset($_SESSION['comida_rapida_captcha_ans'])) {
        comida_rapida_generar_captcha();
    }
    
    // Localizar scripts con variables de AJAX y CAPTCHA
    wp_localize_script('comida-rapida-js', 'comidaRapidaData', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('comida_rapida_nonce'),
        'captcha_q' => $_SESSION['comida_rapida_captcha_q'] ?? '¿Cuánto es 5 + 3?',
        'home_url' => home_url('/')
    ));
}

// Función para generar una pregunta matemática simple para el CAPTCHA
function comida_rapida_generar_captcha() {
    $num1 = rand(1, 9);
    $num2 = rand(1, 9);
    $_SESSION['comida_rapida_captcha_q'] = "¿Cuánto es $num1 + $num2?";
    $_SESSION['comida_rapida_captcha_ans'] = $num1 + $num2;
}

// Encolar scripts específicos de WooCommerce
add_action('wp_enqueue_scripts', 'comida_rapida_wc_scripts', 15);
function comida_rapida_wc_scripts() {
    if (class_exists('WooCommerce')) {
        wp_enqueue_script('wc-add-to-cart');
    }
}

// Actualizar la cabecera cuando se añade al carrito por AJAX
add_filter('woocommerce_add_to_cart_fragments', 'comida_rapida_cart_fragments');
function comida_rapida_cart_fragments($fragments) {
    ob_start();
    ?>
    <span class="cart-badge" id="cart-badge-count"><?php echo esc_html(WC()->cart->get_cart_contents_count()); ?></span>
    <?php
    $fragments['#cart-badge-count'] = ob_get_clean();
    return $fragments;
}

// Inicialización de contenido demo
add_action('after_switch_theme', 'comida_rapida_insertar_contenido_demo');
function comida_rapida_insertar_contenido_demo() {
    if (!class_exists('WooCommerce')) {
        return;
    }
    
    // Asegurar que no re-creamos si ya existen productos
    $existing = get_posts(array('post_type' => 'product', 'posts_per_page' => 1));
    if (!empty($existing)) {
        return;
    }
    
    // Crear Categorías
    $hamburguesas_term = wp_insert_term('Hamburguesas', 'product_cat');
    $pizzas_term = wp_insert_term('Pizzas', 'product_cat');
    $bebidas_term = wp_insert_term('Bebidas', 'product_cat');
    $combos_term = wp_insert_term('Combos', 'product_cat');
    
    $hamb_id = !is_wp_error($hamburguesas_term) ? $hamburguesas_term['term_id'] : get_term_by('name', 'Hamburguesas', 'product_cat')->term_id;
    $pizz_id = !is_wp_error($pizzas_term) ? $pizzas_term['term_id'] : get_term_by('name', 'Pizzas', 'product_cat')->term_id;
    $bebi_id = !is_wp_error($bebidas_term) ? $bebidas_term['term_id'] : get_term_by('name', 'Bebidas', 'product_cat')->term_id;
    $comb_id = !is_wp_error($combos_term) ? $combos_term['term_id'] : get_term_by('name', 'Combos', 'product_cat')->term_id;

    // Productos Demo
    $productos_demo = array(
        array(
            'title' => 'Hamburguesa Monster',
            'desc' => 'Doble carne de res Angus (150g c/u), queso cheddar derretido, crujientes tiras de tocino ahumado, cebolla caramelizada y salsa secreta de la casa en pan brioche.',
            'precio' => '8.50',
            'promo' => '0',
            'precio_promo' => '0',
            'cat_id' => $hamb_id,
            'image' => 'burger_gourmet.png'
        ),
        array(
            'title' => 'Pizza Suprema Pepperoni',
            'desc' => 'Masa madurada por 48h, salsa pomodoro italiana, abundante queso mozzarella y una generosa porción de pepperoni artesanal curado, rociado con aceite de oliva.',
            'precio' => '12.00',
            'promo' => '0',
            'precio_promo' => '0',
            'cat_id' => $pizz_id,
            'image' => 'pizza_pepperoni.png'
        ),
        array(
            'title' => 'Super Combo Familiar',
            'desc' => '2 Hamburguesas Monster con papas fritas rústicas, 1 Pizza Pepperoni grande, 4 porciones de salsas y gaseosa de 1.5L para compartir.',
            'precio' => '28.00',
            'promo' => '1',
            'precio_promo' => '22.50',
            'cat_id' => $comb_id,
            'image' => 'combo_pack.png'
        ),
        array(
            'title' => 'Té Helado Refrescante',
            'desc' => 'Té helado de frutos rojos preparado de forma natural en casa, servido con rodajas de limón, hojitas de menta fresca y abundante hielo picado.',
            'precio' => '2.50',
            'promo' => '0',
            'precio_promo' => '0',
            'cat_id' => $bebi_id,
            'image' => 'bebida_refresh.png'
        ),
        array(
            'title' => 'Papas Fritas Rústicas',
            'desc' => 'Corte grueso con piel, fritas a doble cocción para máxima crocancia externa e interior suave, sazonadas con sal marina y pimentón ahumado.',
            'precio' => '3.50',
            'promo' => '0',
            'precio_promo' => '0',
            'cat_id' => $hamb_id,
            'image' => 'burger_gourmet.png' // reusar
        ),
        array(
            'title' => 'Combo Dúo Burger',
            'desc' => '2 Hamburguesas Clásicas (100g de carne, lechuga, tomate y mayonesa de ajo) acompañadas de una porción familiar de papas fritas y 2 gaseosas de 350ml.',
            'precio' => '15.90',
            'promo' => '1',
            'precio_promo' => '12.99',
            'cat_id' => $comb_id,
            'image' => 'combo_pack.png' // reusar
        )
    );
    
    foreach ($productos_demo as $prod) {
        $post_id = wp_insert_post(array(
            'post_title'   => $prod['title'],
            'post_content' => $prod['desc'],
            'post_status'  => 'publish',
            'post_type'    => 'product',
        ));
        
        if (!is_wp_error($post_id)) {
            // Guardar meta precio de WooCommerce
            update_post_meta($post_id, '_regular_price', $prod['precio']);
            if ($prod['promo'] == '1') {
                update_post_meta($post_id, '_sale_price', $prod['precio_promo']);
                update_post_meta($post_id, '_price', $prod['precio_promo']);
            } else {
                update_post_meta($post_id, '_price', $prod['precio']);
            }
            
            // Requerido por WooCommerce para listados y stock
            update_post_meta($post_id, '_stock_status', 'instock');
            update_post_meta($post_id, '_visibility', 'visible');
            
            // Asignar categoría
            wp_set_object_terms($post_id, $prod['cat_id'], 'product_cat');
            
            // Guardar nombre de imagen demo en un metadato para poder renderizar la imagen demo por defecto
            update_post_meta($post_id, '_image_demo_file', $prod['image']);
        }
    }
}

// Simplificar campos del checkout de WooCommerce
add_filter('woocommerce_checkout_fields', 'comida_rapida_simplify_checkout_fields');
function comida_rapida_simplify_checkout_fields($fields) {
    // Mantener sólo los campos esenciales para el tema de Comida Rápida
    $billing_keys = array('billing_first_name', 'billing_phone', 'billing_email', 'billing_address_1');
    $new_billing = array();
    foreach ($billing_keys as $key) {
        if (isset($fields['billing'][$key])) {
            $new_billing[$key] = $fields['billing'][$key];
            // Estilizar inputs para que sean de ancho completo
            $new_billing[$key]['class'] = array('form-row-wide');
        }
    }
    $fields['billing'] = $new_billing;
    
    // Desactivar campos de envío (usaremos billing_address_1 para envíos a domicilio)
    unset($fields['shipping']);
    unset($fields['order']); // Desactivar notas de pedido si se quiere simplificar aún más
    
    return $fields;
}

// Endpoint AJAX para actualizar cantidades en el carrito personalizado
add_action('wp_ajax_comida_rapida_actualizar_cantidad', 'comida_rapida_ajax_actualizar_cantidad');
add_action('wp_ajax_nopriv_comida_rapida_actualizar_cantidad', 'comida_rapida_ajax_actualizar_cantidad');
function comida_rapida_ajax_actualizar_cantidad() {
    check_ajax_referer('comida_rapida_nonce', 'nonce');

    if (!class_exists('WooCommerce') || !WC()->cart) {
        wp_send_json_error(array('message' => 'WooCommerce no está activo.'));
    }
    
    $cart_item_key = sanitize_text_field($_POST['cart_item_key'] ?? '');
    $new_qty = intval($_POST['qty'] ?? 1);
    
    if (empty($cart_item_key) || $new_qty < 0) {
        wp_send_json_error(array('message' => 'Datos inválidos.'));
    }
    
    if ($new_qty === 0) {
        WC()->cart->remove_cart_item($cart_item_key);
    } else {
        WC()->cart->set_quantity($cart_item_key, $new_qty);
    }
    
    // Devolver subtotales y totales actualizados
    wp_send_json_success(array(
        'subtotal' => '$' . number_format((float)WC()->cart->get_subtotal(), 2),
        'total' => '$' . number_format((float)WC()->cart->get_total(), 2),
        'badge' => WC()->cart->get_cart_contents_count()
    ));
}

// Endpoint AJAX para eliminar productos del carrito personalizado
add_action('wp_ajax_comida_rapida_eliminar_item', 'comida_rapida_ajax_eliminar_item');
add_action('wp_ajax_nopriv_comida_rapida_eliminar_item', 'comida_rapida_ajax_eliminar_item');
function comida_rapida_ajax_eliminar_item() {
    check_ajax_referer('comida_rapida_nonce', 'nonce');

    if (!class_exists('WooCommerce') || !WC()->cart) {
        wp_send_json_error(array('message' => 'WooCommerce no está activo.'));
    }
    
    $cart_item_key = sanitize_text_field($_POST['cart_item_key'] ?? '');
    
    if (empty($cart_item_key)) {
        wp_send_json_error(array('message' => 'Ítem inválido.'));
    }
    
    WC()->cart->remove_cart_item($cart_item_key);
    
    wp_send_json_success(array(
        'subtotal' => '$' . number_format((float)WC()->cart->get_subtotal(), 2),
        'total' => '$' . number_format((float)WC()->cart->get_total(), 2),
        'badge' => WC()->cart->get_cart_contents_count()
    ));
}

/**
 * Desvincular el login y experiencia del usuario del de WordPress estándar.
 */

// 1. Restringir el acceso al panel /wp-admin solo para usuarios que hayan iniciado sesión en WP y no sean administradores
add_action('admin_init', 'comida_rapida_restrict_admin_access');
function comida_rapida_restrict_admin_access() {
    if (defined('DOING_AJAX') && DOING_AJAX) {
        return;
    }
    // Si el usuario tiene sesión iniciada en WP pero no tiene permisos de administrador, redirigir al home
    if (is_user_logged_in() && !current_user_can('manage_options')) {
        wp_safe_redirect(home_url('/'));
        exit;
    }
}

// 5. Mostrar siempre la barra de administración de WordPress en el frontend (para todos los usuarios)
add_filter('show_admin_bar', '__return_true');

/**
 * 6. Crear tabla de base de datos para clientes independientes del sitio web
 */
add_action('init', 'comida_rapida_crear_tabla_clientes');
function comida_rapida_crear_tabla_clientes() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'comida_rapida_clientes';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        username varchar(60) NOT NULL,
        email varchar(100) NOT NULL,
        password varchar(255) NOT NULL,
        role varchar(20) DEFAULT 'cliente' NOT NULL,
        failed_attempts int(11) DEFAULT 0 NOT NULL,
        lockout_until datetime DEFAULT NULL NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY username (username),
        UNIQUE KEY email (email)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Insertar administrador preestablecido si no existe
    $admin = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE role = 'administrador'"
    ));
    if (!$admin) {
        $wpdb->insert(
            $table_name,
            array(
                'username' => 'admin',
                'email' => 'admin@comidarapida.com',
                'password' => password_hash('Admin#Comida2026!', PASSWORD_DEFAULT),
                'role' => 'administrador'
            ),
            array('%s', '%s', '%s', '%s')
        );
    } else {
        // Si existe pero tiene la contraseña por defecto vieja "admin123", actualizarla a la segura por PCI DSS
        if (password_verify('admin123', $admin->password)) {
            $wpdb->update(
                $table_name,
                array('password' => password_hash('Admin#Comida2026!', PASSWORD_DEFAULT)),
                array('id' => $admin->id),
                array('%s'),
                array('%d')
            );
        }
    }
}

/**
 * 7. Funciones auxiliares de autenticación basadas en sesión para el frontend
 */

// Comprobar si el cliente del sitio web ha iniciado sesión
function comida_rapida_is_logged_in() {
    return isset($_SESSION['comida_rapida_cliente_id']);
}

// Comprobar si el usuario en sesión es administrador
function comida_rapida_is_admin() {
    return isset($_SESSION['comida_rapida_role']) && $_SESSION['comida_rapida_role'] === 'administrador';
}

// Obtener los datos del cliente actual en sesión
function comida_rapida_get_current_user() {
    if (!comida_rapida_is_logged_in()) {
        return null;
    }
    return (object) array(
        'id'           => $_SESSION['comida_rapida_cliente_id'],
        'display_name' => $_SESSION['comida_rapida_cliente_name'],
        'user_email'   => $_SESSION['comida_rapida_cliente_email'],
        'role'         => $_SESSION['comida_rapida_role'] ?? 'cliente'
    );
}

// Cerrar sesión del cliente en el sitio web (sin afectar a WP)
function comida_rapida_logout() {
    unset($_SESSION['comida_rapida_cliente_id']);
    unset($_SESSION['comida_rapida_cliente_name']);
    unset($_SESSION['comida_rapida_cliente_email']);
    unset($_SESSION['comida_rapida_role']);
}

/**
 * 8. Forzar a WooCommerce a reconocer las vistas personalizadas como Carrito y Pago
 * Esto asegura que se encolen todos los scripts, estilos y pasarelas de pago (como PayPal) necesarios.
 */
add_filter('woocommerce_is_checkout', 'comida_rapida_force_is_checkout');
function comida_rapida_force_is_checkout($is_checkout) {
    if (isset($_GET['view']) && $_GET['view'] === 'compra') {
        return true;
    }
    return $is_checkout;
}

add_filter('woocommerce_is_cart', 'comida_rapida_force_is_cart');
function comida_rapida_force_is_cart($is_cart) {
    if (isset($_GET['view']) && $_GET['view'] === 'carrito') {
        return true;
    }
    return $is_cart;
}

/**
 * 9. Insertar nuevos productos adicionales en la base de datos de WooCommerce
 */
add_action('init', 'comida_rapida_insertar_nuevos_productos');
function comida_rapida_insertar_nuevos_productos() {
    if (!class_exists('WooCommerce')) {
        return;
    }
    
    // Si ya los insertamos, no hacer nada
    if (get_option('comida_rapida_nuevos_productos_creados')) {
        return;
    }

    // Obtener IDs de categorías
    $hamburguesas_term = get_term_by('name', 'Hamburguesas', 'product_cat');
    $pizzas_term = get_term_by('name', 'Pizzas', 'product_cat');
    $bebidas_term = get_term_by('name', 'Bebidas', 'product_cat');

    $hamb_id = $hamburguesas_term ? $hamburguesas_term->term_id : 0;
    $pizz_id = $pizzas_term ? $pizzas_term->term_id : 0;
    $bebi_id = $bebidas_term ? $bebidas_term->term_id : 0;

    $nuevos_productos = array(
        array(
            'title' => 'Pizza BBQ Chicken Premium',
            'desc' => 'Deliciosa masa artesanal, abundante queso mozzarella, tiras de pollo a la parrilla, cebolla morada fresca, cilantro y un toque irresistible de salsa BBQ ahumada.',
            'precio' => '13.50',
            'cat_id' => $pizz_id,
            'image' => 'pizza_bbq.png'
        ),
        array(
            'title' => 'Hamburguesa Triple Tocino',
            'desc' => 'Para los verdaderos amantes de la carne: triple carne Angus (150g c/u), tres capas de queso cheddar derretido, abundante tocino ahumado crujiente y cebolla caramelizada.',
            'precio' => '11.90',
            'cat_id' => $hamb_id,
            'image' => 'burger_triple.png'
        ),
        array(
            'title' => 'Malteada de Oreo Cremosa',
            'desc' => 'Una espectacular y cremosa malteada helada batida con galletas Oreo originales, decorada con crema batida, sirope de chocolate y trocitos crujientes de galleta.',
            'precio' => '4.20',
            'cat_id' => $bebi_id,
            'image' => 'milkshake_oreo.png'
        )
    );

    foreach ($nuevos_productos as $prod) {
        $existing = get_page_by_title($prod['title'], OBJECT, 'product');
        if ($existing) {
            continue;
        }

        $post_id = wp_insert_post(array(
            'post_title'   => $prod['title'],
            'post_content' => $prod['desc'],
            'post_status'  => 'publish',
            'post_type'    => 'product',
        ));

        if (!is_wp_error($post_id)) {
            update_post_meta($post_id, '_regular_price', $prod['precio']);
            update_post_meta($post_id, '_price', $prod['precio']);
            update_post_meta($post_id, '_stock_status', 'instock');
            update_post_meta($post_id, '_visibility', 'visible');
            
            if ($prod['cat_id']) {
                wp_set_object_terms($post_id, $prod['cat_id'], 'product_cat');
            }
            
            update_post_meta($post_id, '_image_demo_file', $prod['image']);
        }
    }

    update_option('comida_rapida_nuevos_productos_creados', true);
}

/**
 * 10. Insertar segundo lote de nuevos productos (Hamburguesas, Pizzas y Bebidas) en WooCommerce
 */
add_action('init', 'comida_rapida_insertar_nuevos_productos_lote_2');
function comida_rapida_insertar_nuevos_productos_lote_2() {
    if (!class_exists('WooCommerce')) {
        return;
    }
    
    // Si ya los insertamos, no hacer nada
    if (get_option('comida_rapida_nuevos_productos_creados_lote_2')) {
        return;
    }

    // Obtener IDs de categorías
    $hamburguesas_term = get_term_by('name', 'Hamburguesas', 'product_cat');
    $pizzas_term = get_term_by('name', 'Pizzas', 'product_cat');
    $bebidas_term = get_term_by('name', 'Bebidas', 'product_cat');

    $hamb_id = $hamburguesas_term ? $hamburguesas_term->term_id : 0;
    $pizz_id = $pizzas_term ? $pizzas_term->term_id : 0;
    $bebi_id = $bebidas_term ? $bebidas_term->term_id : 0;

    $nuevos_productos = array(
        array(
            'title' => 'Hamburguesa Criolla',
            'desc' => 'Deliciosa carne de res premium, queso blanco frito, huevo frito, tajadas de plátano maduro dulce y salsa especial criolla de la casa.',
            'precio' => '9.50',
            'cat_id' => $hamb_id,
            'image' => 'burger_criolla.png'
        ),
        array(
            'title' => 'Hamburguesa Veggie Portobello',
            'desc' => 'Champiñón Portobello gigante a la plancha, queso provolone fundido, rúcula fresca, tomate deshidratado y mayonesa cremosa de pesto.',
            'precio' => '8.90',
            'cat_id' => $hamb_id,
            'image' => 'burger_veggie.png'
        ),
        array(
            'title' => 'Pizza Cuatro Quesos',
            'desc' => 'Combinación gourmet de queso mozzarella, gorgonzola, parmesano y provolone fundidos sobre una base de salsa pomodoro artesanal.',
            'precio' => '14.00',
            'cat_id' => $pizz_id,
            'image' => 'pizza_four_cheese.png'
        ),
        array(
            'title' => 'Pizza Vegetariana Especial',
            'desc' => 'Salsa de tomate artesanal, mozzarella, pimientos asados, champiñones, aceitunas negras, corazones de alcachofa y hojas de albahaca fresca.',
            'precio' => '12.50',
            'cat_id' => $pizz_id,
            'image' => 'pizza_veggie.png'
        ),
        array(
            'title' => 'Limonada de Coco',
            'desc' => 'Cremosa y refrescante limonada batida con leche de coco y zumo de limón fresco, servida con coco rallado en el borde y abundante hielo.',
            'precio' => '3.50',
            'cat_id' => $bebi_id,
            'image' => 'drink_coco_lemonade.png'
        ),
        array(
            'title' => 'Malteada de Fresa Premium',
            'desc' => 'Exquisito batido helado de fresas naturales, cubierto con una generosa capa de crema batida, sirope de fresa y una fresa fresca arriba.',
            'precio' => '4.00',
            'cat_id' => $bebi_id,
            'image' => 'drink_strawberry_shake.png'
        )
    );

    foreach ($nuevos_productos as $prod) {
        $existing = get_page_by_title($prod['title'], OBJECT, 'product');
        if ($existing) {
            continue;
        }

        $post_id = wp_insert_post(array(
            'post_title'   => $prod['title'],
            'post_content' => $prod['desc'],
            'post_status'  => 'publish',
            'post_type'    => 'product',
        ));

        if (!is_wp_error($post_id)) {
            update_post_meta($post_id, '_regular_price', $prod['precio']);
            update_post_meta($post_id, '_price', $prod['precio']);
            update_post_meta($post_id, '_stock_status', 'instock');
            update_post_meta($post_id, '_visibility', 'visible');
            
            if ($prod['cat_id']) {
                wp_set_object_terms($post_id, $prod['cat_id'], 'product_cat');
            }
            
            update_post_meta($post_id, '_image_demo_file', $prod['image']);
        }
    }

    update_option('comida_rapida_nuevos_productos_creados_lote_2', true);
}

/**
 * 11. Insertar nuevos combos de comidas en WooCommerce
 */
add_action('init', 'comida_rapida_insertar_nuevos_combos');
function comida_rapida_insertar_nuevos_combos() {
    if (!class_exists('WooCommerce')) {
        return;
    }
    
    // Si ya los insertamos, no hacer nada
    if (get_option('comida_rapida_nuevos_combos_creados')) {
        return;
    }

    // Obtener ID de la categoría Combos
    $combos_term = get_term_by('name', 'Combos', 'product_cat');
    $comb_id = $combos_term ? $combos_term->term_id : 0;

    $nuevos_combos = array(
        array(
            'title' => 'Combo Mega Burger para Dos',
            'desc' => 'Para compartir: 2 Hamburguesas Monster, 1 porción grande de papas fritas rústicas, 2 bebidas frías (gaseosa o té helado) y 2 aderezos especiales de la casa.',
            'precio' => '22.00',
            'promo_precio' => '18.99',
            'cat_id' => $comb_id,
            'image' => 'combo_mega_two.png'
        ),
        array(
            'title' => 'Combo Pizza Fiesta & Alitas',
            'desc' => 'El combo definitivo: 1 Pizza Pepperoni grande, 10 alitas crujientes bañadas en salsa BBQ dulce y una gaseosa de 1.5L para compartir.',
            'precio' => '26.50',
            'promo_precio' => '21.50',
            'cat_id' => $comb_id,
            'image' => 'combo_pizza_wings.png'
        )
    );

    foreach ($nuevos_combos as $prod) {
        $existing = get_page_by_title($prod['title'], OBJECT, 'product');
        if ($existing) {
            continue;
        }

        $post_id = wp_insert_post(array(
            'post_title'   => $prod['title'],
            'post_content' => $prod['desc'],
            'post_status'  => 'publish',
            'post_type'    => 'product',
        ));

        if (!is_wp_error($post_id)) {
            update_post_meta($post_id, '_regular_price', $prod['precio']);
            update_post_meta($post_id, '_sale_price', $prod['promo_precio']);
            update_post_meta($post_id, '_price', $prod['promo_precio']);
            update_post_meta($post_id, '_stock_status', 'instock');
            update_post_meta($post_id, '_visibility', 'visible');
            
            if ($prod['cat_id']) {
                wp_set_object_terms($post_id, $prod['cat_id'], 'product_cat');
            }
            
            update_post_meta($post_id, '_image_demo_file', $prod['image']);
        }
    }

    update_option('comida_rapida_nuevos_combos_creados', true);
}

/**
 * 12. Actualizar el producto "Jugo" con mejor descripción e imagen personalizada
 */
add_action('init', 'comida_rapida_actualizar_jugo');
function comida_rapida_actualizar_jugo() {
    if (!class_exists('WooCommerce')) {
        return;
    }
    
    global $wpdb;
    $post_id = $wpdb->get_var($wpdb->prepare(
        "SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type = 'product' LIMIT 1",
        'Jugo'
    ));
    
    if ($post_id) {
        // Actualizar la descripción
        wp_update_post(array(
            'ID'           => $post_id,
            'post_content' => 'Delicioso zumo de frutas 100% natural, exprimido al instante con naranjas seleccionadas y maracuyá fresco. Refrescante, saludable y sin azúcares añadidos.'
        ));
        
        // Asignar la imagen demo
        update_post_meta($post_id, '_image_demo_file', 'drink_juice.png');
    }
}

/**
 * 14. Mostrar el campo de CAPTCHA matemático en el checkout de WooCommerce
 */
add_action('woocommerce_after_checkout_billing_form', 'comida_rapida_checkout_captcha_field');
function comida_rapida_checkout_captcha_field($checkout) {
    if (!isset($_SESSION['comida_rapida_captcha_q']) || !isset($_SESSION['comida_rapida_captcha_ans'])) {
        comida_rapida_generar_captcha();
    }
    
    echo '<div class="captcha-checkout-wrapper" style="margin-top: 25px; padding: 20px; border: var(--border-main); border-radius: 12px; background: var(--bg-primary);">';
    echo '<h4 style="margin-bottom: 8px; font-weight: 700; display: flex; align-items: center; gap: 8px; color: var(--text-primary);">';
    echo '<i class="fa-solid fa-shield-halved" style="color: var(--color-amber);"></i> Verificación de Seguridad Antispam</h4>';
    echo '<p style="font-size: 0.88rem; color: var(--text-secondary); margin-bottom: 15px;">Para procesar tu pedido ficticio, resuelve esta suma:</p>';
    
    woocommerce_form_field('checkout_captcha_ans', array(
        'type'        => 'text',
        'class'       => array('form-row-wide'),
        'label'       => $_SESSION['comida_rapida_captcha_q'],
        'placeholder' => 'Escribe la respuesta aquí...',
        'required'    => true,
    ), $checkout->get_value('checkout_captcha_ans'));
    
    echo '</div>';
}

/**
 * 15. Validar el CAPTCHA del checkout de WooCommerce en el servidor
 */
add_action('woocommerce_checkout_process', 'comida_rapida_checkout_captcha_validation');
function comida_rapida_checkout_captcha_validation() {
    $user_ans = sanitize_text_field($_POST['checkout_captcha_ans'] ?? '');
    $correct_ans = $_SESSION['comida_rapida_captcha_ans'] ?? null;
    
    if (empty($user_ans)) {
        wc_add_notice('<strong>Error de Seguridad:</strong> Por favor, responde la pregunta de verificación antispam.', 'error');
        return;
    }
    
    if ($correct_ans === null || intval($user_ans) !== intval($correct_ans)) {
        wc_add_notice('<strong>Error de Seguridad:</strong> La respuesta antispam es incorrecta. Inténtalo de nuevo.', 'error');
        // Regenerar uno nuevo para el siguiente intento
        comida_rapida_generar_captcha();
    }
}

/**
 * 13. Insertar tercer lote de nuevos productos (Bebidas, Combos, Hamburguesas y Pizzas) en WooCommerce
 */
add_action('init', 'comida_rapida_insertar_nuevos_productos_lote_3');
function comida_rapida_insertar_nuevos_productos_lote_3() {
    if (!class_exists('WooCommerce')) {
        return;
    }
    
    // Si ya los insertamos, no hacer nada
    if (get_option('comida_rapida_nuevos_productos_creados_lote_3')) {
        return;
    }

    // Obtener IDs de categorías
    $hamburguesas_term = get_term_by('name', 'Hamburguesas', 'product_cat');
    $pizzas_term = get_term_by('name', 'Pizzas', 'product_cat');
    $bebidas_term = get_term_by('name', 'Bebidas', 'product_cat');
    $combos_term = get_term_by('name', 'Combos', 'product_cat');

    $hamb_id = $hamburguesas_term ? $hamburguesas_term->term_id : 0;
    $pizz_id = $pizzas_term ? $pizzas_term->term_id : 0;
    $bebi_id = $bebidas_term ? $bebidas_term->term_id : 0;
    $comb_id = $combos_term ? $combos_term->term_id : 0;

    $nuevos_productos = array(
        array(
            'title' => 'Hamburguesa BBQ Crunch',
            'desc' => 'Doble carne de res Angus, crujientes aros de cebolla apanados, doble queso cheddar derretido, tocino ahumado y nuestra salsa barbacoa secreta.',
            'precio' => '10.50',
            'promo' => '0',
            'precio_promo' => '0',
            'cat_id' => $hamb_id,
            'image' => 'burger_bbq_crunch.png'
        ),
        array(
            'title' => 'Pizza Jamón Serrano',
            'desc' => 'Finas y abundantes rebanadas de jamón serrano premium curado, rúcula fresca baby, lascas de queso parmesano y un chorrito de aceite de oliva virgen extra.',
            'precio' => '15.50',
            'promo' => '0',
            'precio_promo' => '0',
            'cat_id' => $pizz_id,
            'image' => 'pizza_serrana.png'
        ),
        array(
            'title' => 'Soda Italiana de Maracuyá',
            'desc' => 'Refrescante jarabe artesanal de maracuyá maduro, agua con gas purificada, cubos de hielo, rodaja de limón y hojas de menta fresca maceradas.',
            'precio' => '3.80',
            'promo' => '0',
            'precio_promo' => '0',
            'cat_id' => $bebi_id,
            'image' => 'soda_maracuya.png'
        ),
        array(
            'title' => 'Combo Pizza & Burger Match',
            'desc' => 'El combo de ensueño: 1 Pizza Pepperoni mediana, 1 Hamburguesa Monster clásica, porción de papas fritas crocantes y 2 vasos de bebida helada.',
            'precio' => '26.00',
            'promo' => '1',
            'precio_promo' => '20.99',
            'cat_id' => $comb_id,
            'image' => 'combo_pizza_burger.png'
        )
    );

    foreach ($nuevos_productos as $prod) {
        $existing = get_page_by_title($prod['title'], OBJECT, 'product');
        if ($existing) {
            continue;
        }

        $post_id = wp_insert_post(array(
            'post_title'   => $prod['title'],
            'post_content' => $prod['desc'],
            'post_status'  => 'publish',
            'post_type'    => 'product',
        ));

        if (!is_wp_error($post_id)) {
            update_post_meta($post_id, '_regular_price', $prod['precio']);
            if ($prod['promo'] == '1') {
                update_post_meta($post_id, '_sale_price', $prod['precio_promo']);
                update_post_meta($post_id, '_price', $prod['precio_promo']);
            } else {
                update_post_meta($post_id, '_price', $prod['precio']);
            }
            update_post_meta($post_id, '_stock_status', 'instock');
            update_post_meta($post_id, '_visibility', 'visible');
            
            if ($prod['cat_id']) {
                wp_set_object_terms($post_id, $prod['cat_id'], 'product_cat');
            }
            
            update_post_meta($post_id, '_image_demo_file', $prod['image']);
        }
    }

    update_option('comida_rapida_nuevos_productos_creados_lote_3', true);
}

/**
 * 16. Cabeceras de seguridad HTTP y endurecimiento (hardening) de WordPress.
 */

// Inyectar cabeceras de seguridad HTTP (incluyendo HSTS para PCI DSS)
add_action('send_headers', 'comida_rapida_send_security_headers');
function comida_rapida_send_security_headers() {
    if (!headers_sent()) {
        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: no-referrer-when-downgrade');
        
        // HSTS (HTTP Strict Transport Security) - Requisito PCI DSS para asegurar canal encriptado
        if (is_ssl()) {
            header('Strict-Transport-Security: max-age=63072000; includeSubDomains; preload');
        }
    }
}

// Forzar HTTPS en páginas críticas (pago, autenticación y administración)
add_action('template_redirect', 'comida_rapida_force_ssl_critical_pages');
function comida_rapida_force_ssl_critical_pages() {
    if (!is_ssl()) {
        $view = $_GET['view'] ?? '';
        $critical_views = array('compra', 'carrito', 'login', 'admin');
        
        if (in_array($view, $critical_views) || is_admin() || (function_exists('is_checkout') && is_checkout())) {
            wp_safe_redirect('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 301);
            exit;
        }
    }
}

// Desactivar XML-RPC para prevenir ataques de fuerza bruta
add_filter('xmlrpc_enabled', '__return_false');

// Restringir el acceso a la API REST de WordPress para usuarios no autenticados
add_filter('rest_authentication_errors', 'comida_rapida_restrict_rest_api');
function comida_rapida_restrict_rest_api($result) {
    if (!empty($result)) {
        return $result;
    }
    // Permitir acceso a la API REST si es un administrador logueado o si se está usando AJAX en admin
    if (!is_user_logged_in()) {
        return new WP_Error('rest_not_logged_in', __('Acceso restringido a la API REST.', 'comida-rapida'), array('status' => 401));
    }
    return $result;
}

// Ocultar versión de WordPress
remove_action('wp_head', 'wp_generator');
add_filter('the_generator', '__return_empty_string');

// Solucionar error DUPLICATE_INVOICE_ID de PayPal en entornos locales/sandbox
add_filter('woocommerce_paypal_payments_purchase_unit_from_wc_order', 'comida_rapida_paypal_unique_invoice_id', 10, 2);
function comida_rapida_paypal_unique_invoice_id($purchase_unit, $order) {
    if (class_exists('WooCommerce\PayPalCommerce\ApiClient\Entity\PurchaseUnit') && method_exists($purchase_unit, 'invoice_id')) {
        $invoice_id = $purchase_unit->invoice_id();
        if ($invoice_id) {
            // Añadir un timestamp o sufijo único para evitar ID de factura duplicado en PayPal
            $new_invoice_id = $invoice_id . '-' . time();
            
            $new_purchase_unit = new \WooCommerce\PayPalCommerce\ApiClient\Entity\PurchaseUnit(
                $purchase_unit->amount(),
                $purchase_unit->items(),
                $purchase_unit->shipping(),
                $purchase_unit->reference_id(),
                $purchase_unit->description(),
                $purchase_unit->custom_id(),
                $new_invoice_id,
                $purchase_unit->soft_descriptor(),
                $purchase_unit->payments(),
                $purchase_unit->supplementary_data()
            );
            return $new_purchase_unit;
        }
    }
    return $purchase_unit;
}
