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
        // Cache social menu items to avoid repeated file operations
        static $social_menu_cache = null;
        if ($social_menu_cache === null) {
            $social_file = get_stylesheet_directory() . '/social-menu-items.php';
            if (file_exists($social_file)) {
                // Use output buffering to execute PHP code
                ob_start();
                include $social_file;
                $social_menu_cache = ob_get_clean();
            } else {
                $social_menu_cache = '';
            }
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
            'logo' => home_url(str_replace(ABSPATH, '/', get_stylesheet_directory()) . '/assets/img/Logo.SVG'),
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'Назарбаева 1',
                'addressLocality' => 'Казахстан'
            ],
            'sameAs' => [
                'https://www.instagram.com/bottigo_official/',
                'https://wa.me/79876543210'
            ]
        ];
        echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
    }
});

// === WOOCOMMERCE PRODUCT FILTERS CUSTOMIZATION ===

// Customize WooCommerce product filters to use submit button instead of auto-update
add_action('wp_enqueue_scripts', function() {
    if (is_shop() || is_product_category() || is_product_tag()) {
        wp_enqueue_script(
            'bottigo-filters-custom',
            get_stylesheet_directory_uri() . '/js/filters-custom.js',
            array('jquery'),
            wp_get_theme()->get('Version'),
            true
        );
        
        // Add localization for AJAX
        wp_localize_script('bottigo-filters-custom', 'bottigoFilters', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('bottigo_filters_nonce'),
            'shop_url' => wc_get_page_permalink('shop')
        ));
    }
});

// Add custom CSS for filters
add_action('wp_head', function() {
    if (is_shop() || is_product_category() || is_product_tag()) {
        echo '<style>

        .bottigo-filters-submit-container {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }
        
        .bottigo-filters-submit {
            width: 100%;
            padding: 12px 20px;
            background: #333;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .bottigo-filters-submit:hover {
            background: #555;
        }
        
        .bottigo-filters-submit:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .bottigo-filters-loading {
            opacity: 0.6;
            pointer-events: none;
        }
        
        /* Custom Clear Filters Button - Always Visible with new design */
        .wp-block-woocommerce-product-filter-active {
            display: block !important;
        }
        
        .wp-block-woocommerce-product-filter-active[hidden] {
            display: block !important;
        }
        
        .wc-block-product-filter--hidden {
            display: block !important;
        }
        
        /* Hide removable chips */
        .wp-block-woocommerce-product-filter-removable-chips,
        .wc-block-product-filter-removable-chips,
        .wp-block-product-filter-removable-chips-is-layout-flex {
            display: none !important;
        }
        
      
        
        
        /* Hide auto-update functionality */
        .wc-block-product-filters [data-wp-on--change],
        .wc-block-product-filters [data-wp-on--input],
        .wc-block-product-filters [data-wp-on--mouseup],
        .wc-block-product-filters [data-wp-on--keyup],
        .wc-block-product-filters [data-wp-on--touchend] {
            pointer-events: auto;
        }
        
        /* Smooth transitions for filter elements */
        .wc-block-product-filter-checkbox-list__item,
        .wc-block-product-filter-price-slider {
            transition: opacity 0.2s ease;
        }
        </style>';
    }
});

// Disable WooCommerce Interactivity API auto-navigation for filters
add_action('wp_footer', function() {
    if (is_shop() || is_product_category() || is_product_tag()) {
        ?>
        <script>
        // Override WooCommerce Interactivity API to prevent auto-navigation
        document.addEventListener('DOMContentLoaded', function() {
 
            
            // Comprehensive disable of automatic navigation on all filter changes
            const disableAutoNavigation = () => {
                // Find all filter elements and remove auto-navigation attributes
                const filterElements = document.querySelectorAll('[data-wp-on--change], [data-wp-on--input], [data-wp-on--mouseup], [data-wp-on--keyup], [data-wp-on--touchend], [data-wp-on--click]');
                
                filterElements.forEach(element => {
                    // Store original handlers for potential restoration
                    const originalHandlers = {};
                    
                    // Remove all navigation-triggering attributes
                    ['data-wp-on--change', 'data-wp-on--input', 'data-wp-on--mouseup', 'data-wp-on--keyup', 'data-wp-on--touchend', 'data-wp-on--click'].forEach(attr => {
                        if (element.hasAttribute(attr)) {
                            originalHandlers[attr] = element.getAttribute(attr);
                            const handlerValue = originalHandlers[attr];
                            
                            // Remove handlers that cause navigation for all filter types
                            if (handlerValue.includes('navigate') || 
                                handlerValue.includes('actions.toggleFilter') ||
                                handlerValue.includes('actions.setMinPrice') ||
                                handlerValue.includes('actions.setMaxPrice') ||
                                handlerValue.includes('actions.debounce')) {
                                element.removeAttribute(attr);
                            }
                        }
                    });
                    
                    // Store original handlers for potential restoration
                    element._originalHandlers = originalHandlers;
                });
                
                // Also disable any form submissions within filters
                const filterForms = document.querySelectorAll('.wc-block-product-filters form');
                filterForms.forEach(form => {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    });
                });
            };
            
            // Run immediately
            disableAutoNavigation();
            

            
            // Observer for dynamically added content
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes.length > 0) {
                        disableAutoNavigation();

                    }
                });
            });
            
            const filtersContainer = document.querySelector('.wc-block-product-filters');
            if (filtersContainer) {
                observer.observe(filtersContainer, {
                    childList: true,
                    subtree: true
                });
            }
        });
        </script>
        <?php
    }
});

// Add submit button to product filters and enhanced price slider functionality
add_action('wp_footer', function() {
    if (is_shop() || is_product_category() || is_product_tag()) {
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addSubmitButton = () => {
                // Find the filters overlay content
                const filtersContent = document.querySelector('.wc-block-product-filters__overlay-content');
                
                
                if (filtersContent && !document.querySelector('.bottigo-filters-submit-container')) {
                    // Create submit button container
                    const submitContainer = document.createElement('div');
                    submitContainer.className = 'bottigo-filters-submit-container';
                    
                    const submitButton = document.createElement('button');
                    submitButton.className = 'bottigo-filters-submit';
                    submitButton.textContent = 'Применить фильтры';
                    submitButton.type = 'button';
                    submitButton.setAttribute('aria-label', 'Применить выбранные фильтры товаров');
                    
                    submitContainer.appendChild(submitButton);
                    
                    
                }
            };

            // Enhanced price slider functionality - click to position
            const enhancePriceSlider = () => {
                const priceSliders = document.querySelectorAll('.wp-block-woocommerce-product-filter-price-slider, [data-block-name="woocommerce/product-filter-price-slider"]');
                
                priceSliders.forEach(sliderBlock => {
                    // Skip if already enhanced
                    if (sliderBlock.classList.contains('bottigo-enhanced')) return;
                    sliderBlock.classList.add('bottigo-enhanced');
                    
                    const minInput = sliderBlock.querySelector('input[type="range"].min, input[type="range"][data-wp-bind--value*="minPrice"]');
                    const maxInput = sliderBlock.querySelector('input[type="range"].max, input[type="range"][data-wp-bind--value*="maxPrice"]');
                    
                    if (!minInput || !maxInput) return;
                    
                    // Create a wrapper for the slider track
                    const sliderWrapper = document.createElement('div');
                    sliderWrapper.className = 'bottigo-price-slider-wrapper';
                    
                    // Insert wrapper before the first input
                    minInput.parentNode.insertBefore(sliderWrapper, minInput);
                    
                    // Move both inputs into the wrapper
                    sliderWrapper.appendChild(minInput);
                    sliderWrapper.appendChild(maxInput);
                    
                    // Add click handler to the wrapper
                    sliderWrapper.addEventListener('click', function(e) {
                        // Don't handle clicks on the actual inputs
                        if (e.target === minInput || e.target === maxInput) return;
                        
                        const rect = sliderWrapper.getBoundingClientRect();
                        const clickX = e.clientX - rect.left;
                        const sliderWidth = rect.width;
                        
                        // Calculate the percentage of where the click occurred
                        const clickPercent = Math.max(0, Math.min(1, clickX / sliderWidth));
                        
                        // Get min and max values
                        const minValue = parseFloat(minInput.min);
                        const maxValue = parseFloat(minInput.max);
                        const range = maxValue - minValue;
                        
                        // Calculate the target value based on click position
                        const targetValue = minValue + (range * clickPercent);
                        
                        // Get current values
                        const currentMin = parseFloat(minInput.value);
                        const currentMax = parseFloat(maxInput.value);
                        
                        // Determine which slider is closer to the click position
                        const distanceToMin = Math.abs(targetValue - currentMin);
                        const distanceToMax = Math.abs(targetValue - currentMax);
                        
                        let inputToMove, newValue;
                        
                        if (distanceToMin <= distanceToMax) {
                            // Move min slider, but don't let it go above max
                            inputToMove = minInput;
                            newValue = Math.min(targetValue, currentMax);
                        } else {
                            // Move max slider, but don't let it go below min
                            inputToMove = maxInput;
                            newValue = Math.max(targetValue, currentMin);
                        }
                        
                        // Update the slider value
                        inputToMove.value = Math.round(newValue);
                        
                        // Trigger input event to update any bound values
                        const inputEvent = new Event('input', { bubbles: true });
                        inputToMove.dispatchEvent(inputEvent);
                        
                        // Update any associated text inputs
                        updatePriceInputs(sliderBlock);
                        
                        // Update visual representation
                        updateSliderVisuals(sliderBlock);
                    });
                    
                    // Add input event listeners to update visuals
                    [minInput, maxInput].forEach(input => {
                        input.addEventListener('input', () => {
                            updatePriceInputs(sliderBlock);
                            updateSliderVisuals(sliderBlock);
                        });
                    });
                    
                    // Initial visual update
                    updateSliderVisuals(sliderBlock);
                });
            };
            
            // Update price text inputs based on slider values
            const updatePriceInputs = (sliderBlock) => {
                const minSlider = sliderBlock.querySelector('input[type="range"].min, input[type="range"][data-wp-bind--value*="minPrice"]');
                const maxSlider = sliderBlock.querySelector('input[type="range"].max, input[type="range"][data-wp-bind--value*="maxPrice"]');
                const minTextInput = sliderBlock.querySelector('input[type="text"], input[type="number"]');
                const maxTextInput = sliderBlock.querySelectorAll('input[type="text"], input[type="number"]')[1];
                
                if (minSlider && minTextInput) {
                    minTextInput.value = minSlider.value;
                }
                if (maxSlider && maxTextInput) {
                    maxTextInput.value = maxSlider.value;
                }
            };
            
            // Update slider visual representation
            const updateSliderVisuals = (sliderBlock) => {
                const minSlider = sliderBlock.querySelector('input[type="range"].min, input[type="range"][data-wp-bind--value*="minPrice"]');
                const maxSlider = sliderBlock.querySelector('input[type="range"].max, input[type="range"][data-wp-bind--value*="maxPrice"]');
                
                if (!minSlider || !maxSlider) return;
                
                const min = parseFloat(minSlider.min);
                const max = parseFloat(minSlider.max);
                const currentMin = parseFloat(minSlider.value);
                const currentMax = parseFloat(maxSlider.value);
                
                // Calculate percentages
                const minPercent = ((currentMin - min) / (max - min)) * 100;
                const maxPercent = ((currentMax - min) / (max - min)) * 100;
                
                // Update CSS custom properties for visual styling
                sliderBlock.style.setProperty('--range-min', minPercent + '%');
                sliderBlock.style.setProperty('--range-max', maxPercent + '%');
            };
            
            // Make clear button always visible
            const makeClearButtonVisible = () => {
                const clearButtonContainer = document.querySelector('.wc-block-product-filter-active');
                if (clearButtonContainer) {
                    clearButtonContainer.style.display = 'block';
                    clearButtonContainer.removeAttribute('hidden');
                    
                    // Change button text
                    const clearButton = clearButtonContainer.querySelector('button');
                    if (clearButton && clearButton.textContent.trim() === 'Очистить фильтры') {
                        clearButton.textContent = 'Сбросить фильтры';
                    }
                }
            };
            
            // Custom checkbox filter enhancement
            const enhanceCheckboxFilters = () => {
                const checkboxLists = document.querySelectorAll('.wc-block-product-filter-checkbox-list__list');
                
                checkboxLists.forEach(list => {
                    // Skip if already enhanced
                    if (list.classList.contains('bottigo-enhanced-checkboxes')) return;
                    list.classList.add('bottigo-enhanced-checkboxes');
                    
                    const items = list.querySelectorAll('.wc-block-product-filter-checkbox-list__item');
                    
                    items.forEach(item => {
                        const input = item.querySelector('.wc-block-product-filter-checkbox-list__input');
                        const label = item.querySelector('.wc-block-product-filter-checkbox-list__label');
                        const text = item.querySelector('.wc-block-product-filter-checkbox-list__text');
                        const textWrapper = item.querySelector('.wc-block-product-filter-checkbox-list__text-wrapper');
                        
                        if (!input || !label || !text) return;
                        
                        // Extract count from data-wp-context
                        let count = '';
                        try {
                            const contextData = input.getAttribute('data-wp-context');
                            if (contextData) {
                                const context = JSON.parse(contextData);
                                if (context.item && context.item.count) {
                                    count = context.item.count;
                                }
                            }
                        } catch (e) {
                            console.log('Could not parse context data:', e);
                        }
                        
                        // Get filter name from text content or aria-label
                        const filterName = text.textContent.trim() || input.getAttribute('aria-label') || input.value;
                        
                        // Create count element if we have count data
                        if (count && count > 0) {
                            const countSpan = document.createElement('span');
                            countSpan.className = 'filter-count';
                            countSpan.textContent = `(${count})`;
                            countSpan.style.cssText = `
                                color: var(--filter-muted);
                                font-size: 0.9em;
                                margin-left: 6px;
                                font-weight: normal;
                            `;
                            
                            // Add count to the text
                            text.appendChild(countSpan);
                        }
                        
                        // Ensure proper styling is applied
                        if (label) {
                            label.style.display = 'flex';
                            label.style.justifyContent = 'space-between';
                            label.style.alignItems = 'center';
                            label.style.width = '100%';
                        }
                    });
                });
            };
            
            // Initialize everything
            addSubmitButton();
            enhancePriceSlider();
            makeClearButtonVisible();
            enhanceCheckboxFilters();
            
            // Also add after a delay to catch dynamically loaded content
            setTimeout(() => {
                addSubmitButton();
                enhancePriceSlider();
                makeClearButtonVisible();
                enhanceCheckboxFilters();
            }, 1000);
            
            // Observer for dynamically added content
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes.length > 0) {
                        setTimeout(() => {
                            addSubmitButton();
                            enhancePriceSlider();
                            makeClearButtonVisible();
                            enhanceCheckboxFilters();
                        }, 100);
                    }
                });
            });
            
            const body = document.querySelector('body');
            if (body) {
                observer.observe(body, {
                    childList: true,
                    subtree: true
                });
            }
        });
        </script>
        <?php
    }
});

// === WOOCOMMERCE CATALOG LAYOUT CUSTOMIZATION ===

// Remove default WooCommerce content wrappers and sidebar to prevent duplication
add_action('template_redirect', function() {
    // Only apply to WooCommerce shop/catalog pages
    if (!function_exists('is_woocommerce') || !is_woocommerce()) {
        return;
    }
    
    // Only apply to shop, category, tag pages (not single product pages)
    if (!is_shop() && !is_product_category() && !is_product_tag()) {
        return;
    }
    
    // Remove ALL default WooCommerce wrappers to prevent duplication
    remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
    remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
    
    // Remove Storefront theme specific wrappers that might cause duplication
    remove_action('woocommerce_before_main_content', 'storefront_before_content', 10);
    remove_action('woocommerce_after_main_content', 'storefront_after_content', 10);
    
    // Remove any other potential wrapper functions from Storefront
    remove_action('woocommerce_before_main_content', 'storefront_content_wrapper_start', 10);
    remove_action('woocommerce_after_main_content', 'storefront_content_wrapper_end', 10);
    
    // Remove default sidebar output - we'll handle it in our custom template
    remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
    
    // Remove duplicate sorting after products to prevent duplication
    remove_action('woocommerce_after_shop_loop', 'woocommerce_result_count', 20);
    remove_action('woocommerce_after_shop_loop', 'woocommerce_catalog_ordering', 30);
    
    // Remove all actions from woocommerce_after_shop_loop to completely prevent any content after products
    remove_all_actions('woocommerce_after_shop_loop');
});

// === MOBILE FILTERS BUTTON ===

// Add mobile filters button to sorting area
add_action('woocommerce_before_shop_loop', 'add_mobile_filters_button', 25);

function add_mobile_filters_button() {
    // Only add on shop/catalog pages
    if (!is_shop() && !is_product_category() && !is_product_tag()) {
        return;
    }
    
    // Check if we're in the main query and have products
    if (!woocommerce_product_loop()) {
        return;
    }
    
    ?>
    <div class="mobile-filters-button-wrapper">
        <button type="button" class="mobile-filters-button" aria-label="Открыть фильтры товаров">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M3 7H21L19 12H5L3 7Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M12 12V20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <path d="M8 16L12 20L16 16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>Фильтры</span>
        </button>
    </div>
    <?php
}

// Enqueue mobile filters JavaScript
add_action('wp_enqueue_scripts', function() {
    // Only load on WooCommerce shop pages
    if (!function_exists('is_woocommerce') || !is_woocommerce()) {
        return;
    }
    
    if (!is_shop() && !is_product_category() && !is_product_tag()) {
        return;
    }
    
    // Enqueue the mobile filters script
    wp_enqueue_script(
        'mobile-filters',
        get_stylesheet_directory_uri() . '/js/mobile-filters.js',
        array(),
        wp_get_theme()->get('Version'),
        true
    );
});

// Add modern styling for sorting area and mobile filters button
add_action('wp_head', function() {
    if (!function_exists('is_woocommerce') || !is_woocommerce()) {
        return;
    }
    
    if (!is_shop() && !is_product_category() && !is_product_tag()) {
        return;
    }
    
    echo '<style>
        /* Clean styling for storefront-sorting area */
        .storefront-sorting {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 15px;
            justify-content: flex-end;
        }
        
        /* Clean select styling */
        .mobile-filters-container .woocommerce-ordering select.orderby,
        .mobile-sorting-wrapper .woocommerce-ordering select.orderby {
            background: #fff;
            border: 1px solid #ccc;
            border-radius: 3px;
            padding: 8px 30px 8px 10px;
            font-size: 14px;
            color: #333;
            min-width: 180px;
            appearance: none;
            background-position: right 8px center;
            background-repeat: no-repeat;
            background-size: 14px;
            transition: border-color 0.2s ease;
        }
        
        .storefront-sorting .woocommerce-ordering select.orderby:focus {
            outline: none;
            border-color: #333;
        }
        
        .storefront-sorting .woocommerce-ordering select.orderby:hover {
            border-color: #999;
        }
        
        /* Clean result count styling */
        .storefront-sorting .woocommerce-result-count {
            color: #666;
            font-size: 14px;
            margin: 0;
            font-weight: normal;
        }
        
        /* Clean mobile filters button */
        .mobile-filters-button-wrapper {
            margin: 0;
        }
        
        .mobile-filters-button {
            background: #333;
            border: 1px solid #333;
            border-radius: 3px;
            color: #fff;
            padding: 10px 16px;
            font-size: 14px;
            font-weight: normal;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: background-color 0.2s ease;
            min-height: 44px;
            text-transform: none;
            letter-spacing: 0;
        }
        
        .mobile-filters-button:hover {
            background: #555;
            border-color: #555;
        }
        
        .mobile-filters-button:active,
        .mobile-filters-button.clicked {
            background: #222;
            border-color: #222;
        }
        
        .mobile-filters-button svg {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
        }
        
        .mobile-filters-button span {
            font-weight: normal;
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .storefront-sorting {
                display: flex;
                width: 100%;
                max-width: 100%;
                padding: 0;
                border: none;
                background: transparent;
                margin-bottom: 20px;
            }
            
            .mobile-filters-button-wrapper {
                flex: 1;
            }
            
            .mobile-filters-button {
                flex: 1;
                width: 100%;
                height: 44px;
                font-size: 14px;
                color: #242d4a;
                background-color: #fff;
                border: 1px solid #e6e7eb;
                cursor: pointer;
                border-radius: 0;
                transition: background-color 0.2s;
                justify-content: center;
                padding: 0;
                border-right: 0;
            }
            
            .mobile-filters-button:hover {
                background-color: #f3f3f3;
            }
            
            .mobile-filters-button:active,
            .mobile-filters-button.clicked {
                background-color: #f3f3f3;
            }
            
            .storefront-sorting .woocommerce-ordering {
                flex: 1;
                margin: 0;
            }
            
            .storefront-sorting .woocommerce-ordering select.orderby {
                width: 100%;
                height: 44px;
                font-size: 14px;
                color: #242d4a;
                background-color: #fff;
                border: 1px solid #e6e7eb;
                cursor: pointer;
                border-radius: 0;
                transition: background-color 0.2s;
                padding: 0 30px 0 12px;
                min-width: auto;
                appearance: none;
                background-position: right 8px center;
                background-repeat: no-repeat;
                background-size: 14px;
            }
            
            .storefront-sorting .woocommerce-ordering select.orderby:hover {
                background-color: #f3f3f3;
            }
            
            .storefront-sorting .woocommerce-ordering select.orderby:focus {
                outline: none;
                background-color: #f3f3f3;
            }
            
            .storefront-sorting .woocommerce-result-count {
                display: none;
            }
        }
        
        @media (min-width: 769px) {
            .mobile-filters-button-wrapper {
                display: none;
            }
        }
        
        /* Hide any storefront-sorting blocks that appear after products */
        .products + .storefront-sorting {
            display: none !important;
        }
    </style>';
});

// === STOCK MANAGEMENT FOR FILTERS ===

// Hide out-of-stock products from main queries
add_action('pre_get_posts', function($query) {
    // Only apply to frontend main queries
    if (is_admin() || !$query->is_main_query()) {
        return;
    }
    
    // Only apply to WooCommerce shop/catalog pages
    if (!is_shop() && !is_product_category() && !is_product_tag() && !is_product_taxonomy()) {
        return;
    }
    
    // Get existing meta query or create new one
    $meta_query = $query->get('meta_query') ?: array();
    
    // Add stock status filter
    $meta_query[] = array(
        'key' => '_stock_status',
        'value' => 'instock',
        'compare' => '='
    );
    
    $query->set('meta_query', $meta_query);
});

// Filter WooCommerce product queries to show only in-stock products
add_filter('woocommerce_product_query_meta_query', function($meta_query, $query) {
    // Only apply to frontend queries
    if (is_admin()) {
        return $meta_query;
    }
    
    // Add stock status filter
    $meta_query[] = array(
        'key' => '_stock_status',
        'value' => 'instock',
        'compare' => '='
    );
    
    return $meta_query;
}, 10, 2);

// Filter layered navigation widget queries to count only in-stock products
add_filter('woocommerce_layered_nav_count_maybe_cache', function($cached_counts, $taxonomy, $query_type) {
    // Don't use cached counts, force recalculation with stock filter
    return false;
}, 10, 3);

/**
 * ЭФФЕКТИВНО пересчитывает количество товаров для layered navigation виджетов,
 * учитывая только товары "в наличии". Использует оптимизированные SQL-запросы.
 */
add_filter('woocommerce_get_layered_nav_filters', 'optimized_layered_nav_filters_by_stock_status');

function optimized_layered_nav_filters_by_stock_status($filters) {
    if (!is_array($filters) || is_admin()) {
        return $filters;
    }
    
    global $wpdb;
    
    foreach ($filters as $taxonomy => $terms) {
        if (!is_array($terms) || empty($terms)) {
            continue;
        }
        
        // Собираем все term_id для текущей таксономии
        $term_ids = array_keys($terms);
        
        if (empty($term_ids)) {
            continue;
        }
        
        // Создаем один SQL-запрос для всех терминов таксономии
        $term_ids_placeholder = implode(',', array_fill(0, count($term_ids), '%d'));
        
        $sql = $wpdb->prepare(
            "SELECT
                tr.term_taxonomy_id AS term_id,
                COUNT(DISTINCT p.ID) AS product_count
            FROM
                {$wpdb->posts} AS p
            INNER JOIN
                {$wpdb->term_relationships} AS tr ON p.ID = tr.object_id
            INNER JOIN
                {$wpdb->term_taxonomy} AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            INNER JOIN
                {$wpdb->postmeta} AS pm ON p.ID = pm.post_id
            WHERE
                p.post_type = 'product'
                AND p.post_status = 'publish'
                AND pm.meta_key = '_stock_status'
                AND pm.meta_value = 'instock'
                AND tt.taxonomy = %s
                AND tt.term_id IN ({$term_ids_placeholder})
            GROUP BY
                tr.term_taxonomy_id",
            array_merge(array($taxonomy), $term_ids)
        );
        
        // Выполняем запрос
        $results = $wpdb->get_results($sql, OBJECT_K);
        
        // Обновляем счетчики
        foreach ($term_ids as $term_id) {
            if (isset($results[$term_id])) {
                $filters[$taxonomy][$term_id] = (int) $results[$term_id]->product_count;
            } else {
                $filters[$taxonomy][$term_id] = 0;
            }
            
            // Удаляем термины с нулевым счетчиком
            if ($filters[$taxonomy][$term_id] <= 0) {
                unset($filters[$taxonomy][$term_id]);
            }
        }
        
        // Удаляем таксономию, если не осталось терминов
        if (empty($filters[$taxonomy])) {
            unset($filters[$taxonomy]);
        }
    }
    
    return $filters;
}

/**
 * ЭФФЕКТИВНО пересчитывает количество товаров для каждого атрибута в блоке фильтров,
 * учитывая только товары "в наличии". Использует один SQL-запрос для всех терминов,
 * чтобы избежать проблем с производительностью (N+1 запросов).
 */
add_filter('woocommerce_blocks_product_filter_attribute_counts', 'optimized_filter_attribute_terms_by_stock_status', 10, 2);

function optimized_filter_attribute_terms_by_stock_status($counts, $request) {
    // Не выполняем в админ-панели и если нет данных для подсчета
    if (is_admin() || !is_array($counts) || empty($counts)) {
        return $counts;
    }

    global $wpdb;

    // Обрабатываем каждый атрибут отдельно
    foreach ($counts as $attribute_name => $terms) {
        if (!is_array($terms) || empty($terms)) {
            continue;
        }

        // Шаг 1: Собираем ID всех терминов для текущего атрибута
        $term_ids = array();
        $term_slug_to_id_map = array();
        
        foreach ($terms as $term_slug => $count) {
            $term = get_term_by('slug', $term_slug, $attribute_name);
            if ($term && !is_wp_error($term)) {
                $term_ids[] = $term->term_id;
                $term_slug_to_id_map[$term->term_id] = $term_slug;
            }
        }

        // Если нет валидных терминов, пропускаем этот атрибут
        if (empty($term_ids)) {
            continue;
        }

        // Шаг 2: Создаем ОДИН SQL-запрос для получения всех счетчиков для текущего атрибута
        $term_ids_placeholder = implode(',', array_fill(0, count($term_ids), '%d'));
        
        $sql = $wpdb->prepare(
            "SELECT
                tr.term_taxonomy_id AS term_id,
                COUNT(DISTINCT p.ID) AS product_count
            FROM
                {$wpdb->posts} AS p
            INNER JOIN
                {$wpdb->term_relationships} AS tr ON p.ID = tr.object_id
            INNER JOIN
                {$wpdb->term_taxonomy} AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            INNER JOIN
                {$wpdb->postmeta} AS pm ON p.ID = pm.post_id
            WHERE
                p.post_type = 'product'
                AND p.post_status = 'publish'
                AND pm.meta_key = '_stock_status'
                AND pm.meta_value = 'instock'
                AND tt.taxonomy = %s
                AND tt.term_id IN ({$term_ids_placeholder})
            GROUP BY
                tr.term_taxonomy_id",
            array_merge(array($attribute_name), $term_ids)
        );

        // Выполняем запрос и получаем результаты
        $results = $wpdb->get_results($sql, OBJECT_K);

        // Шаг 3: Обновляем оригинальные счетчики новыми данными
        foreach ($term_ids as $term_id) {
            $term_slug = $term_slug_to_id_map[$term_id];
            
            if (isset($results[$term_id])) {
                // Если для термина нашлись товары в наличии, обновляем счетчик
                $counts[$attribute_name][$term_slug] = (int) $results[$term_id]->product_count;
            } else {
                // Если для термина не нашлось товаров в наличии, обнуляем счетчик
                // Блок WooCommerce автоматически скроет эту опцию
                $counts[$attribute_name][$term_slug] = 0;
            }
        }

        // Удаляем термины с нулевым счетчиком
        $counts[$attribute_name] = array_filter($counts[$attribute_name], function($count) {
            return $count > 0;
        });

        // Удаляем атрибут, если не осталось терминов
        if (empty($counts[$attribute_name])) {
            unset($counts[$attribute_name]);
        }
    }

    return $counts;
}

// Filter REST API product queries to show only in-stock products
add_filter('woocommerce_rest_product_object_query', function($args, $request) {
    // Add stock status filter to REST API queries
    if (!isset($args['meta_query'])) {
        $args['meta_query'] = array();
    }
    
    $args['meta_query'][] = array(
        'key' => '_stock_status',
        'value' => 'instock',
        'compare' => '='
    );
    
    return $args;
}, 10, 2);

// Add JavaScript to hide filter options with zero count
add_action('wp_footer', function() {
    if (!is_shop() && !is_product_category() && !is_product_tag()) {
        return;
    }
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Function to hide filter options with zero count
        const hideEmptyFilterOptions = () => {
            // Find all filter items with counts
            const filterItems = document.querySelectorAll('.wc-block-product-filter-attribute .wc-block-components-checkbox__label, .wc-block-product-filter-attribute .wc-block-components-radio-control__option');
            
            filterItems.forEach(item => {
                // Look for count in parentheses
                const countMatch = item.textContent.match(/\((\d+)\)/);
                if (countMatch) {
                    const count = parseInt(countMatch[1], 10);
                    
                    // Hide items with zero count
                    if (count <= 0) {
                        const parentItem = item.closest('li, .wc-block-components-checkbox, .wc-block-components-radio-control__option');
                        if (parentItem) {
                            parentItem.style.display = 'none';
                        }
                    }
                }
            });
            
            // Also handle layered navigation widgets
            const layeredNavItems = document.querySelectorAll('.woocommerce-widget-layered-nav-list li');
            layeredNavItems.forEach(item => {
                const countElement = item.querySelector('.count');
                if (countElement) {
                    const countText = countElement.textContent.replace(/[()]/g, '');
                    const count = parseInt(countText, 10);
                    
                    if (count <= 0) {
                        item.style.display = 'none';
                    }
                }
            });
        };
        
        // Run immediately
        hideEmptyFilterOptions();
        
        // Run after AJAX updates
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length > 0) {
                    hideEmptyFilterOptions();
                }
            });
        });
        
        // Observe filter containers
        const filterContainers = document.querySelectorAll('.wc-block-product-filters, .woocommerce-widget-layered-nav');
        filterContainers.forEach(container => {
            observer.observe(container, {
                childList: true,
                subtree: true
            });
        });
    });
    </script>
    <?php
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

