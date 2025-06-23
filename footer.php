		</div><!-- .col-full -->
	</div><!-- #content -->

	<?php do_action( 'storefront_before_footer' ); ?>

	<footer id="colophon" class="site-footer footer-section" role="contentinfo">
	<div class="footer__container">
		<div class="footer-logo">
			<div class="logo noise footer-img">
				<a href="<?php echo esc_url(home_url('/')); ?>">
					<img
											loading="lazy"
											width="298"
											height="54"
											src="<?php echo esc_html(get_stylesheet_directory_uri()); ?>/assets/img/Logo.SVG"
											alt="Гарантия на обувь"
											data-width="298"
											data-height="54" />
				</a>
			</div>
			<div class="footer-img-bank">
				<a href="<?php echo esc_url(home_url('/')); ?>">
					<p class="logo__icon-bank"><img
											loading="lazy"
											width="64"
											height="48"
											src="<?php echo esc_html(get_stylesheet_directory_uri()); ?>/assets/img/Kaspi.kz.svg"
											alt="kaspi"
											data-width="96"
											data-height="48" />
				</a>
				<a href="<?php echo esc_url(home_url('/')); ?>">
					<p class="logo__icon-bank"><img
											loading="lazy"
											width="64"
											height="48"
											src="<?php echo esc_html(get_stylesheet_directory_uri()); ?>/assets/img/Halyk-Bank.svg"
											alt="halyk"
											data-width="96"
											data-height="48" />
				</a>
				<a href="<?php echo esc_url(home_url('/')); ?>">
					<p class="logo__icon-bank"><img
											loading="lazy"
											width="48"
											height="48"
											src="<?php echo esc_html(get_stylesheet_directory_uri()); ?>/assets/img/visa.svg"
											alt="halyk"
											data-width="48"
											data-height="48" />
				</a>
			</div>
		</div>
		<div class="footer-cta">

			<!-- Адрес (без ссылки) -->
			<div class="single-cta">
				<i class="fas fa-map-marker-alt"></i>
				<div class="cta-text">
					<h4>Адрес</h4>
					<span> Назарбаева 1.</span>
					<br>
					<span>А - бутик 23</span>
					<br>
					<span>А  - бутик 8</span>
					<br>
					<span>А - бутик 25</span>
				</div>
			</div>

			<!-- Instagram (кликабельная вся карточка) -->
			<a href="https://www.instagram.com/ваш_аккаунт/" target="_blank" class="single-cta cta-link">
				<i class="fab fa-instagram"></i>
				<div class="cta-text">
					<h4>Instagram</h4>
					<span>@ваш_аккаунт</span>
				</div>
			</a>

			<!-- WhatsApp (кликабельная вся карточка) -->
			<a href="https://wa.me/79876543210" target="_blank" class="single-cta cta-link">
				<i class="fab fa-whatsapp"></i>
				<div class="cta-text">
					<h4>WhatsApp</h4>
					<span>+7 (987) 654-32-10</span>
				</div>
			</a>


		</div>
	</div>
	<div class="copyright-area">
		<div class="copyright-area__container">

			<div class="copyright-text">
				<p>&copy; 2025, <b>BOTTIGO</b> Интернет-магазин женской обуви</p>
			</div>


		</div>
	</div>
	<?php if ( function_exists('storefront_handheld_footer_bar') ) storefront_handheld_footer_bar(); ?>
	</footer><!-- #colophon -->

	<?php do_action( 'storefront_after_footer' ); ?>

</div><!-- #page -->

<?php wp_footer(); ?>
