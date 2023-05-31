<?php

/**
 * WP Media Category Management Shortcode class
 * 
 * @since  2.0.0
 * @author DeBAAT
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( !class_exists( 'WP_MCM_Shortcode' ) ) {
    class WP_MCM_Shortcode
    {
        /**
         * Parameters for handling the settable options for this plugin.
         *
         * @var mixed[] $options
         */
        public  $mcm_shortcodes = array() ;
        /**
         * Class constructor
         */
        function __construct()
        {
            $this->includes();
            $this->initialize();
            $this->add_hooks_and_filters();
        }
        
        /**
         * Include the required files.
         *
         * @since 2.0.0
         * @return void
         */
        public function includes()
        {
        }
        
        /**
         * Init the required classes.
         *
         * @since 2.0.0
         * @return void
         */
        public function initialize()
        {
            $this->debugMP( 'msg', __FUNCTION__ . ' started.' );
            // Define the wp_mcm_shortcodes, including version independent of upper or lower case
            $wp_mcm_shortcodes = $this->get_wp_mcm_shortcodes();
            foreach ( $wp_mcm_shortcodes as $shortcode ) {
                $shortcode_lc = strtolower( $shortcode['label'] );
                $shortcode_uc = strtoupper( $shortcode['label'] );
                add_shortcode( $shortcode['label'], array( $this, $shortcode['function'] ) );
                add_shortcode( $shortcode_lc, array( $this, $shortcode['function'] ) );
                add_shortcode( $shortcode_uc, array( $this, $shortcode['function'] ) );
            }
        }
        
        /**
         * Get all shortcodes defined for WP Media Category Management
         *
         * @return $shortcodes[]
         */
        function get_wp_mcm_shortcodes()
        {
            $this->debugMP( 'msg', __FUNCTION__ );
            global  $wp_mcm_options ;
            // Get the value for wp_mcm_default_media_category to show in the explanation
            $wp_mcm_media_taxonomy_to_use_value = $wp_mcm_options->get_value( 'wp_mcm_media_taxonomy_to_use' );
            $wp_mcm_default_media_category_text = __( 'No default category', 'wp-media-category-management' );
            $wp_mcm_default_media_category_value = $wp_mcm_options->get_value( 'wp_mcm_default_media_category' );
            if ( $wp_mcm_default_media_category_value == WP_MCM_OPTION_NONE ) {
                $wp_mcm_default_media_category_value = $wp_mcm_default_media_category_text;
            }
            $link_to_template = '<code>%s</code>: <strong>%s</strong> - ';
            // Generate the parameters for the shortcodes
            $mcm_shortcodes_parameters = array();
            $mcm_shortcodes_parameters['WP_MCM'] = array();
            $mcm_shortcodes_parameters['WP-MCM'] = array();
            $mcm_shortcodes_parameters['WP_MCM'][] = sprintf( '<strong>taxonomy</strong>="&lt;%s&gt;"', __( 'slug', 'wp-media-category-management' ) ) . '<br/>' . __( 'The <code>slug</code> of the taxonomy to be used to filter the media to show.', 'wp-media-category-management' ) . '<br/>' . sprintf( __( 'The default value is as defined in MCM Settings for <strong>Media Taxonomy To Use</strong>, currently defined as <code>%s</code>.', 'wp-media-category-management' ), $wp_mcm_media_taxonomy_to_use_value );
            $mcm_shortcodes_parameters['WP_MCM'][] = sprintf( '<strong>category</strong>="&lt;%s&gt;"', __( 'slugs', 'wp-media-category-management' ) ) . '<br/>' . __( 'A comma separated list of category <code>slugs</code> to be used to filter the media to show.', 'wp-media-category-management' ) . '<br/>' . sprintf( __( 'The default value is as defined in MCM Settings for <strong>Default Media Category</strong>, currently defined as <code>%s</code>.', 'wp-media-category-management' ), $wp_mcm_default_media_category_value ) . '<br/>' . sprintf( __( 'When the default value is <code>%s</code>, only the media are shown that have no category assigned.', 'wp-media-category-management' ), $wp_mcm_default_media_category_text );
            $mcm_shortcodes_parameters['WP_MCM'][] = sprintf( '<strong>show_category_link</strong>="&lt;%s&gt;"', __( 'string', 'wp-media-category-management' ) ) . '<br/>' . __( 'A string that can be used to indicate that all category links assigned to the attachment need to be shown below the image of the attachment.', 'wp-media-category-management' ) . '<br/>' . __( 'Any non-empty value indicates that the category links must be shown.', 'wp-media-category-management' ) . ' ' . __( 'A single space indicates that each category link is shown and followed by a space.', 'wp-media-category-management' ) . ' ' . __( 'The string "br" indicates that each category link is shown and followed by a new line.', 'wp-media-category-management' ) . '<br/>' . __( 'The default value is an empty string (<strong>""</strong>) indicating that the category links should not be shown.', 'wp-media-category-management' );
            $mcm_shortcodes_parameters['WP_MCM'][] = sprintf( '<strong>alternative_shortcode</strong>="&lt;%s&gt;"', __( 'alternative', 'wp-media-category-management' ) ) . '<br/>' . __( 'This parameter can be used to overrule the default <code>gallery</code> shortcode as used by WordPress and most plugins.', 'wp-media-category-management' ) . '<br/>' . sprintf( __( 'The default value is %s.', 'wp-media-category-management' ), '<code>gallery</code>' );
            $mcm_shortcodes_parameters['WP-MCM'][] = __( 'See the parameters of the <code>WP_MCM</code> shortcode above.', 'wp-media-category-management' );
            // Generate the list of shortcodes supported
            $this->mcm_shortcodes = array();
            $this->mcm_shortcodes['WP_MCM'] = array(
                'label'       => 'WP_MCM',
                'description' => __( 'The basic shortcode to show a gallery of all media for the taxonomy and categories specified in the parameters.', 'wp-media-category-management' ),
                'class'       => $this,
                'parameters'  => $this->mcm_create_parameter_list( $mcm_shortcodes_parameters['WP_MCM'] ),
                'function'    => 'wp_mcm_do_shortcode',
            );
            $this->mcm_shortcodes['WP-MCM'] = array(
                'label'       => 'WP-MCM',
                'description' => sprintf( __( 'A shortcode with the same functionality as %s listed above.', 'wp-media-category-management' ), '<code>WP_MCM</code>' ),
                'class'       => $this,
                'parameters'  => sprintf( __( 'See the parameters of the %s shortcode above.', 'wp-media-category-management' ), '<code>WP_MCM</code>' ),
                'function'    => 'wp_mcm_do_shortcode',
            );
            return $this->mcm_shortcodes;
        }
        
        /**
         * Add the hooks and filters.
         *
         * @since 2.0.0
         * @return void
         */
        public function add_hooks_and_filters()
        {
            $this->debugMP( 'msg', __FUNCTION__ . ' started.' );
            add_filter( 'wp_print_styles', array( $this, 'wp_mcm_wp_print_styles' ) );
            $this->debugMP( 'msg', __FUNCTION__ . ' add_filter for several functions.' );
        }
        
        /**
         * Create the list of parameters to show.
         *
         * @since 2.0.0
         * @return void
         */
        public function mcm_create_parameter_list( $parameters = array() )
        {
            $this->debugMP( 'msg', __FUNCTION__ . ' started.' );
            // Generate only a list when there are parameters to list
            $parameter_output = '';
            
            if ( is_array( $parameters ) && count( $parameters ) > 0 ) {
                $this->debugMP( 'msg', __FUNCTION__ . ' started for ' . count( $parameters ) . ' parameters' );
                $parameter_output .= '<ul>';
                foreach ( $parameters as $parameter_text ) {
                    $parameter_output .= '<li>';
                    $parameter_output .= $parameter_text;
                    $parameter_output .= '</li>';
                }
                $parameter_output .= '</ul>';
            }
            
            return $parameter_output;
        }
        
        /**
         * Initialize all our jquery goodness.
         *
         */
        public static function wp_mcm_wp_print_styles()
        {
            // $this->debugMP('msg',__FUNCTION__.' started.');
        }
        
        /**
         * Handles the WP_MCM shortcode
         *
         * @param  $attr
         * @return string
         */
        function wp_mcm_do_shortcode( $attr, $content )
        {
            global  $wp_mcm_plugin ;
            global  $wp_mcm_taxonomy ;
            $this->debugMP( 'msg', __FUNCTION__ . ' started!' );
            $mcm_shortcode_output = '';
            // Get the shortcode_attributes
            $mcm_specific_atts = array(
                'taxonomy'              => '',
                'category'              => '',
                'show_category_link'    => '',
                'alternative_shortcode' => 'gallery',
            );
            // Get the shortcode_attributes
            $mcm_atts = shortcode_atts( $mcm_specific_atts, $attr );
            // Check attribute mcm_show_category_link
            if ( is_object( $wp_mcm_plugin ) ) {
                $wp_mcm_plugin->mcm_shortcode_attributes = $mcm_atts;
            }
            $this->debugMP( 'pr', __FUNCTION__ . ' attributes', $attr );
            $this->debugMP( 'pr', __FUNCTION__ . ' mcm attributes', $mcm_atts );
            $this->debugMP( 'msg', __FUNCTION__ . ' content', esc_html( $content ) );
            // Get the id's to include in the gallery to show
            $mcm_gallery_ids = $wp_mcm_taxonomy->mcm_get_attachment_ids( $mcm_atts );
            // Check whether mcm_gallery_ids are found
            
            if ( $mcm_gallery_ids === '' ) {
                $mcm_shortcode_output = '';
                $mcm_shortcode_output .= '<div>';
                $mcm_shortcode_output .= __( 'Nothing to show!', 'wp-media-category-management' );
                $mcm_shortcode_output .= '</div>';
                $this->debugMP( 'msg', __FUNCTION__ . ' Nothing to show! ' );
                return $mcm_shortcode_output;
            }
            
            // Check and use the alternative_shortcode
            $mcm_alternative_shortcode = '';
            
            if ( isset( $mcm_atts['alternative_shortcode'] ) && $mcm_atts['alternative_shortcode'] != '' ) {
                $mcm_alternative_shortcode = $mcm_atts['alternative_shortcode'];
                unset( $mcm_atts['alternative_shortcode'] );
            }
            
            $this->debugMP( 'pr', __FUNCTION__ . ' mcm_alternative_shortcode = ' . $mcm_alternative_shortcode . ', mcm_atts:', $mcm_atts );
            // Check and use the link_attribute
            
            if ( !isset( $attr['link'] ) || $attr['link'] == '' ) {
                $attr['link'] = WP_MCM_LINK_DESTINATION_FILE;
                $this->debugMP( 'msg', __FUNCTION__ . ' RESET attr[link] to ' . $attr['link'] );
            }
            
            // Use original attr to prepare gallery atts
            $mcm_gallery_atts = 'include="' . $mcm_gallery_ids . '"';
            if ( is_array( $attr ) ) {
                foreach ( $attr as $key => $value ) {
                    // // Do not include WP-MCM specific shortcode attributes
                    // if (! isset($mcm_specific_atts[$key])) {
                    $mcm_gallery_atts .= ' ' . $key . '="' . $value . '"';
                    // } else {
                    // $this->debugMP('msg',__FUNCTION__ . ' Do not include WP-MCM specific shortcode attribute ' . $key . '="' . $value . '"');
                    // }
                }
            }
            // Do the shortcode translation
            $mcm_gallery_shortcode = '[' . $mcm_alternative_shortcode . ' ' . $mcm_gallery_atts . ']';
            $this->debugMP( 'pr', __FUNCTION__ . ' mcm_gallery_shortcode= ' . $mcm_gallery_shortcode . ', attr:', $attr );
            $mcm_shortcode_output = do_shortcode( $mcm_gallery_shortcode );
            // Reset shortcode attribute
            if ( is_object( $wp_mcm_plugin ) ) {
                $wp_mcm_plugin->mcm_shortcode_attributes = '';
            }
            return $mcm_shortcode_output;
        }
        
        /**
         * Builds the Gallery shortcode output.
         *
         * This implements the functionality of the Gallery Shortcode for displaying
         * WordPress images on a post.
         *
         * @since 2.0.0 Based on the basic WP Gallery.
         *
         * @param array $attr {
         *     Attributes of the gallery shortcode.
         *
         *     @type string       $order      Order of the images in the gallery. Default 'ASC'. Accepts 'ASC', 'DESC'.
         *     @type string       $orderby    The field to use when ordering the images. Default 'menu_order ID'.
         *                                    Accepts any valid SQL ORDERBY statement.
         *     @type int          $id         Post ID.
         *     @type string       $itemtag    HTML tag to use for each image in the gallery.
         *                                    Default 'dl', or 'figure' when the theme registers HTML5 gallery support.
         *     @type string       $icontag    HTML tag to use for each image's icon.
         *                                    Default 'dt', or 'div' when the theme registers HTML5 gallery support.
         *     @type string       $captiontag HTML tag to use for each image's caption.
         *                                    Default 'dd', or 'figcaption' when the theme registers HTML5 gallery support.
         *     @type int          $columns    Number of columns of images to display. Default 3.
         *     @type string|int[] $size       Size of the images to display. Accepts any registered image size name, or an array
         *                                    of width and height values in pixels (in that order). Default 'thumbnail'.
         *     @type string       $ids        A comma-separated list of IDs of attachments to display. Default empty.
         *     @type string       $include    A comma-separated list of IDs of attachments to include. Default empty.
         *     @type string       $exclude    A comma-separated list of IDs of attachments to exclude. Default empty.
         *     @type string       $link       What to link each image to. Default empty (links to the attachment page).
         *                                    Accepts 'file', 'none'.
         * }
         * @return string HTML content to display gallery.
         */
        function wp_mcm_gallery_shortcode( $attr )
        {
            $post = get_post();
            $this->debugMP( 'pr', __FUNCTION__ . ' started with attr = ', $attr );
            static  $instance = 0 ;
            $instance++;
            
            if ( !empty($attr['ids']) ) {
                // 'ids' is explicitly ordered, unless you specify otherwise.
                if ( empty($attr['orderby']) ) {
                    $attr['orderby'] = 'post__in';
                }
                $attr['include'] = $attr['ids'];
            }
            
            /**
             * Filters the default gallery shortcode output.
             *
             * If the filtered output isn't empty, it will be used instead of generating
             * the default gallery template.
             *
             * @since 2.0.0 The `$instance` parameter was added.
             *
             * @see gallery_shortcode()
             *
             * @param string $output   The gallery output. Default empty.
             * @param array  $attr     Attributes of the gallery shortcode.
             * @param int    $instance Unique numeric ID of this gallery shortcode instance.
             */
            $output = apply_filters(
                'post_gallery',
                '',
                $attr,
                $instance
            );
            if ( !empty($output) ) {
                return $output;
            }
            $html5 = current_theme_supports( 'html5', 'gallery' );
            $atts = shortcode_atts( array(
                'order'      => 'ASC',
                'orderby'    => 'menu_order ID',
                'id'         => ( $post ? $post->ID : 0 ),
                'itemtag'    => ( $html5 ? 'figure' : 'dl' ),
                'icontag'    => ( $html5 ? 'div' : 'dt' ),
                'captiontag' => ( $html5 ? 'figcaption' : 'dd' ),
                'columns'    => 3,
                'size'       => 'thumbnail',
                'include'    => '',
                'exclude'    => '',
                'link'       => '',
                'link_to'    => '',
            ), $attr, 'gallery' );
            $id = (int) $atts['id'];
            
            if ( !empty($atts['include']) ) {
                $_attachments = get_posts( array(
                    'include'     => $atts['include'],
                    'post_status' => 'inherit',
                    'post_type'   => 'attachment',
                    'order'       => $atts['order'],
                    'orderby'     => $atts['orderby'],
                ) );
                $attachments = array();
                foreach ( $_attachments as $key => $val ) {
                    $attachments[$val->ID] = $_attachments[$key];
                }
            } elseif ( !empty($atts['exclude']) ) {
                $attachments = get_children( array(
                    'post_parent' => $id,
                    'exclude'     => $atts['exclude'],
                    'post_status' => 'inherit',
                    'post_type'   => 'attachment',
                    'order'       => $atts['order'],
                    'orderby'     => $atts['orderby'],
                ) );
            } else {
                $attachments = get_children( array(
                    'post_parent' => $id,
                    'post_status' => 'inherit',
                    'post_type'   => 'attachment',
                    'order'       => $atts['order'],
                    'orderby'     => $atts['orderby'],
                ) );
            }
            
            
            if ( empty($attachments) ) {
                $this->debugMP( 'msg', __FUNCTION__ . ' found NO attachments!' );
                return '';
            }
            
            
            if ( is_feed() ) {
                $output = "\n";
                foreach ( $attachments as $att_id => $attachment ) {
                    
                    if ( !empty($atts['link_to']) ) {
                        
                        if ( 'none' === $atts['link_to'] ) {
                            $output .= wp_get_attachment_image(
                                $att_id,
                                $atts['size'],
                                false,
                                $attr
                            );
                        } else {
                            $output .= wp_get_attachment_link( $att_id, $atts['size'], false );
                        }
                    
                    } else {
                        $output .= wp_get_attachment_link( $att_id, $atts['size'], true );
                    }
                    
                    $output .= "\n";
                }
                return $output;
            }
            
            $itemtag = tag_escape( $atts['itemtag'] );
            $captiontag = tag_escape( $atts['captiontag'] );
            $icontag = tag_escape( $atts['icontag'] );
            $valid_tags = wp_kses_allowed_html( 'post' );
            if ( !isset( $valid_tags[$itemtag] ) ) {
                $itemtag = 'dl';
            }
            if ( !isset( $valid_tags[$captiontag] ) ) {
                $captiontag = 'dd';
            }
            if ( !isset( $valid_tags[$icontag] ) ) {
                $icontag = 'dt';
            }
            $columns = (int) $atts['columns'];
            $itemwidth = ( $columns > 0 ? floor( 100 / $columns ) : 100 );
            $float = ( is_rtl() ? 'right' : 'left' );
            $selector = "gallery-{$instance}";
            $gallery_style = '';
            /**
             * Filters whether to print default gallery styles.
             *
             * @since 2.0.0
             *
             * @param bool $print Whether to print default gallery styles.
             *                    Defaults to false if the theme supports HTML5 galleries.
             *                    Otherwise, defaults to true.
             */
            
            if ( apply_filters( 'use_default_gallery_style', !$html5 ) ) {
                $type_attr = ( current_theme_supports( 'html5', 'style' ) ? '' : ' type="text/css"' );
                $gallery_style = "\r\n\t\t\t\t<style{$type_attr}>\r\n\t\t\t\t\t#{$selector} {\r\n\t\t\t\t\t\tmargin: auto;\r\n\t\t\t\t\t}\r\n\t\t\t\t\t#{$selector} .gallery-item {\r\n\t\t\t\t\t\tfloat: {$float};\r\n\t\t\t\t\t\tmargin-top: 10px;\r\n\t\t\t\t\t\ttext-align: center;\r\n\t\t\t\t\t\twidth: {$itemwidth}%;\r\n\t\t\t\t\t}\r\n\t\t\t\t\t#{$selector} img {\r\n\t\t\t\t\t\tborder: 2px solid #cfcfcf;\r\n\t\t\t\t\t}\r\n\t\t\t\t\t#{$selector} .gallery-caption {\r\n\t\t\t\t\t\tmargin-left: 0;\r\n\t\t\t\t\t}\r\n\t\t\t\t\t/* see gallery_shortcode() in wp-includes/media.php */\r\n\t\t\t\t</style>\n\t\t";
            }
            
            $size_class = sanitize_html_class( ( is_array( $atts['size'] ) ? implode( 'x', $atts['size'] ) : $atts['size'] ) );
            $gallery_div = "<div id='{$selector}' class='gallery galleryid-{$id} gallery-columns-{$columns} gallery-size-{$size_class}'>";
            /**
             * Filters the default gallery shortcode CSS styles.
             *
             * @since 2.0.0
             *
             * @param string $gallery_style Default CSS styles and opening HTML div container
             *                              for the gallery shortcode output.
             */
            $output = apply_filters( 'gallery_style', $gallery_style . $gallery_div );
            $i = 0;
            $this->debugMP( 'pr', __FUNCTION__ . ' id= ' . $id . ', atts:', $atts );
            $this->debugMP( 'pr', __FUNCTION__ . ' id= ' . $id . ', attr:', $attr );
            foreach ( $attachments as $attachment_id => $attachment ) {
                $this->debugMP( 'pr', __FUNCTION__ . ' attachment_id= ' . $attachment_id . ', attachment:', $attachment );
                $attr = ( trim( $attachment->post_excerpt ) ? array(
                    'aria-describedby' => "{$selector}-{$attachment_id}",
                ) : '' );
                $output_image = wp_get_attachment_image(
                    $attachment_id,
                    $atts['size'],
                    true,
                    $attr
                );
                $output_image = str_replace( 'width:100%;', '', $output_image );
                $image_output = $output_image;
                // Handle non-images differently
                
                if ( wp_attachment_is_image( $attachment_id ) ) {
                    if ( !empty($atts['link_to']) ) {
                        switch ( $atts['link_to'] ) {
                            case WP_MCM_LINK_DESTINATION_ATTACHMENT:
                                $image_output = wp_get_attachment_link(
                                    $attachment_id,
                                    $atts['size'],
                                    true,
                                    false,
                                    false,
                                    $attr
                                );
                                break;
                            case WP_MCM_LINK_DESTINATION_MEDIA:
                                $image_output = wp_get_attachment_link(
                                    $attachment_id,
                                    $atts['size'],
                                    false,
                                    false,
                                    false,
                                    $attr
                                );
                                break;
                            case WP_MCM_LINK_DESTINATION_NONE:
                            default:
                                break;
                        }
                    }
                } else {
                    // Generate the image output depending on the attributes set
                    if ( !empty($atts['link_to']) ) {
                        switch ( $atts['link_to'] ) {
                            case WP_MCM_LINK_DESTINATION_ATTACHMENT:
                            case WP_MCM_LINK_DESTINATION_MEDIA:
                                $image_link = wp_get_attachment_url( $attachment_id );
                                $image_output = '<a href="' . $image_link . '">' . $output_image . '</a>';
                                $image_output .= '<br/>';
                                $image_output .= wp_get_attachment_link(
                                    $attachment_id,
                                    $atts['size'],
                                    false,
                                    false,
                                    false,
                                    $attr
                                );
                                $this->debugMP( 'pr', __FUNCTION__ . ' attachment_id= ' . $attachment_id . ', image_link:', $image_link );
                                $this->debugMP( 'pr', __FUNCTION__ . ' attachment_id= ' . $attachment_id . ', output_image:', $output_image );
                                break;
                            case WP_MCM_LINK_DESTINATION_NONE:
                            default:
                                $image_output .= '<br/>';
                                $image_output .= $attachment->post_title;
                                break;
                        }
                    }
                }
                
                $image_meta = wp_get_attachment_metadata( $attachment_id );
                $orientation = '';
                if ( isset( $image_meta['height'], $image_meta['width'] ) ) {
                    $orientation = ( $image_meta['height'] > $image_meta['width'] ? 'portrait' : 'landscape' );
                }
                $output .= "<{$itemtag} class='gallery-item' style='padding: 5px;'>";
                $output .= "\r\n\t\t\t\t\t<{$icontag} class='gallery-icon {$orientation}'>\r\n\t\t\t\t\t\t{$image_output}\r\n\t\t\t\t\t</{$icontag}>";
                if ( $captiontag && trim( $attachment->post_excerpt ) ) {
                    $output .= "\r\n\t\t\t\t\t\t<{$captiontag} class='wp-caption-text gallery-caption' id='{$selector}-{$attachment_id}'>\r\n\t\t\t\t\t\t" . wptexturize( $attachment->post_excerpt ) . "\r\n\t\t\t\t\t\t</{$captiontag}>";
                }
                $output .= "</{$itemtag}>";
                if ( !$html5 && $columns > 0 && 0 === ++$i % $columns ) {
                    $output .= '<br style="clear: both" />';
                }
            }
            if ( !$html5 && $columns > 0 && 0 !== $i % $columns ) {
                $output .= "\r\n\t\t\t\t\t<br style='clear: both' />";
            }
            $output .= "\r\n\t\t\t\t</div>\n";
            return $output;
        }
        
        /**
         * Simplify the plugin debugMP interface.
         *
         * Typical start of function call: $this->debugMP('msg',__FUNCTION__);
         *
         * @param string $type
         * @param string $hdr
         * @param string $msg
         */
        function debugMP( $type, $hdr, $msg = '' )
        {
            if ( $type === 'msg' && $msg !== '' ) {
                $msg = esc_html( $msg );
            }
            if ( $hdr !== '' ) {
                // Adding __CLASS__ to non-empty hdr
                $hdr = __CLASS__ . '::' . $hdr;
            }
            WP_MCM_debugMP(
                $type,
                $hdr,
                $msg,
                NULL,
                NULL,
                true
            );
        }
    
    }
}