<?php
// Подключение стилей родительской темы и дочерней темы
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', array('parent-style'), time());
});

// Включение поддержки WooCommerce (Storefront уже поддерживает WooCommerce)
add_action('after_setup_theme', function() {
    add_theme_support('woocommerce');
});

// Вставка Instagram и WhatsApp после последнего элемента в primary menu Storefront
add_filter('wp_nav_menu_items', function($items, $args) {
    if (isset($args->theme_location) && $args->theme_location === 'primary') {
        $items .= PHP_EOL . file_get_contents(get_stylesheet_directory() . '/social-menu-items.php');
    }
    return $items;
}, 20, 2);

// Подключение svg-спрайта в <body> для видимости иконок
add_action('wp_footer', function() {
    $svg_sprite_path = get_stylesheet_directory() . '/js/svg-sprite.js';
    if (file_exists($svg_sprite_path)) {
        // Получаем содержимое window.SVG_SPRITE = '...';
        $js = file_get_contents($svg_sprite_path);
        if (preg_match("/window.SVG_SPRITE\s*=\s*'([^']+)'/s", $js, $m)) {
            echo $m[1];
        } elseif (preg_match('/window.SVG_SPRITE\s*=\s*`([^`]+)`/s', $js, $m)) {
            echo $m[1];
        }
    }
}, 1);

// Отключить заголовок <h1 class="woocommerce-products-header__title page-title"> на страницах магазина
add_filter('woocommerce_show_page_title', '__return_false');
