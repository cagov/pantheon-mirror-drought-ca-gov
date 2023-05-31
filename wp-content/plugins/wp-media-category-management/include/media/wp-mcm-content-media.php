<?php
/**
 * The template for displaying archive pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package WP Media Category Management
 * @since 2.1.0
 */

global $wp_mcm_taxonomy;

get_header();

$description = get_the_archive_description();
?>

	<header class="page-header alignwide">
		<?php the_archive_title( '<h1 class="page-title">', '</h1>' ); ?>
		<?php if ( $description ) : ?>
			<div class="archive-description"><?php echo wp_kses_post( wpautop( $description ) ); ?></div>
		<?php endif; ?>
	</header><!-- .page-header -->

	<div class="page-content default-max-width">

<?php if ( have_posts() && ( is_object( $wp_mcm_taxonomy ) ) ) : ?>

		<?php echo $wp_mcm_taxonomy->wp_mcm_create_content_media__premium_only(); ?>

<?php else : ?>

			<p><?php esc_html_e( 'It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'wp-media-category-management' ); ?></p>
			<?php get_search_form(); ?>

<?php endif; ?>

	</div><!-- .page-content -->

<?php
get_footer();
