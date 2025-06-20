<?php
/**
 * The main template file for Woo Custom Child theme
 *
 * @package Woo_Custom_Child
 */

global $wp_query;

get_header();
?>

<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<?php
		if ( have_posts() ) :
			while ( have_posts() ) : the_post();
				get_template_part( 'content', get_post_format() );
			endwhile;
			// Pagination
			the_posts_navigation();
		else :
			get_template_part( 'content', 'none' );
		endif;
		?>
	</main><!-- #main -->
</div><!-- #primary -->

<?php get_footer(); ?>
