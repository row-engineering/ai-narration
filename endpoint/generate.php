<?php

/**
 * This is a publicaly accessible endpoint used by the plugin to independenlty generate narrations.
 */

if (!defined('AI_NARRATION_BASE_PATH')) {
	define('AI_NARRATION_BASE_PATH', realpath($_SERVER['DOCUMENT_ROOT']));
}

require AI_NARRATION_BASE_PATH . '/wp-load.php';

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

$plugin        = new AI_Narration();
$plugin_public = new AI_Narration_Public( $plugin->get_plugin_name(), $plugin->get_version() );
$response      = $plugin_public->request_new_audio('publish', 'draft', $post);

// echo '<pre style="white-space: break-spaces">';
// var_dump(json_encode($response));
// echo '</pre>';
