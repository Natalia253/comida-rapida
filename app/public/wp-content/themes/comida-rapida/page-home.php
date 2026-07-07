<?php
/**
 * Template Name: Página Inicio
 * Description: Vista para la página de inicio con Hero Slider y Promociones.
 */
?>

<!-- 1. HERO SLIDER / CAROUSEL -->
<section class="hero" id="home">
    <div class="slider-container" id="hero-slider">
        <!-- Slide 1 -->
        <div class="slide active">
            <div class="slide-bg" style="background-image: url('<?php echo get_template_directory_uri(); ?>/assets/images/hero_banner.png');"></div>
            <div class="slide-content">
                <h1 class="text-gradient">Disfruta nuestras comidas rápidas</h1>
                <p>Sabores intensos, ingredientes seleccionados de primera calidad y la mejor preparación tradicional al instante. Elige tu favorita hoy.</p>
                <div class="btn-wrapper">
                    <a href="<?php echo esc_url(add_query_arg('view', 'menu', home_url('/'))); ?>" class="btn btn-primary">Ver Menú <i class="fa-solid fa-arrow-right"></i></a>
                    <a href="<?php echo esc_url(add_query_arg('view', 'compra', home_url('/'))); ?>" class="btn btn-secondary">Pedir Ahora</a>
                </div>
            </div>
        </div>
        
        <!-- Slide 2 -->
        <div class="slide">
            <div class="slide-bg" style="background-image: url('<?php echo get_template_directory_uri(); ?>/assets/images/combo_pack.png');"></div>
            <div class="slide-content">
                <h1 class="text-gradient">Combos especiales para compartir</h1>
                <p>Los mejores combos familiares y promociones exclusivas diseñadas para compartir con amigos y familia. ¡Más sabor a menor precio!</p>
                <div class="btn-wrapper">
                    <a href="<?php echo esc_url(add_query_arg('view', 'menu', home_url('/'))); ?>" class="btn btn-primary">Ver Combos <i class="fa-solid fa-pizza-slice"></i></a>
                </div>
            </div>
        </div>
        
        <!-- Slide 3 -->
        <div class="slide">
            <div class="slide-bg" style="background-image: url('<?php echo get_template_directory_uri(); ?>/assets/images/burger_gourmet.png');"></div>
            <div class="slide-content">
                <h1 class="text-gradient">Haz tu pedido en línea rápido</h1>
                <p>Prueba nuestro sistema de compra ficticio y seguro. Selecciona tus antojos, completa el CAPTCHA antispam y envíalo directamente.</p>
                <div class="btn-wrapper">
                    <a href="<?php echo esc_url(add_query_arg('view', 'compra', home_url('/'))); ?>" class="btn btn-primary">Hacer Pedido <i class="fa-solid fa-cart-shopping"></i></a>
                </div>
            </div>
        </div>
        
        <!-- Slider Controls -->
        <button class="slider-control slider-prev" id="slider-prev-btn" aria-label="Anterior slide">
            <i class="fa-solid fa-chevron-left"></i>
        </button>
        <button class="slider-control slider-next" id="slider-next-btn" aria-label="Siguiente slide">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
        
        <!-- Slider Dots -->
        <div class="slider-dots" id="slider-dots-container">
            <span class="slider-dot active" data-index="0"></span>
            <span class="slider-dot" data-index="1"></span>
            <span class="slider-dot" data-index="2"></span>
        </div>
    </div>
</section>

<!-- 4. SECCIÓN DE NOVEDADES Y RECOMENDADOS -->
<section class="section-padding" id="novedades" style="background: linear-gradient(to bottom, var(--bg-primary), var(--bg-secondary));">
    <div class="container">
        <div class="section-header">
            <span class="section-subtitle">Lo más pedido</span>
            <h2 class="section-title">Novedades y Recomendados</h2>
            <p class="section-desc">Descubre los nuevos sabores y las combinaciones más aclamadas de esta semana. Preparados al instante para ti.</p>
        </div>
        
        <div class="menu-grid">
            <!-- Novedad 1 -->
            <div class="product-card">
                <div class="product-img-wrapper">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/burger_gourmet.png" alt="Mega Burger Gourmet" class="product-img">
                    <span class="product-tag" style="background: var(--color-amber); color: white; border-color: var(--color-amber); position: absolute; top: 14px; left: 14px; padding: 4px 10px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">¡NUEVO!</span>
                </div>
                <div class="product-info">
                    <h3 class="product-title">Mega Burger Gourmet</h3>
                    <p class="product-desc">Doble carne de res premium a la parrilla, queso cheddar fundido, tocino crujiente, cebolla caramelizada y salsa secreta de la casa en pan brioche.</p>
                    <div class="product-footer">
                        <span class="product-price">$9.99</span>
                        <a href="<?php echo esc_url(add_query_arg('view', 'menu', home_url('/'))); ?>" class="add-to-cart-btn" title="Ver en el Menú">
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Novedad 2 -->
            <div class="product-card">
                <div class="product-img-wrapper">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/combo_pack.png" alt="Combo Fiesta Familiar" class="product-img">
                    <span class="product-tag" style="background: var(--color-red); color: white; border-color: var(--color-red); position: absolute; top: 14px; left: 14px; padding: 4px 10px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">POPULAR</span>
                </div>
                <div class="product-info">
                    <h3 class="product-title">Combo Fiesta Familiar</h3>
                    <p class="product-desc">Perfecto para compartir: 2 Hamburguesas clásicas, 1 Pizza Pepperoni mediana, 2 porciones grandes de papas fritas y una gaseosa de 1.5L.</p>
                    <div class="product-footer">
                        <span class="product-price">$24.99</span>
                        <a href="<?php echo esc_url(add_query_arg('view', 'menu', home_url('/'))); ?>" class="add-to-cart-btn" title="Ver en el Menú">
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Novedad 3 -->
            <div class="product-card">
                <div class="product-img-wrapper">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/pizza_pepperoni.png" alt="Pizza Pepperoni Premium" class="product-img">
                    <span class="product-tag" style="background: var(--color-green); color: white; border-color: var(--color-green); position: absolute; top: 14px; left: 14px; padding: 4px 10px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">RECOMENDADO</span>
                </div>
                <div class="product-info">
                    <h3 class="product-title">Pizza Pepperoni Premium</h3>
                    <p class="product-desc">Masa artesanal crujiente de fermentación lenta, salsa de tomates italianos, abundante queso mozzarella y rodajas selectas de pepperoni premium.</p>
                    <div class="product-footer">
                        <span class="product-price">$12.50</span>
                        <a href="<?php echo esc_url(add_query_arg('view', 'menu', home_url('/'))); ?>" class="add-to-cart-btn" title="Ver en el Menú">
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
