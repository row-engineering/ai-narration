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
		$this->exclusion_tax        = $this->get_exclusion_tax();
		$this->cutoff_date          = $this->get_cutoff_date();
		$this->word_limit_min       = $this->get_word_limit_min();
		$this->word_limit_max       = $this->get_word_limit_max();

		// Features
		$this->intro_text = '';
		$this->outro_text = '';
	}

	private function get_post_info( $post = false ) {

		if ( ! is_singular('post') ) {
			return;
		}

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

	public function request_new_audio($new_status, $old_status, $post) {
		if ($new_status === 'publish' && $old_status !== 'publish') {
			$this->get_post_info( $post );

			$eligible_post = $this->is_post_eligible();
			if ( $eligible_post ) {
				$text_groups = $this->get_text_block_groups();
				if ( !empty($text_groups) ) {
					return $this->generate_audio_files($text_groups);
				}
			}
		}

		return false;
	}

	private function is_post_eligible() {
		$post_type = get_post_type( $this->post );
		if ( !in_array( $post_type, $this->eligible_post_types) ) {
			// error_log('is_post_eligible: FAIL: post type');
			return false;
		}

		$post_terms = get_the_terms( $this->post_id, $this->exclusion_tax );
		if ( $post_terms ) {
			$term_slugs = array_map(function($t) { return $t->slug; }, $post_terms );
			if ( count(array_intersect($term_slugs, $this->exclusion_terms)) > 0 ) {
				// error_log('is_post_eligible: FAIL: terms');
				return false;
			}
		}

		$post_date = $this->post->post_date;
		if ( strtotime($post_date) < strtotime($this->cutoff_date) ) {
			// error_log('is_post_eligible: FAIL: date');
			return false;
		}

		// error_log('is_post_eligible: PASS');
		return true;
	}

	/**
	 * Combine all the post blocks into a group array to be processed
	 */
	private function get_text_block_groups() {
		$blocks = parse_blocks( $this->post->post_content );

		$block_groups = array();
		$block_groups_index = 0;
		$current_group = '';

		$min_length =  600;
		$max_length = 2000;
		$wc = 0;

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

		foreach ($blocks as $block) {
			switch($block['blockName']) {
				case 'core/paragraph':
				case 'core/heading':
					if (isset($block['innerHTML'])) {
						$block_content = trim(rtrim(strip_tags($block['innerHTML']), "&nbsp;\n"));
						if (!empty($block_content)) {
							$block_len = strlen($block_content);
							$wc += $block_len;

							/* The first MP3 file should be shorter and therfore a smaller filesize */
							$len = ($block_groups_index === 0) ? $min_length : $max_length;

							if (strlen($current_group) + $block_len > $len) {
								$block_groups_index++;
								$current_group = '';
							}

							$block_groups[$block_groups_index][] = $block_content;
							$current_group .= $block_content;
						}
					}
					break;
				case 'core/separator':
					if ($block_groups_index > 0) {
						$block_groups[$block_groups_index][] = '...';
					}
					break;
			}
		}

		//	If it's too short or too long then return nothing
		$wc_in_range = $wc >= $this->word_limit_min && $wc <= $this->word_limit_max;
		if ( !$wc_in_range ) {
			// error_log("get_text_block_groups: word count out of allowable range. min: {$this->word_limit_min} max: {$this->word_limit_max} total: $wc");
			$block_groups = array();
		}

		// error_log('get_text_block_groups: ' . count($block_groups));
		return $block_groups;
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

			$responses = array();
			foreach ($text_groups as $text_group) {
				$data['segment']++;
				$data['text'] = implode("\n\n", $text_group);

				// error_log(json_encode(array_map(function($d) { return gettype($d) === 'string' && strlen($d)>197 ? substr($d,0,197).'...' : $d; }, $data)));

				$responses[] = wp_remote_post(
					"https://{$_SERVER['HTTP_HOST']}/wp-content/plugins/ai-narration/endpoint/listen.php",
					array(
						'method'  => 'POST',
						'body'    => json_encode($data),
						'headers' => array(
							'Content-Type' => 'application/json',
						),
						'timeout' => 5,
					)
				);
			}

			return $responses;
		}
	}

	/******************
	 * TEMPLATE LOGIC *
	 ******************/

	public function output_audio_js_obj() {
		$this->get_post_info();
		$eligible_post = $this->is_post_eligible();
		if ( $eligible_post ) {
			if ( $index_file = $this->get_index_file($this->post) ) {
				$narration_json = file_get_contents($index_file);
				$narration_data = json_decode($narration_json, true);
				if ( $narration_data['audio']['total'] === count($narration_data['audio']['tracks']) ) {
					$narration_data['config'] = array();
					$narration_data['config']['learnMoreLink'] = get_option('learn_more_link');
					$narration_json = json_encode($narration_data);
					echo "<script id='ai-narration-data'>window.AINarrationData = $narration_json</script>";
				}
			}
		}
	}

	/**
	 * {
	 * 		"@type": "MediaObject",
	 * 		"contentUrl": "https://www.yourwebsite.com/wp-content/uploads/audio_1.mp3",
	 * 		"encodingFormat": "audio/mpeg",
	 * 		"duration": "PT5M30S",
	 * 		"position": 1,
	 * 		"description": "Part 1 of the audio narration",
	 * 		"inLanguage": "en",
	 * 		"isPartOf": {
	 * 			"@type": "CreativeWork",
	 * 			"@id": "https://www.yourwebsite.com/your-article-url/"
	 * 		}
	 * }
	 */
	public function output_audio_schema($schema) {

		if ( ! is_singular('post') ) {
			return;
		}

		global $post;

		$date = DateTime::createFromFormat('Y-m-d H:i:s', $post->post_date);
		$year = $date->format('Y');
		$slug = $post->post_name;
		$index_file = AI_NARRATION_PATH . "/$year/$slug/index.json";
		$has_narration = file_exists($index_file);

		if ($has_narration) {
			$article_schema_idx = $this->find_newsarticle_schema($schema['@graph']);

			if ($article_schema_idx > -1) {
				if ( !isset($schema['@graph'][$article_schema_idx]['associatedMedia']) ) {
					$schema['@graph'][$article_schema_idx]['associatedMedia'] = array();
				}
	
				$index_data = json_decode(file_get_contents($index_file), true);
				$tracks = $index_data['audio']['tracks'];

				foreach ( $tracks as $idx => $track ) {
					$track_num = $idx + 1;

					$duration = 0;
					if (isset($index_data['audio']['duration'])){
						$duration = $this->get_iso8601_duration($index_data['audio']['duration'][$idx]);
					}

					$schema['@graph'][$article_schema_idx]['associatedMedia'][] = array(
						'@type'          => 'AudioObject',
						'contentUrl'     => $index_data['url'],
						'encodingFormat' => 'audio/mpeg',
						'position'       => $track_num,
						'duration'       => $duration,
						'name'           => $schema['@graph'][$article_schema_idx]['headline'],
						'description'    => $schema['@graph'][$article_schema_idx]['description'],
						'dateUploaded'   => $schema['@graph'][$article_schema_idx]['dateModified'],
						'inLanguage'     => 'en',
						'isPartOf'       => array(
							'@type' => 'CreativeWork',
							'@id'   => $index_data['url']
						)
					);
				}
			}
		}

		return $schema;
	}

	private function get_iso8601_duration($seconds) {
		$minutes = floor($seconds / 60);
		$seconds = floor($seconds % 60);
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
				echo '<div style="display: none;">';
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
