<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://https://github.com/row-engineering/
 * @since      1.0.0
 *
 * @package    AI_Narration
 * @subpackage AI_Narration/includes
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
 * @package    AI_Narration
 * @subpackage AI_Narration/includes
 */
class AI_Narration {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      AI_Narration_Loader    $loader    Maintains and registers all hooks for the plugin.
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
		if ( defined( 'AI_NARRATION_VERSION' ) ) {
			$this->version = AI_NARRATION_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'ai-narration';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

		require_once plugin_dir_path( __FILE__ ) . '../configuration.php';
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - AI_Narration_Loader. Orchestrates the hooks of the plugin.
	 * - AI_Narration_i18n. Defines internationalization functionality.
	 * - AI_Narration_Admin. Defines all hooks for the admin area.
	 * - AI_Narration_Public. Defines all hooks for the public side of the site.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ai-narration-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ai-narration-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the settings area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ai-narration-admin.php';

		/**
		 * The class responsible for creating the Post Narrations management in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ai-narration-posts-list.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-ai-narration-public.php';

		/**
		 * The class responsible for defining all actions for actual Translation work
		 */
		// require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ai-narration-translate.php';

		$this->loader = new AI_Narration_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the AI_Narration_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new AI_Narration_i18n();

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

		$plugin_admin = new AI_Narration_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new AI_Narration_Public( $this->get_plugin_name(), $this->get_version() );

		/* 
			If plugin is enabled, then run
		*/

		$ai_narration_vendor = get_option( 'ai_narration_service_vendor' );
		$ai_narration_api = get_option( 'ai_narration_service_api_key' );

		if ( !empty($ai_narration_vendor) && $ai_narration_vendor[0] !== 'none' && !empty($ai_narration_api) ) {
			$this->loader->add_action( 'wp_enqueue_scripts',     $plugin_public, 'enqueue_styles' );
			$this->loader->add_action( 'wp_enqueue_scripts',     $plugin_public, 'enqueue_scripts' );
			$this->loader->add_action( 'wp_footer',              $plugin_public, 'enqueue_svg_sprite' );
			// $this->loader->add_filter( 'transition_post_status', $plugin_public, 'request_new_audio', 20, 3 );
			$this->loader->add_action( 'wp_head',                $plugin_public, 'output_audio_js_obj', 20, 0 );
		}
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	public function test() {
		return 'Test';
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
	 * @return    AI_Narration_Loader    Orchestrates the hooks of the plugin.
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
