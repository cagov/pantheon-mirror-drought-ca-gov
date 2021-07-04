<?php
/*
 * Template Name: ca.gov Page
 * Template Post Type: page
 */
?>

<div id="page-container" class="with-sidebar has-sidebar-left page-container-ds">
    <div id="main-content" class="main-content-ds" tabindex="-1">
        <div class="narrow-page-title">
            <?php esc_html(the_title('<h1 class="page-title">', '</h1>')); ?>
        </div>
        <div class="ds-content-layout">
            <div class="sidebar-container everylayout" style="z-index: 1;">
                <sidebar space="0" side="left">
                    <cagov-content-navigation data-selector="main" data-type="wordpress" data-label="On this page"></cagov-content-navigation>
                </sidebar>
            </div>

            <div class="everylayout">
                <main class="main-primary">
                    <?php
                    while (have_posts()) :
                        the_post();
                    ?>

                        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    
                        <?php 
                            esc_html(the_title('<h1 class="page-title">', '</h1>')); 

                            print '<div class="entry-content">';

                            the_content();

                            print '</div>';

                            ?>

                        </article>

                    <?php endwhile; ?>
                    <span class="return-top hidden-print"></span>

                </main>
            </div>
        </div>
    </div> <!-- #main-content -->

</div>

<?php
    do_action("cagov_content_menu");
?>

<?php get_footer(); ?>