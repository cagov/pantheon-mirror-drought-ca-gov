<?php
/**
 * The template for displaying the footer.
 *
 * @package GeneratePress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<?php
/**
 * generate_before_footer hook.
 *
 * @since 0.1
 */
do_action( 'generate_before_footer' );
?>



<?php do_action( 'cagov_content_menu' ); ?>

<div>
	<?php do_action( 'cagov_statewide_footer_menu' ); ?>
</div>

<?php
wp_footer();
?>

</body>
</html>
