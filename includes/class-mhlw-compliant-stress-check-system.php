<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://https://www.linkedin.com/in/muhammad-ali-hamza-0b71281a3/
 * @since      1.0.0
 *
 * @package    Mhlw_Compliant_Stress_Check_System
 * @subpackage Mhlw_Compliant_Stress_Check_System/includes
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
 * @package    Mhlw_Compliant_Stress_Check_System
 * @subpackage Mhlw_Compliant_Stress_Check_System/includes
 * @author     Muhammad Ali HAMZA <hamzamuhammadali0@gmail.com>
 */
class Mhlw_Compliant_Stress_Check_System {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Mhlw_Compliant_Stress_Check_System_Loader    $loader    Maintains and registers all hooks for the plugin.
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

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'MHLW_COMPLIANT_STRESS_CHECK_SYSTEM_VERSION' ) ) {
			$this->version = MHLW_COMPLIANT_STRESS_CHECK_SYSTEM_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'mhlw-compliant-stress-check-system';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Mhlw_Compliant_Stress_Check_System_Loader. Orchestrates the hooks of the plugin.
	 * - Mhlw_Compliant_Stress_Check_System_i18n. Defines internationalization functionality.
	 * - Mhlw_Compliant_Stress_Check_System_Admin. Defines all hooks for the admin area.
	 * - Mhlw_Compliant_Stress_Check_System_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-mhlw-compliant-stress-check-system-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-mhlw-compliant-stress-check-system-i18n.php';

		/**
		 * The class responsible for defining stress check configuration.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-mhlw-stress-check-config.php';

		/**
		 * The class responsible for stress check scoring logic.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-mhlw-stress-check-scoring.php';

		/**
		 * The class responsible for database operations.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-mhlw-stress-check-database.php';

		/**
		 * The class responsible for access control and security.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-mhlw-stress-check-security.php';

		/**
		 * The class responsible for PDF generation.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-mhlw-stress-check-pdf.php';

		/**
		 * The class responsible for user profile fields.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-mhlw-stress-check-user-fields.php';

		/**
		 * Debug file for AJAX testing.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'debug-ajax.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-mhlw-compliant-stress-check-system-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-mhlw-compliant-stress-check-system-public.php';

		$this->loader = new Mhlw_Compliant_Stress_Check_System_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Mhlw_Compliant_Stress_Check_System_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Mhlw_Compliant_Stress_Check_System_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Mhlw_Compliant_Stress_Check_System_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// Register admin menus
		$this->loader->add_action('admin_menu', $plugin_admin, 'register_admin_menus');

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Mhlw_Compliant_Stress_Check_System_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		// Initialize plugin (for PDF downloads and other early processing)
		$this->loader->add_action( 'init', $this, 'init' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();

		// Initialize security features
		Mhlw_Stress_Check_Security::init();
		
		// Initialize user fields
		Mhlw_Stress_Check_User_Fields::init();
	}

	/**
	 * Initialize plugin - handle PDF downloads and other early hooks
	 *
	 * @since    1.0.0
	 */
	public function init() {
		// Handle PDF download requests
		if (isset($_GET['mhlw_download_pdf'])) {
			$response_id = intval($_GET['mhlw_download_pdf']);
			Mhlw_Stress_Check_PDF::output_pdf($response_id);
		}

		// Handle temp PDF downloads
		if (isset($_GET['mhlw_pdf_token'])) {
			Mhlw_Stress_Check_PDF::handle_temp_download();
		}
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Mhlw_Compliant_Stress_Check_System_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
