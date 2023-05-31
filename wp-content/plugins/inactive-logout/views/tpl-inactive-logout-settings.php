<?php
/**
 * Template for Settings page.
 *
 * @package inactive-logout
 */
?>

<h1><?php esc_html_e( 'Inactive User Logout Settings', 'inactive-logout' ); ?></h1>

<noscript>
    <div id="message" class="notice notice-warning ina-logout-like-dismiss-wrapper">
        <p>Your browser does not support JavaScript! Enable Javascript in order to use this plugin.</p>
    </div>
</noscript>

<?php if ( ! \Codemanas\InactiveLogout\Helpers::get_option( 'ina_dismiss_like_notice' ) ) { ?>
    <div id="message" class="notice notice-warning ina-logout-like-dismiss-wrapper">
        <p>
			<?php
			printf( esc_html__( 'Please consider giving a %1$s5 star thumbs up%2$s if you found this useful at wordpress.org.', 'inactive-logout' ), '<a href="https://wordpress.org/support/plugin/inactive-logout/reviews/#new-post" target="_blank">', '</a>' );
			?>
            <a href="javascript:void(0);" id="ina-logout-like-dismiss"><?php esc_html_e( 'Already Rated. Don\'t show this message again.', 'inactive-logout' ); ?></a>
        </p>
    </div>
<?php } ?>

<div class="message">
	<?php
	$message = self::get_message();
	if ( isset( $message ) && ! empty( $message ) ) {
		echo $message;
	}
	?>

	<?php
	if ( ! empty( \Codemanas\InactiveLogout\Helpers::get_option( '__ina_saved_options' ) ) ) {
		echo '<div class="updated"><p>' . __( 'Settings saved.', 'inactive-logout' ) . '</p></div>';
		\Codemanas\InactiveLogout\Helpers::update_option( '__ina_saved_options', '' );
	}
	?>
</div>
<?php
$multi_role_enabled = \Codemanas\InactiveLogout\Helpers::get_option( '__ina_enable_timeout_multiusers' );
?>
<h2 class="nav-tab-wrapper">
    <a href="?page=inactive-logout&tab=ina-basic" class="nav-tab <?php echo ( 'ina-basic' === $active_tab ) ? esc_attr( 'nav-tab-active' ) : ''; ?>">
		<?php esc_html_e( 'General Settings', 'inactive-logout' ); ?>
    </a>
    <a href="?page=inactive-logout&tab=ina-advanced" class="nav-tab <?php echo ( 'ina-advanced' === $active_tab ) ? esc_attr( 'nav-tab-active' ) : ''; ?>">
		<?php esc_html_e( 'Role Based Settings', 'inactive-logout' ); ?><?php echo ! empty( $multi_role_enabled ) ? ' <span class="dashicons dashicons-yes-alt" style="color:#008000;"></span>' : ''; ?>
    </a>
	<?php do_action( 'ina_settings_page_tabs_before' ); ?>
    <a href="?page=inactive-logout&tab=ina-support" class="nav-tab <?php echo ( 'ina-support' === $active_tab ) ? esc_attr( 'nav-tab-active' ) : ''; ?>"><?php esc_html_e( 'Support', 'inactive-logout' ); ?></a>
	<?php if ( ! \Codemanas\InactiveLogout\Helpers::is_pro_version_active() ) { ?>
        <a href="http://inactive-logout.com/" target="_blank" class="nav-tab"><?php esc_html_e( 'Go Pro', 'inactive-logout' ); ?> <span style="color:#ffa500;" class="dashicons dashicons-star-filled"></span></a>
	<?php } ?>
	<?php do_action( 'ina_settings_page_tabs_after' ); ?>
</h2>