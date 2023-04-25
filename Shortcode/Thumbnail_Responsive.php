<?php
/**
 * The [cn_thumbr] shortcode.
 *
 * @since 1.0
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Extension\Thumbnail_Shortcodes\Shortcode
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2023, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Extension\Thumbnail_Shortcodes\Shortcode;

use cnEntry;
use cnImage;
use Connections_Directory\Shortcode\Do_Shortcode;
use Connections_Directory\Utility\_format;

final class Thumbnail_Responsive {

	use Do_Shortcode;

	/**
	 * The shortcode tag.
	 *
	 * @since 1.0
	 */
	const TAG = 'cn_thumbr';

	/**
	 * Register the shortcode.
	 *
	 * @since 10.4.40
	 */
	public static function add() {

		add_filter( 'pre_do_shortcode_tag', array( __CLASS__, 'maybeDoShortcode' ), 10, 4 );
		add_shortcode( self::TAG, array( __CLASS__, 'shortcode' ) );
	}

	public static function shortcode( array $atts, string $content = '', string $tag = self::TAG ): string {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		$log       = array();
		$srcset    = array();
		$permitted = array( 'attachment', 'featured', 'path', 'url', 'logo', 'photo' );
		$defaults  = array(
			'type'          => 'url',
			'source'        => null,
			'negate'        => false,
			'grayscale'     => false,
			'brightness'    => 0,
			'colorize'      => null,
			'contrast'      => 0,
			'detect_edges'  => false,
			'emboss'        => false,
			'gaussian_blur' => false,
			'blur'          => false,
			'sketchy'       => false,
			'sharpen'       => false,
			'smooth'        => null,
			'opacity'       => 100,
			'crop_mode'     => 1,
			'crop_focus'    => array( .5, .5 ),
			'crop_only'     => false,
			'canvas_color'  => '#FFFFFF',
			'quality'       => 90,
			'sizes'         => '1024|640|320',
		);

		$defaults = apply_filters( 'cn_thumbr_shortcode_atts', $defaults );

		$atts = shortcode_atts( $defaults, $atts, $tag );

		if ( ! in_array( $atts['type'], $permitted ) ) {

			return esc_html__( 'Valid image source type not supplied.', 'connections' );
		}

		/*
		 * Convert some of the $atts values in the array to boolean because the Shortcode API passes all values as strings.
		 */
		_format::toBoolean( $atts['negate'] );
		_format::toBoolean( $atts['grayscale'] );
		_format::toBoolean( $atts['detect_edges'] );
		_format::toBoolean( $atts['emboss'] );
		_format::toBoolean( $atts['gaussian_blur'] );
		_format::toBoolean( $atts['blur'] );
		_format::toBoolean( $atts['sketchy'] );
		_format::toBoolean( $atts['sharpen'] );

		// cnFormatting::toBoolean( $atts['crop'] );
		_format::toBoolean( $atts['crop_only'] );

		$atts['sizes'] = explode( '|', $atts['sizes'] );
		array_map( 'trim', $atts['sizes'] );
		array_map( 'absint', $atts['sizes'] );

		if ( empty( $atts['sizes'] ) ) {

			return esc_html__( 'No image sizes were supplied or supplied values were invalid.', 'connections' );
		}

		switch ( $atts['type'] ) {

			case 'attachment':
				$source = wp_get_attachment_url( absint( $atts['source'] ) );
				break;

			case 'featured':
				$source = wp_get_attachment_url( get_post_thumbnail_id() );
				break;

			case 'path':
				$source = $atts['source'];
				break;

			case 'url':
				$source = esc_url( $atts['source'] );
				break;

			case 'logo':
				$result = $instance->retrieve->entry( absint( $atts['source'] ) );

				$entry = new cnEntry( $result );

				$meta = $entry->getImageMeta( array( 'type' => 'logo' ) );

				if ( is_wp_error( $meta ) ) {

					// Display the error messages.
					return implode( PHP_EOL, $meta->get_error_messages() );
				}

				$source = $meta['url'];

				break;

			case 'photo':
				$result = $instance->retrieve->entry( absint( $atts['source'] ) );

				$entry = new cnEntry( $result );

				$meta = $entry->getImageMeta( array( 'type' => 'photo' ) );

				if ( is_wp_error( $meta ) ) {

					// Display the error messages.
					return implode( PHP_EOL, $meta->get_error_messages() );
				}

				$source = $meta['url'];

				break;
		}

		// Unset $atts['source'] because passing that $atts to cnImage::get() extracts and overwrite the $source var.
		unset( $atts['source'] );

		foreach ( $atts['sizes'] as $width ) {

			$atts['width'] = $width;

			$image = cnImage::get( $source, $atts, 'data' );

			if ( is_wp_error( $image ) ) {

				// Display the error messages.
				return implode( PHP_EOL, $image->get_error_messages() );

			} elseif ( false === $image ) {

				return esc_html__( 'An error has occurred while creating the thumbnail.', 'connections' );
			}

			if ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) {

				$log[] = '<pre>' . $image['log'] . '</pre>';
			}

			$srcset[] = $image['url'] . ' ' . $width . 'w';
		}

		$html = sprintf(
			'<img class="cn-image" srcset="%1$s" sizes="100vw"%2$s />',
			implode( ',', $srcset ),
			empty( $content ) ? '' : ' alt="' . esc_attr( $content ) . '"'
		);

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) {

			$html .= implode( '', $log );
		}

		return $html;
	}
}
