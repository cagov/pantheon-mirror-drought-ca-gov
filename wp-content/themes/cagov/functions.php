<?php

/**
 * cagov theme
 *
 * @package ca-design-system
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Set our theme version.
define('CAGOV_THEME_VERSION', '1.0.0');

add_action('wp_enqueue_style', 'cagov_enqueue_styles');
// add_action('wp_head', 'cagov_header_scripts');
add_action('cagov_breadcrumb', 'cagov_breadcrumb');
add_action('cagov_content_menu', 'cagov_content_menu');
add_action('cagov_social_media_menu', 'cagov_social_media_menu');
add_action('cagov_statewide_footer_menu', 'cagov_statewide_footer_menu');

function cagov_enqueue_scripts()
{
    // $critical_css = file_get_contents( get_stylesheet_directory_uri() . 'page.css');
    // echo '<style>' . $critical_css . '</style>';
}

/**
 * CADesignSystem Breadcrumb
 *
 * @todo update function to render web component if we build it OR export compiled breadcrumb markup or JSON to WP API
 *
 * @return HTML
 */
function cagov_breadcrumb()
{
    /* Quick breadcrumb function */

    global $post;

    $separator = "<span class=\"crumb separator\">/</span>";
    $linkOff = true;

    $items = wp_get_nav_menu_items('header-menu');

    if ($items !== "undefined" && isset($items)) {
        if (is_array($items) || is_object($items)) {
            _wp_menu_item_classes_by_context($items); // Set up the class variables, including current-classes

            $crumbs = array(
                "<a class=\"crumb\" href=\"https:\/\/ca.gov\" title=\"CA.GOV\">CA.GOV</a>",
                "<a class=\"crumb\" href=\"/\" title=\"" . get_bloginfo('name') . "\">" . get_bloginfo('name') . "</a>"
            );

            foreach ($items as $item) {
                if ($item->current_item_ancestor) {
                    if ($linkOff == true) {
                        $crumbs[] = "<span class=\"crumb\">{$item->title}</span>";
                    } else {
                        $crumbs[] = "<a class=\"crumb\" href=\"{$item->url}\" title=\"{$item->title}\">{$item->title}</a>";
                    }
                } else if ($item->current) {
                    $crumbs[] = "<span class=\"crumb current\">{$item->title}</span>";
                }
            }

            if (is_category()) {
                global $wp_query;
                $category = get_category(get_query_var('cat'), false);
                $crumbs[] = "<span class=\"crumb current\">{$category->name}</span>";
            }

            // Configuration note: requires that a menu item link to a category page.
            if (count($crumbs) == 2 && !is_category()) {
                $category = get_the_category($post->ID);

                // Get category menu item from original menu item
                $category_menu_item_found = false;

                foreach ($items as $category_item) {
                    if (isset($category_item->type_label) && $category_item->type_label === "Category") { // or ->type == "taxonomy"
                        if (isset($category[0]->name) && $category[0]->name == $category_item->title) {
                            $crumbs[] = "<span class=\"crumb current\">" . $category_item->title . "</span>";
                            $category_menu_item_found = true;
                        }
                    }
                }

                // If not found, just use the category name
                if (isset($category[0]) && $category[0] && $category_menu_item_found == false) {
                    $crumbs[] = "<span class=\"crumb current\">" . $category[0]->name . "</span>";
                }
            }

            echo '<div class="breadcrumb" aria-label="Breadcrumb" role="region">' . implode($separator, $crumbs) . '</div>';
        }
    }
}

function cagov_header_scripts()
{
    /* Register cagov scripts */
    wp_register_script('twitter-timeline', 'https://platform.twitter.com/widgets.js', array(), CA_DESIGN_SYSTEM_GUTENBERG_BLOCKS__VERSION, false);

    wp_enqueue_script('twitter-timeline');
}

/**
 * CADesignSystem Pre Main Primary
 *
 * @category add_action( 'caweb_pre_main_primary', 'cagov_pre_main_primary');
 * @return HTML
 */
function cagov_pre_main_primary()
{
    global $post;

    $cagov_content_menu_sidebar = get_post_meta($post->ID, '_cagov_content_menu_sidebar', true);

    // Dont render cagov-content-navigation sidebar on front page, post,
    // or if content navigation sidebar not enabled.
    // @TODO This logic needs to be recorded, documented for headless unless we do a simpler method of just doing templates & not adding extra logic that needs to be maintained.

    if ('on' !== $cagov_content_menu_sidebar || is_front_page() || is_single()) {
        return;
    }
?>
    <div class="sidebar-container" style="z-index: 1;">
        <sidebar space="0" side="left">
            <cagov-content-navigation data-selector="main" data-type="wordpress" data-label="On this page"></cagov-content-navigation>
        </sidebar>
    </div>
<?php
}

function cagov_statewide_footer_menu() {
    $nav_links = '';

    /* loop thru and create a link (parent nav item only) */
    $nav_menus = get_nav_menu_locations();

    if (!isset($nav_menus['statewide-footer-menu'])) {
        return;
    }
?>

    <div class="statewide-footer-container">
        <div class="statewide-footer">
            <div class="menu-section">
                <ul class="statewide-footer-menu-links">
                    <?php
                    $menuitems = wp_get_nav_menu_items($nav_menus['statewide-footer-menu']);

                    foreach ($menuitems as $item) {
                        if (!$item->menu_item_parent) {
                            $class  = !empty($item->classes) ? implode(' ', $item->classes) : '';
                            $rel    = !empty($item->xfn) ? $item->xfn : '';
                            $target = !empty($item->target) ? $item->target : '_blank';
                    ?>
                            <li class="<?php echo esc_attr($class); ?>" title="<?php echo esc_attr($item->attr_title); ?>" rel="<?php echo esc_attr($rel); ?>">
                                <a href="<?php echo esc_url($item->url); ?>" target="<?php echo esc_attr($target); ?>"><?php echo esc_attr($item->title); ?></a>
                            </li>
                    <?php
                        }
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>
<?php
}

/**
 * CADesignSystem Content Menu
 *
 * @category add_action( 'caweb_pre_footer', 'cagov_content_menu' );
 * @return HTML
 */
function cagov_content_menu()
{
    $nav_links = '';

    /* loop thru and create a link (parent nav item only) */
    $nav_menus = get_nav_menu_locations();

    if (!isset($nav_menus['content-menu'])) {
        return;
    }
?>
    <div class="per-page-feedback-container">
        <cagov-feedback data-endpoint-url="https://fa-go-feedback-001.azurewebsites.net/sendfeedback"></cagov-feedback>
    </div>
    <div class="content-footer-container">
        <div class="content-footer">
            <div class="menu-section">
                <div class="logo-small">
                </div>
            </div>
            <div class="menu-section">
                <ul class="content-menu-links">
                    <?php
                    $menuitems = wp_get_nav_menu_items($nav_menus['content-menu']);

                    foreach ($menuitems as $item) {
                        if (!$item->menu_item_parent) {
                            $class  = !empty($item->classes) ? implode(' ', $item->classes) : '';
                            $rel    = !empty($item->xfn) ? $item->xfn : '';
                            $target = !empty($item->target) ? $item->target : '_blank';
                    ?>
                            <li class="<?php echo esc_attr($class); ?>" title="<?php echo esc_attr($item->attr_title); ?>" rel="<?php echo esc_attr($rel); ?>">
                                <a href="<?php echo esc_url($item->url); ?>" target="<?php echo esc_attr($target); ?>"><?php echo esc_attr($item->title); ?></a>
                            </li>
                    <?php
                        }
                    }
                    ?>
                </ul>
            </div>
            <div class="menu-section menu-section-social">
                <?php
                cagov_content_social_menu();
                ?>
            </div>
        </div>
    </div>
<?php
}

/**
 * CADesignSystem Content Social Menu
 *
 * @return HTML
 */
function cagov_content_social_menu()
{


    /* loop thru and create a link (parent nav item only) */
    $nav_menus = get_nav_menu_locations();

    if (!isset($nav_menus['social-media-links'])) {
        return;
    }

?>
    <ul class="social-links-container">

        <?php
        $menuitems = wp_get_nav_menu_items($nav_menus['social-media-links']);

        foreach ($menuitems as $item) {
            if (!$item->menu_item_parent) {
                $class  = !empty($item->classes) ? implode(' ', $item->classes) : '';
                $rel    = !empty($item->xfn) ? $item->xfn : '';
                $target = !empty($item->target) ? $item->target : '_blank';
        ?>
                <li class="<?php echo esc_attr($class); ?>" title="<?php echo esc_attr($item->attr_title); ?>" rel="<?php echo esc_attr($rel); ?>">
                    <a href="<?php echo esc_url($item->url); ?>" target="<?php echo esc_attr($target); ?>"><?php echo esc_attr($item->title); ?></a>
                </li>
        <?php
            }
        }
        ?>
    </ul>



<?php
}
