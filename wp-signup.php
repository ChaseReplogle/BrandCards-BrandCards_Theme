<?php
/**
 * The template for displaying 404 pages (not found).
 *
 * @package brandcards
 */
require( dirname(__FILE__) . ‘/wp-load.php’ );
require( ‘wp-blog-header.php’ )

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<section class="error-404 not-found">
				<div class="container row">
					<div class="col span_24">
						<h1 class="centered">Sorry, We can't find this page.</h1>
						<hr>
						<h3 class="centered">“Never ruin an apology with an excuse.”</h3>
						<h4 class="secondary centered">- Unknown</h4>
					</div>
				</div><!-- .page-content -->
			</section><!-- .error-404 -->

		</main><!-- #main -->
	</div><!-- #primary -->

<?php get_footer(); ?>