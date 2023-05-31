<?php
/**
 * Post permalink merge tag
 *
 * Requirements:
 * - Trigger property of the post type slug with WP_Post object
 *
 * @package notification
 */

namespace BracketSpace\Notification\Defaults\MergeTag\Post;

use BracketSpace\Notification\Defaults\MergeTag\UrlTag;
use BracketSpace\Notification\Utils\WpObjectHelper;

/**
 * Post permalink merge tag class
 */
class PostPermalink extends UrlTag {
	/**
	 * Merge tag constructor
	 *
	 * @since 5.0.0
	 * @param array $params merge tag configuration params.
	 */
	public function __construct( $params = [] ) {

		$this->set_trigger_prop( $params['post_type'] ?? 'post' );

		$post_type_name = WpObjectHelper::get_post_type_name( $this->get_trigger_prop() );

		$args = wp_parse_args(
			$params,
			[
				'slug'        => sprintf( '%s_permalink', $this->get_trigger_prop() ),
				// translators: singular post name.
				'name'        => sprintf( __( '%s permalink', 'notification' ), $post_type_name ),
				'description' => __( 'https://example.com/hello-world/', 'notification' ),
				'example'     => true,
				'group'       => $post_type_name,
				'resolver'    => function ( $trigger ) {
					return get_permalink( $trigger->{ $this->get_trigger_prop() }->ID );
				},
			]
		);

		parent::__construct( $args );

	}

}
