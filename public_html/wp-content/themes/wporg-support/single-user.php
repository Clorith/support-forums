<?php get_header(); ?>

<main id="main" class="site-main" role="main">
	<div class="wrapper">
		<div id="bbp-user-<?php bbp_current_user_id(); ?>" class="bbp-single-user">
			<div class="entry-content">

				<?php bbp_get_template_part( 'content', 'single-user' ); ?>

			</div><!-- .entry-content -->
		</div><!-- #bbp-user-<?php bbp_current_user_id(); ?> -->
	</div>
</main>

<?php get_footer(); ?>
