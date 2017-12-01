<?php

namespace runcommand\Doctor\Checks;

use WP_CLI;

/**
 * Warns when there are greater than %threshold_count% plugins activated.
 */
class Check_Meta_Tags extends Plugin {

	// Meta tags reg. ex.
	private $metatags_expression = "/<meta[^>]+(?:name|property)=\"([^\"]*)\"[^>]+content=\"([^\"]*)\"[^>]*>/";

	public function run() {

		// Fetch homepage resposne.
		$response = wp_remote_get(site_url());

		if ( ! is_wp_error( $response ) ) {

			// Get the homepage source.
			$body = wp_remote_retrieve_body( $response );

			$meta_tags = $this->getMetaTags( $body );

		}
		error_log( print_r( $meta_tags, true ) );
		$this->set_status( 'success' );
		$this->set_message( 'Meta tags checking initiated.' );

	}

	/**
	 * Extract all metatags sources from content
	 *
	 * @param string $content full body source of webpage.
	 * @return array, an array of extracted metatags
	 */

	public function getMetaTags( $content = '' ) {

		$metatags = array();

		if ( empty( $content ) ) {
			return;
		}

		preg_match_all( $this->metatags_expression, $content, $match_tags );

		if ( isset( $match_tags[2] ) && count( $match_tags[2]) ) {

			foreach ( $match_tags[2] as $key => $match_tag ) {

				$key = trim( $match_tags[1][ $key ] );
				$match_tag = trim( $match_tag );

				if ( $match_tag ) {
					$metatags[] = array( $key, $match_tag );
				}
			}
		}

		return $metatags;

	}

}
