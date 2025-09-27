<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://https://github.com/row-engineering/
 * @since      1.0.0
 *
 * @package    AI_Narration
 * @subpackage AI_Narration/posts-list
 */

if (!class_exists('WP_List_Table')) {
	require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Posts_Narration_List_Table extends WP_List_Table {

	protected $_column_headers;
	public    $items;

	public function prepare_items() {
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = array();

		$this->_column_headers = array($columns, $hidden, $sortable);

		// Get posts from last two weeks
		$args = array(
			'post_type'   => 'post',
			'post_status' => 'publish',
			'date_query'  => array(
				array(
					'after' => '2023-01-01'
				)
			),
			'posts_per_page' => -1
		);

		$posts = get_posts($args);
		$this->items = $posts;
	}

	public function get_columns() {
		return array(
			'cb'      => '<input type="checkbox" />',
			'title'   => 'Post Title',
			'date'    => 'Published',
			'actions' => 'Actions'
		);
	}

	public function column_cb($item) {
		return sprintf(
			'<input type="checkbox" name="post[]" value="%s" />',
			$item->ID
		);
	}

	public function column_title($item) {
		return sprintf(
			'<a href="%s" target="_blank">%s</a>',
			get_permalink($item->ID),
			$item->post_title
		);
	}

	public function column_date($item) {
		return get_the_date('', $item->ID);
	}

	public function column_actions($post) {
		$date = DateTime::createFromFormat('Y-m-d H:i:s', $post->post_date);
		$year = $date->format('Y');
		$slug = $post->post_name;
		$index_file = AI_NARRATION_PATH . "/$year/$slug/index.json";
		$has_narration = file_exists($index_file);

		$generate_button = sprintf(
			'<button type="button" class="button action generate-narration" data-post-id="%d" data-post-name="%s" data-post-year="%d">%s</button>',
			$post->ID,
			$post->post_name,
			get_the_date('Y', $post->ID),
			$has_narration ? 'Regenerate' : 'Generate'
		);

		$delete_button = $has_narration ? sprintf(
			'<button type="button" class="button action delete-narration" data-post-id="%d">Delete</button>',
			$post->ID
		) : '';

		return $generate_button . ' ' . $delete_button;
	}
}