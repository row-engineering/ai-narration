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
	public  $cutoff_date;
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
		$this->plugin_name      = $plugin_name;
		$this->version          = $version;

		$this->service_provider = $this->get_service_provider();
		$this->api_key          = $this->get_api_key();
		$this->voice            = $this->get_voice();
		$this->files_dir        = 'narrations';

		$this->get_options();
	}

	private function get_options() {
		// Exclusions
		$this->eligible_post_types  = $this->get_eligible_post_types();
		$this->exclusion_terms      = $this->get_exclusion_terms();
		$this->cutoff_date          = $this->get_cutoff_date();
		$this->word_limit_min       = $this->get_word_limit_min();
		$this->word_limit_max       = $this->get_word_limit_max();

		// Features
		$this->intro_text = '';
		$this->outro_text = '';
	}

	private function get_post_info( $post = false ) {
		$this->post         = $post;
		$this->post_id      = false;
		$this->post_title   = '';
		$this->post_slug    = '';
		$this->post_authors = array();
		$this->post_date    = '';

		if ( !$post ) {
			global $post;
		}

		if ( $post ) {
			$this->post         = $post;
			$this->post_id      = $post->ID;
			$this->post_title   = get_the_title( $post );
			$this->post_slug    = $post->post_name;
			$this->post_date    = $post->post_date;
			$this->post_authors = $this->get_post_authors();
		}
	}

	/***************************
	 * GET OPTIONS & POST DATA *
	 ***************************/

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
			$intro_text = $this->sprintf_post_meta($ain_intro_text);
		}

		return $intro_text;
	}

	private function get_outro_text() {
		$outro_text = '';

		$ain_outro_text = get_option( 'ai_narration_outro_text' );
		if ( !empty($ain_outro_text) ) {
			$outro_text = $this->sprintf_post_meta($ain_outro_text);
		}

		return $outro_text;
	}

	public function sprintf_post_meta( $text ) {
		$text = preg_replace( '/<Headline>/i', $this->post_title,             $text );
		$text = preg_replace( '/<Date>/i',     $this->get_readable_date(),    $text );
		$text = preg_replace( '/<Authors>/i',  $this->get_readable_authors(), $text );

		return $text;
	}

	/*
		Convert a publish date to a format better suited for reading
	*/
	private function get_readable_date() {
		$date = new DateTime($this->post_date);
		$readable_date = $date->format('F jS, Y');
		return $readable_date;
	}

	/*
		Convert a list of author names to a format better suited for reading
	*/
	private function get_readable_authors() {
		$post_authors = $this->get_post_authors();
		if (count($post_authors) > 1) {
			$final_name = array_pop($post_authors);  // Remove and get the final name
			$name_list = implode(', ', $post_authors) . ' and ' . $final_name;
		} else {
			$name_list = $post_authors[0];
		}
		return $name_list;
	}

	private function get_post_authors() {
		$authors = array();

		if ( function_exists('get_coauthors') ) {
			$coauthors = get_coauthors( $this->post_id );
			foreach ($coauthors as &$coauthor) {
				$authors[] = $coauthor->display_name;
			}
		}

		if ( empty($authors) ) {
			$authors[] = get_the_author_meta( 'display_name', $this->post->post_author );
		}

		return $authors;
	}

	/******************
	 * GENERATE AUDIO *
	 ******************/

	/**
	 * STATUS CODES
	 *
	 * 28 - Request to listen() timed out (API request possibly still succesful, just taking longer than usual)
	 * 3X - Post not eligible
	 * 4  - Post not found
	 * 5  - Post failed after narration_request filter
	 */
	public function request_new_audio($new_status, $old_status, $post) {
		$response = array(
			'status' => 200
		);

		if ($new_status === 'publish' && $old_status !== 'publish') {
			$this->get_post_info( $post );

			if (!$this->post) {
				$response = array(
					'status'  => 4,
					'message' => 'Could not find post'
				);
			}

			$post_eligibility = $this->is_post_eligible();
			if ( $post_eligibility['status'] === 200 ) {
				$text_groups = $this->get_text_block_groups();
				if ( !empty($text_groups) ) {
					$response = $this->generate_audio_files($text_groups);
				}
			} else {
				$response = $post_eligibility;
			}
		} else {
			$response = array(
				'status' => 6,
				'message' => 'Post must be newly published.'
			);
		}

		$response['post_id'] = $this->post_id;

		return $response;
	}

	private function is_post_eligible() {
		$response = array(
			'status'  => 200,
			'message' => 'Post passes exclusion criteria'
		);

		$post_type = get_post_type( $this->post );
		if ( !in_array( $post_type, $this->eligible_post_types) ) {
			$response = array(
				'status'  => 31,
				'message' => 'Post excluded based on: post type'
			);
		}

		$post_terms = get_the_terms( $this->post_id, 'post_tag' );
		if ( $post_terms ) {
			$term_slugs = array_map(function($t) { return $t->slug; }, $post_terms );
			if ( count(array_intersect($term_slugs, $this->exclusion_terms)) > 0 ) {
				$response = array(
					'status'  => 32,
					'message' => 'Post excluded based on: post tag'
				);
			}
		}

		$post_date = $this->post->post_date;
		if ( strtotime($post_date) < strtotime($this->cutoff_date) ) {
			$response = array(
				'status'  => 33,
				'message' => 'Post excluded based on: post date'
			);
		}

		$request_groups = $this->get_text_block_groups(false);
		$wc = array_sum( array_map(function($paragraphs) {
			return array_sum( array_map('str_word_count', $paragraphs) );
		}, $request_groups) );
		if ( $wc < $this->word_limit_min || $wc > $this->word_limit_max ) {
			$response = array(
				'status'  => 34,
				'message' => "Post excluded based on: word count (min: {$this->word_limit_min} max: {$this->word_limit_max} total: $wc)"
			);
		}

		return $response;
	}

	/**
	 * Combine all the post blocks into a group array to be processed
	 */
	private function get_text_block_groups($include_intro_outro = true) {
		$blocks = parse_blocks( $this->post->post_content );

		if ($include_intro_outro) {
			$intro_text = $this->get_intro_text();
			if (!empty($intro_text)) {
				array_unshift($blocks, array(
					'blockName' => 'core/paragraph',
					'innerHTML' => $intro_text
				));
			}

			$outro_text = $this->get_outro_text();
			if (!empty($outro_text)) {
				$blocks[] = array(
					'blockName' => 'core/paragraph',
					'innerHTML' => $outro_text
				);
			}
		}

		$text_groups   = array();
		$current_group = 0;
		$current_text  = '';

		$max_words  = 2000;
		$max_chars  = 4096;	// OpenAI limit

		foreach ($blocks as $block) {
			switch($block['blockName']) {
				case 'core/paragraph':
				case 'core/heading':
					if (isset($block['innerHTML'])) {
						$block_content = trim(rtrim(strip_tags($block['innerHTML']), "&nbsp;\n"));
						if (!empty($block_content)) {
							$block_words = str_word_count($block_content);
							$block_chars = strlen($block_content);

							/* The first MP3 file should be a smaller filesize and therefore shorter */
							$len = ($current_group === 0) ? 600 : $max_words;

							if (str_word_count($current_text) + $block_words > $len || strlen($current_text) + $block_chars > $max_chars) {
								$current_group++;
								$current_text = '';
							}

							$text_groups[$current_group][] = $block_content;
							$current_text .= $block_content;
						}
					}
					break;
				case 'core/separator':
					if ($current_group > 0) {
						$text_groups[$current_group][] = '...';
					}
					break;
			}
		}

		return $text_groups;
	}

	/*
	Generate Audio Files

	Kicks-off multiple independent requests for TTS audios file based on groups of text.
	*/
	private function generate_audio_files($text_groups = array()) {
		if (!empty($this->post)) {

			$data = array(
				'key'     => AI_NARRATION_KEY,
				'id'      => $this->post_id,
				'title'   => $this->post_title,
				'date'    => $this->post_date,
				'url'     => get_permalink($this->post_id),
				'slug'    => $this->post_slug,
				'authors' => $this->post_authors,
				'segment' => 0,
				'total'   => count($text_groups),
				'text'    => ''
			);

			$response = array();
			foreach ($text_groups as $text_group) {
				// if there's an error, no use in continuing to try sending requests
				if ( is_wp_error($response) || ( array_key_exists('status', $response) && $response['status'] !== 200 ) ) {
					break;
				}

				$data['segment']++;
				$data['text'] = implode("\n\n", $text_group);

				// mainly this is an opportunity to pass back a false value and stop the request
				$data_mod = apply_filters('narration_request', $data);

				// error_log(json_encode(array_map(function($d) { return gettype($d) === 'string' && strlen($d)>197 ? substr($d,0,197).'...' : $d; }, $data)));

				if ( $this->validate_data($data, $data_mod) ) {
					$request = wp_remote_post(
						"https://{$_SERVER['HTTP_HOST']}/wp-content/plugins/ai-narration/endpoint/listen.php",
						array(
							'method'  => 'POST',
							'body'    => json_encode($data_mod),
							'headers' => array(
								'Content-Type' => 'application/json',
							),
							'timeout' => 180,
						)
					);
					$response_body = json_decode($request['body'], true);
					$response = array(
						'status' => $request['response']['code'],
						'message' => $response_body['data']['message'],
					);
				} else {
					$response = array(
						'status'  => 5,
						'message' => 'Request stopped due to narration_request filter (see your dev team)',
						'data'    => $data_mod
					);
				}
			}

			if ( is_wp_error($response) ) {
				$response = array(
					'status' => 28,
					'message' => 'Request timed out'
				);
			}

			return $response;
		}
	}

	private function validate_data($data, $data_mod) {
		if (!$data || !$data_mod) {
			return false;
		}

		$is_valid = true;

		$uneditable_values = array(
			'key',
			'id',
			'date',
			'segment',
			'total'
		);

		foreach ($uneditable_values as $key) {
			if ( !array_key_exists($key, $data_mod) || $data[$key] !== $data_mod[$key] ) {
				error_log("The value of $key cannot be modified.");
				$is_valid = false;
				break;
			}
		}

		$expected_types = array(
			'title'   => 'string',
			'url'     => 'string',
			'slug'    => 'string',
			'authors' => 'array',
			'text'    => 'string'
		);

		foreach ($expected_types as $key => $type) {
			if ( !array_key_exists($key, $data_mod) || gettype($data[$key]) !== $type ) {
				error_log("Expecting the value of $key to be of type $type.");
				$is_valid = false;
				break;
			}
		}

		return $is_valid;
	}

	/******************
	 * TEMPLATE LOGIC *
	 ******************/

	public function output_audio_js_obj() {

		if ( !is_single() ) return;

		$this->get_post_info();
		$post_eligibility = $this->is_post_eligible();
		if ( $post_eligibility['status'] === 200 ) {
			if ( $index_file = $this->get_index_file($this->post) ) {
				$narration_json = file_get_contents($index_file);
				$narration_data = json_decode($narration_json, true);
				if ( $narration_data['audio']['total'] === count($narration_data['audio']['tracks']) ) {
					$narration_data['config'] = array(
						'cdn'           => trim(get_option('cdn'), '/'),
						'learnMoreLink' => get_option('learn_more_link')
					);
					$narration_json = json_encode($narration_data);
					echo "<script id='ai-narration-data'>window.AINarrationData = $narration_json</script>";
				}
			}
		}
	}

	public function output_audio_schema($schema) {

		if ( !is_single() ) return $schema;

		global $post;

		$date = DateTime::createFromFormat('Y-m-d H:i:s', $post->post_date);
		$year = $date->format('Y');
		$slug = $post->post_name;
		$index_file = AI_NARRATION_PATH . "/$year/$slug/index.json";
		$has_narration = file_exists($index_file);

		if ($has_narration) {
			$article_schema_idx = $this->find_newsarticle_schema($schema['@graph']);

			if ($article_schema_idx > -1) {
				if ( !isset($schema['@graph'][$article_schema_idx]['audio']) ) {

					$index_data = json_decode(file_get_contents($index_file), true);
					$contentUrl = '';
					$duration   = 0;

					if (isset($index_data['audio'])) {
						if (isset($index_data['audio']['duration'])) {
							$duration = array_sum($index_data['audio']['duration']);
							$duration = $this->get_iso8601_duration($duration);
						}
						$contentUrl = get_site_url() . $index_data['audio']['tracks'][0];
					}

					$description = '';
					if (isset($schema['@graph'][$article_schema_idx]['description'])) {
						$description = $schema['@graph'][$article_schema_idx]['description'];
					}

					$schema['@graph'][$article_schema_idx]['audio'] = array(
						'@type'          => 'AudioObject',
						'contentUrl'     => $contentUrl,
						'encodingFormat' => 'audio/mpeg',
						'inLanguage'     => 'en',
						'duration'       => $duration,
						'uploadDate'     => $schema['@graph'][$article_schema_idx]['dateModified'],
						'name'           => $schema['@graph'][$article_schema_idx]['headline'],
						'description'    => $description
					);

				}
			}
		}

		return $schema;
	}

	private function get_iso8601_duration($seconds) {
		$minutes = floor($seconds / 60);
		$seconds = floor($seconds) % 60;
		return "PT{$minutes}M{$seconds}S";
	}

	private function find_newsarticle_schema($graph) {
		foreach ($graph as $idx => $item) {
			if (is_array($item) && isset($item['@type']) && $item['@type'] === 'NewsArticle') {
				return $idx;
			}
		}
		return -1;
	}

	/**************************
	 * ENQUEUE SCRIPTS/STYLES *
	 **************************/

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		global $post;
		if (!is_single()) return;

		$has_narration = $this->get_index_file($post);
		if ( $has_narration ) {
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/ain-public.css', array(), $this->version, 'all' );
		}
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		global $post;
		if (!is_single()) return;

		$has_narration = $this->get_index_file($post);
		if ( $has_narration ) {
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/ain-public.js', array(), $this->version, false );
		}
	}

	public function enqueue_svg_sprite() {
		global $post;
		if (!is_single()) return;

		$has_narration = $this->get_index_file($post);
		if ( $has_narration ) {
			$sprite = plugin_dir_path( __FILE__ ) . 'assets/sprite.svg';
			if ( file_exists($sprite) ) {
				echo '<div id="ai-narration-sprite" style="display: none;">';
				include_once $sprite;
				echo '</div>';
			}
		}
	}

	public function get_index_file( $post ) {
		if (!$post) return;

		$date = DateTime::createFromFormat('Y-m-d H:i:s', $post->post_date);
		$year = $date->format('Y');
		$slug = $post->post_name;
		$index_file = AI_NARRATION_PATH . "/$year/$slug/index.json";
		return file_exists($index_file) ? $index_file : false;
	}
}
