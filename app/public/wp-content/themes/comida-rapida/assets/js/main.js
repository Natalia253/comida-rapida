document.addEventListener('DOMContentLoaded', () => {
    
    // --- 1. CABECERA & NAVBAR ---
    const header = document.getElementById('header');
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const navLinks = document.getElementById('nav-links');
    const navLinksItems = document.querySelectorAll('.nav-links a');
    
    // Header pegajoso
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });
    
    // Menú móvil
    if (mobileMenuToggle && navLinks) {
        mobileMenuToggle.addEventListener('click', () => {
            navLinks.classList.toggle('active');
            // Cambiar icono de barras a equis
            const icon = mobileMenuToggle.querySelector('i');
            if (icon.classList.contains('fa-bars')) {
                icon.className = 'fa-solid fa-xmark';
            } else {
                icon.className = 'fa-solid fa-bars';
            }
        });
    }
    
    // Cerrar menú móvil al hacer click en un enlace
    navLinksItems.forEach(link => {
        link.addEventListener('click', () => {
            if (navLinks.classList.contains('active')) {
                navLinks.classList.remove('active');
                mobileMenuToggle.querySelector('i').className = 'fa-solid fa-bars';
            }
        });
    });

    // --- 2. CAROUSEL / SLIDER DE HERO ---
    const slides = document.querySelectorAll('.slide');
    const prevBtn = document.getElementById('slider-prev-btn');
    const nextBtn = document.getElementById('slider-next-btn');
    const dots = document.querySelectorAll('.slider-dot');
    let currentSlide = 0;
    let slideInterval;
    
    function showSlide(index) {
        slides.forEach(slide => slide.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));
        
        currentSlide = (index + slides.length) % slides.length;
        
        slides[currentSlide].classList.add('active');
        if (dots[currentSlide]) {
            dots[currentSlide].classList.add('active');
        }
    }
    
    function nextSlide() {
        showSlide(currentSlide + 1);
    }
    
    function prevSlide() {
        showSlide(currentSlide - 1);
    }
    
    function startSlideShow() {
        stopSlideShow();
        slideInterval = setInterval(nextSlide, 6000);
    }
    
    function stopSlideShow() {
        if (slideInterval) {
            clearInterval(slideInterval);
        }
    }
    
    if (slides.length > 0) {
        if (nextBtn) nextBtn.addEventListener('click', () => { prevSlide(); startSlideShow(); });
        if (prevBtn) prevBtn.addEventListener('click', () => { nextSlide(); startSlideShow(); });
        
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                showSlide(index);
                startSlideShow();
            });
        });
        
        startSlideShow();
    }

    // --- 3. FILTRO DE MENÚ ---
    const filterButtons = document.querySelectorAll('.filter-btn');
    const productCards = document.querySelectorAll('.product-card');
    
    filterButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            // Activar botón
            filterButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            
            const filterValue = btn.getAttribute('data-filter');
            
            productCards.forEach(card => {
                if (filterValue === 'all' || card.classList.contains(filterValue)) {
                    card.style.display = 'flex';
                    card.style.opacity = '1';
                    card.style.transform = 'scale(1)';
                } else {
                    card.style.display = 'none';
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.95)';
                }
            });
        });
    });

    // --- 4. INTEGRACIÓN CON CARRITO DE WOOCOMMERCE ---
    // Animación del botón del carrito en el navbar cuando se agrega un producto
    if (typeof jQuery !== 'undefined') {
        jQuery(document.body).on('added_to_cart', function() {
            const cartBtnEl = document.getElementById('cart-toggle-btn');
            if (cartBtnEl) {
                cartBtnEl.style.transform = 'scale(1.25)';
                setTimeout(() => {
                    cartBtnEl.style.transform = '';
                }, 200);
            }
        });
    }

    // Manejar clics para incrementar, decrementar y eliminar en WooCommerce por AJAX
    document.body.addEventListener('click', (e) => {
        const incBtn = e.target.closest('.inc-qty-wc');
        if (incBtn) {
            const key = incBtn.getAttribute('data-key');
            const currentQty = parseInt(incBtn.getAttribute('data-qty'));
            const newQty = currentQty + 1;
            actualizarCantidadWc(key, newQty, incBtn);
        }
        
        const decBtn = e.target.closest('.dec-qty-wc');
        if (decBtn) {
            const key = decBtn.getAttribute('data-key');
            const currentQty = parseInt(decBtn.getAttribute('data-qty'));
            const newQty = currentQty - 1;
            actualizarCantidadWc(key, newQty, decBtn);
        }
        
        const removeBtn = e.target.closest('.cart-item-remove-wc');
        if (removeBtn) {
            const key = removeBtn.getAttribute('data-key');
            eliminarItemWc(key, removeBtn);
        }
    });

    function actualizarCantidadWc(key, qty, btnElement) {
        const itemEl = btnElement.closest('.cart-item');
        if (itemEl) itemEl.style.opacity = '0.5';
        
        const formData = new FormData();
        formData.append('action', 'comida_rapida_actualizar_cantidad');
        formData.append('nonce', comidaRapidaData.nonce);
        formData.append('cart_item_key', key);
        formData.append('qty', qty);
        
        fetch(comidaRapidaData.ajax_url, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                console.error(data.data.message);
                if (itemEl) itemEl.style.opacity = '1';
            }
        })
        .catch(err => {
            console.error(err);
            if (itemEl) itemEl.style.opacity = '1';
        });
    }

    function eliminarItemWc(key, btnElement) {
        const itemEl = btnElement.closest('.cart-item');
        if (itemEl) itemEl.style.opacity = '0.5';
        
        const formData = new FormData();
        formData.append('action', 'comida_rapida_eliminar_item');
        formData.append('nonce', comidaRapidaData.nonce);
        formData.append('cart_item_key', key);
        
        fetch(comidaRapidaData.ajax_url, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                console.error(data.data.message);
                if (itemEl) itemEl.style.opacity = '1';
            }
        })
        .catch(err => {
            console.error(err);
            if (itemEl) itemEl.style.opacity = '1';
        });
    }
});
