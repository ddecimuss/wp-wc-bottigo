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
		<div class="col-full">
			<?php // Branding ?>
			<div class="site-branding">
				<?php storefront_site_title_or_logo(); ?>
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

			<?php // Search ?>
			<div class="site-search">
				<?php the_widget( 'WC_Widget_Product_Search', 'title=' ); ?>
			</div>
		</div>
	</header>
	<?php do_action( 'storefront_before_content' ); ?>
	<div id="content" class="site-content" tabindex="-1">
		<div class="col-full">
			<?php do_action( 'storefront_content_top' ); ?>
