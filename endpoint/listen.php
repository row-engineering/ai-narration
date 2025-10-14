<?php
/*
	Get responses
	curl -X POST -d "{'data':'hello'}" http://restofworld.test/wp-content/plugins/ai-narration/endpoint/listen.php
*/

header('Content-Type: application/json; charset=utf-8');

$base_path = realpath($_SERVER['DOCUMENT_ROOT']);

require $base_path . '/wp-load.php';
require dirname(__DIR__) . '/endpoint/class-ai-narration-endpoint.php';

function listen() {
	$endpoint = new AI_Narration_Endpoint();
	$endpoint->listen();
}
listen();
