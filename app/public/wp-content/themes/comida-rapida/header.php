<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="header" id="header">
    <div class="header-container">
        <?php
        // Obtener la vista actual
        $current_view = isset($_GET['view']) ? sanitize_text_field($_GET['view']) : 'home';
        ?>
        <!-- Logo -->
        <a href="<?php echo esc_url(home_url('/')); ?>" class="logo">
            <i class="fa-solid fa-burger" style="color: var(--color-amber);"></i>
            Comida<span>Rápida</span>
        </a>
        
        <!-- Navigation Menu -->
        <nav>
            <ul class="nav-links" id="nav-links">
                <li><a href="<?php echo esc_url(home_url('/')); ?>" class="<?php echo $current_view === 'home' ? 'active' : ''; ?>">Inicio</a></li>
                <li><a href="<?php echo esc_url(add_query_arg('view', 'menu', home_url('/'))); ?>" class="<?php echo $current_view === 'menu' ? 'active' : ''; ?>">Menú</a></li>
                <li><a href="<?php echo esc_url(add_query_arg('view', 'promociones', home_url('/'))); ?>" class="<?php echo $current_view === 'promociones' ? 'active' : ''; ?>">Promociones</a></li>
                <li><a href="<?php echo esc_url(add_query_arg('view', 'contacto', home_url('/'))); ?>" class="<?php echo $current_view === 'contacto' ? 'active' : ''; ?>">Contacto</a></li>
            </ul>
        </nav>
        
        <!-- Actions (Cart & Toggle) -->
        <div class="header-actions">
            <!-- Botón de Carrito -->
            <a href="<?php echo esc_url(add_query_arg('view', 'carrito', home_url('/'))); ?>" class="cart-icon-btn <?php echo $current_view === 'carrito' ? 'active' : ''; ?>" id="cart-toggle-btn" aria-label="Ver carrito">
                <i class="fa-solid fa-shopping-basket"></i>
                <span class="cart-badge" id="cart-badge-count"><?php echo class_exists('WooCommerce') && WC()->cart ? esc_html(WC()->cart->get_cart_contents_count()) : 0; ?></span>
            </a>

            <!-- Botón de Cuenta -->
            <a href="<?php echo esc_url(add_query_arg('view', 'login', home_url('/'))); ?>" class="cart-icon-btn <?php echo $current_view === 'login' ? 'active' : ''; ?>" id="user-toggle-btn" aria-label="Mi cuenta" title="<?php echo comida_rapida_is_logged_in() ? 'Mi Cuenta' : 'Iniciar Sesión'; ?>">
                <i class="fa-solid fa-user"></i>
            </a>

            <?php if (comida_rapida_is_admin()) : ?>
                <!-- Botón de Administrador -->
                <a href="<?php echo esc_url(add_query_arg('view', 'admin', home_url('/'))); ?>" class="cart-icon-btn <?php echo $current_view === 'admin' ? 'active' : ''; ?>" id="admin-toggle-btn" aria-label="Panel de Administración" title="Panel de Administración" style="background: rgba(249, 115, 22, 0.08); color: var(--color-amber); border-color: var(--color-amber);">
                    <i class="fa-solid fa-screwdriver-wrench"></i>
                </a>
            <?php endif; ?>
            
            <div class="mobile-menu-btn" id="mobile-menu-toggle">
                <i class="fa-solid fa-bars"></i>
            </div>
        </div>
    </div>
</header>
