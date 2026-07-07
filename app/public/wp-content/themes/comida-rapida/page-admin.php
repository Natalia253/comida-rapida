<?php
/**
 * Template Name: Panel de Administración Completo
 * Description: Vista para el panel de control del administrador en el frontend con Dashboard, Gestión de Productos, Pedidos y Usuarios.
 */

// 1. Control de Acceso: Solo administradores del sitio web
if (!comida_rapida_is_admin()) {
    ?>
    <section class="section-padding" style="min-height: 80vh; display: flex; align-items: center; background-color: var(--bg-primary);">
        <div class="container" style="max-width: 500px; text-align: center;">
            <div class="form-card" style="border-radius:16px; box-shadow: var(--shadow-lg); border: var(--border-main);">
                <i class="fa-solid fa-triangle-exclamation" style="font-size: 4rem; color: var(--color-red); margin-bottom: 20px;"></i>
                <h2 class="form-title" style="justify-content: center; border:none; margin-bottom: 10px; padding-bottom:0;">Acceso Denegado</h2>
                <p style="color: var(--text-secondary); margin-bottom: 24px; font-size: 0.95rem;">
                    No tienes permisos de administrador para visualizar este panel. Por favor, inicia sesión con una cuenta autorizada.
                </p>
                <a href="<?php echo esc_url(add_query_arg('view', 'login', home_url('/'))); ?>" class="btn btn-primary" style="border-radius: 50px;">
                    Iniciar Sesión <i class="fa-solid fa-right-to-bracket"></i>
                </a>
            </div>
        </div>
    </section>
    <?php
    return;
}

// 2. PROCESAMIENTO DE ACCIONES EN EL SERVIDOR
$action_msg = '';
$action_type = 'success';

// Iniciar nonce de verificación común
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_nonce'])) {
    if (!wp_verify_nonce($_POST['admin_nonce'], 'comida_rapida_admin_action')) {
        $action_msg = 'Error de seguridad. Por favor, intente de nuevo.';
        $action_type = 'error';
    } else {
        // ACCIÓN: ELIMINAR PEDIDO
        if (isset($_POST['action_delete_pedido'])) {
            $pid = (int)$_POST['pedido_id'];
            if (class_exists('WooCommerce')) {
                $order = wc_get_order($pid);
                if ($order) {
                    $order->delete(true);
                    $action_msg = 'Pedido #' . $pid . ' eliminado correctamente.';
                }
            }
        }
        
        // ACCIÓN: CAMBIAR ESTADO DE PEDIDO
        elseif (isset($_POST['action_update_order_status'])) {
            $pid = (int)$_POST['pedido_id'];
            $nuevo_estado = sanitize_text_field($_POST['nuevo_estado']);
            
            // Mapeamos los estados del formulario a los de WooCommerce
            $wc_status = 'pending';
            if ($nuevo_estado === 'preparacion') {
                $wc_status = 'processing';
            } elseif ($nuevo_estado === 'entregado') {
                $wc_status = 'completed';
            } elseif ($nuevo_estado === 'cancelado') {
                $wc_status = 'cancelled';
            }
            
            if (class_exists('WooCommerce')) {
                $order = wc_get_order($pid);
                if ($order) {
                    $order->update_status($wc_status);
                    $action_msg = 'El estado del Pedido #' . $pid . ' fue cambiado a "' . ucfirst($nuevo_estado) . '".';
                }
            }
        }
        
        // ACCIÓN: ELIMINAR USUARIO
        elseif (isset($_POST['action_delete_user'])) {
            $uid = (int)$_POST['user_id'];
            $current_user_data = comida_rapida_get_current_user();
            if ($current_user_data && $uid === (int)$current_user_data->id) {
                $action_msg = 'No puedes eliminar tu propia cuenta de administrador.';
                $action_type = 'error';
            } else {
                global $wpdb;
                $table_name = $wpdb->prefix . 'comida_rapida_clientes';
                $deleted = $wpdb->delete($table_name, array('id' => $uid), array('%d'));
                if ($deleted) {
                    $action_msg = 'Usuario eliminado exitosamente.';
                } else {
                    $action_msg = 'Error al eliminar el usuario.';
                    $action_type = 'error';
                }
            }
        }
        
        // ACCIÓN: CREAR PRODUCTO
        elseif (isset($_POST['action_add_product'])) {
            $title = sanitize_text_field($_POST['prod_title']);
            $desc = sanitize_textarea_field($_POST['prod_desc']);
            $price = sanitize_text_field($_POST['prod_price']);
            $is_promo = isset($_POST['prod_promo']) ? '1' : '0';
            $promo_price = sanitize_text_field($_POST['prod_promo_price']);
            $category = (int)$_POST['prod_category'];
            
            $post_id = wp_insert_post(array(
                'post_title'   => $title,
                'post_content' => $desc,
                'post_status'  => 'publish',
                'post_type'    => 'product',
            ));
            
            if (is_wp_error($post_id)) {
                $action_msg = $post_id->get_error_message();
                $action_type = 'error';
            } else {
                update_post_meta($post_id, '_regular_price', $price);
                if ($is_promo === '1' && !empty($promo_price)) {
                    update_post_meta($post_id, '_sale_price', $promo_price);
                    update_post_meta($post_id, '_price', $promo_price);
                } else {
                    update_post_meta($post_id, '_price', $price);
                }
                update_post_meta($post_id, '_stock_status', 'instock');
                update_post_meta($post_id, '_visibility', 'visible');
                update_post_meta($post_id, '_image_demo_file', 'burger_gourmet.png'); // Default demo icon
                
                if ($category) {
                    wp_set_object_terms($post_id, $category, 'product_cat');
                }
                
                // Procesar la imagen subida
                if (!empty($_FILES['prod_image']['name'])) {
                    require_once(ABSPATH . 'wp-admin/includes/image.php');
                    require_once(ABSPATH . 'wp-admin/includes/file.php');
                    require_once(ABSPATH . 'wp-admin/includes/media.php');
                    
                    $attachment_id = media_handle_upload('prod_image', $post_id);
                    if (!is_wp_error($attachment_id)) {
                        set_post_thumbnail($post_id, $attachment_id);
                    }
                }
                
                $action_msg = 'Producto "' . esc_html($title) . '" creado correctamente.';
            }
        }
        
        // ACCIÓN: EDITAR PRODUCTO
        elseif (isset($_POST['action_edit_product'])) {
            $post_id = (int)$_POST['prod_id'];
            $title = sanitize_text_field($_POST['prod_title']);
            $desc = sanitize_textarea_field($_POST['prod_desc']);
            $price = sanitize_text_field($_POST['prod_price']);
            $is_promo = isset($_POST['prod_promo']) ? '1' : '0';
            $promo_price = sanitize_text_field($_POST['prod_promo_price']);
            $category = (int)$_POST['prod_category'];
            
            $updated = wp_update_post(array(
                'ID'           => $post_id,
                'post_title'   => $title,
                'post_content' => $desc,
            ));
            
            if (is_wp_error($updated)) {
                $action_msg = $updated->get_error_message();
                $action_type = 'error';
            } else {
                update_post_meta($post_id, '_regular_price', $price);
                if ($is_promo === '1' && !empty($promo_price)) {
                    update_post_meta($post_id, '_sale_price', $promo_price);
                    update_post_meta($post_id, '_price', $promo_price);
                } else {
                    delete_post_meta($post_id, '_sale_price');
                    update_post_meta($post_id, '_price', $price);
                }
                if ($category) {
                    wp_set_object_terms($post_id, $category, 'product_cat');
                }
                
                // Procesar la imagen subida
                if (!empty($_FILES['prod_image']['name'])) {
                    require_once(ABSPATH . 'wp-admin/includes/image.php');
                    require_once(ABSPATH . 'wp-admin/includes/file.php');
                    require_once(ABSPATH . 'wp-admin/includes/media.php');
                    
                    $attachment_id = media_handle_upload('prod_image', $post_id);
                    if (!is_wp_error($attachment_id)) {
                        set_post_thumbnail($post_id, $attachment_id);
                    }
                }
                
                $action_msg = 'Producto actualizado exitosamente.';
            }
        }
        
        // ACCIÓN: ELIMINAR PRODUCTO
        elseif (isset($_POST['action_delete_product'])) {
            $post_id = (int)$_POST['prod_id'];
            if (get_post_type($post_id) === 'product') {
                wp_delete_post($post_id, true);
                $action_msg = 'Producto eliminado exitosamente.';
            }
        }
    }
}

// 3. CONSULTAS DE DATOS DE ADMINISTRADOR
// Pedidos de WooCommerce
$all_pedidos = array();
$total_pedidos = 0;
$pedidos_pendientes = 0;
$total_ventas = 0;

if (class_exists('WooCommerce')) {
    $all_pedidos = wc_get_orders(array('limit' => -1));
    $total_pedidos = count($all_pedidos);
    
    foreach ($all_pedidos as $order) {
        $status = $order->get_status();
        $total_ventas += (float)$order->get_total();
        
        // Contar pedidos pendientes y en preparación
        if (in_array($status, array('pending', 'processing'))) {
            $pedidos_pendientes++;
        }
    }
}

// Productos de WooCommerce
$all_productos = get_posts(array(
    'post_type'      => 'product',
    'posts_per_page' => -1,
    'post_status'    => 'publish'
));
$total_productos = count($all_productos);

// Usuarios de la base de datos personalizada
global $wpdb;
$table_clientes = $wpdb->prefix . 'comida_rapida_clientes';
$all_users = $wpdb->get_results("SELECT id, username as user_login, username as display_name, email as user_email, role FROM $table_clientes");
$total_usuarios = count($all_users);

// Obtener categorías reales de WooCommerce (product_cat)
$categories_terms = get_terms(array(
    'taxonomy'   => 'product_cat',
    'hide_empty' => false
));

// Pestaña activa por defecto
$active_admin_tab = 'dashboard';
if (isset($_POST['action_add_product']) || isset($_POST['action_edit_product']) || isset($_POST['action_delete_product'])) {
    $active_admin_tab = 'productos';
} elseif (isset($_POST['action_update_order_status']) || isset($_POST['action_delete_pedido'])) {
    $active_admin_tab = 'pedidos';
} elseif (isset($_POST['action_delete_user'])) {
    $active_admin_tab = 'usuarios';
}
?>

<section class="section-padding" style="background-color: var(--bg-primary); min-height: 90vh;">
    <div class="container" style="max-width: 1100px;">
        
        <!-- Encabezado del Panel -->
        <div class="section-header" style="margin-bottom: 30px; text-align: left; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
            <div>
                <span class="section-subtitle">Panel Administrativo</span>
                <h2 class="section-title" style="margin-bottom: 0;">Panel de Gestión de Negocio</h2>
                <p class="section-desc" style="margin: 5px 0 0 0;">Visualiza estadísticas, administra pedidos, catálogo de comida y usuarios registrados.</p>
            </div>
            <a href="<?php echo esc_url(add_query_arg(array('view' => 'login', 'action' => 'logout'), home_url('/'))); ?>" class="btn btn-secondary btn-sm" style="border-radius: 50px;">
                Cerrar Sesión <i class="fa-solid fa-right-from-bracket"></i>
            </a>
        </div>

        <!-- Alertas de Acciones -->
        <?php if (!empty($action_msg)) : ?>
            <div class="form-status-msg <?php echo $action_type; ?>" style="margin-bottom: 30px; display: block; padding: 14px; border-radius: 8px;">
                <i class="fa-solid <?php echo $action_type == 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'; ?>"></i> <?php echo esc_html($action_msg); ?>
            </div>
        <?php endif; ?>

        <!-- Selector de Pestañas del Panel de Admin -->
        <div class="admin-menu-tabs" style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 30px;">
            <button class="filter-btn <?php echo $active_admin_tab === 'dashboard' ? 'active' : ''; ?>" data-target="admin-dashboard-tab">
                <i class="fa-solid fa-chart-pie"></i> Dashboard
            </button>
            <button class="filter-btn <?php echo $active_admin_tab === 'productos' ? 'active' : ''; ?>" data-target="admin-productos-tab">
                <i class="fa-solid fa-burger"></i> Gestión de Productos
            </button>
            <button class="filter-btn <?php echo $active_admin_tab === 'pedidos' ? 'active' : ''; ?>" data-target="admin-pedidos-tab">
                <i class="fa-solid fa-receipt"></i> Gestión de Pedidos (<?php echo $pedidos_pendientes; ?>)
            </button>
            <button class="filter-btn <?php echo $active_admin_tab === 'usuarios' ? 'active' : ''; ?>" data-target="admin-usuarios-tab">
                <i class="fa-solid fa-users"></i> Usuarios Registrados
            </button>
        </div>

        <!-- ==================== PESTAÑA: DASHBOARD ==================== -->
        <div class="admin-tab-content" id="admin-dashboard-tab" style="display: <?php echo $active_admin_tab === 'dashboard' ? 'block' : 'none'; ?>;">
            <!-- Grid de Métricas -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 24px; margin-bottom: 40px;">
                <!-- Usuarios -->
                <div class="product-card" style="border-top: 3px solid var(--color-amber); border-radius: 12px; padding: 24px; background: var(--bg-secondary); display: flex; align-items: center; gap: 20px;">
                    <div style="width: 56px; height: 56px; border-radius: 50%; background: rgba(249, 115, 22, 0.08); color: var(--color-amber); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;"><i class="fa-solid fa-users"></i></div>
                    <div>
                        <span style="font-size: 0.85rem; font-weight:600; color:var(--text-secondary); text-transform:uppercase;">Usuarios Registrados</span>
                        <h3 style="font-size: 1.8rem; font-weight:800; margin:0;"><?php echo $total_usuarios; ?></h3>
                    </div>
                </div>
                <!-- Pedidos -->
                <div class="product-card" style="border-top: 3px solid var(--color-green); border-radius: 12px; padding: 24px; background: var(--bg-secondary); display: flex; align-items: center; gap: 20px;">
                    <div style="width: 56px; height: 56px; border-radius: 50%; background: rgba(16, 185, 129, 0.08); color: var(--color-green); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;"><i class="fa-solid fa-cart-shopping"></i></div>
                    <div>
                        <span style="font-size: 0.85rem; font-weight:600; color:var(--text-secondary); text-transform:uppercase;">Pedidos Totales</span>
                        <h3 style="font-size: 1.8rem; font-weight:800; margin:0;"><?php echo $total_pedidos; ?></h3>
                    </div>
                </div>
                <!-- Pedidos Pendientes -->
                <div class="product-card" style="border-top: 3px solid var(--color-red); border-radius: 12px; padding: 24px; background: var(--bg-secondary); display: flex; align-items: center; gap: 20px;">
                    <div style="width: 56px; height: 56px; border-radius: 50%; background: rgba(239, 68, 68, 0.08); color: var(--color-red); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;"><i class="fa-solid fa-clock"></i></div>
                    <div>
                        <span style="font-size: 0.85rem; font-weight:600; color:var(--text-secondary); text-transform:uppercase;">Pedidos Pendientes</span>
                        <h3 style="font-size: 1.8rem; font-weight:800; color: var(--color-red); margin:0;"><?php echo $pedidos_pendientes; ?></h3>
                    </div>
                </div>
                <!-- Productos Disponibles -->
                <div class="product-card" style="border-top: 3px solid var(--text-primary); border-radius: 12px; padding: 24px; background: var(--bg-secondary); display: flex; align-items: center; gap: 20px;">
                    <div style="width: 56px; height: 56px; border-radius: 50%; background: rgba(15, 23, 42, 0.08); color: var(--text-primary); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;"><i class="fa-solid fa-utensils"></i></div>
                    <div>
                        <span style="font-size: 0.85rem; font-weight:600; color:var(--text-secondary); text-transform:uppercase;">Comidas en Menú</span>
                        <h3 style="font-size: 1.8rem; font-weight:800; margin:0;"><?php echo $total_productos; ?></h3>
                    </div>
                </div>
            </div>

            <!-- Resumen Financiero Rápido -->
            <div class="form-card" style="border-radius:12px; padding: 30px;">
                <h3 class="form-title" style="margin-bottom: 15px;"><span><i class="fa-solid fa-wallet" style="color:var(--color-amber);"></i> Balance General del Negocio</span></h3>
                <div style="display:flex; justify-content: space-between; align-items: center; flex-wrap:wrap; gap:20px;">
                    <p style="color:var(--text-secondary); max-width:600px; font-size:0.95rem; margin:0;">
                        Este panel muestra la simulación financiera global. Las compras reales no están habilitadas, por lo que estas métricas corresponden a las pruebas de simulación académica enviadas.
                    </p>
                    <div style="text-align: right; background: var(--bg-primary); padding: 15px 30px; border-radius:10px; border:var(--border-main);">
                        <span style="font-size:0.8rem; font-weight:700; color:var(--text-secondary); text-transform: uppercase;">Recaudado Total</span>
                        <h2 style="font-size: 2.2rem; font-weight: 800; color: var(--color-green); margin:0;">$<?php echo number_format($total_ventas, 2); ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== PESTAÑA: GESTIÓN DE PRODUCTOS ==================== -->
        <div class="admin-tab-content" id="admin-productos-tab" style="display: <?php echo $active_admin_tab === 'productos' ? 'block' : 'none'; ?>;">
            <div class="form-card" style="border-radius: 12px; padding: 30px;">
                <div class="form-title" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center;">
                    <span><i class="fa-solid fa-utensils" style="color: var(--color-amber);"></i> Catálogo de Productos</span>
                    <button type="button" class="btn btn-primary btn-sm" id="btn-add-product" style="border-radius: 50px;">
                        Añadir Producto <i class="fa-solid fa-plus"></i>
                    </button>
                </div>

                <!-- Tabla de Productos -->
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.95rem;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--text-primary); color: var(--text-primary); font-weight: 700;">
                                <th style="padding: 14px 10px;">Nombre</th>
                                <th style="padding: 14px 10px;">Categoría</th>
                                <th style="padding: 14px 10px; text-align: right;">Precio ($)</th>
                                <th style="padding: 14px 10px; text-align: right;">Precio Promo ($)</th>
                                <th style="padding: 14px 10px; text-align: center;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_productos as $prod) : 
                                $price = get_post_meta($prod->ID, '_regular_price', true);
                                $promo_price = get_post_meta($prod->ID, '_sale_price', true);
                                $is_promo = !empty($promo_price) ? '1' : '0';
                                $cats = get_the_terms($prod->ID, 'product_cat');
                                $cat_name = ($cats && !is_wp_error($cats)) ? $cats[0]->name : 'Sin Categoría';
                                $cat_id = ($cats && !is_wp_error($cats)) ? $cats[0]->term_id : 0;
                            ?>
                                <tr style="border-bottom: 1px solid rgba(15, 23, 42, 0.08);" class="admin-table-row">
                                    <td style="padding: 14px 10px; font-weight: 600;"><?php echo esc_html($prod->post_title); ?></td>
                                    <td style="padding: 14px 10px; color: var(--text-secondary);"><?php echo esc_html($cat_name); ?></td>
                                    <td style="padding: 14px 10px; text-align: right; font-weight: 600;">$<?php echo number_format((float)$price, 2); ?></td>
                                    <td style="padding: 14px 10px; text-align: right; font-weight: 600; color: var(--color-red);">
                                        <?php echo ($is_promo == '1') ? '$' . number_format((float)$promo_price, 2) : 'No aplica'; ?>
                                    </td>
                                    <td style="padding: 14px 10px; text-align: center; display: flex; justify-content: center; gap: 8px;">
                                        <button type="button" class="btn btn-secondary btn-sm edit-product-btn" 
                                                data-id="<?php echo $prod->ID; ?>" 
                                                data-title="<?php echo esc_attr($prod->post_title); ?>" 
                                                data-desc="<?php echo esc_attr($prod->post_content); ?>"
                                                data-price="<?php echo esc_attr($price); ?>"
                                                data-promo="<?php echo esc_attr($is_promo); ?>"
                                                data-promo-price="<?php echo esc_attr($promo_price); ?>"
                                                data-cat="<?php echo esc_attr($cat_id); ?>"
                                                style="padding: 6px 12px; border-radius: 4px;">
                                            <i class="fa-solid fa-pen"></i> Editar
                                        </button>
                                        
                                        <form action="" method="post" onsubmit="return confirm('¿Confirmas que deseas eliminar este producto de la carta?');" style="margin:0;">
                                            <?php wp_nonce_field('comida_rapida_admin_action', 'admin_nonce'); ?>
                                            <input type="hidden" name="prod_id" value="<?php echo $prod->ID; ?>">
                                            <button type="submit" name="action_delete_product" class="btn btn-secondary btn-sm" style="padding: 6px 12px; border-radius: 4px; border-color: #fca5a5; color: var(--color-red); background: #fef2f2;">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ==================== PESTAÑA: GESTIÓN DE PEDIDOS ==================== -->
        <div class="admin-tab-content" id="admin-pedidos-tab" style="display: <?php echo $active_admin_tab === 'pedidos' ? 'block' : 'none'; ?>;">
            <div class="form-card" style="border-radius: 12px; padding: 30px;">
                <h3 class="form-title" style="margin-bottom: 24px;">
                    <span><i class="fa-solid fa-receipt" style="color: var(--color-amber);"></i> Gestión de Pedidos</span>
                </h3>

                <!-- Tabla de Pedidos -->
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.95rem;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--text-primary); color: var(--text-primary); font-weight: 700;">
                                <th style="padding: 14px 10px;">Cliente</th>
                                <th style="padding: 14px 10px;">Contacto</th>
                                <th style="padding: 14px 10px;">Tipo</th>
                                <th style="padding: 14px 10px; text-align: right;">Total</th>
                                <th style="padding: 14px 10px; text-align: center;">Estado</th>
                                <th style="padding: 14px 10px; text-align: center;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_pedidos as $order) : 
                                $order_id = $order->get_id();
                                $tel = $order->get_billing_phone();
                                
                                // Determinar si es retiro o delivery según método de envío
                                $shipping_methods = $order->get_shipping_methods();
                                $method_title = '';
                                if (!empty($shipping_methods)) {
                                    $method = reset($shipping_methods);
                                    $method_title = strtolower($method->get_name());
                                }
                                $entrega = (strpos($method_title, 'envio') !== false || strpos($method_title, 'domicilio') !== false || strpos($method_title, 'delivery') !== false) ? 'delivery' : 'retiro';
                                
                                $total = $order->get_total();
                                
                                // Mapeamos el estado de WooCommerce
                                $wc_status = $order->get_status();
                                $estado = 'pendiente';
                                if ($wc_status === 'processing') {
                                    $estado = 'preparacion';
                                } elseif ($wc_status === 'completed') {
                                    $estado = 'entregado';
                                } elseif ($wc_status === 'cancelled') {
                                    $estado = 'cancelado';
                                }
                                
                                $fecha = $order->get_date_created() ? $order->get_date_created()->date('d/m/Y H:i') : '';
                                
                                // Nombre completo del cliente
                                $title = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
                                if (trim($title) === '') {
                                    $title = "Cliente #" . $order_id;
                                }

                                // Detalle de productos ordenados para el modal popup
                                $items_desc = '<strong>Productos Pedidos:</strong><ul style="margin-left: 20px; margin-top: 10px; margin-bottom: 15px; list-style-type: disc;">';
                                foreach ($order->get_items() as $item_id => $item) {
                                    $product_name = $item->get_name();
                                    $quantity     = $item->get_quantity();
                                    $item_total   = $item->get_total();
                                    $items_desc  .= '<li>' . esc_html($product_name) . ' x ' . intval($quantity) . ' - <strong>$' . number_format((float)$item_total, 2) . '</strong></li>';
                                }
                                $items_desc .= '</ul>';
                                
                                // Dirección y Correo
                                $address = $order->get_billing_address_1();
                                if ($order->get_billing_address_2()) {
                                    $address .= ' - ' . $order->get_billing_address_2();
                                }
                                $items_desc .= '<strong>Dirección del Cliente:</strong><br>' . esc_html($address) . '<br><br>';
                                $items_desc .= '<strong>Correo del Cliente:</strong><br>' . esc_html($order->get_billing_email());
                            ?>
                                <tr style="border-bottom: 1px solid rgba(15, 23, 42, 0.08);" class="admin-table-row">
                                    <td style="padding: 14px 10px; font-weight: 600;"><?php echo esc_html($title); ?></td>
                                    <td style="padding: 14px 10px; font-size:0.88rem; color: var(--text-secondary);">
                                        <strong>Telf:</strong> <?php echo esc_html($tel); ?><br>
                                        <span style="font-size:0.78rem;"><?php echo esc_html($fecha); ?></span>
                                    </td>
                                    <td style="padding: 14px 10px;">
                                        <span style="font-size: 0.8rem; font-weight: 600; padding: 2px 6px; border-radius: 4px; border: 1px solid <?php echo $entrega == 'delivery' ? 'var(--color-amber)' : 'var(--text-secondary)'; ?>; color: <?php echo $entrega == 'delivery' ? 'var(--color-amber)' : 'var(--text-secondary)'; ?>;">
                                            <?php echo $entrega == 'delivery' ? 'Domicilio' : 'Retiro'; ?>
                                        </span>
                                    </td>
                                    <td style="padding: 14px 10px; text-align: right; font-weight: 700; color: var(--color-green);">$<?php echo number_format((float)$total, 2); ?></td>
                                    <td style="padding: 14px 10px; text-align: center;">
                                        <!-- Selector directo del estado del pedido -->
                                        <form action="" method="post" style="margin:0;">
                                            <?php wp_nonce_field('comida_rapida_admin_action', 'admin_nonce'); ?>
                                            <input type="hidden" name="pedido_id" value="<?php echo $order_id; ?>">
                                            <input type="hidden" name="action_update_order_status" value="1">
                                            
                                            <select name="nuevo_estado" onchange="this.form.submit();" style="font-size: 0.85rem; font-weight: 600; padding: 4px 8px; border-radius: 6px; border: var(--border-main); background: white; cursor: pointer; color: <?php 
                                                if ($estado === 'entregado') echo 'var(--color-green)';
                                                elseif ($estado === 'preparacion') echo 'var(--color-amber)';
                                                elseif ($estado === 'cancelado') echo 'var(--color-red)';
                                                else echo 'var(--text-secondary)';
                                            ?>;">
                                                <option value="pendiente" <?php selected($estado, 'pendiente'); ?>>Pendiente</option>
                                                <option value="preparacion" <?php selected($estado, 'preparacion'); ?>>En preparación</option>
                                                <option value="entregado" <?php selected($estado, 'entregado'); ?>>Entregado</option>
                                                <option value="cancelado" <?php selected($estado, 'cancelado'); ?>>Cancelado</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td style="padding: 14px 10px; text-align: center; display: flex; justify-content: center; gap: 8px;">
                                        <button type="button" class="btn btn-secondary btn-sm open-details-btn" style="padding: 6px 12px; border-radius: 4px;" data-title="Pedido de <?php echo esc_attr($title); ?>" data-content="<?php echo esc_attr($items_desc); ?>">
                                            <i class="fa-solid fa-eye"></i> Detalle
                                        </button>
                                        
                                        <form action="" method="post" onsubmit="return confirm('¿Eliminar definitivamente este pedido del panel?');" style="margin:0;">
                                            <?php wp_nonce_field('comida_rapida_admin_action', 'admin_nonce'); ?>
                                            <input type="hidden" name="pedido_id" value="<?php echo $order_id; ?>">
                                            <input type="hidden" name="action_delete_pedido" value="1">
                                            <button type="submit" class="btn btn-secondary btn-sm" style="padding: 6px 12px; border-radius: 4px; border-color: #fca5a5; color: var(--color-red); background: #fef2f2;">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ==================== PESTAÑA: GESTIÓN DE USUARIOS ==================== -->
        <div class="admin-tab-content" id="admin-usuarios-tab" style="display: <?php echo $active_admin_tab === 'usuarios' ? 'block' : 'none'; ?>;">
            <div class="form-card" style="border-radius: 12px; padding: 30px;">
                <h3 class="form-title" style="margin-bottom: 24px;">
                    <span><i class="fa-solid fa-users" style="color: var(--color-amber);"></i> Clientes y Personal Registrado</span>
                </h3>

                <!-- Tabla de Usuarios -->
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.95rem;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--text-primary); color: var(--text-primary); font-weight: 700;">
                                <th style="padding: 14px 10px;">Usuario</th>
                                <th style="padding: 14px 10px;">Nombre Completo</th>
                                <th style="padding: 14px 10px;">Correo Electrónico</th>
                                <th style="padding: 14px 10px;">Rol de Sistema</th>
                                <th style="padding: 14px 10px; text-align: center;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_users as $usr) : 
                                $is_admin_user = ($usr->role === 'administrador');
                                $role_display = $is_admin_user ? 'Administrador' : 'Cliente';
                                $current_user_data = comida_rapida_get_current_user();
                            ?>
                                <tr style="border-bottom: 1px solid rgba(15, 23, 42, 0.08);" class="admin-table-row">
                                    <td style="padding: 14px 10px; font-weight: 600;">
                                        <i class="fa-solid fa-circle-user" style="color:var(--text-muted); margin-right:6px;"></i> <?php echo esc_html($usr->user_login); ?>
                                    </td>
                                    <td style="padding: 14px 10px; color: var(--text-secondary);"><?php echo esc_html($usr->display_name); ?></td>
                                    <td style="padding: 14px 10px; color: var(--text-secondary);"><?php echo esc_html($usr->user_email); ?></td>
                                    <td style="padding: 14px 10px;">
                                        <span style="font-size: 0.78rem; font-weight: 700; padding: 2px 8px; border-radius: 4px; background: <?php echo $is_admin_user ? '#ecfdf5' : 'var(--bg-primary)'; ?>; color: <?php echo $is_admin_user ? 'var(--color-green)' : 'var(--text-secondary)'; ?>; border: 1px solid <?php echo $is_admin_user ? 'var(--color-green)' : '#cbd5e1'; ?>;">
                                            <?php echo esc_html($role_display); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 14px 10px; text-align: center;">
                                        <!-- Formulario para eliminar usuario -->
                                        <?php if ($current_user_data && (int)$usr->id !== (int)$current_user_data->id) : ?>
                                            <form action="" method="post" onsubmit="return confirm('¿Confirma que desea eliminar permanentemente esta cuenta del sistema? Todos sus datos serán eliminados.');" style="margin: 0;">
                                                <?php wp_nonce_field('comida_rapida_admin_action', 'admin_nonce'); ?>
                                                <input type="hidden" name="user_id" value="<?php echo $usr->id; ?>">
                                                <input type="hidden" name="action_delete_user" value="1">
                                                <button type="submit" class="btn btn-secondary btn-sm" style="padding: 6px 12px; border-radius: 4px; border-color: #fca5a5; color: var(--color-red); background: #fef2f2;">
                                                    <i class="fa-solid fa-user-xmark"></i> Eliminar
                                                </button>
                                            </form>
                                        <?php else : ?>
                                            <span style="font-size:0.85rem; color:var(--text-muted); font-style:italic;">Tu cuenta</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- MODAL: AGREGAR / EDITAR PRODUCTO -->
<div class="success-modal-wrapper" id="admin-product-modal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); display: none; align-items: center; justify-content: center; z-index: 2000; padding: 20px;">
    <div class="form-card" style="width: 100%; max-width: 550px; border-radius: 12px; box-shadow: var(--shadow-lg); animation: slideUp 0.3s ease; max-height: 95vh; overflow-y: auto;">
        <h3 class="form-title" style="margin-bottom: 20px;">
            <span id="product-modal-title">Añadir Nuevo Producto</span>
            <button type="button" id="close-product-modal-btn" style="cursor:pointer; background:none; border:none; font-size:1.4rem; color:var(--text-muted);"><i class="fa-solid fa-xmark"></i></button>
        </h3>
        
        <form action="" method="post" id="admin-product-form" enctype="multipart/form-data">
            <?php wp_nonce_field('comida_rapida_admin_action', 'admin_nonce'); ?>
            <input type="hidden" name="prod_id" id="form-prod-id" value="">
            <input type="hidden" name="action_add_product" id="form-prod-action-flag" value="1"> <!-- Cambiado dinámicamente a action_edit_product al editar -->

            <div class="form-group">
                <label for="prod_title" class="form-label">Nombre del Producto *</label>
                <input type="text" id="prod_title" name="prod_title" class="form-input" placeholder="Ej. Burger Hawaiana" required>
            </div>

            <div class="form-group">
                <label for="prod_desc" class="form-label">Descripción del Producto *</label>
                <textarea id="prod_desc" name="prod_desc" class="form-input" rows="3" placeholder="Ej. Doble carne, piña asada, queso mozzarella..." required></textarea>
            </div>

            <div class="form-row-grid">
                <div class="form-group">
                    <label for="prod_price" class="form-label">Precio Regular ($) *</label>
                    <input type="number" step="0.01" id="prod_price" name="prod_price" class="form-input" placeholder="Ej. 7.99" required>
                </div>
                <div class="form-group">
                    <label for="prod_category" class="form-label">Categoría *</label>
                    <select id="prod_category" name="prod_category" class="form-input" required style="cursor:pointer; appearance: none; -webkit-appearance: none;">
                        <option value="">Seleccionar...</option>
                        <?php foreach ($categories_terms as $term) : ?>
                            <option value="<?php echo $term->term_id; ?>"><?php echo esc_html($term->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="prod_image" class="form-label">Imagen del Producto (Opcional)</label>
                <input type="file" id="prod_image" name="prod_image" class="form-input" accept="image/*" style="padding: 10px;">
                <small style="font-size:0.8rem; color:var(--text-muted); display:block; margin-top:4px;">Deja en blanco para conservar la actual o usar por defecto.</small>
            </div>

            <div style="margin: 15px 0; padding: 12px; background:var(--bg-primary); border-radius:8px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight:700; color:var(--text-primary); margin-bottom:8px;">
                    <input type="checkbox" id="prod_promo" name="prod_promo" value="1" style="width: 18px; height: 18px; accent-color: var(--color-amber);"> ¿Tiene precio de Combo / Promocional?
                </label>
                <div id="promo-price-field-group" style="display:none;">
                    <label for="prod_promo_price" class="form-label">Precio Combo Especial ($)</label>
                    <input type="number" step="0.01" id="prod_promo_price" name="prod_promo_price" class="form-input" placeholder="Ej. 5.99">
                </div>
            </div>

            <button type="submit" class="btn btn-primary form-submit-btn" style="border-radius: 50px; margin-top:10px;">
                Guardar Producto <i class="fa-solid fa-cloud-arrow-up"></i>
            </button>
        </form>
    </div>
</div>

<!-- MODAL: DETALLES DE PEDIDOS -->
<div class="success-modal-wrapper" id="admin-details-modal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); display: none; align-items: center; justify-content: center; z-index: 2000; padding: 20px;">
    <div class="form-card" style="width: 100%; max-width: 600px; border-radius: 12px; box-shadow: var(--shadow-lg); animation: slideUp 0.3s ease; max-height: 90vh; overflow-y: auto;">
        <h3 class="form-title" style="margin-bottom: 20px;">
            <span id="modal-details-title">Detalle del Pedido</span>
            <button type="button" id="close-details-modal-btn" style="cursor:pointer; background:none; border:none; font-size:1.4rem; color:var(--text-muted);"><i class="fa-solid fa-xmark"></i></button>
        </h3>
        
        <div id="modal-details-body" style="font-size: 0.92rem; color: var(--text-secondary); margin-bottom: 24px;">
            <!-- Contenido dinámico -->
        </div>

        <button type="button" class="btn btn-primary" id="accept-details-modal-btn" style="width:100%; border-radius: 50px;">Aceptar y Volver</button>
    </div>
</div>

<style>
.admin-table-row:hover {
    background: rgba(15, 23, 42, 0.02);
}
@keyframes slideUp {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // --- 1. MANEJO DE PESTAÑAS (TABS) ---
    const filterButtons = document.querySelectorAll('.admin-menu-tabs .filter-btn');
    const tabContents = document.querySelectorAll('.admin-tab-content');
    
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            filterButtons.forEach(b => b.classList.remove('active'));
            tabContents.forEach(tc => tc.style.display = 'none');
            
            this.classList.add('active');
            const targetId = this.getAttribute('data-target');
            const targetEl = document.getElementById(targetId);
            if (targetEl) {
                targetEl.style.display = 'block';
            }
        });
    });

    // --- 2. MODAL DETALLES PEDIDOS ---
    const adminDetailsModal = document.getElementById('admin-details-modal');
    const closeDetailsModalBtn = document.getElementById('close-details-modal-btn');
    const acceptDetailsModalBtn = document.getElementById('accept-details-modal-btn');
    const modalDetailsTitle = document.getElementById('modal-details-title');
    const modalDetailsBody = document.getElementById('modal-details-body');

    document.querySelectorAll('.open-details-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            modalDetailsTitle.textContent = this.getAttribute('data-title');
            modalDetailsBody.innerHTML = this.getAttribute('data-content');
            adminDetailsModal.style.display = 'flex';
        });
    });

    function closeDetailsModal() {
        if (adminDetailsModal) adminDetailsModal.style.display = 'none';
    }
    if (closeDetailsModalBtn) closeDetailsModalBtn.addEventListener('click', closeDetailsModal);
    if (acceptDetailsModalBtn) acceptDetailsModalBtn.addEventListener('click', closeDetailsModal);

    // --- 3. MODAL PRODUCTOS (CREAR / EDITAR) ---
    const adminProductModal = document.getElementById('admin-product-modal');
    const btnAddProduct = document.getElementById('btn-add-product');
    const closeProductModalBtn = document.getElementById('close-product-modal-btn');
    const productModalTitle = document.getElementById('product-modal-title');
    const prodPromoCheckbox = document.getElementById('prod_promo');
    const promoPriceFieldGroup = document.getElementById('promo-price-field-group');
    
    // Elementos del Formulario
    const formProdId = document.getElementById('form-prod-id');
    const formProdActionFlag = document.getElementById('form-prod-action-flag');
    const formProdTitle = document.getElementById('prod_title');
    const formProdDesc = document.getElementById('prod_desc');
    const formProdPrice = document.getElementById('prod_price');
    const formProdPromoPrice = document.getElementById('prod_promo_price');
    const formProdCat = document.getElementById('prod_category');

    // Toggle para campo precio combo
    if (prodPromoCheckbox) {
        prodPromoCheckbox.addEventListener('change', function() {
            promoPriceFieldGroup.style.display = this.checked ? 'block' : 'none';
            if (this.checked) {
                formProdPromoPrice.setAttribute('required', 'required');
            } else {
                formProdPromoPrice.removeAttribute('required');
                formProdPromoPrice.value = '';
            }
        });
    }

    // Abrir para añadir
    if (btnAddProduct) {
        btnAddProduct.addEventListener('click', function() {
            productModalTitle.textContent = 'Añadir Nuevo Producto';
            formProdId.value = '';
            formProdActionFlag.name = 'action_add_product';
            document.getElementById('admin-product-form').reset();
            promoPriceFieldGroup.style.display = 'none';
            adminProductModal.style.display = 'flex';
        });
    }

    // Abrir para editar
    document.querySelectorAll('.edit-product-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            productModalTitle.textContent = 'Editar Producto';
            formProdId.value = this.getAttribute('data-id');
            formProdActionFlag.name = 'action_edit_product';
            
            formProdTitle.value = this.getAttribute('data-title');
            formProdDesc.value = this.getAttribute('data-desc');
            formProdPrice.value = this.getAttribute('data-price');
            formProdCat.value = this.getAttribute('data-cat');
            
            const isPromo = this.getAttribute('data-promo') === '1';
            prodPromoCheckbox.checked = isPromo;
            promoPriceFieldGroup.style.display = isPromo ? 'block' : 'none';
            
            if (isPromo) {
                formProdPromoPrice.value = this.getAttribute('data-promo-price');
                formProdPromoPrice.setAttribute('required', 'required');
            } else {
                formProdPromoPrice.value = '';
                formProdPromoPrice.removeAttribute('required');
            }
            
            adminProductModal.style.display = 'flex';
        });
    });

    function closeProductModal() {
        if (adminProductModal) adminProductModal.style.display = 'none';
    }
    if (closeProductModalBtn) closeProductModalBtn.addEventListener('click', closeProductModal);

    // Cerrar modals al hacer clic fuera
    window.addEventListener('click', function(e) {
        if (e.target === adminDetailsModal) closeDetailsModal();
        if (e.target === adminProductModal) closeProductModal();
    });
});
</script>
