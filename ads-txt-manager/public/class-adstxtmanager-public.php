<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Ads.txt Manager
 * @subpackage Ads.txt Manager/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Ads.txt Manager
 * @subpackage Ads.txt Manager/public
 * @author     Ads.txt Manager <tech@adstxtmanager.com>
 */
class AdstxtManager_Public
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	public function handle_adstxt()
	{
		global $wp;

		$request = isset($_SERVER['REQUEST_URI']) ? esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])) : false;
		// Parse the URL to handle query parameters
		$url_path = parse_url($request, PHP_URL_PATH);
		if ('/ads.txt' == $url_path && get_option('permalink_structure')) {
			// Use the helper function to get the ID value
			$adstxtmanager_id_value = AdstxtManager::get_adstxtmanager_id_value();

			if ($adstxtmanager_id_value > 0) {
				// Get the ads.txt URL using the helper function
				$ads_txt_url = AdstxtManager::get_ads_txt_url();

				if ($ads_txt_url) {
					header("HTTP/1.1 301 Moved Permanently");
					header('Location: ' . $ads_txt_url);
					exit();
				}
			}
		}
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {}
}
