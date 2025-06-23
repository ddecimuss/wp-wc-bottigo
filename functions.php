<?php
// Подключение стилей родительской темы и дочерней темы
add_action('wp_enqueue_scripts', function() {
    // Use proper versioning instead of time() for better caching
    $theme_version = wp_get_theme()->get('Version');
    
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css', [], $theme_version);
    wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', array('parent-style'), $theme_version);
});

// Включение поддержки WooCommerce (Storefront уже поддерживает WooCommerce)
add_action('after_setup_theme', function() {
    add_theme_support('woocommerce');
});


// Вставка Instagram, WhatsApp и корзины после последнего элемента в primary и handheld меню Storefront
add_filter('wp_nav_menu_items', function($items, $args) {
    if (
        isset($args->theme_location)
        && in_array($args->theme_location, ['primary', 'handheld'])
    ) {
        // Cache social menu items to avoid repeated file reads
        static $social_menu_cache = null;
        if ($social_menu_cache === null) {
            $social_file = get_stylesheet_directory() . '/social-menu-items.php';
            $social_menu_cache = file_exists($social_file) ? file_get_contents($social_file) : '';
        }
        $items .= PHP_EOL . $social_menu_cache;
    }
    return $items;
}, 20, 2);

// Подключение svg-спрайта в <body> для видимости иконок
add_action('wp_footer', function() {
    static $svg_sprite_cache = null;

    if ($svg_sprite_cache === null) {
        $svg_sprite_path = get_stylesheet_directory() . '/js/svg-sprite.js';

        if (file_exists($svg_sprite_path)) {
            $js = file_get_contents($svg_sprite_path);

            if (preg_match("/window\.SVG_SPRITE\s*=\s*'([^']+)'/s", $js, $m) ||
                preg_match('/window\.SVG_SPRITE\s*=\s*`([^`]+)`/s', $js, $m)) {
                $svg_sprite_cache = $m[1];
            } else {
                $svg_sprite_cache = '';
            }
        } else {
            $svg_sprite_cache = '';
        }
    }

    if (!empty($svg_sprite_cache)) {
        echo '<div style="display:none" aria-hidden="true">';
        echo $svg_sprite_cache;
        echo '</div>';
    }
}, 1);

// Отключить заголовок <h1 class="woocommerce-products-header__title page-title"> на страницах магазина
add_filter('woocommerce_show_page_title', '__return_false');

// Подключение собственного JS-файла дочерней темы
add_action('wp_enqueue_scripts', function() {
    $theme_version = wp_get_theme()->get('Version');
    $js_file = get_stylesheet_directory() . '/js/index.js';
    
    // Use file modification time only in development, theme version in production
    $version = (defined('WP_DEBUG') && WP_DEBUG && file_exists($js_file)) 
        ? filemtime($js_file) 
        : $theme_version;
    
    wp_enqueue_script(
        'bottigo-child-js',
        get_stylesheet_directory_uri() . '/js/index.js',
        array(),
        $version,
        true
    );
});



// --- Убрать "Мой аккаунт" из мобильного footer bar Storefront ---
add_filter('storefront_handheld_footer_bar_links', function($links) {
    unset($links['my-account']);
    return $links;
}, 10);

// --- Отключить регистрацию и вход WooCommerce (только гость) ---
add_filter('woocommerce_checkout_registration_enabled', '__return_false');
add_filter('woocommerce_enable_checkout_login_reminder', '__return_false');
add_filter('woocommerce_enable_myaccount_registration', '__return_false');
add_filter('woocommerce_myaccount_show_login_form', '__return_false');

// === PERFORMANCE OPTIMIZATIONS ===

// Remove unnecessary WordPress features for better performance
add_action('init', function() {
    // Remove emoji scripts and styles
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('admin_print_styles', 'print_emoji_styles');
    
    // Remove unnecessary REST API links
    remove_action('wp_head', 'rest_output_link_wp_head');
    remove_action('wp_head', 'wp_oembed_add_discovery_links');
    
    // Remove Windows Live Writer support
    remove_action('wp_head', 'wlwmanifest_link');
    
    // Remove RSD link
    remove_action('wp_head', 'rsd_link');
    
    // Remove shortlink
    remove_action('wp_head', 'wp_shortlink_wp_head');
});

// Optimize database queries
add_action('init', function() {
    // Remove unnecessary queries
    remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
    remove_action('wp_head', 'wp_generator');
});

// === SEO OPTIMIZATIONS ===

// Add proper meta tags and structured data
add_action('wp_head', function() {
    if (is_front_page()) {
        echo '<meta name="description" content="BOTTIGO - Интернет-магазин женской обуви в Казахстане. Доставка в любую точку, гарантия качества, оперативная отправка.">' . "\n";
        echo '<meta name="keywords" content="женская обувь, обувь Казахстан, интернет-магазин обуви, BOTTIGO">' . "\n";
        
        // Open Graph tags
        echo '<meta property="og:title" content="BOTTIGO - Интернет-магазин женской обуви">' . "\n";
        echo '<meta property="og:description" content="Качественная женская обувь с доставкой по Казахстану">' . "\n";
        echo '<meta property="og:type" content="website">' . "\n";
        echo '<meta property="og:url" content="' . esc_url(home_url('/')) . '">' . "\n";
        echo '<meta property="og:site_name" content="BOTTIGO">' . "\n";
    }
    
    // Add JSON-LD structured data for organization
    if (is_front_page()) {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => 'BOTTIGO',
            'description' => 'Интернет-магазин женской обуви',
            'url' => home_url('/'),
            'logo' => get_stylesheet_directory_uri() . '/assets/img/Logo.SVG',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'Назарбаева 1',
                'addressLocality' => 'Казахстан'
            ],
            'sameAs' => [
                'https://www.instagram.com/ваш_аккаунт/',
                'https://wa.me/79876543210'
            ]
        ];
        echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
    }
});

// === ACCESSIBILITY IMPROVEMENTS ===

// Add skip link for keyboard navigation
add_action('wp_body_open', function() {
    echo '<a class="skip-link screen-reader-text" href="#content">Перейти к содержимому</a>' . "\n";
});

// Improve image accessibility
add_filter('wp_get_attachment_image_attributes', function($attr, $attachment, $size) {
    // Ensure all images have proper alt text
    if (empty($attr['alt'])) {
        $attr['alt'] = get_post_meta($attachment->ID, '_wp_attachment_image_alt', true) ?: '';
    }
    return $attr;
}, 10, 3);

