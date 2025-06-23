<?php
/**
 * Custom header for child theme: Branding -> Primary Menu -> Search
 * Сохраняет поддержку мобильного меню Storefront
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php do_action( 'storefront_before_site' ); ?>
<div id="page" class="hfeed site">
	<?php do_action( 'storefront_before_header' ); ?>
	<header id="masthead" class="site-header" role="banner" style="<?php storefront_header_styles(); ?>">
		<div
			id="block-advantages"
			class="block-type-list-icons block block__block-content advantages">
			<div class="block__layout">
				<div
					class="block__content block-content__container advantages__container">
					<div
						class="field field-block-paragraph field__entity-reference-revisions field-label__hidden">
						<div class="field-block-paragraph__items field__items">
							<div
								class="field-paragraph-icon-title field-block-paragraph__item field__item">
								<div
									class="paragraph paragraph--type--icon-title paragraph--view-mode--default">
									<div
										class="field field-paragraph-image field__image field-label__hidden">
										<img
											loading="lazy"
											width="24"
											height="25"
											src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/dil/a-1.svg"
											alt="Бесплатная доставка"
											data-width="24"
											data-height="25" />
									</div>
									<div
										class="clearfix text-formatted field field-paragraph-text field__text-long field-label__hidden">
										<p>Доставка в любую точку</p>
									</div>
								</div>
							</div>
							<div
								class="field-paragraph-icon-title field-block-paragraph__item field__item">
								<div
									class="paragraph paragraph--type--icon-title paragraph--view-mode--default">
									<div
										class="field field-paragraph-image field__image field-label__hidden">
										<img
											loading="lazy"
											width="24"
											height="25"
											src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/dil/a-2.svg"
											alt="Быстрая доставка"
											data-width="24"
											data-height="25" />
									</div>
									<div
										class="clearfix text-formatted field field-paragraph-text field__text-long field-label__hidden">
										<p>Оперативная отправка в день оплаты</p>
									</div>
								</div>
							</div>
							<div
								class="field-paragraph-icon-title field-block-paragraph__item field__item">
								<div
									class="paragraph paragraph--type--icon-title paragraph--view-mode--default">
									<div
										class="field field-paragraph-image field__image field-label__hidden">
										<img
											loading="lazy"
											width="24"
											height="25"
											src="<?php echo esc_html(get_stylesheet_directory_uri()); ?>/assets/img/dil/a-3_0.svg"
											alt="Гарантия на обувь"
											data-width="24"
											data-height="25" />
									</div>
									<div
										class="clearfix text-formatted field field-paragraph-text field__text-long field-label__hidden">
										<p>Гарантия на обувь</p>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="advantages__btn js-close-btn"></div>
				</div>
			</div>
		</div>

        <div class="col-full">
			<?php // Branding ?>
			<div class="site-branding">
				<a href=<?php echo esc_url(home_url('/')); ?>>
					<img
											loading="lazy"
											width="176"
											height="43"
											src="<?php echo esc_html(get_stylesheet_directory_uri()); ?>/assets/img/Logo.SVG"
											alt="Гарантия на обувь"
											data-width="176"
											data-height="43" />
				 
				 
			</div>

			<?php // Primary menu + handheld menu для мобильных! ?>
			<nav id="site-navigation" class="main-navigation" role="navigation" aria-label="Primary Navigation">
				<button id="site-navigation-menu-toggle" class="menu-toggle" aria-controls="site-navigation" aria-expanded="false"><span><?php echo esc_html( apply_filters( 'storefront_menu_toggle_text', __( 'Menu', 'storefront' ) ) ); ?></span></button>
				<?php
				wp_nav_menu([
					'theme_location'  => 'primary',
					'container_class' => 'primary-navigation',
				]);
				wp_nav_menu([
					'theme_location'  => 'handheld',
					'container_class' => 'handheld-navigation',
				]);
				?>
			</nav>

            <?php
            // Компактный поиск с иконкой-кнопкой внутри поля, с сохранением функциональности стандартного поиска WooCommerce
            if ( function_exists( 'woocommerce_product_search' ) ) {
                // Используем стандартный виджет WooCommerce поиска товаров, но с кастомным классом для стилизации
                the_widget(
                    'WC_Widget_Product_Search',
                    [],
                    [
                        'before_widget' => '<div class="site-search site-search--compact">',
                        'after_widget'  => '</div>',
                        'before_title'  => '',
                        'after_title'   => '',
                    ]
                );
            } else {
                // Фоллбэк: обычная форма поиска
            ?>
            <form role="search" method="get" class="site-search site-search--compact" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                <label class="screen-reader-text" for="site-search-field"><?php _e( 'Search for:', 'storefront' ); ?></label>
                <input
                    type="search"
                    id="site-search-field"
                    class="search-field"
                    placeholder="<?php echo esc_attr_x( 'Поиск товаров…', 'placeholder', 'storefront' ); ?>"
                    value="<?php echo get_search_query(); ?>"
                    name="s"
                />
                <input type="hidden" name="post_type" value="product" />
                <button type="submit" class="search-submit" aria-label="<?php esc_attr_e( 'Search', 'storefront' ); ?>">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true" focusable="false">
                        <circle cx="9" cy="9" r="7" stroke="#888" stroke-width="2"/>
                        <line x1="14.5" y1="14.5" x2="19" y2="19" stroke="#888" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            </form>
            <?php } ?>
			<?php // Корзина после поиска ?>
			<div class="header-cart-link">
				<a href="/cart/" class="social-link cart-link cart-custom" title="Корзина">
					<span class="fa-cart-icon" aria-hidden="true"></span>
				</a>
			</div>
		</div>
	</header>
	<?php do_action( 'storefront_before_content' ); ?>
	<div id="content" class="site-content" tabindex="-1">
		<div class="col-full">
			<?php do_action( 'storefront_content_top' ); ?>
