<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://https://github.com/row-engineering/
 * @since      1.0.0
 *
 * @package    AI_Narration
 * @subpackage AI_Narration/admin
 */
class AI_Narration_Admin {

	private $plugin_name;
	private $version;
	private $pages;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name     The name of this plugin.
	 * @param      string    $version         The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		$this->pages = array(
			'settings' => array(
				'title' => 'Settings',
			),
			'narrations' => array(
				'title' => 'Narrations',
			),
			'documentation' => array(
				'title' => 'Documentation'
			)
		);

		$this->add_actions();
	}

	private function add_actions() {
		add_action( 'admin_menu', array( $this, 'create_plugin_settings_page' ) );
		add_filter( 'plugin_action_links_' . AI_NARRATION_BASENAME, array( $this, 'add_plugin_action_link' ), 10, 2 );

		add_action( 'admin_init', array( $this, 'setup_settings_sections' ) );
		add_action( 'admin_init', array( $this, 'setup_settings_fields' ) );

		// Auto-suggest
		add_action('wp_ajax_post_lookup',        array( $this, 'post_lookup'));
		add_action('wp_ajax_nopriv_post_lookup', array( $this, 'post_lookup'));
		add_action('wp_ajax_tag_lookup',         array( $this, 'tag_lookup'));
		add_action('wp_ajax_nopriv_tag_lookup',  array( $this, 'tag_lookup'));
		
		// Posts/Narrations admin callbacks
		add_action('wp_ajax_generate_narration',  array( $this, 'handle_generate_narration'));
		add_action('wp_ajax_delete_narration',   array( $this, 'handle_delete_narration'));
	}

	/**
	 * Setup Page and Menu item
	 *
	 * @since    1.0.0
	 */
	public function create_plugin_settings_page() {
		$page_title = 'Settings';
		$menu_title = 'AI Narrations';
		$capability = 'manage_options';
		$slug       = $this->plugin_name . '-settings';
		$callback   = array( $this, 'plugin_settings_page_content' );

		add_menu_page( $page_title, $menu_title, $capability, $slug, $callback, 'dashicons-controls-volumeon', 100 );

		foreach($this->pages as $key => $page) {
			$s = (isset($page['hide_from_menu'])) ? '' : $slug;
			add_submenu_page(
				$s,
				$page['title'],
				$page['title'],
				$capability,
				str_replace('settings', $key, $slug),
				$callback
			);
		}
	}

	/*********************************************** SETTINGS PAGE ***********************************************/

	public function plugin_settings_page_content() {
		$page_name = str_replace("{$this->plugin_name}-", '', $_GET["page"]);
		if (isset($this->pages[$page_name])){
			require_once "partials/{$this->plugin_name}-admin-header.php";
			require_once "partials/{$this->plugin_name}-admin-{$page_name}.php";
			require_once "partials/{$this->plugin_name}-admin-footer.php";
		}
	}

	public function add_plugin_action_link(array $links) {
		$url = get_admin_url() . "options-general.php?page={$this->plugin_name}-settings";
		$settings_link = "<a href=\"{$url}\">Settings</a>";
		$links[] = $settings_link;
		return $links;
	}

	public function setup_settings_sections() {
		$args = array(
			'before_section' => '<div class="ain-box ain-section ain-section-%s">',
			'after_section'  => '</div>',
			'section_class'  => 'none'
		);

		$args['section_class'] = 'service';
		add_settings_section( 'ai_narration_service',  'Service',   array( $this, 'section_callback' ),  'ain-settings',  $args );

		$args['section_class'] = 'features';
		add_settings_section( 'ai_narration_features',   'Features',  array( $this, 'section_callback' ),  'ain-settings',  $args );

		$args['section_class'] = 'exclusions';
		add_settings_section( 'ai_narration_exclusions', 'Exclusions', array( $this, 'section_callback' ),  'ain-settings', $args );
	}

	public function section_callback( $arguments ) {
		switch( $arguments['id'] ){
			case 'ai_narration_service':
				echo 'Currently the only available service is <a href="https://platform.openai.com/docs/guides/text-to-speech?lang=node" target="_blank">OpenAI TTS</a>';
				break;
		}
	}

	public function setup_settings_fields() {
		$ai_narration_services = array();
		foreach (AI_NARRATION_SERVICES as $key => $service) {
			if (isset($service['name'])) {
				$ai_narration_services[$key] = $service['name'];
			}
		}
		$ai_narration_services['none'] = 'None';

		$ai_narration_voices = AI_NARRATION_SERVICES['openai']['voices'];

		$pages = array(
			'ain-settings' => array(

				/*	Section: Service */

				array(
					'uid'     => 'ai_narration_service_vendor',
					'label'   => 'Narration Service',
					'section' => 'ai_narration_service',
					'type'    => 'select',
					'options' => $ai_narration_services,
					'default' => array('none')
				),
				array(
					'uid'     => 'ai_narration_service_api_key',
					'label'   => 'API Key',
					'section' => 'ai_narration_service',
					'type'    => 'password',
					'supplemental' => 'API keys should not be shared',
				),
				array(
					'uid'     => 'ai_narration_voice',
					'label'   => 'Narration Voice',
					'section' => 'ai_narration_service',
					'type'    => 'select',
					'options' => $ai_narration_voices,
					'default' => array('shimmer')
				),

				/*	Section: Features */

				// TO DO: select which block types to narration? Paragraph & pullquote by default

				array(
					'uid'     => 'ai_narration_intro_text',
					'label'   => 'Introduction Text',
					'section' => 'ai_narration_features',
					'type'    => 'textarea',
					'supplemental' => 'Available variables: Headline, Authors, Date.',
					'default' => '<Headline> by <Authors>. Published <Date>. Narrated by AI.'
				),
				array(
					'uid'     => 'ai_narration_outro_text',
					'label'   => 'Outro Text',
					'section' => 'ai_narration_features',
					'supplemental' => 'Available variables: Headline, Authors, Date.',
					'type'    => 'textarea',
				),
				array(
					'uid'     => 'ai_narration_intro_mp3',
					'label'   => 'Intro MP3 File Path',
					'section' => 'ai_narration_features',
					'type'    => 'text',
				),
				array(
					'uid'     => 'ai_narration_outro_mp3',
					'label'   => 'Outro MP3 File Path',
					'section' => 'ai_narration_features',
					'type'    => 'text',
				),

				// TO DO: how to handle this value changing? move all previous files?
				// array(
				// 	'uid'     => 'ai_narration_base_dir',
				// 	'label'   => 'Base Directory Name',
				// 	'section' => 'ai_narration_features',
				// 	'type'    => 'text',
				// 	'default' => 'narrations',
				// 	'supplemental' => 'Directory structure: ai-narration/DIRECTORY-NAME/YEAR/POST-SLUG/'
				// ),

				/*	Section: Exclusions */

				array(
					'uid'     => 'ai_narration_post_types',
					'label'   => 'Post Types',
					'section' => 'ai_narration_exclusions',
					'type'    => 'checkbox',
					'options' => array(
						'post'    => 'Post',
						'blog'    => 'Blog',
						// TO DO: auto-populate from custom post types
					),
					'default' => array('post')
				),
				array(
					'uid'     => 'ai_narration_excluded_terms',
					'label'   => 'Excluded Terms',
					'section' => 'ai_narration_exclusions',
					'type'    => 'textarea',
					'supplemental' => 'Comma-separated.',
					'default' => 'skip-ai-narration'
				),
				array(
					'uid'     => 'ai_narration_exclusion_taxonomy',
					'label'   => 'Exclusion Taxonomy',
					'section' => 'ai_narration_exclusions',
					'type'    => 'select',
					'options' => array(
						'post_tag' => 'Tag',
						'category' => 'Category',
						// TO DO: auto-populate from custom taxonomies
					),
					'default' => array('post_tag')
				),
				array(
					'uid'     => 'ai_narration_cutoff',
					'label'   => 'Cut-Off Date',
					'section' => 'ai_narration_exclusions',
					'type'    => 'text',	// TO DO: date field
					'supplemental' => 'Posts published prior to this date will not be narrated. YYYY-MM-DD format.',
				),
				array(
					'uid'     => 'ai_narration_wc_limit_min',
					'label'   => 'Minimum Word Limit',
					'section' => 'ai_narration_exclusions',
					'type'    => 'number',
					'default' => 1000,
					'supplemental' => 'Posts with a lower word count will not be narrated.'
				),
				array(
					'uid'     => 'ai_narration_wc_limit_max',
					'label'   => 'Maximum Word Limit',
					'section' => 'ai_narration_exclusions',
					'type'    => 'number',
					'default' => 20000,
					'supplemental' => 'Posts with a higher word count will not be narrated.'
				),
			),
		);

		foreach( $pages as $option_group => $fields ){
			foreach( $fields as $field ){
				add_settings_field( $field['uid'], $field['label'], array( $this, 'field_callback' ), $option_group, $field['section'], $field );
				register_setting( $option_group, $field['uid'] );
			}
		}
	}

	public function field_callback( $arguments ) {
		$value = get_option( $arguments['uid'] );

		if( ! $value && isset($arguments['default']) ) {
			$value = $arguments['default'];
		}

		$placeholder = (isset($arguments['placeholder'])) ? $arguments['placeholder'] : '';

		switch( $arguments['type'] ){
			case 'text':
			case 'password':
			case 'number':
			case 'hidden':
				printf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />', $arguments['uid'], $arguments['type'], $placeholder, $value );
				break;

			case 'textarea':
				printf( '<textarea name="%1$s" id="%1$s" placeholder="%2$s" rows="5" cols="50">%3$s</textarea>', $arguments['uid'], $placeholder, $value );
				break;

			case 'select':
			case 'multiselect':
				if( ! empty ( $arguments['options'] ) && is_array( $arguments['options'] ) ){
					$attributes = '';
					$options_markup = '';
					foreach( $arguments['options'] as $key => $label ){
						$options_markup .= sprintf( '<option value="%s" %s>%s</option>', $key, selected( $value[ array_search( $key, $value, true ) ], $key, false ), $label );
					}
					if( $arguments['type'] === 'multiselect' ){
						$attributes = ' multiple="multiple" ';
					}
					printf( '<select name="%1$s[]" id="%1$s" %2$s>%3$s</select>', $arguments['uid'], $attributes, $options_markup );
				}
				break;

			case 'radio':
			case 'checkbox':
				if( ! empty ( $arguments['options'] ) && is_array( $arguments['options'] ) ){
					$options_markup = '';
					$iterator = 0;
					foreach( $arguments['options'] as $key => $label ){
						$iterator++;
						$checked = (in_array($key, $value)) ? 'checked' : '';
						$options_markup .= sprintf( '<label for="%1$s_%6$s"><input id="%1$s_%6$s" name="%1$s[]" type="%2$s" value="%3$s" %4$s /> %5$s</label><br/>', $arguments['uid'], $arguments['type'], $key, $checked, $label, $iterator );
					}
					printf( '<fieldset>%s</fieldset>', $options_markup );
				}
				break;
		}

		if( isset($arguments['helper']) && $helper = $arguments['helper'] ){
			printf( '<span class="helper"> %s</span>', $helper );
		}

		if( isset($arguments['supplemental']) && $supplemental = $arguments['supplemental'] ){
			printf( '<p class="description">%s</p>', $supplemental );
		}
	}

	public function admin_notice() { ?>
		<div class="notice notice-success inline is-dismissible">
			<p>Your settings have been updated</p>
		</div><?php
	}

	/**
	 * Exclusions post auto-suggest
	 */
	public function post_lookup() {
		global $wpdb;
		$search = like_escape($_REQUEST['q']);
		$query = 'SELECT ID,post_title FROM ' . $wpdb->posts . '
			WHERE post_title LIKE \'%' . $search . '%\'
			AND post_type = \'post\'
			AND post_status = \'publish\'
			ORDER BY post_title ASC LIMIT 10';
		foreach ($wpdb->get_results($query) as $row) {
			$title = $row->post_title;
			$id = $row->ID;
			echo "$title ($id)\n";
		}
		die();
	}

	public function tag_lookup() {
		$search = like_escape($_REQUEST['q']);
		$args = array(
			'taxonomy'   => 'post_tag',
			'hide_empty' => false,
			'name__like' => $search,
		);
		$tags = get_terms($args);
		foreach ($tags as $tag) {
			$name = $tag->name;
			$id = $tag->term_id;
			echo "$name ($id)\n";
		}
		die();
	}

	/********************************************** NARRATIONS PAGE **********************************************/

	function handle_generate_narration() {
		check_ajax_referer('narration_nonce', 'nonce');
		
		if (!current_user_can('manage_options')) {
			wp_send_json_error('Unauthorized');
		}
		
		$post_ids = $_POST['post_ids'];
		
		foreach ($post_ids as $post_id) {
			$post = get_post($post_id);

			$plugin = new AI_Narration();
			$plugin_public = new AI_Narration_Public( $plugin->get_plugin_name(), $plugin->get_version() );

			// TO DO: ensure response was successful and, if OpenAI returned an error, then serve up an error here accordingly
			$plugin_public->request_new_audio('publish', 'draft', $post);
		}
		
		wp_send_json_success();
	}

	function handle_delete_narration() {
		check_ajax_referer('narration_nonce', 'nonce');
		
		if (!current_user_can('manage_options')) {
			wp_send_json_error('Unauthorized');
		}
		
		$post_ids = $_POST['post_ids'];
		
		foreach ($post_ids as $post_id) {
			$post = get_post($post_id);
			$date = DateTime::createFromFormat('Y-m-d H:i:s', $post->post_date);
			$year = $date->format('Y');
			$slug = $post->post_name;
			$narr_dir = AI_NARRATION_PATH . "/$year/$slug/";
			if ( is_dir($narr_dir) ) {
				rmdir($narr_dir);
			}
		}
		
		wp_send_json_success();
	}
	
	/************************************************** GENERAL **************************************************/

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
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/ain-admin.css', array(), $this->version, 'all' );
	}

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
		 */

		wp_enqueue_script('suggest');
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/ain-admin.js', array(), $this->version, false );
		wp_localize_script( $this->plugin_name, 'narrationAdmin', array( 'nonce' => wp_create_nonce('narration_nonce') ) );
	}
}
