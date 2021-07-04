<?php
/**
 * The template used for displaying page content in page.php
 *
 * @package GeneratePress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<?php
// Pull header file from theme if it exists.
if (file_exists(get_stylesheet_directory() . '/header.php')) {
    require_once get_stylesheet_directory() . '/header.php';
}
?>

<div id="page-container" class="page-container-ds">
    <div id="main-content" class="main-content-ds single-column" tabindex="-1">
        <div class="ds-content-layout">
            <main class="main-primary">
                <div>
                        <?php
                        $category = get_the_category();
                        ?>
                        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                            <category-label><?php echo isset($category[0]) ? $category[0]->cat_name : ""; ?></category-label>
                            <!-- Page Title-->
                            <?php

                            esc_html(the_title(sprintf('<h1 class="page-title">', ), '</h1>'));

                            print '<div class="entry-content">';

                            the_content();

                            printf('<p class="page-date published">Updated: <time datetime="%1$s">%1$s</time></p>', get_the_date('F j, Y g:i a'));
                            print '</div>';
                            ?>
                        </article>
                    <span class="return-top hidden-print"></span>
            </main>
        </div>
    </div>
</div>

<?php get_footer(); ?>