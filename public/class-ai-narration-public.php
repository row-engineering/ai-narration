<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://https://github.com/row-engineering/
 * @since      1.0.0
 *
 * @package    AI_Narration
 * @subpackage AI_Narration/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    AI_Narration
 * @subpackage AI_Narration/public
 * @author     Anna Rasshivkina <annarasshivkina@gmail.com>
 */
class AI_Narration_Public {

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

	private $service_provider;
	private $api_key;
	private $voice;
	private $files_dir;
	private $eligible_post_types;
	private $exclusion_terms;
	private $exclusion_tax;
	private $cutoff_date;
	private $word_limit_min;
	private $word_limit_max;
	private $text_content;
	private $intro_text;
	private $outro_text;
	private $post;
	private $post_id;
	private $post_title;
	private $post_slug;
	private $post_authors;
	private $post_date;
	private $narration;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since      1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version           The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		// Basic Info
		$this->plugin_name      = $plugin_name;
		$this->version          = $version;
		$this->service_provider = $this->get_service_provider();
		$this->api_key          = $this->get_api_key();
		$this->voice            = $this->get_voice();
		$this->files_dir        = 'narrations';

		// Exclusions
		$this->eligible_post_types  = $this->get_eligible_post_types();
		$this->exclusion_terms      = $this->get_exclusion_terms();
		$this->exclusion_tax        = $this->get_exclusion_tax();
		$this->cutoff_date          = $this->get_cutoff_date();
		$this->word_limit_min       = $this->get_word_limit_min();
		$this->word_limit_max       = $this->get_word_limit_max();

		// Features
		$this->intro_text = '';
		$this->outro_text = '';

		// Post Info
		$this->post_id      = false;
		$this->post_title   = '';
		$this->post_slug    = '';
		$this->post_authors = array();
		$this->post_date    = '';
	}

	/************** GET CONFIGURATION OPTIONS **************/

	private function get_service_provider() {
		$service_provider = '';

		$ain_service_provider = get_option( 'ain_service_provider' );
		if ( !empty($ain_service_provider) && isset($ain_service_provider[0]) ) {
			$service_provider = $ain_service_provider[0];
		}

		return $service_provider;
	}

	private function get_api_key() {
		$api_key = '';

		$ain_service_api_key = get_option( 'ain_service_api_key' );
		if ( !empty($ain_service_api_key) ) {
			$api_key = $ain_service_api_key;
		}

		return $api_key;
	}

	private function get_voice() {
		$voice = '';

		$ain_narration_voice = get_option( 'ai_narration_voice' );
		if ( !empty($ain_narration_voice) ) {
			$voice = $ain_narration_voice;
		}

		return $voice;
	}

	private function get_eligible_post_types() {
		$post_types = array();

		$ain_post_types = get_option( 'ai_narration_post_types' );
		if ( !empty($ain_post_types) ) {
			$post_types = $ain_post_types;
		}

		return $post_types;
	}

	private function get_exclusion_terms() {
		$excl_terms = array();

		$ain_excl_terms = get_option( 'ai_narration_excluded_terms' );
		if ( !empty($ain_excl_terms) ) {
			$excl_terms = array_map(
				function($term) { return trim($term); },
				explode(',', $ain_excl_terms)
			);
		}

		return $excl_terms;
	}

	private function get_exclusion_tax() {
		$excl_taxonomy = '';

		$ain_excl_taxonomy = get_option( 'ai_narration_exclusion_taxonomy' );
		if ( !empty($ain_excl_taxonomy) && isset($ain_excl_taxonomy[0]) ) {
			$excl_taxonomy = $ain_excl_taxonomy[0];
		}

		return $excl_taxonomy;
	}

	private function get_cutoff_date() {
		$cutoff_date = '1111-11-11 12:00:01';

		$ain_cutoff_date = trim( get_option( 'ai_narration_cutoff' ) );
		if ( !empty($ain_cutoff_date) ) {
			if ( preg_match('/^[\d]{4}\-[\d]{2}\-[\d]{2}$/', $ain_cutoff_date) ) {
				$date_parts = explode('-', $ain_cutoff_date);
				$date_valid = checkdate($date_parts[1], $date_parts[2], $date_parts[0]);	// (month, day, year)
				if ($date_valid) {
					$cutoff_date = $ain_cutoff_date . ' 12:00:01';
				}
			}
		}

		return $cutoff_date;
	}

	private function get_word_limit_min() {
		$min_limit = 0;

		$ain_min_limit = get_option( 'ai_narration_wc_limit_min' );
		if ( !empty($ain_min_limit) ) {
			$min_limit = $ain_min_limit;
		}

		return $min_limit;
	}

	private function get_word_limit_max() {
		$max_limit = 50000;

		$ain_max_limit = get_option( 'ai_narration_wc_limit_max' );
		if ( !empty($ain_max_limit) ) {
			$max_limit = $ain_max_limit;
		}

		return $max_limit;
	}

	private function get_intro_text() {
		$intro_text = '';

		$ain_intro_text = get_option( 'ai_narration_intro_text' );
		if ( !empty($ain_intro_text) ) {
			$intro_text = sprintf_post_meta($ain_intro_text);
		}

		return $intro_text;
	}

	private function get_outro_text() {
		$outro_text = '';

		$ain_outro_text = get_option( 'ai_narration_outro_text' );
		if ( !empty($ain_outro_text) ) {
			$outro_text = sprintf_post_meta($ain_outro_text);
		}

		return $outro_text;
	}

	private function sprintf_post_meta( $text ) {
		$text = preg_replace( '/<headline>/i', $this->post_title, $text );

		// TO DO: author string, see row_posted_by

		// TO DO: date string

		return $text;
	}

	/************** GENERATE AUDIO **************/

	public function generate_new_audio($new_status, $old_status, $post) {
		// error_log('generate_new_audio');
		$this->post         = $post;
		$this->post_id      = $post->ID;
		$this->post_title   = get_the_title( $post );
		$this->post_slug    = $post->post_name;
		$this->post_date    = $post->post_date;
		$this->post_authors = $this->get_post_authors();

		if ($new_status === 'publish' && $old_status !== 'publish') {
			$eligible_post = $this->is_post_eligible();
			if ( $eligible_post ) {
				$this->text_content = $this->get_text_content();

				$wc = str_word_count( $this->text_content );
				$wc_in_range = $wc >= $this->word_limit_min && $wc <= $this->word_limit_max;

				if ( $wc_in_range ) {
					$this->generate_audio_files();
				}
			}
		}
	}

	private function is_post_eligible() {

		$post_type = get_post_type($this->post);
		if ( !in_array( $post_type, $this->eligible_post_types) ) {
			// error_log('FAIL: post type');
			return false;
		}

		$post_terms = get_the_terms( $this->post_id, $this->exclusion_tax );
		if ( $post_terms ) {
			if ( count(array_intersect($post_terms, $this->exclusion_terms)) > 0 ) {
			// error_log('FAIL: terms');
				return false;
			}
		}

		$post_date = $this->post->post_date;
		if ( strtotime($post_date) < strtotime($this->cutoff_date) ) {
			// error_log('FAIL: date');
			return false;
		}

		// error_log('PASS');
		return true;
	}

	private function get_text_content() {
		$included_blocks = array( 'core/paragraph', 'core/pullquote' );
		$text_content = '';

		$blocks = parse_blocks( $this->post->post_content );
		foreach ($blocks as $block) {
			if ( in_array($block['blockName'], $included_blocks) ) {
				$block_text = strip_tags( $block['innerHTML'] );
				$text_content .= " $block_text";
			}
		}

		return $text_content;
	}

	private function generate_audio_files() {
		// error_log('generate_audio_files');
		$this->intro_text = $this->get_intro_text();
		$this->outro_text = $this->get_outro_text();

		/**
		 * TO DO:
		 * - combine text
		 * - API request
		 */
	}

	private function get_post_authors() {
		$authors = array();

		if ( function_exists('get_coauthors') ) {
			$coauthors = get_coauthors( $this->post_id );
			foreach ($coauthors as &$coauthor) {
				$authors[] = $coauthor->display_name;
			}
		} else {
			$authors[] = get_the_author_meta( 'display_name', $this->post->post_author );
		}

		return $authors;
	}

	/************** ENQUEUE SCRIPTS/STYLES **************/

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in AI_Narration_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The AI_Narration_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/ain-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in AI_Narration_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The AI_Narration_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 *
		 */

		// wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/ain-public.js', array( 'jquery' ), $this->version, false );

	}
}
