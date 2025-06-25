<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 * 
 * Custom template with clean structure: sidebar inside main content area to avoid HTML duplication
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

get_header( 'shop' );
?>

<div id="primary" class="content-area">
	<main id="main" class="site-main woocommerce-main-with-sidebar" role="main">
		
		<?php
		/**
		 * Manual breadcrumb output instead of using woocommerce_before_main_content hook
		 * to prevent any theme-specific wrapper duplication
		 */
		if ( function_exists( 'woocommerce_breadcrumb' ) ) {
			woocommerce_breadcrumb();
		}
		?>
		
		<div class="woocommerce-content-wrapper">
			<div class="woocommerce-products-area">
				
				<?php if ( woocommerce_product_loop() ) : ?>
					
					<?php
					/**
					 * Hook: woocommerce_before_shop_loop.
					 *
					 * @hooked woocommerce_output_all_notices - 10
					 * @hooked woocommerce_result_count - 20
					 * @hooked woocommerce_catalog_ordering - 30
					 */
					do_action( 'woocommerce_before_shop_loop' );
					?>

					<?php woocommerce_product_loop_start(); ?>

					<?php if ( wc_get_loop_prop( 'total' ) ) : ?>
						<?php while ( have_posts() ) : ?>
							<?php the_post(); ?>

							<?php
							/**
							 * Hook: woocommerce_shop_loop.
							 */
							do_action( 'woocommerce_shop_loop' );

							wc_get_template_part( 'content', 'product' );
							?>

						<?php endwhile; ?>
					<?php endif; ?>

					<?php woocommerce_product_loop_end(); ?>

					<?php
					/**
					 * Hook: woocommerce_after_shop_loop.
					 *
					 * @hooked woocommerce_pagination - 10
					 */
					do_action( 'woocommerce_after_shop_loop' );
					?>

				<?php else : ?>

					<?php
					/**
					 * Hook: woocommerce_no_products_found.
					 *
					 * @hooked wc_no_products_found - 10
					 */
					do_action( 'woocommerce_no_products_found' );
					?>

				<?php endif; ?>
				
			</div><!-- .woocommerce-products-area -->
			
			<div class="woocommerce-sidebar-area">
				<aside id="secondary" class="widget-area" role="complementary">
					<?php
					/**
					 * Output WooCommerce sidebar content
					 * Note: Default sidebar hook is removed in functions.php to prevent duplication
					 */
					if ( function_exists( 'woocommerce_get_sidebar' ) ) {
						woocommerce_get_sidebar();
					}
					?>
				</aside><!-- #secondary -->
			</div><!-- .woocommerce-sidebar-area -->
			
		</div><!-- .woocommerce-content-wrapper -->
		
		<?php
		/**
		 * No woocommerce_after_main_content hook to prevent any theme-specific wrapper duplication
		 * All necessary content is handled within our custom template structure
		 */
		?>
		
	</main><!-- #main -->
</div><!-- #primary -->

<?php get_footer( 'shop' );
