<?php
/**
 * Experimental thumbnail shortcodes for the Connections Business Directory plugin.
 *
 * @package   Connections Business Directory Extension - Thumbnail Shortcodes
 * @category  Template
 * @author    Steven A. Zahm
 * @license   GPL-2.0+
 * @link      https://connections-pro.com
 * @copyright 2023 Steven A. Zahm
 *
 * @wordpress-plugin
 * Plugin Name:       Connections Business Directory Extension - Thumbnail Shortcodes
 * Plugin URI:        https://connections-pro.com
 * Description:       Experimental thumbnail shortcodes for the Connections Business Directory plugin.
 * Version:           1.0
 * Requires at least: 5.6
 * Requires PHP:      7.0
 * Author:            Steven A. Zahm
 * Author URI:        https://connections-pro.com
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cn_thumbnail_shortcodes
 * Domain Path:       /languages
 */

namespace Connections_Directory\Extension;

use Connections_Directory\Extension\Thumbnail_Shortcodes\Shortcode\Thumbnail;
use Connections_Directory\Extension\Thumbnail_Shortcodes\Shortcode\Thumbnail_Responsive;

final class Thumbnail_Shortcodes {

	/**
	 * @var self
	 */
	private static $instance;

	/**
	 * @var string The absolute path this file.
	 *
	 * @since 1.0
	 */
	private $file = '';

	/**
	 * @var string The URL to the plugin's folder.
	 *
	 * @since 1.0
	 */
	private $url = '';

	/**
	 * @var string The absolute path to this plugin's folder.
	 *
	 * @since 1.0
	 */
	private $path = '';

	/**
	 * @var string The basename of the plugin.
	 *
	 * @since 1.0
	 */
	private $basename = '';

	public static function instance(): self {

		if ( ! self::$instance instanceof self ) {

			$self = new self();

			$self->file     = __FILE__;
			$self->url      = plugin_dir_url( $self->file );
			$self->path     = plugin_dir_path( $self->file );
			$self->basename = plugin_basename( $self->file );

			$self->includeDependencies();

			Thumbnail::add();
			Thumbnail_Responsive::add();

			self::$instance = $self;
		}

		return self::$instance;
	}

	/**
	 * Include plugin dependencies.
	 *
	 * @since 1.0
	 */
	private function includeDependencies() {

		include_once 'Shortcode/Thumbnail.php';
		include_once 'Shortcode/Thumbnail_Responsive.php';
	}
}

add_action(
	'Connections_Directory/Loaded',
	static function() {
		Thumbnail_Shortcodes::instance();
	}
);
