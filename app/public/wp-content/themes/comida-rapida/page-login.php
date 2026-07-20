<?php
/**
 * Template Name: Página Iniciar Sesión / Registro
 * Description: Vista para el inicio de sesión y registro de clientes en el frontend con diseño premium mejorado.
 */

// Procesar cierre de sesión personalizado
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    comida_rapida_logout();
    wp_safe_redirect(home_url('?view=login'));
    exit;
}

// Procesar inicio de sesión
$login_error = '';
if (isset($_POST['login_submit'])) {
    if (!isset($_POST['login_nonce']) || !wp_verify_nonce($_POST['login_nonce'], 'comida_rapida_login_action')) {
        $login_error = 'Error de seguridad. Por favor, intente de nuevo.';
    } else {
        global $wpdb;
        $table_name = $wpdb->prefix . 'comida_rapida_clientes';
        
        $login_input = sanitize_text_field($_POST['log']);
        $password = $_POST['pwd'];
        
        $user = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE username = %s OR email = %s",
            $login_input, $login_input
        ));

        if (!$user) {
            // Mensaje genérico para evitar la enumeración de nombres de usuario
            $login_error = 'El nombre de usuario/correo o la contraseña no son válidos.';
            comida_rapida_registrar_evento_seguridad('ADVERTENCIA', 'Intento de login fallido con usuario inexistente: ' . $login_input, '', 'Acceso Denegado');
        } else {
            // Verificar bloqueo de cuenta
            $current_time = time();
            if ($user->lockout_until && strtotime($user->lockout_until) > $current_time) {
                $remaining = strtotime($user->lockout_until) - $current_time;
                $minutes = ceil($remaining / 60);
                $login_error = "Esta cuenta ha sido bloqueada temporalmente debido a múltiples intentos de inicio de sesión fallidos. Inténtalo de nuevo en $minutes minutos.";
                comida_rapida_registrar_evento_seguridad('ADVERTENCIA', 'Intento de inicio de sesión en cuenta temporalmente bloqueada: ' . $user->username, '', 'Bloqueado');
            } else {
                if (password_verify($password, $user->password)) {
                    // Restablecer intentos fallidos tras inicio de sesión exitoso
                    $wpdb->update(
                        $table_name,
                        array('failed_attempts' => 0, 'lockout_until' => null),
                        array('id' => $user->id),
                        array('%d', '%s'),
                        array('%d')
                    );

                    // Iniciar sesión en el sitio web
                    $_SESSION['comida_rapida_cliente_id'] = $user->id;
                    $_SESSION['comida_rapida_cliente_name'] = $user->username;
                    $_SESSION['comida_rapida_cliente_email'] = $user->email;
                    $_SESSION['comida_rapida_role'] = $user->role;
                    
                    comida_rapida_registrar_evento_seguridad('INFO', 'Inicio de sesión exitoso del usuario: ' . $user->username . ' (' . ucfirst($user->role) . ')', '', 'Acceso Concedido');
                    
                    if ($user->role === 'administrador') {
                        wp_safe_redirect(add_query_arg('view', 'admin', home_url('/')));
                    } else {
                        wp_safe_redirect(home_url('/'));
                    }
                    exit;
                } else {
                    // Incrementar intentos fallidos
                    $failed_attempts = intval($user->failed_attempts) + 1;
                    $lockout_until = null;
                    
                    if ($failed_attempts >= 5) {
                        // Bloquear por 30 minutos (1800 segundos)
                        $lockout_until = date('Y-m-d H:i:s', $current_time + 1800);
                        $login_error = 'Demasiados intentos fallidos. Tu cuenta ha sido bloqueada por 30 minutos.';
                        comida_rapida_registrar_evento_seguridad('CRÍTICO', 'Cuenta bloqueada por múltiples intentos de login fallidos: ' . $user->username, '', 'Bloqueo 30m');
                    } else {
                        $remaining_attempts = 5 - $failed_attempts;
                        $login_error = "El nombre de usuario/correo o la contraseña no son válidos. Te quedan $remaining_attempts intentos antes del bloqueo.";
                        comida_rapida_registrar_evento_seguridad('ADVERTENCIA', 'Contraseña incorrecta para el usuario: ' . $user->username, '', 'Intento ' . $failed_attempts . '/5');
                    }

                    $wpdb->update(
                        $table_name,
                        array('failed_attempts' => $failed_attempts, 'lockout_until' => $lockout_until),
                        array('id' => $user->id),
                        array('%d', '%s'),
                        array('%d')
                    );
                }
            }
        }
    }
}

// Procesar registro
$register_error = '';
if (isset($_POST['register_submit'])) {
    if (!isset($_POST['register_nonce']) || !wp_verify_nonce($_POST['register_nonce'], 'comida_rapida_register_action')) {
        $register_error = 'Error de seguridad. Por favor, intente de nuevo.';
    } else {
        global $wpdb;
        $table_name = $wpdb->prefix . 'comida_rapida_clientes';
        
        $username = sanitize_user($_POST['reg_username']);
        $email = sanitize_email($_POST['reg_email']);
        $password = $_POST['reg_password'];
        $password_confirm = $_POST['reg_password_confirm'];

        // Comprobaciones de existencia
        $user_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE username = %s", $username
        ));
        $email_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE email = %s", $email
        ));

        if (empty($username) || empty($email) || empty($password)) {
            $register_error = 'Por favor complete todos los campos obligatorios.';
        } elseif (!is_email($email)) {
            $register_error = 'El correo electrónico no es válido.';
        } elseif ($user_exists > 0) {
            $register_error = 'Este nombre de usuario ya está en uso.';
        } elseif ($email_exists > 0) {
            $register_error = 'Este correo electrónico ya está registrado.';
        } elseif ($password !== $password_confirm) {
            $register_error = 'Las contraseñas no coinciden.';
        } elseif (strlen($password) < 8) {
            $register_error = 'La contraseña debe tener al menos 8 caracteres para cumplir con las políticas PCI DSS.';
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $register_error = 'La contraseña debe incluir al menos una letra mayúscula.';
        } elseif (!preg_match('/[a-z]/', $password)) {
            $register_error = 'La contraseña debe incluir al menos una letra minúscula.';
        } elseif (!preg_match('/[0-9]/', $password)) {
            $register_error = 'La contraseña debe incluir al menos un número.';
        } elseif (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $register_error = 'La contraseña debe incluir al menos un carácter especial (ej. !@#$%^&*).';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $inserted = $wpdb->insert(
                $table_name,
                array(
                    'username' => $username,
                    'email' => $email,
                    'password' => $hashed_password,
                    'role' => 'cliente'
                ),
                array('%s', '%s', '%s', '%s')
            );
            
            if ($inserted === false) {
                $register_error = 'Error al registrar el usuario en la base de datos.';
            } else {
                $client_id = $wpdb->insert_id;
                
                // Iniciar sesión automáticamente
                $_SESSION['comida_rapida_cliente_id'] = $client_id;
                $_SESSION['comida_rapida_cliente_name'] = $username;
                $_SESSION['comida_rapida_cliente_email'] = $email;
                $_SESSION['comida_rapida_role'] = 'cliente';
                
                wp_safe_redirect(home_url('/'));
                exit;
            }
        }
    }
}

// Procesar solicitud de recuperación de contraseña
$forgot_error = '';
$forgot_success = '';
if (isset($_POST['forgot_submit'])) {
    if (!isset($_POST['forgot_nonce']) || !wp_verify_nonce($_POST['forgot_nonce'], 'comida_rapida_forgot_action')) {
        $forgot_error = 'Error de seguridad. Por favor, intente de nuevo.';
    } else {
        global $wpdb;
        $table_name = $wpdb->prefix . 'comida_rapida_clientes';
        $forgot_input = sanitize_text_field($_POST['forgot_input']);
        
        $user = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE username = %s OR email = %s",
            $forgot_input, $forgot_input
        ));
        
        // Mensaje de éxito genérico para evitar la enumeración de usuarios
        $forgot_success = 'Si la cuenta existe, se ha enviado un correo electrónico con instrucciones para restablecer tu contraseña.';
        
        if ($user) {
            $token = wp_generate_password(32, false);
            $expiry = date('Y-m-d H:i:s', current_time('timestamp') + 3600); // 1 hora de validez
            
            $wpdb->update(
                $table_name,
                array(
                    'reset_token' => $token,
                    'reset_token_expiry' => $expiry
                ),
                array('id' => $user->id),
                array('%s', '%s'),
                array('%d')
            );
            
            comida_rapida_registrar_evento_seguridad('INFO', 'Solicitud de recuperación de contraseña para: ' . $user->username, '', 'Recuperación Solicitada');
            
            // Enviar correo
            $reset_link = add_query_arg(array(
                'view' => 'login',
                'action' => 'reset_password',
                'token' => $token,
                'email' => $user->email
            ), home_url('/'));
            
            $subject = 'Recuperar contraseña - Comida Rápida';
            $headers = array(
                'Content-Type: text/html; charset=UTF-8',
                'From: Comida Rápida <no-reply@' . parse_url(home_url(), PHP_URL_HOST) . '>'
            );
            
            $message = '
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; background-color: #0f172a; color: #f1f5f9; padding: 20px; }
                    .card { background-color: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 30px; max-width: 500px; margin: 0 auto; }
                    .header { text-align: center; margin-bottom: 20px; }
                    .title { color: #f97316; font-size: 24px; font-weight: bold; }
                    .text { font-size: 16px; line-height: 1.5; color: #cbd5e1; }
                    .btn-container { text-align: center; margin: 30px 0; }
                    .btn { background-color: #f97316; color: #ffffff !important; padding: 12px 24px; text-decoration: none; border-radius: 50px; font-weight: bold; display: inline-block; }
                    .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #64748b; border-top: 1px solid #334155; padding-top: 20px; }
                </style>
            </head>
            <body>
                <div class="card">
                    <div class="header">
                        <span class="title">Comida Rápida</span>
                    </div>
                    <p class="text">Hola <strong>' . esc_html($user->username) . '</strong>,</p>
                    <p class="text">Hemos recibido una solicitud para restablecer la contraseña de tu cuenta de cliente. Haz clic en el botón de abajo para elegir una nueva contraseña. Este enlace es válido por 1 hora.</p>
                    <div class="btn-container">
                        <a href="' . esc_url($reset_link) . '" class="btn">Restablecer Contraseña</a>
                    </div>
                    <p class="text" style="font-size: 13px; color: #94a3b8;">Si no has solicitado este cambio, puedes ignorar este correo con tranquilidad. Tu contraseña seguirá siendo segura y sin cambios.</p>
                    <div class="footer">
                        Proyecto Académico Simulador de Comercio Electrónico.
                    </div>
                </div>
            </body>
            </html>';
            
            wp_mail($user->email, $subject, $message, $headers);
        }
    }
}

// Procesar el restablecimiento de contraseña
$reset_error = '';
$valid_reset_token = false;
$reset_user = null;

if (isset($_GET['action']) && $_GET['action'] === 'reset_password' && isset($_GET['token']) && isset($_GET['email'])) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'comida_rapida_clientes';
    $token = sanitize_text_field($_GET['token']);
    $email = sanitize_email($_GET['email']);
    
    $reset_user = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE email = %s AND reset_token = %s AND reset_token_expiry > %s",
        $email, $token, current_time('mysql')
    ));
    
    if ($reset_user) {
        $valid_reset_token = true;
        
        if (isset($_POST['reset_submit'])) {
            if (!isset($_POST['reset_nonce']) || !wp_verify_nonce($_POST['reset_nonce'], 'comida_rapida_reset_action')) {
                $reset_error = 'Error de seguridad. Por favor, intente de nuevo.';
            } else {
                $password = $_POST['new_password'];
                $password_confirm = $_POST['new_password_confirm'];
                
                if (empty($password)) {
                    $reset_error = 'Por favor complete todos los campos obligatorios.';
                } elseif ($password !== $password_confirm) {
                    $reset_error = 'Las contraseñas no coinciden.';
                } elseif (strlen($password) < 8) {
                    $reset_error = 'La contraseña debe tener al menos 8 caracteres para cumplir con las políticas PCI DSS.';
                } elseif (!preg_match('/[A-Z]/', $password)) {
                    $reset_error = 'La contraseña debe incluir al menos una letra mayúscula.';
                } elseif (!preg_match('/[a-z]/', $password)) {
                    $reset_error = 'La contraseña debe incluir al menos una letra minúscula.';
                } elseif (!preg_match('/[0-9]/', $password)) {
                    $reset_error = 'La contraseña debe incluir al menos un número.';
                } elseif (!preg_match('/[^A-Za-z0-9]/', $password)) {
                    $reset_error = 'La contraseña debe incluir al menos un carácter especial (ej. !@#$%^&*).';
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    $wpdb->update(
                        $table_name,
                        array(
                            'password' => $hashed_password,
                            'reset_token' => null,
                            'reset_token_expiry' => null,
                            'failed_attempts' => 0,
                            'lockout_until' => null
                        ),
                        array('id' => $reset_user->id),
                        array('%s', '%s', '%s', '%d', '%s'),
                        array('%d')
                    );
                    
                    comida_rapida_registrar_evento_seguridad('INFO', 'Contraseña restablecida con éxito para el usuario: ' . $reset_user->username, '', 'Contraseña Restablecida');
                    
                    wp_safe_redirect(add_query_arg(array('view' => 'login', 'notice' => 'password_reset_success'), home_url('/')));
                    exit;
                }
            }
        }
    } else {
        $reset_error = 'El enlace de recuperación es inválido o ha expirado. Por favor, solicita uno nuevo.';
    }
}

// Determinar pestaña activa
$active_tab = 'login';
if ($valid_reset_token) {
    $active_tab = 'reset';
} elseif (!empty($register_error) || isset($_POST['register_submit'])) {
    $active_tab = 'register';
} elseif (!empty($forgot_error) || !empty($forgot_success) || isset($_POST['forgot_submit'])) {
    $active_tab = 'forgot';
}
?>

<?php if (comida_rapida_is_logged_in()) : 
    $current_user = comida_rapida_get_current_user();
    $is_admin = comida_rapida_is_admin();
    
    // Obtener pedidos por correo de WooCommerce
    $customer_orders = array();
    if (class_exists('WooCommerce')) {
        $customer_orders = wc_get_orders(array(
            'billing_email' => $current_user->user_email,
            'limit'         => 10,
            'orderby'       => 'date',
            'order'         => 'DESC',
        ));
    }
    
    // Estadísticas
    $total_spent = 0;
    $order_count = count($customer_orders);
    $active_order = null;
    foreach ($customer_orders as $o) {
        $status = $o->get_status();
        if ($status !== 'cancelled' && $status !== 'failed') {
            $total_spent += floatval($o->get_total());
        }
        if (!$active_order && in_array($status, array('pending', 'on-hold', 'processing'))) {
            $active_order = $o;
        }
    }
    if (!$active_order && !empty($customer_orders)) {
        $active_order = $customer_orders[0];
    }
    
    // Configurar tracker
    $progress_percent = 25;
    $step_class = array('active', '', '', '');
    $status_desc = 'Tu pedido ha sido recibido y está en cola.';
    
    if ($active_order) {
        $status = $active_order->get_status();
        if ($status === 'pending' || $status === 'on-hold') {
            $progress_percent = 25;
            $step_class = array('active', '', '', '');
            $status_desc = 'Tu pedido ha sido recibido y estamos esperando confirmar el pago.';
        } elseif ($status === 'processing') {
            $progress_percent = 60;
            $step_class = array('active', 'active', 'active', '');
            $status_desc = '¡Tu pedido se está preparando en la cocina y pronto saldrá con el repartidor!';
        } elseif ($status === 'completed') {
            $progress_percent = 100;
            $step_class = array('active', 'active', 'active', 'active');
            $status_desc = 'Tu pedido ha sido entregado. ¡Que lo disfrutes!';
        } else {
            $progress_percent = 0;
            $step_class = array('', '', '', '');
            $status_desc = 'Este pedido fue cancelado o fallido.';
        }
    }
?>
<!-- VISTA DE USUARIO AUTENTICADO (PORTAL CLIENTE) -->
<section class="section-padding order-section" id="inicio-sesion" style="min-height: 85vh; display: flex; align-items: center; background-color: var(--bg-primary);">
    <div class="container" style="max-width: 1100px; display: grid; grid-template-columns: 320px 1fr; gap: 30px; width: 100%;">
        
        <!-- Panel Izquierdo: Datos de Perfil -->
        <div style="background: var(--bg-secondary); border: var(--border-main); border-radius: 16px; padding: 30px; text-align: center; box-shadow: var(--shadow-lg); display: flex; flex-direction: column; align-items: center; height: fit-content;">
            <div class="user-avatar-wrapper" style="margin-bottom: 20px; position: relative;">
                <i class="fa-solid fa-circle-user" style="font-size: 6rem; color: var(--color-amber);"></i>
                <span style="position: absolute; bottom: 0; right: 0; background: var(--color-green); color: white; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; border: 2px solid var(--bg-secondary);" title="Cuenta Activa">
                    <i class="fa-solid fa-check"></i>
                </span>
            </div>
            <h3 style="font-family: var(--font-sans); font-size: 1.4rem; font-weight: 700; color: var(--text-primary); margin-bottom: 4px;">
                <?php echo esc_html($current_user->display_name); ?>
            </h3>
            <span style="background: rgba(249, 115, 22, 0.08); color: var(--color-amber); font-size: 0.78rem; font-weight: 700; padding: 4px 12px; border-radius: 50px; text-transform: uppercase; margin-bottom: 20px; border: 1px solid rgba(249, 115, 22, 0.2);">
                <?php echo $is_admin ? 'Administrador' : 'Cliente VIP'; ?>
            </span>
            
            <div style="width: 100%; border-top: var(--border-main); padding-top: 20px; margin-bottom: 25px; text-align: left; font-size: 0.88rem; color: var(--text-secondary);">
                <div style="margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-envelope" style="color: var(--text-muted); width: 16px;"></i>
                    <span style="word-break: break-all;"><?php echo esc_html($current_user->user_email); ?></span>
                </div>
                <div style="margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-calendar-alt" style="color: var(--text-muted); width: 16px;"></i>
                    <span>Miembro desde 2026</span>
                </div>
            </div>

            <!-- Stats Grid -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; width: 100%; margin-bottom: 30px;">
                <div style="background: var(--bg-primary); padding: 12px; border-radius: 8px; border: var(--border-main);">
                    <span style="font-size: 0.75rem; color: var(--text-muted); display: block; text-transform: uppercase; font-weight: 600;">Pedidos</span>
                    <strong style="font-size: 1.25rem; color: var(--text-primary);"><?php echo $order_count; ?></strong>
                </div>
                <div style="background: var(--bg-primary); padding: 12px; border-radius: 8px; border: var(--border-main);">
                    <span style="font-size: 0.75rem; color: var(--text-muted); display: block; text-transform: uppercase; font-weight: 600;">Gastado</span>
                    <strong style="font-size: 1.1rem; color: var(--color-green);">$<?php echo number_format($total_spent, 2); ?></strong>
                </div>
            </div>

            <!-- Acciones -->
            <div style="display: flex; flex-direction: column; gap: 12px; width: 100%;">
                <?php if ($is_admin) : ?>
                    <a href="<?php echo esc_url(add_query_arg('view', 'admin', home_url('/'))); ?>" class="btn btn-primary" style="border-radius: 50px; width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 0.9rem;">
                        Panel de Administración <i class="fa-solid fa-screwdriver-wrench"></i>
                    </a>
                <?php else : ?>
                    <a href="<?php echo esc_url(add_query_arg('view', 'menu', home_url('/'))); ?>" class="btn btn-primary" style="border-radius: 50px; width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 0.9rem;">
                        Ver el Menú <i class="fa-solid fa-utensils"></i>
                    </a>
                <?php endif; ?>
                <a href="<?php echo esc_url(add_query_arg(array('view' => 'login', 'action' => 'logout'), home_url('/'))); ?>" class="btn btn-secondary" style="border-radius: 50px; width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 0.9rem;">
                    Cerrar Sesión <i class="fa-solid fa-right-from-bracket"></i>
                </a>
            </div>
        </div>

        <!-- Panel Derecho: Seguimiento e Historial -->
        <div style="display: flex; flex-direction: column; gap: 30px;">
            
            <!-- Rastreo de pedido -->
            <div style="background: var(--bg-secondary); border: var(--border-main); border-radius: 16px; padding: 30px; box-shadow: var(--shadow-lg);">
                <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--text-primary); margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-truck-ramp-box" style="color: var(--color-amber);"></i> Estado de tu Pedido
                </h3>
                
                <?php if ($active_order) : ?>
                    <div style="background: var(--bg-primary); border: var(--border-main); border-radius: 12px; padding: 20px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                        <div>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">Pedido en seguimiento</span>
                            <h4 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary); margin: 2px 0 0 0;">Pedido #<?php echo $active_order->get_id(); ?></h4>
                        </div>
                        <div style="text-align: right;">
                            <span style="font-size: 0.8rem; color: var(--text-muted);">Total</span>
                            <div style="font-size: 1.1rem; font-weight: 700; color: var(--color-amber);">$<?php echo number_format($active_order->get_total(), 2); ?></div>
                        </div>
                    </div>

                    <!-- Barra de progreso -->
                    <div class="tracker-wrapper" style="position: relative; margin: 40px 10px 20px 10px;">
                        <div style="position: absolute; top: 20px; left: 0; width: 100%; height: 6px; background: rgba(15, 23, 42, 0.08); z-index: 1; border-radius: 3px;"></div>
                        <div style="position: absolute; top: 20px; left: 0; width: <?php echo $progress_percent; ?>%; height: 6px; background: linear-gradient(90deg, var(--color-amber), var(--color-green)); z-index: 2; border-radius: 3px; transition: width 0.8s ease;"></div>
                        
                        <div style="position: relative; z-index: 3; display: flex; justify-content: space-between; text-align: center;">
                            <div style="width: 80px;">
                                <div style="width: 44px; height: 44px; border-radius: 50%; background: <?php echo $step_class[0] ? 'var(--color-amber)' : 'var(--bg-secondary)'; ?>; color: <?php echo $step_class[0] ? 'white' : 'var(--text-muted)'; ?>; border: 3px solid <?php echo $step_class[0] ? 'var(--color-amber)' : 'var(--border-main)'; ?>; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px auto; box-shadow: var(--shadow-sm);">
                                    <i class="fa-solid fa-receipt"></i>
                                </div>
                                <span style="font-size: 0.78rem; font-weight: 700; color: <?php echo $step_class[0] ? 'var(--text-primary)' : 'var(--text-muted)'; ?>;">Recibido</span>
                            </div>
                            
                            <div style="width: 80px;">
                                <div style="width: 44px; height: 44px; border-radius: 50%; background: <?php echo $step_class[1] ? 'var(--color-amber)' : 'var(--bg-secondary)'; ?>; color: <?php echo $step_class[1] ? 'white' : 'var(--text-muted)'; ?>; border: 3px solid <?php echo $step_class[1] ? 'var(--color-amber)' : 'var(--border-main)'; ?>; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px auto; box-shadow: var(--shadow-sm);">
                                    <i class="fa-solid fa-fire-burner"></i>
                                </div>
                                <span style="font-size: 0.78rem; font-weight: 700; color: <?php echo $step_class[1] ? 'var(--text-primary)' : 'var(--text-muted)'; ?>;">Preparando</span>
                            </div>

                            <div style="width: 80px;">
                                <div style="width: 44px; height: 44px; border-radius: 50%; background: <?php echo $step_class[2] ? 'var(--color-amber)' : 'var(--bg-secondary)'; ?>; color: <?php echo $step_class[2] ? 'white' : 'var(--text-muted)'; ?>; border: 3px solid <?php echo $step_class[2] ? 'var(--color-amber)' : 'var(--border-main)'; ?>; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px auto; box-shadow: var(--shadow-sm);">
                                    <i class="fa-solid fa-motorcycle"></i>
                                </div>
                                <span style="font-size: 0.78rem; font-weight: 700; color: <?php echo $step_class[2] ? 'var(--text-primary)' : 'var(--text-muted)'; ?>;">En Camino</span>
                            </div>

                            <div style="width: 80px;">
                                <div style="width: 44px; height: 44px; border-radius: 50%; background: <?php echo $step_class[3] ? 'var(--color-green)' : 'var(--bg-secondary)'; ?>; color: <?php echo $step_class[3] ? 'white' : 'var(--text-muted)'; ?>; border: 3px solid <?php echo $step_class[3] ? 'var(--color-green)' : 'var(--border-main)'; ?>; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px auto; box-shadow: var(--shadow-sm);">
                                    <i class="fa-solid fa-box-open"></i>
                                </div>
                                <span style="font-size: 0.78rem; font-weight: 700; color: <?php echo $step_class[3] ? 'var(--text-primary)' : 'var(--text-muted)'; ?>;">Entregado</span>
                            </div>
                        </div>
                    </div>

                    <div style="text-align: center; margin-top: 25px; padding: 12px; background: rgba(249, 115, 22, 0.05); border-radius: 8px; border: 1px dashed rgba(249, 115, 22, 0.2); font-size: 0.88rem; color: var(--text-secondary);">
                        <i class="fa-solid fa-circle-info" style="color: var(--color-amber); margin-right: 4px;"></i> <?php echo esc_html($status_desc); ?>
                    </div>
                <?php else : ?>
                    <div style="text-align: center; padding: 30px 20px; color: var(--text-muted);">
                        <i class="fa-solid fa-clock" style="font-size: 2.5rem; color: var(--text-muted); margin-bottom: 12px;"></i>
                        <p style="margin: 0; font-size: 0.95rem;">No tienes pedidos activos en preparación en este momento.</p>
                        <a href="<?php echo esc_url(add_query_arg('view', 'menu', home_url('/'))); ?>" class="btn btn-primary" style="margin-top: 15px; border-radius: 50px; padding: 8px 24px; font-size: 0.88rem;">¡Pedir algo delicioso!</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Historial de Compras -->
            <div style="background: var(--bg-secondary); border: var(--border-main); border-radius: 16px; padding: 30px; box-shadow: var(--shadow-lg);">
                <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--text-primary); margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-clock-rotate-left" style="color: var(--color-amber);"></i> Historial de Compras
                </h3>

                <?php if (!empty($customer_orders)) : ?>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                            <thead>
                                <tr style="border-bottom: 2px solid var(--border-main); color: var(--text-muted); font-weight: 700;">
                                    <th style="padding: 12px 8px;">Pedido</th>
                                    <th style="padding: 12px 8px;">Fecha</th>
                                    <th style="padding: 12px 8px;">Detalle</th>
                                    <th style="padding: 12px 8px;">Total</th>
                                    <th style="padding: 12px 8px; text-align: right;">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customer_orders as $order) : 
                                    $status = $order->get_status();
                                    $status_label = 'Desconocido';
                                    $status_color = 'var(--text-muted)';
                                    $status_bg = 'rgba(15, 23, 42, 0.05)';
                                    
                                    if ($status === 'completed') {
                                        $status_label = 'Entregado';
                                        $status_color = 'var(--color-green)';
                                        $status_bg = 'rgba(16, 185, 129, 0.08)';
                                    } elseif ($status === 'processing') {
                                        $status_label = 'Preparando';
                                        $status_color = 'var(--color-amber)';
                                        $status_bg = 'rgba(249, 115, 22, 0.08)';
                                    } elseif ($status === 'pending' || $status === 'on-hold') {
                                        $status_label = 'Pendiente';
                                        $status_color = '#3b82f6';
                                        $status_bg = 'rgba(59, 130, 246, 0.08)';
                                    } elseif ($status === 'cancelled' || $status === 'failed') {
                                        $status_label = 'Cancelado';
                                        $status_color = '#ef4444';
                                        $status_bg = 'rgba(239, 68, 68, 0.08)';
                                    }
                                    
                                    // Obtener items
                                    $items_summary = array();
                                    foreach ($order->get_items() as $item) {
                                        $items_summary[] = $item->get_name() . ' (x' . $item->get_quantity() . ')';
                                    }
                                    $items_str = implode(', ', $items_summary);
                                    if (strlen($items_str) > 45) {
                                        $items_str = mb_substr($items_str, 0, 42) . '...';
                                    }
                                ?>
                                    <tr style="border-bottom: 1px solid var(--border-main);">
                                        <td style="padding: 15px 8px; font-weight: 700; color: var(--text-primary);">#<?php echo $order->get_id(); ?></td>
                                        <td style="padding: 15px 8px; color: var(--text-secondary);"><?php echo esc_html(wc_format_datetime($order->get_date_created(), 'd/m/Y')); ?></td>
                                        <td style="padding: 15px 8px; color: var(--text-muted); font-size: 0.82rem;" title="<?php echo esc_attr(implode(', ', $items_summary)); ?>"><?php echo esc_html($items_str); ?></td>
                                        <td style="padding: 15px 8px; font-weight: 700; color: var(--text-primary);">$<?php echo number_format($order->get_total(), 2); ?></td>
                                        <td style="padding: 15px 8px; text-align: right;">
                                            <span style="color: <?php echo $status_color; ?>; background: <?php echo $status_bg; ?>; padding: 4px 10px; border-radius: 50px; font-size: 0.78rem; font-weight: 700; display: inline-block;">
                                                <?php echo $status_label; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else : ?>
                    <p style="text-align: center; color: var(--text-muted); margin: 0; padding: 20px 0;">No has realizado ninguna compra todavía.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php else : ?>

<!-- NORMAL LOGIN/REGISTER VISTA (NO LOGUEADO) -->
<section class="section-padding order-section" id="inicio-sesion" style="min-height: 85vh; display: flex; align-items: center; background-color: var(--bg-primary);">
    <div class="container" style="max-width: 900px; display: grid; grid-template-columns: 1fr 1fr; gap: 0; border: var(--border-main); border-radius: 16px; overflow: hidden; background: var(--bg-secondary); box-shadow: var(--shadow-lg);">
        
        <!-- Columna Izquierda: Decoración visual Premium -->
        <div class="login-banner" style="background: var(--text-primary); color: white; padding: 40px; display: flex; flex-direction: column; justify-content: space-between; position: relative; overflow: hidden;">
            <!-- Círculos decorativos de fondo -->
            <div style="position: absolute; top: -50px; right: -50px; width: 150px; height: 150px; border-radius: 50%; background: rgba(249, 115, 22, 0.15); filter: blur(20px);"></div>
            <div style="position: absolute; bottom: -80px; left: -80px; width: 250px; height: 250px; border-radius: 50%; background: rgba(249, 115, 22, 0.1); filter: blur(40px);"></div>
            
            <!-- Contenido del Mensaje -->
            <div style="margin: 60px 0; position: relative; z-index: 2;">
                <h2 style="font-family: var(--font-serif); font-size: 2.2rem; line-height: 1.2; margin-bottom: 20px; font-weight: 600;">
                    ¡Tus antojos favoritos a un clic de distancia!
                </h2>
                <p style="color: var(--text-muted); font-size: 1rem; line-height: 1.6;">
                    Inicia sesión para realizar pedidos de forma rápida, acceder a combos exclusivos y guardar tu historial.
                </p>
            </div>

            <!-- Footer Académico del Banner -->
            <div style="border-top: 1px solid rgba(255, 255, 255, 0.1); padding-top: 20px; font-size: 0.8rem; color: var(--text-muted);">
                <i class="fa-solid fa-graduation-cap"></i> Proyecto Académico Simulador de Comercio Electrónico.
            </div>
        </div>

        <!-- Columna Derecha: Formulario interactivo -->
        <div class="login-form-side" style="padding: 40px; display: flex; flex-direction: column; justify-content: center; background: var(--bg-secondary);">
            
                
                <!-- Selector de Pestañas (Tabs) Estilo Premium (Bordes finos, contrastes fuertes) -->
                <div class="login-tabs" id="login-tabs" style="display: <?php echo ($active_tab === 'forgot' || $valid_reset_token) ? 'none' : 'flex'; ?>; border: var(--border-main); border-radius: 8px; overflow: hidden; margin-bottom: 30px; background: var(--bg-primary);">
                    <button type="button" class="login-tab-btn <?php echo $active_tab === 'login' ? 'active' : ''; ?>" id="tab-login-btn" style="flex: 1; padding: 12px; text-align: center; font-weight: 700; font-size: 0.95rem; cursor: pointer; transition: var(--transition-fast); border: none;">
                        Iniciar Sesión
                    </button>
                    <button type="button" class="login-tab-btn <?php echo $active_tab === 'register' ? 'active' : ''; ?>" id="tab-register-btn" style="flex: 1; padding: 12px; text-align: center; font-weight: 700; font-size: 0.95rem; cursor: pointer; transition: var(--transition-fast); border-left: var(--border-main); border-top: none; border-bottom: none; border-right: none;">
                        Registrarse
                    </button>
                </div>

                <!-- CONTENEDOR FORMULARIO DE INICIO DE SESIÓN -->
                <div class="login-form-container" id="login-form-container" style="display: <?php echo $active_tab === 'login' ? 'block' : 'none'; ?>;">
                    <h3 style="font-size: 1.35rem; font-weight: 700; color: var(--text-primary); margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-shield-halved" style="color: var(--color-amber);"></i> Ingresa a tu cuenta
                    </h3>

                    <?php if (isset($_GET['notice']) && $_GET['notice'] === 'compra_requiere_login') : ?>
                        <div class="form-status-msg error" style="margin-bottom: 20px; display: block; border-color: var(--color-amber); background: rgba(249, 115, 22, 0.05); color: var(--color-amber);">
                            <i class="fa-solid fa-circle-exclamation"></i> Por favor, inicia sesión o regístrate para proceder con tu pedido.
                        </div>
                    <?php elseif (isset($_GET['notice']) && $_GET['notice'] === 'session_timeout') : ?>
                        <div class="form-status-msg error" style="margin-bottom: 20px; display: block; border-color: var(--color-amber); background: rgba(249, 115, 22, 0.05); color: var(--color-amber);">
                            <i class="fa-solid fa-circle-exclamation"></i> Tu sesión ha expirado por inactividad. Por favor, ingresa de nuevo.
                        </div>
                    <?php elseif (isset($_GET['notice']) && $_GET['notice'] === 'password_reset_success') : ?>
                        <div class="form-status-msg success" style="margin-bottom: 20px; display: block;">
                            <i class="fa-solid fa-circle-check"></i> Tu contraseña ha sido restablecida con éxito. Ya puedes iniciar sesión.
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($login_error)) : ?>
                        <div class="form-status-msg error" style="margin-bottom: 20px; display: block;">
                            <i class="fa-solid fa-circle-xmark"></i> <?php echo esc_html($login_error); ?>
                        </div>
                    <?php endif; ?>

                    <form action="" method="post">
                        <?php wp_nonce_field('comida_rapida_login_action', 'login_nonce'); ?>

                        <div class="form-group">
                            <label for="user_login" class="form-label">Usuario o Correo Electrónico *</label>
                            <div style="position: relative;">
                                <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted);">
                                    <i class="fa-solid fa-user"></i>
                                </span>
                                <input type="text" id="user_login" name="log" class="form-input" placeholder="Ej. juanperez" required style="padding-left: 45px;" value="<?php echo isset($_POST['log']) ? esc_attr($_POST['log']) : ''; ?>">
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 12px;">
                            <label for="user_pass" class="form-label">Contraseña *</label>
                            <div style="position: relative;">
                                <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted);">
                                    <i class="fa-solid fa-key"></i>
                                </span>
                                <input type="password" id="user_pass" name="pwd" class="form-input" placeholder="••••••••" required style="padding-left: 45px; padding-right: 45px;">
                                <span class="toggle-pwd-btn" data-target="user_pass" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted); cursor: pointer;" title="Mostrar contraseña">
                                    <i class="fa-solid fa-eye-slash"></i>
                                </span>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; font-size: 0.88rem;">
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: var(--text-secondary);">
                                <input type="checkbox" name="rememberme" value="forever" style="width: 16px; height: 16px; accent-color: var(--color-amber);"> Recordarme
                            </label>
                            <a href="#" id="forgot-password-link" style="color: var(--color-amber); text-decoration: none; font-weight: 600; transition: var(--transition-fast);">¿Olvidaste tu contraseña?</a>
                        </div>

                        <button type="submit" name="login_submit" class="btn btn-primary form-submit-btn" style="border-radius: 50px;">
                            Entrar <i class="fa-solid fa-right-to-bracket"></i>
                        </button>
                    </form>
                </div>

                <!-- CONTENEDOR FORMULARIO DE REGISTRO -->
                <div class="register-form-container" id="register-form-container" style="display: <?php echo $active_tab === 'register' ? 'block' : 'none'; ?>;">
                    <h3 style="font-size: 1.35rem; font-weight: 700; color: var(--text-primary); margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-user-plus" style="color: var(--color-amber);"></i> Regístrate hoy
                    </h3>

                    <?php if (!empty($register_error)) : ?>
                        <div class="form-status-msg error" style="margin-bottom: 20px; display: block;">
                            <i class="fa-solid fa-circle-xmark"></i> <?php echo esc_html($register_error); ?>
                        </div>
                    <?php endif; ?>

                    <form action="" method="post">
                        <?php wp_nonce_field('comida_rapida_register_action', 'register_nonce'); ?>

                        <div class="form-group">
                            <label for="reg_username" class="form-label">Nombre de Usuario *</label>
                            <div style="position: relative;">
                                <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted);">
                                    <i class="fa-solid fa-user"></i>
                                </span>
                                <input type="text" id="reg_username" name="reg_username" class="form-input" placeholder="Ej. juanp123" required style="padding-left: 45px;" value="<?php echo isset($_POST['reg_username']) ? esc_attr($_POST['reg_username']) : ''; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="reg_email" class="form-label">Correo Electrónico *</label>
                            <div style="position: relative;">
                                <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted);">
                                    <i class="fa-solid fa-envelope"></i>
                                </span>
                                <input type="email" id="reg_email" name="reg_email" class="form-input" placeholder="Ej. juan@correo.com" required style="padding-left: 45px;" value="<?php echo isset($_POST['reg_email']) ? esc_attr($_POST['reg_email']) : ''; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="reg_password" class="form-label">Contraseña *</label>
                            <div style="position: relative; margin-bottom: 8px;">
                                <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted);">
                                    <i class="fa-solid fa-key"></i>
                                </span>
                                <input type="password" id="reg_password" name="reg_password" class="form-input" placeholder="Mínimo 8 caracteres" required style="padding-left: 45px; padding-right: 45px;">
                                <span class="toggle-pwd-btn" data-target="reg_password" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted); cursor: pointer;" title="Mostrar contraseña">
                                    <i class="fa-solid fa-eye-slash"></i>
                                </span>
                            </div>
                            
                            <!-- Indicador visual de políticas de contraseña -->
                            <div id="password-policies-box" style="margin-top: 10px; font-size: 0.82rem; background: var(--bg-primary); border: var(--border-main); padding: 12px; border-radius: 8px; display: none;">
                                <span style="font-weight: 700; color: var(--text-primary); display: block; margin-bottom: 6px;">La contraseña debe incluir:</span>
                                <ul style="list-style: none; padding-left: 0; margin: 0; display: flex; flex-direction: column; gap: 6px;">
                                    <li id="policy-len" style="display: flex; align-items: center; gap: 8px; color: var(--text-muted); transition: color 0.2s ease;">
                                        <span id="policy-len-icon-container" style="width: 16px; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-circle" style="font-size: 0.5rem; opacity: 0.5;"></i></span> Al menos 8 caracteres
                                    </li>
                                    <li id="policy-upper" style="display: flex; align-items: center; gap: 8px; color: var(--text-muted); transition: color 0.2s ease;">
                                        <span id="policy-upper-icon-container" style="width: 16px; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-circle" style="font-size: 0.5rem; opacity: 0.5;"></i></span> Una letra mayúscula (A-Z)
                                    </li>
                                    <li id="policy-lower" style="display: flex; align-items: center; gap: 8px; color: var(--text-muted); transition: color 0.2s ease;">
                                        <span id="policy-lower-icon-container" style="width: 16px; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-circle" style="font-size: 0.5rem; opacity: 0.5;"></i></span> Una letra minúscula (a-z)
                                    </li>
                                    <li id="policy-num" style="display: flex; align-items: center; gap: 8px; color: var(--text-muted); transition: color 0.2s ease;">
                                        <span id="policy-num-icon-container" style="width: 16px; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-circle" style="font-size: 0.5rem; opacity: 0.5;"></i></span> Un número (0-9)
                                    </li>
                                    <li id="policy-spec" style="display: flex; align-items: center; gap: 8px; color: var(--text-muted); transition: color 0.2s ease;">
                                        <span id="policy-spec-icon-container" style="width: 16px; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-circle" style="font-size: 0.5rem; opacity: 0.5;"></i></span> Un carácter especial (ej. !@#$%^&*)
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 24px;">
                            <label for="reg_password_confirm" class="form-label">Confirmar Contraseña *</label>
                            <div style="position: relative;">
                                <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted);">
                                    <i class="fa-solid fa-shield-check"></i>
                                </span>
                                <input type="password" id="reg_password_confirm" name="reg_password_confirm" class="form-input" placeholder="Repite tu contraseña" required style="padding-left: 45px; padding-right: 45px;">
                                <span class="toggle-pwd-btn" data-target="reg_password_confirm" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted); cursor: pointer;" title="Mostrar contraseña">
                                    <i class="fa-solid fa-eye-slash"></i>
                                </span>
                            </div>
                        </div>

                        <button type="submit" name="register_submit" class="btn btn-primary form-submit-btn" style="border-radius: 50px;">
                            Registrarme <i class="fa-solid fa-user-plus"></i>
                        </button>
                    </form>
                </div>

                <!-- CONTENEDOR FORMULARIO DE RECUPERACIÓN DE CONTRASEÑA -->
                <div class="forgot-form-container" id="forgot-form-container" style="display: <?php echo $active_tab === 'forgot' ? 'block' : 'none'; ?>;">
                    <h3 style="font-size: 1.35rem; font-weight: 700; color: var(--text-primary); margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-key" style="color: var(--color-amber);"></i> Recuperar contraseña
                    </h3>
                    <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 20px; line-height: 1.5;">
                        Ingresa tu nombre de usuario o correo electrónico y te enviaremos un enlace para restablecer tu contraseña.
                    </p>

                    <?php if (!empty($forgot_error)) : ?>
                        <div class="form-status-msg error" style="margin-bottom: 20px; display: block;">
                            <i class="fa-solid fa-circle-xmark"></i> <?php echo esc_html($forgot_error); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($forgot_success)) : ?>
                        <div class="form-status-msg success" style="margin-bottom: 20px; display: block;">
                            <i class="fa-solid fa-circle-check"></i> <?php echo esc_html($forgot_success); ?>
                        </div>
                    <?php endif; ?>

                    <form action="" method="post">
                        <?php wp_nonce_field('comida_rapida_forgot_action', 'forgot_nonce'); ?>

                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="forgot_input" class="form-label">Usuario o Correo Electrónico *</label>
                            <div style="position: relative;">
                                <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted);">
                                    <i class="fa-solid fa-envelope"></i>
                                </span>
                                <input type="text" id="forgot_input" name="forgot_input" class="form-input" placeholder="Ej. juanperez o juan@correo.com" required style="padding-left: 45px;" value="<?php echo isset($_POST['forgot_input']) ? esc_attr($_POST['forgot_input']) : ''; ?>">
                            </div>
                        </div>

                        <button type="submit" name="forgot_submit" class="btn btn-primary form-submit-btn" style="border-radius: 50px; margin-bottom: 15px;">
                            Enviar Enlace <i class="fa-solid fa-paper-plane"></i>
                        </button>
                        
                        <div style="text-align: center;">
                            <a href="#" id="back-to-login-link" style="color: var(--text-secondary); text-decoration: none; font-size: 0.9rem; font-weight: 600; transition: var(--transition-fast);">
                                <i class="fa-solid fa-arrow-left" style="margin-right: 4px;"></i> Volver al inicio de sesión
                            </a>
                        </div>
                    </form>
                </div>

                <!-- CONTENEDOR FORMULARIO DE RESTABLECIMIENTO DE CONTRASEÑA -->
                <div class="reset-form-container" id="reset-form-container" style="display: <?php echo $valid_reset_token ? 'block' : 'none'; ?>;">
                    <h3 style="font-size: 1.35rem; font-weight: 700; color: var(--text-primary); margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-lock-open" style="color: var(--color-amber);"></i> Elige tu nueva contraseña
                    </h3>

                    <?php if (!empty($reset_error)) : ?>
                        <div class="form-status-msg error" style="margin-bottom: 20px; display: block;">
                            <i class="fa-solid fa-circle-xmark"></i> <?php echo esc_html($reset_error); ?>
                        </div>
                    <?php endif; ?>

                    <form action="" method="post">
                        <?php wp_nonce_field('comida_rapida_reset_action', 'reset_nonce'); ?>

                        <div class="form-group">
                            <label for="new_password" class="form-label">Nueva Contraseña *</label>
                            <div style="position: relative; margin-bottom: 8px;">
                                <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted);">
                                    <i class="fa-solid fa-key"></i>
                                </span>
                                <input type="password" id="new_password" name="new_password" class="form-input" placeholder="Mínimo 8 caracteres" required style="padding-left: 45px; padding-right: 45px;">
                                <span class="toggle-pwd-btn" data-target="new_password" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted); cursor: pointer;" title="Mostrar contraseña">
                                    <i class="fa-solid fa-eye-slash"></i>
                                </span>
                            </div>
                            
                            <!-- Indicador visual de políticas de contraseña -->
                            <div id="reset-password-policies-box" style="margin-top: 10px; font-size: 0.82rem; background: var(--bg-primary); border: var(--border-main); padding: 12px; border-radius: 8px; display: none;">
                                <span style="font-weight: 700; color: var(--text-primary); display: block; margin-bottom: 6px;">La contraseña debe incluir:</span>
                                <ul style="list-style: none; padding-left: 0; margin: 0; display: flex; flex-direction: column; gap: 6px;">
                                    <li id="reset-policy-len" style="display: flex; align-items: center; gap: 8px; color: var(--text-muted); transition: color 0.2s ease;">
                                        <span id="reset-policy-len-icon-container" style="width: 16px; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-circle" style="font-size: 0.5rem; opacity: 0.5;"></i></span> Al menos 8 caracteres
                                    </li>
                                    <li id="reset-policy-upper" style="display: flex; align-items: center; gap: 8px; color: var(--text-muted); transition: color 0.2s ease;">
                                        <span id="reset-policy-upper-icon-container" style="width: 16px; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-circle" style="font-size: 0.5rem; opacity: 0.5;"></i></span> Una letra mayúscula (A-Z)
                                    </li>
                                    <li id="reset-policy-lower" style="display: flex; align-items: center; gap: 8px; color: var(--text-muted); transition: color 0.2s ease;">
                                        <span id="reset-policy-lower-icon-container" style="width: 16px; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-circle" style="font-size: 0.5rem; opacity: 0.5;"></i></span> Una letra minúscula (a-z)
                                    </li>
                                    <li id="reset-policy-num" style="display: flex; align-items: center; gap: 8px; color: var(--text-muted); transition: color 0.2s ease;">
                                        <span id="reset-policy-num-icon-container" style="width: 16px; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-circle" style="font-size: 0.5rem; opacity: 0.5;"></i></span> Un número (0-9)
                                    </li>
                                    <li id="reset-policy-spec" style="display: flex; align-items: center; gap: 8px; color: var(--text-muted); transition: color 0.2s ease;">
                                        <span id="reset-policy-spec-icon-container" style="width: 16px; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-circle" style="font-size: 0.5rem; opacity: 0.5;"></i></span> Un carácter especial (ej. !@#$%^&*)
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 24px;">
                            <label for="new_password_confirm" class="form-label">Confirmar Nueva Contraseña *</label>
                            <div style="position: relative;">
                                <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted);">
                                    <i class="fa-solid fa-shield-check"></i>
                                </span>
                                <input type="password" id="new_password_confirm" name="new_password_confirm" class="form-input" placeholder="Repite tu nueva contraseña" required style="padding-left: 45px; padding-right: 45px;">
                                <span class="toggle-pwd-btn" data-target="new_password_confirm" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted); cursor: pointer;" title="Mostrar contraseña">
                                    <i class="fa-solid fa-eye-slash"></i>
                                </span>
                            </div>
                        </div>

                        <button type="submit" name="reset_submit" class="btn btn-primary form-submit-btn" style="border-radius: 50px;">
                            Restablecer Contraseña <i class="fa-solid fa-check"></i>
                        </button>
                    </form>
                </div>

            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ESTILOS INTERNOS PARA TABS -->
<style>
.login-tab-btn {
    background: transparent;
    color: var(--text-secondary);
}
.login-tab-btn:hover {
    color: var(--text-primary);
    background: rgba(15, 23, 42, 0.05);
}
.login-tab-btn.active {
    background: var(--text-primary);
    color: white;
}

@media (max-width: 768px) {
    #inicio-sesion .container {
        grid-template-columns: 1fr !important;
    }
    .login-banner {
        display: none !important; /* Oculta banner en móvil para mantener la legibilidad del form */
    }
}
</style>

<!-- SCRIPT INTERNO PARA TABS Y PASSWORD TOGGLE -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabLoginBtn = document.getElementById('tab-login-btn');
    const tabRegisterBtn = document.getElementById('tab-register-btn');
    const loginFormContainer = document.getElementById('login-form-container');
    const registerFormContainer = document.getElementById('register-form-container');
    const forgotFormContainer = document.getElementById('forgot-form-container');
    const loginTabs = document.getElementById('login-tabs');
    const forgotPasswordLink = document.getElementById('forgot-password-link');
    const backToLoginLink = document.getElementById('back-to-login-link');
    
    if (tabLoginBtn && tabRegisterBtn && loginFormContainer && registerFormContainer) {
        tabLoginBtn.addEventListener('click', function() {
            tabLoginBtn.classList.add('active');
            tabRegisterBtn.classList.remove('active');
            loginFormContainer.style.display = 'block';
            registerFormContainer.style.display = 'none';
            if (forgotFormContainer) forgotFormContainer.style.display = 'none';
        });

        tabRegisterBtn.addEventListener('click', function() {
            tabRegisterBtn.classList.add('active');
            tabLoginBtn.classList.remove('active');
            registerFormContainer.style.display = 'block';
            loginFormContainer.style.display = 'none';
            if (forgotFormContainer) forgotFormContainer.style.display = 'none';
        });
    }

    if (forgotPasswordLink && loginFormContainer && forgotFormContainer && loginTabs) {
        forgotPasswordLink.addEventListener('click', function(e) {
            e.preventDefault();
            loginFormContainer.style.display = 'none';
            registerFormContainer.style.display = 'none';
            forgotFormContainer.style.display = 'block';
            loginTabs.style.display = 'none';
        });
    }

    if (backToLoginLink && loginFormContainer && forgotFormContainer && loginTabs) {
        backToLoginLink.addEventListener('click', function(e) {
            e.preventDefault();
            forgotFormContainer.style.display = 'none';
            registerFormContainer.style.display = 'none';
            loginFormContainer.style.display = 'block';
            loginTabs.style.display = 'flex';
            if (tabLoginBtn && tabRegisterBtn) {
                tabLoginBtn.classList.add('active');
                tabRegisterBtn.classList.remove('active');
            }
        });
    }

    const toggleButtons = document.querySelectorAll('.toggle-pwd-btn');
    toggleButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const inputField = document.getElementById(targetId);
            if (inputField) {
                const isPassword = inputField.getAttribute('type') === 'password';
                inputField.setAttribute('type', isPassword ? 'text' : 'password');
                
                const icon = this.querySelector('i');
                if (isPassword) {
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                    this.setAttribute('title', 'Ocultar contraseña');
                } else {
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                    this.setAttribute('title', 'Mostrar contraseña');
                }
            }
        });
    });

    // Helper para actualizar UI de políticas de seguridad
    function updatePolicyUI(element, iconContainer, isMet) {
        if (!element || !iconContainer) return;
        if (isMet) {
            element.style.color = 'var(--color-green)';
            iconContainer.innerHTML = '<i class="fa-solid fa-check" style="font-size: 0.8rem; color: var(--color-green);"></i>';
        } else {
            element.style.color = 'var(--text-muted)';
            iconContainer.innerHTML = '<i class="fa-solid fa-circle" style="font-size: 0.5rem; opacity: 0.5;"></i>';
        }
    }

    // Validación en tiempo real de la contraseña de registro
    const regPassword = document.getElementById('reg_password');
    const policiesBox = document.getElementById('password-policies-box');
    
    if (regPassword && policiesBox) {
        const policyLen = document.getElementById('policy-len');
        const policyLenIcon = document.getElementById('policy-len-icon-container');
        const policyUpper = document.getElementById('policy-upper');
        const policyUpperIcon = document.getElementById('policy-upper-icon-container');
        const policyLower = document.getElementById('policy-lower');
        const policyLowerIcon = document.getElementById('policy-lower-icon-container');
        const policyNum = document.getElementById('policy-num');
        const policyNumIcon = document.getElementById('policy-num-icon-container');
        const policySpec = document.getElementById('policy-spec');
        const policySpecIcon = document.getElementById('policy-spec-icon-container');
        
        regPassword.addEventListener('focus', function() {
            policiesBox.style.display = 'block';
        });
        
        regPassword.addEventListener('input', function() {
            const val = regPassword.value;
            
            updatePolicyUI(policyLen, policyLenIcon, val.length >= 8);
            updatePolicyUI(policyUpper, policyUpperIcon, /[A-Z]/.test(val));
            updatePolicyUI(policyLower, policyLowerIcon, /[a-z]/.test(val));
            updatePolicyUI(policyNum, policyNumIcon, /[0-9]/.test(val));
            updatePolicyUI(policySpec, policySpecIcon, /[^A-Za-z0-9]/.test(val));
        });
    }

    // Validación en tiempo real de la contraseña de restablecimiento
    const newPassword = document.getElementById('new_password');
    const resetPoliciesBox = document.getElementById('reset-password-policies-box');
    
    if (newPassword && resetPoliciesBox) {
        const policyLen = document.getElementById('reset-policy-len');
        const policyLenIcon = document.getElementById('reset-policy-len-icon-container');
        const policyUpper = document.getElementById('reset-policy-upper');
        const policyUpperIcon = document.getElementById('reset-policy-upper-icon-container');
        const policyLower = document.getElementById('reset-policy-lower');
        const policyLowerIcon = document.getElementById('reset-policy-lower-icon-container');
        const policyNum = document.getElementById('reset-policy-num');
        const policyNumIcon = document.getElementById('reset-policy-num-icon-container');
        const policySpec = document.getElementById('reset-policy-spec');
        const policySpecIcon = document.getElementById('reset-policy-spec-icon-container');
        
        newPassword.addEventListener('focus', function() {
            resetPoliciesBox.style.display = 'block';
        });
        
        newPassword.addEventListener('input', function() {
            const val = newPassword.value;
            
            updatePolicyUI(policyLen, policyLenIcon, val.length >= 8);
            updatePolicyUI(policyUpper, policyUpperIcon, /[A-Z]/.test(val));
            updatePolicyUI(policyLower, policyLowerIcon, /[a-z]/.test(val));
            updatePolicyUI(policyNum, policyNumIcon, /[0-9]/.test(val));
            updatePolicyUI(policySpec, policySpecIcon, /[^A-Za-z0-9]/.test(val));
        });
    }
});
</script>
