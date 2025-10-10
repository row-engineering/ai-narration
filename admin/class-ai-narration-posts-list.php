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

		global $plugin_public;
		$cutoff_date = $plugin_public->get_cutoff_date();

		$current_page = $this->get_pagenum();
		$per_page = 20;

		// get total count for pagination
		$count_args = array(
			'post_type'   => 'post',
			'post_status' => 'publish',
			'date_query'  => array(
				array(
					'after' => $cutoff_date
				)
			),
			'posts_per_page' => -1,
			'fields' => 'ids'
		);
		$total_items = count(get_posts($count_args));

		$args = array(
			'post_type'   => 'post',
			'post_status' => 'publish',
			'date_query'  => array(
				array(
					'after' => $cutoff_date
				)
			),
			'posts_per_page' => $per_page,
			'paged' => $current_page,
			'offset' => ($current_page - 1) * $per_page
		);

		$posts = get_posts($args);
		$this->items = $posts;

		$this->set_pagination_args(array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil($total_items / $per_page)
		));
	}

	public function get_columns() {
		return array(
			'cb'      => '<input type="checkbox" />',
			'title'   => 'Post Title',
			'date'    => 'Published',
			'actions' => 'Actions'
		);
	}

	protected function column_cb($item) {
		return sprintf(
			'<input type="checkbox" name="post[]" value="%s" />',
			$item->ID
		);
	}

	protected function column_title($item) {
		return sprintf(
			'<a href="%s" target="_blank">%s</a>',
			get_permalink($item->ID),
			$item->post_title
		);
	}

	protected function column_date($item) {
		return get_the_date('M d, Y', $item->ID);
	}

	protected function column_actions($post) {
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

	public function single_row($item) {
		$date = DateTime::createFromFormat('Y-m-d H:i:s', $item->post_date);
		$year = $date->format('Y');
		$slug = $item->post_name;
		
		echo sprintf(
			'<tr data-slug="%s" data-year="%s">',
			esc_attr($slug),
			esc_attr($year)
		);
		$this->single_row_columns($item);
		echo '</tr>';
	}
}