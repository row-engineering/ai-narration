<?php

// /wp-content/plugins/ai-narration/endpoint/generate.php

if (!defined('BASE_PATH')) {
	define('BASE_PATH', realpath($_SERVER['DOCUMENT_ROOT']));
}
require BASE_PATH . '/wp-load.php';

$post_id = $_GET['p'];

if (!$post_id) {
	return;
}

$post = get_post($post_id);

$plugin = new AI_Narration();
$plugin_public = new AI_Narration_Public( $plugin->get_plugin_name(), $plugin->get_version() );

$response = $plugin_public->request_new_audio('publish', 'draft', $post);
error_log(json_encode($response));