<?php

if (!defined('AI_NARRATION_BASE_PATH')) {
	define('AI_NARRATION_BASE_PATH', realpath($_SERVER['DOCUMENT_ROOT']));
}

define('AI_NARRATION_KEY',  'fskjdhg8765fs!');
// define('AI_NARRATION_KEY',  ain_get_secret_key() );

define('AI_NARRATION_DIR',  '/wp-content/narrations');
define('AI_NARRATION_PATH', AI_NARRATION_BASE_PATH . AI_NARRATION_DIR);

/* 
    Services

    Reference:
    - https://platform.openai.com/docs/guides/text-to-speech/quickstart
    - https://learn.microsoft.com/en-us/azure/ai-services/speech-service/index-text-to-speech
*/

define('AI_NARRATION_SERVICES', array(
	'openai' => array(
		'name' => 'OpenAI TTS',
		'models' => array(
			array(
				'model' => 'tts-1',
				'description' => 'Optimized for speed',
			),
			array(
				'model' => 'tts-1-hd',
				'description' => 'Optimized for quality',
			)
		),
		'voices' => array(
			'alloy'   => 'Alloy',
			'echo'    => 'Echo',
			'fable'   => 'Fable',
			'onyx'    => 'Onyx',
			'nova'    => 'Nova',
			'shimmer' => 'Shimmer',
		),
		'endpoint'      => 'https://api.openai.com/v1/audio/speech',
		'documentation' => 'https://platform.openai.com/docs/guides/text-to-speech',
		'updated'       => '2024-10-18'
	),

	// 'azure' => array(
	// 	'name' => 'Azure AI Speech',
	// 	'models' => array(
	// 		array(
	// 			'model' => 'Neural',
	// 			'description' => 'Standard option',
	// 		)
	// 	),
	// 	'voices' => array(
	// 		'en-US-AmandaMultilingualNeural' => 'Amanda',
	// 		'en-US-NancyMultilingualNeural'  => 'Nancy',
	// 		'en-US-LewisMultilingualNeural'  => 'Lewis',
	// 		'en-US-AndrewMultilingualNeural' => 'Andrew',
	// 		'en-US-AvaMultilingualNeural'    => 'Ava'
	// 	),
	// 	'endpoint'      => 'https://example.com/v1/audio/speech',
	// 	'documentation' => 'https://learn.microsoft.com/en-us/azure/ai-services/speech-service/text-to-speech',
	// 	'updated'       => '2024-10-18'
	// )
));
