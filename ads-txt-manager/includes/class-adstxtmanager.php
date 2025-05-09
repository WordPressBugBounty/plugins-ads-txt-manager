<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Ads.txt Manager
 * @subpackage Ads.txt Manager/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Ads.txt Manager
 * @subpackage Ads.txt Manager/includes
 * @author     Ads.txt Manager <tech@adstxtmanager.com>
 */
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-adstxtmanager-solution-factory.php';

class AdstxtManager
{

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      AdstxtManager_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	protected $wp_filesystem = null;

	/**
	 * Get the Ads.txt URL from settings.
	 * 
	 * Returns the full URL to the Ads.txt file based on the Ads.txt Manager ID and domain.
	 * If the ID is not set or invalid, returns false.
	 *
	 * @since    1.1.1
	 * @access   public
	 * @return   string|bool    The complete ads.txt URL or false if ID is not valid
	 */
	public static function get_ads_txt_url()
	{
		// Use our helper function to safely extract the ID value
		$adstxtmanager_id_value = self::get_adstxtmanager_id_value();        // Return false if we don't have a valid ID
		if ($adstxtmanager_id_value <= 0) {
			return false;
		}

		// Get domain for the ads.txt URL
		$domain = parse_url(home_url(), PHP_URL_HOST);
		if (empty($domain)) {
			// If we can't get the domain, return false
			return false;
		}

		// Remove www prefix if present
		$domain = preg_replace('#^(http(s)?://)?w{3}\.#', '$1', $domain);

		// Final validation of the domain
		if (empty($domain) || strpos($domain, '.') === false) {
			// If domain is empty or doesn't contain a dot, it's likely invalid
			return false;
		}

		// Generate and return the complete URL
		return "https://srv.adstxtmanager.com/{$adstxtmanager_id_value}/{$domain}";
	}

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{
		if (defined('ADSTXT_MANAGER_VERSION')) {
			$this->version = ADSTXT_MANAGER_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'adstxtmanager';

		$this->load_dependencies();
		$this->setup_wp_filesystem();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - AdstxtManager_Loader. Orchestrates the hooks of the plugin.
	 * - AdstxtManager_i18n. Defines internationalization functionality.
	 * - AdstxtManager_Admin. Defines all hooks for the admin area.
	 * - AdstxtManager_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies()
	{

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-adstxtmanager-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-adstxtmanager-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-adstxtmanager-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-adstxtmanager-public.php';

		$this->loader = new AdstxtManager_Loader();
	}

	/**
	 * Initialize the WP file system.
	 *
	 * @return object
	 */
	private function setup_wp_filesystem()
	{
		global $wp_filesystem;

		if (empty($wp_filesystem)) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}

		$this->wp_filesystem = $wp_filesystem;
		return $this->wp_filesystem;
	} // setup_wp_filesystem

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the AdstxtManager_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale()
	{

		$plugin_i18n = new AdstxtManager_i18n();

		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks()
	{

		$plugin_admin = new AdstxtManager_Admin($this->get_plugin_name(), $this->get_version());
		$solutionFactory = new AdsTxtManager_Solution_Factory();
		$adsTxtSolution = $solutionFactory->GetBestSolution();

		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_page');
		$this->loader->add_action('admin_init', $plugin_admin, 'page_init');
		$this->loader->add_action('admin_notices', $plugin_admin, 'display_notice');
		$this->loader->add_action('update_option_adstxtmanager_id', $adsTxtSolution, 'SetupSolution');

		$this->loader->add_action('update_option_adstxtmanager_id', $plugin_admin, 'verify_adstxt_redirect', 20);

		// Add Settings link to the plugin.
		$plugin_basename = plugin_basename(plugin_dir_path(__DIR__) . $this->plugin_name . '.php');
		$this->loader->add_filter('plugin_action_links_' . $plugin_basename, $plugin_admin, 'add_action_links');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks()
	{

		$plugin_public = new AdstxtManager_Public($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('init', $plugin_public, 'handle_adstxt', 1);
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
	{
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name()
	{
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    AdstxtManager_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader()
	{
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version()
	{
		return $this->version;
	}

	/**
	 * Sanitize and extract the correct Ads.txt Manager ID value from settings.
	 * 
	 * This helper function handles all possible formats of the adstxtmanager_id option:
	 * - Array with "adstxtmanager_id" key containing another array
	 * - Array with "adstxtmanager_id" key containing a value
	 * - Scalar value
	 *
	 * @since    1.1.1
	 * @access   public
	 * @param    mixed    $adstxtmanager_id    Optional. The ID to sanitize. If not provided, gets from options.
	 * @return   int      The sanitized ID value, or 0 if no valid ID is found
	 */
	public static function get_adstxtmanager_id_value($adstxtmanager_id = null)
	{
		// If no ID provided, get it from options
		if ($adstxtmanager_id === null) {
			$adstxtmanager_id = get_option('adstxtmanager_id');
		}

		$adstxtmanager_id_value = 0;

		if (is_array($adstxtmanager_id) && isset($adstxtmanager_id["adstxtmanager_id"])) {
			// Handle the case where adstxtmanager_id["adstxtmanager_id"] is also an array
			if (is_array($adstxtmanager_id["adstxtmanager_id"])) {
				// Try to get the first numeric value if it's an array
				foreach ($adstxtmanager_id["adstxtmanager_id"] as $potential_id) {
					if (is_numeric($potential_id)) {
						$adstxtmanager_id_value = intval($potential_id);
						break;
					}
				}
			} else {
				$adstxtmanager_id_value = intval($adstxtmanager_id["adstxtmanager_id"]);
			}
		} elseif (is_scalar($adstxtmanager_id)) {
			$adstxtmanager_id_value = intval($adstxtmanager_id);
		}

		return $adstxtmanager_id_value;
	}
}
