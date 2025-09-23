<?php

// /wp-content/plugins/ai-narration/endpoint/generate.php

if (!defined('BASE_PATH')) {
	define('BASE_PATH', realpath($_SERVER['DOCUMENT_ROOT']));
}
require BASE_PATH . '/wp-load.php';

$post_id = $_GET['p'];
if (!$post_id) {
	echo 'Make sure URL includes a post ID, e.g. generate.php?p=76333';
	return;
}

$post = get_post($post_id);
if (!$post) {
	echo "No post found matching the ID: $post_id";
	return;
}

global $plugin_public;

$response = $plugin_public->request_new_audio('publish', 'draft', $post);
// echo '<pre style="white-space: break-spaces">';
// var_dump(json_encode($response));
// echo '</pre>';