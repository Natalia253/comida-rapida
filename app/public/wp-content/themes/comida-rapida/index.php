<?php
/**
 * Plantilla principal del tema - Comida Rápida
 * Actúa como despachador de vistas dinámicas basado en el parámetro 'view'.
 */
get_header();

// Obtener y sanitizar la vista actual (por defecto 'home')
$view = isset($_GET['view']) ? sanitize_text_field($_GET['view']) : '';

// Si es una página nativa de WooCommerce, asignar la vista correspondiente automáticamente
if (class_exists('WooCommerce')) {
    if (is_cart()) {
        $view = 'carrito';
    } elseif (is_checkout() || is_wc_endpoint_url('order-received')) {
        $view = 'compra';
    }
}

// Si sigue vacía, por defecto es 'home'
if (empty($view)) {
    $view = 'home';
}

// Validar vistas permitidas para mayor seguridad
$allowed_views = array('home', 'menu', 'carrito', 'compra', 'contacto', 'login', 'admin', 'promociones');
if (!in_array($view, $allowed_views)) {
    $view = 'home';
}

?>

<main style="<?php echo $view !== 'home' ? 'padding-top: var(--header-height);' : ''; ?>">
    <?php
    // Cargar la interfaz del archivo propio correspondiente
    get_template_part('page', $view);
    ?>
</main>

<?php
get_footer();
