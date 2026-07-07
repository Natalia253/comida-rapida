<?php
/**
 * Template Name: Página Promociones
 * Description: Vista dedicada para mostrar promociones activas y cupones de descuento interactivos con diseño de ticket premium.
 */
?>

<!-- Estilos específicos para Promociones (Bypass de caché del navegador) -->
<style>
/* --- PREMIUM PROMOTIONS PAGE STYLES --- */
.promo-countdown-banner {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, var(--text-primary) 0%, #1e293b 100%);
    border: var(--border-main);
    border-radius: 12px;
    padding: 30px 40px;
    color: white;
    box-shadow: var(--shadow-md);
    margin-top: 30px;
    gap: 30px;
}

.countdown-info h3 {
    font-size: 1.8rem;
    font-weight: 800;
    margin-top: 8px;
    margin-bottom: 6px;
    font-family: var(--font-serif);
    color: white !important;
    letter-spacing: -0.5px;
}

.countdown-info p {
    color: var(--text-muted) !important;
    font-size: 0.95rem;
}

.badge-live {
    background: var(--color-red);
    color: white;
    padding: 4px 12px;
    font-size: 0.75rem;
    font-weight: 700;
    border-radius: 50px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 0 12px rgba(239, 68, 68, 0.4);
}

.countdown-timer-wrapper {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}

.timer-label {
    font-size: 0.8rem;
    text-transform: uppercase;
    font-weight: 700;
    color: var(--text-muted);
    margin-bottom: 8px;
    letter-spacing: 1px;
}

.countdown-timer {
    display: flex;
    align-items: center;
    gap: 8px;
}

.timer-segment {
    display: flex;
    flex-direction: column;
    align-items: center;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.12);
    min-width: 60px;
    padding: 8px 10px;
    border-radius: 8px;
}

.timer-num {
    font-size: 1.6rem;
    font-weight: 800;
    color: var(--color-amber);
    line-height: 1;
}

.timer-text {
    font-size: 0.7rem;
    text-transform: uppercase;
    color: var(--text-muted);
    margin-top: 4px;
    font-weight: 600;
}

.timer-colon {
    font-size: 1.6rem;
    font-weight: 800;
    color: rgba(255, 255, 255, 0.3);
    line-height: 1;
    margin-top: -15px;
}

.promo-tickets-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(290px, 1fr));
    gap: 30px;
    margin-top: 40px;
}

.promo-ticket {
    background: var(--bg-secondary);
    border: var(--border-main);
    border-radius: 16px;
    overflow: hidden;
    position: relative;
    display: flex;
    flex-direction: column;
    box-shadow: var(--shadow-sm);
    transition: transform var(--transition-smooth), box-shadow var(--transition-smooth), border-color var(--transition-smooth);
    border-top: 6px solid var(--accent-color);
}

.promo-ticket:hover {
    transform: translateY(-6px);
    box-shadow: var(--shadow-lg);
    border-color: var(--accent-color);
}

.ticket-header {
    padding: 24px 24px 12px 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.ticket-icon {
    font-size: 2.2rem;
    color: var(--accent-color);
    transition: transform var(--transition-smooth);
}

.promo-ticket:hover .ticket-icon {
    transform: scale(1.15) rotate(8deg);
}

.ticket-badge {
    padding: 5px 12px;
    border-radius: 50px;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border: 1px solid rgba(15, 23, 42, 0.08);
}

.ticket-body {
    padding: 0 24px 24px 24px;
    flex-grow: 1;
}

.ticket-title {
    font-size: 1.35rem;
    font-weight: 800;
    color: var(--text-primary);
    margin-bottom: 8px;
    font-family: var(--font-sans);
}

.ticket-desc {
    font-size: 0.9rem;
    color: var(--text-secondary);
    line-height: 1.5;
}

/* Notched divider */
.ticket-divider {
    height: 1px;
    background-image: linear-gradient(to right, rgba(15, 23, 42, 0.12) 50%, transparent 50%);
    background-size: 12px 1px;
    background-repeat: repeat-x;
    position: relative;
    margin: 0 24px;
}

.ticket-divider .notch {
    width: 22px;
    height: 22px;
    background: var(--bg-primary);
    border: var(--border-main);
    border-radius: 50%;
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 10;
}

.ticket-divider .notch.left {
    left: -36px;
}

.ticket-divider .notch.right {
    right: -36px;
}

.ticket-footer {
    padding: 20px 24px 24px 24px;
}

.coupon-box-premium {
    display: flex;
    align-items: center;
    background: var(--bg-primary);
    border: var(--border-main);
    border-radius: 8px;
    overflow: hidden;
}

.coupon-code-val {
    flex-grow: 1;
    font-family: monospace;
    font-weight: 800;
    font-size: 1.05rem;
    padding: 10px 10px;
    letter-spacing: 1px;
    color: var(--text-primary);
    user-select: all;
    text-align: center;
}

.copy-coupon-btn-premium {
    background: var(--text-primary);
    color: white;
    border: none;
    border-left: var(--border-main);
    font-weight: 700;
    font-size: 0.8rem;
    padding: 12px 14px;
    cursor: pointer;
    transition: var(--transition-fast);
    text-transform: uppercase;
    display: flex;
    align-items: center;
    gap: 6px;
    flex-shrink: 0;
}

.copy-coupon-btn-premium:hover {
    background: var(--color-amber);
    color: white;
}

.copy-coupon-btn-premium.copied {
    background: var(--color-green);
    color: white;
}

/* Toast */
.copy-toast {
    position: fixed;
    bottom: 30px;
    right: 30px;
    background: var(--text-primary);
    color: white;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
    box-shadow: var(--shadow-lg);
    border: 1px solid rgba(255, 255, 255, 0.1);
    transform: translateY(100px);
    opacity: 0;
    visibility: hidden;
    transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.4s;
    z-index: 10000;
}

.copy-toast.show {
    transform: translateY(0);
    opacity: 1;
    visibility: visible;
}

@media (max-width: 768px) {
    .promo-countdown-banner {
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 24px 20px;
    }
    
    .countdown-timer-wrapper {
        align-items: center;
    }
}
</style>

<section class="section-padding" id="promociones-page" style="background-color: var(--bg-primary);">
    <div class="container" style="max-width: 1000px;">
        <div class="section-header">
            <span class="section-subtitle">Zona de Ahorro</span>
            <h2 class="section-title">Nuestras Promociones Activas</h2>
            <p class="section-desc">Copia tus cupones preferidos y utilízalos para obtener descuentos increíbles en tus pedidos ficticios.</p>
        </div>

        <!-- Banner de Oferta Especial con Cuenta Regresiva -->
        <div class="promo-countdown-banner">
            <div class="countdown-info">
                <span class="badge-live"><i class="fa-solid fa-circle-play fa-pulse"></i> ¡OFERTA DEL DÍA!</span>
                <h3>Martes de 2x1 en Hamburguesas</h3>
                <p>Duplica tu sabor hoy: compra cualquier hamburguesa Monster y la segunda va por nuestra cuenta.</p>
            </div>
            <div class="countdown-timer-wrapper">
                <p class="timer-label">Termina en:</p>
                <div class="countdown-timer" id="promo-timer">
                    <div class="timer-segment">
                        <span class="timer-num" id="timer-hours">00</span>
                        <span class="timer-text">Hrs</span>
                    </div>
                    <span class="timer-colon">:</span>
                    <div class="timer-segment">
                        <span class="timer-num" id="timer-mins">00</span>
                        <span class="timer-text">Min</span>
                    </div>
                    <span class="timer-colon">:</span>
                    <div class="timer-segment">
                        <span class="timer-num" id="timer-secs">00</span>
                        <span class="timer-text">Seg</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grid de Cupones con Diseño de Ticket Físico -->
        <div class="promo-tickets-grid">
            
            <!-- Ticket 1: Combo Familiar -->
            <div class="promo-ticket" style="--accent-color: #f97316;">
                <div class="ticket-header">
                    <div class="ticket-icon"><i class="fa-solid fa-pizza-slice"></i></div>
                    <span class="ticket-badge" style="background: rgba(249, 115, 22, 0.08); color: #f97316;">Familiar</span>
                </div>
                <div class="ticket-body">
                    <h3 class="ticket-title">🍕 Combo Familiar</h3>
                    <p class="ticket-desc">Ideal para compartir en familia. Incluye 2 pizzas medianas, 1 orden de pan de ajo y 4 bebidas por un precio especial de $24.99.</p>
                </div>
                <div class="ticket-divider">
                    <span class="notch left"></span>
                    <span class="notch right"></span>
                </div>
                <div class="ticket-footer">
                    <div class="coupon-box-premium">
                        <span class="coupon-code-val">FAMILIACOMBO</span>
                        <button class="copy-coupon-btn-premium" data-code="FAMILIACOMBO">
                            <i class="fa-regular fa-copy"></i> Copiar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Ticket 2: Hora Feliz -->
            <div class="promo-ticket" style="--accent-color: #eab308;">
                <div class="ticket-header">
                    <div class="ticket-icon"><i class="fa-solid fa-clock"></i></div>
                    <span class="ticket-badge" style="background: rgba(234, 179, 8, 0.08); color: #eab308;">Happy Hour</span>
                </div>
                <div class="ticket-body">
                    <h3 class="ticket-title">🍔 Hora Feliz</h3>
                    <p class="ticket-desc">Descuento especial de 3:00 p.m. a 5:00 p.m. Obtén un 15% de descuento en hamburguesas y acompañamientos.</p>
                </div>
                <div class="ticket-divider">
                    <span class="notch left"></span>
                    <span class="notch right"></span>
                </div>
                <div class="ticket-footer">
                    <div class="coupon-box-premium">
                        <span class="coupon-code-val">HAPPY15</span>
                        <button class="copy-coupon-btn-premium" data-code="HAPPY15">
                            <i class="fa-regular fa-copy"></i> Copiar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Ticket 3: Descuento Estudiantil -->
            <div class="promo-ticket" style="--accent-color: #3b82f6;">
                <div class="ticket-header">
                    <div class="ticket-icon"><i class="fa-solid fa-graduation-cap"></i></div>
                    <span class="ticket-badge" style="background: rgba(59, 130, 246, 0.08); color: #3b82f6;">Estudiantes</span>
                </div>
                <div class="ticket-body">
                    <h3 class="ticket-title">🎓 Desc. Estudiantil</h3>
                    <p class="ticket-desc">Presenta tu carnet estudiantil vigente y obtén un 10% de descuento en el total de tu compra.</p>
                </div>
                <div class="ticket-divider">
                    <span class="notch left"></span>
                    <span class="notch right"></span>
                </div>
                <div class="ticket-footer">
                    <div class="coupon-box-premium">
                        <span class="coupon-code-val">ESTUDIANTE10</span>
                        <button class="copy-coupon-btn-premium" data-code="ESTUDIANTE10">
                            <i class="fa-regular fa-copy"></i> Copiar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Ticket 4: Combo Doble -->
            <div class="promo-ticket" style="--accent-color: #ef4444;">
                <div class="ticket-header">
                    <div class="ticket-icon"><i class="fa-solid fa-burger"></i></div>
                    <span class="ticket-badge" style="background: rgba(239, 68, 68, 0.08); color: #ef4444;">Doble</span>
                </div>
                <div class="ticket-body">
                    <h3 class="ticket-title">🍟 Combo Doble</h3>
                    <p class="ticket-desc">Compra dos combos y ahorra más. Ahorra $5.00 en la compra combinada de dos combos Monster.</p>
                </div>
                <div class="ticket-divider">
                    <span class="notch left"></span>
                    <span class="notch right"></span>
                </div>
                <div class="ticket-footer">
                    <div class="coupon-box-premium">
                        <span class="coupon-code-val">DOBLEMONSTER</span>
                        <button class="copy-coupon-btn-premium" data-code="DOBLEMONSTER">
                            <i class="fa-regular fa-copy"></i> Copiar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Ticket 5: Bebida Gratis -->
            <div class="promo-ticket" style="--accent-color: #06b6d4;">
                <div class="ticket-header">
                    <div class="ticket-icon"><i class="fa-solid fa-glass-water"></i></div>
                    <span class="ticket-badge" style="background: rgba(6, 182, 212, 0.08); color: #06b6d4;">Regalo</span>
                </div>
                <div class="ticket-body">
                    <h3 class="ticket-title">🥤 Bebida Gratis</h3>
                    <p class="ticket-desc">Por compras superiores a $20. Recibe una gaseosa grande totalmente gratis en tu orden.</p>
                </div>
                <div class="ticket-divider">
                    <span class="notch left"></span>
                    <span class="notch right"></span>
                </div>
                <div class="ticket-footer">
                    <div class="coupon-box-premium">
                        <span class="coupon-code-val">BEBIDAGRATIS</span>
                        <button class="copy-coupon-btn-premium" data-code="BEBIDAGRATIS">
                            <i class="fa-regular fa-copy"></i> Copiar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Ticket 6: Jueves de Pizza -->
            <div class="promo-ticket" style="--accent-color: #f97316;">
                <div class="ticket-header">
                    <div class="ticket-icon"><i class="fa-solid fa-pizza-slice"></i></div>
                    <span class="ticket-badge" style="background: rgba(249, 115, 22, 0.08); color: #f97316;">Pizza Day</span>
                </div>
                <div class="ticket-body">
                    <h3 class="ticket-title">🍕 Jueves de Pizza</h3>
                    <p class="ticket-desc">Promoción especial semanal. Todas las pizzas medianas con un 25% de descuento en local.</p>
                </div>
                <div class="ticket-divider">
                    <span class="notch left"></span>
                    <span class="notch right"></span>
                </div>
                <div class="ticket-footer">
                    <div class="coupon-box-premium">
                        <span class="coupon-code-val">PIZZA25</span>
                        <button class="copy-coupon-btn-premium" data-code="PIZZA25">
                            <i class="fa-regular fa-copy"></i> Copiar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Ticket 7: Cliente Frecuente -->
            <div class="promo-ticket" style="--accent-color: #8b5cf6;">
                <div class="ticket-header">
                    <div class="ticket-icon"><i class="fa-solid fa-star"></i></div>
                    <span class="ticket-badge" style="background: rgba(139, 92, 246, 0.08); color: #8b5cf6;">Fidelidad</span>
                </div>
                <div class="ticket-body">
                    <h3 class="ticket-title">⭐ Cliente Frecuente</h3>
                    <p class="ticket-desc">Premiamos tu fidelidad. Acumula 5 compras registradas en el local y recibe un combo gratis.</p>
                </div>
                <div class="ticket-divider">
                    <span class="notch left"></span>
                    <span class="notch right"></span>
                </div>
                <div class="ticket-footer">
                    <div class="coupon-box-premium">
                        <span class="coupon-code-val">VIPMONSTER</span>
                        <button class="copy-coupon-btn-premium" data-code="VIPMONSTER">
                            <i class="fa-regular fa-copy"></i> Copiar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Ticket 8: Pedido Online -->
            <div class="promo-ticket" style="--accent-color: #10b981;">
                <div class="ticket-header">
                    <div class="ticket-icon"><i class="fa-solid fa-mobile-screen-button"></i></div>
                    <span class="ticket-badge" style="background: rgba(16, 185, 129, 0.08); color: #10b981;">Online</span>
                </div>
                <div class="ticket-body">
                    <h3 class="ticket-title">📱 Pedido Online</h3>
                    <p class="ticket-desc">Promoción exclusiva web. Obtén un 5% de descuento adicional al realizar tu pedido desde el sitio.</p>
                </div>
                <div class="ticket-divider">
                    <span class="notch left"></span>
                    <span class="notch right"></span>
                </div>
                <div class="ticket-footer">
                    <div class="coupon-box-premium">
                        <span class="coupon-code-val">WEB5</span>
                        <button class="copy-coupon-btn-premium" data-code="WEB5">
                            <i class="fa-regular fa-copy"></i> Copiar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Ticket 9: Temporada Especial -->
            <div class="promo-ticket" style="--accent-color: #ec4899;">
                <div class="ticket-header">
                    <div class="ticket-icon"><i class="fa-solid fa-calendar-days"></i></div>
                    <span class="ticket-badge" style="background: rgba(236, 72, 153, 0.08); color: #ec4899;">Estacional</span>
                </div>
                <div class="ticket-body">
                    <h3 class="ticket-title">🎄 Promo de Temporada</h3>
                    <p class="ticket-desc">Disponible por tiempo limitado. Descuentos y combos temáticos de Navidad, Verano o Fiestas Patrias.</p>
                </div>
                <div class="ticket-divider">
                    <span class="notch left"></span>
                    <span class="notch right"></span>
                </div>
                <div class="ticket-footer">
                    <div class="coupon-box-premium">
                        <span class="coupon-code-val">TEMPORADA2026</span>
                        <button class="copy-coupon-btn-premium" data-code="TEMPORADA2026">
                            <i class="fa-regular fa-copy"></i> Copiar
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Toast de Notificación Flotante -->
    <div id="copy-toast" class="copy-toast">¡Código copiado al portapapeles!</div>
</section>

<!-- JavaScript local para la interactividad de promociones -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Cuenta Regresiva de Oferta Especial
    function updateCountdown() {
        const now = new Date();
        const target = new Date();
        target.setHours(23, 59, 59, 999);
        
        const diff = target - now;
        
        const h = document.getElementById('timer-hours');
        const m = document.getElementById('timer-mins');
        const s = document.getElementById('timer-secs');
        
        if (!h || !m || !s) return;
        
        if (diff <= 0) {
            h.textContent = '00';
            m.textContent = '00';
            s.textContent = '00';
            return;
        }
        
        const hours = Math.floor(diff / (1000 * 60 * 60));
        const mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const secs = Math.floor((diff % (1000 * 60)) / 1000);
        
        h.textContent = String(hours).padStart(2, '0');
        m.textContent = String(mins).padStart(2, '0');
        s.textContent = String(secs).padStart(2, '0');
    }
    
    updateCountdown();
    setInterval(updateCountdown, 1000);
    
    // 2. Copiar Códigos de Cupón con el botón premium
    const copyButtons = document.querySelectorAll('.copy-coupon-btn-premium');
    const toast = document.getElementById('copy-toast');
    
    copyButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const code = this.getAttribute('data-code');
            
            navigator.clipboard.writeText(code).then(() => {
                // Cambiar clase e icono temporalmente
                const originalContent = this.innerHTML;
                this.innerHTML = '<i class="fa-solid fa-check"></i> Copiado';
                this.classList.add('copied');
                
                // Mostrar Toast
                if (toast) {
                    toast.classList.add('show');
                }
                
                setTimeout(() => {
                    this.innerHTML = originalContent;
                    this.classList.remove('copied');
                    if (toast) {
                        toast.classList.remove('show');
                    }
                }, 2000);
            }).catch(err => {
                console.error('Error al copiar el código: ', err);
            });
        });
    });
});
</script>
