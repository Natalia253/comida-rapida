# 🍔 Comida Rápida - Sistema de Pedidos y Menú Online

Este proyecto es un sitio web completo para un restaurante de comida rápida, desarrollado sobre **WordPress** utilizando un tema totalmente personalizado y dinámico. Permite a los clientes explorar el menú, gestionar un carrito de compras, realizar pedidos en línea y cuenta con un panel de administración para gestionar el flujo de ventas.

---

## 🚀 Características Principales

*   **Menú Interactivo:** Visualización dinámica de platos, bebidas y combos.
*   **Carrito de Compras:** Sistema persistente para añadir, modificar y eliminar productos.
*   **Flujo de Compra (Checkout):** Formulario integrado para procesar pedidos de manera rápida y sencilla.
*   **Panel de Administración Personalizado:** Vista administrativa para gestionar los pedidos entrantes y el estado de las ventas.
*   **Autenticación de Usuarios:** Sistema de inicio de sesión y registro para clientes y administradores.
*   **Diseño Premium y Responsivo:** Optimizado para dispositivos móviles y computadoras de escritorio.

---

## 🛠️ Tecnologías Utilizadas

*   **Core:** [WordPress](https://wordpress.org/)
*   **Lenguajes:** PHP, JavaScript (ES6+), HTML5, CSS3
*   **Servidor Local:** LocalWP (configuración incluida)
*   **Control de Versiones:** Git & GitHub

---

## 📁 Estructura del Proyecto

Las partes más importantes del desarrollo a medida se encuentran dentro del tema personalizado:

```text
comida-rapida/
├── app/
│   └── public/
│       └── wp-content/
│           └── themes/
│               └── comida-rapida/      <-- Tema personalizado (Lógica y Vistas)
│                   ├── assets/         <-- Archivos CSS, JavaScript e imágenes
│                   ├── functions.php   <-- Configuración del tema y endpoints AJAX
│                   ├── header.php      <-- Cabecera global y navegación
│                   ├── footer.php      <-- Pie de página global
│                   ├── page-menu.php   <-- Página de catálogo del menú
│                   ├── page-carrito.php<-- Vista detallada del carrito
│                   ├── page-compra.php <-- Procesamiento de pedidos
│                   ├── page-login.php  <-- Formulario de acceso
│                   └── page-admin.php  <-- Panel de control de pedidos
└── .gitignore                          <-- Exclusiones de Git para LocalWP
```

---

## 💻 Instalación y Configuración Local

1.  **Clonar el repositorio:**
    ```bash
    git clone https://github.com/Natalia253/comida-rapida.git
    ```
2.  **Importar en LocalWP:**
    *   Abre **LocalWP** (Local by Flywheel).
    *   Arrastra y suelta la carpeta del proyecto o crea un sitio nuevo e importa los archivos de la carpeta `app/`.
3.  **Base de Datos:**
    *   La base de datos inicial está disponible en la ruta `app/sql/local.sql` para ser importada en la herramienta de base de datos de tu elección (como Adminer o phpMyAdmin).
4.  **Activar el Tema:**
    *   Accede al panel de administración de WordPress (`/wp-admin`).
    *   Ve a **Apariencia > Temas** y activa el tema **Comida Rápida**.
