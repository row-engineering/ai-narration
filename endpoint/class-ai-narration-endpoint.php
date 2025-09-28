<?php

if ( ! defined( 'WPINC' ) ) {
	die;
}

if (!function_exists('wp_read_audio_metadata')) {
	require_once(ABSPATH . 'wp-admin/includes/media.php');
}

class AI_Narration_Endpoint {

	private $api_service;
	private $api_key;
	private $api_url;
	private $model;
	private $voice;
	private $debug;

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct($options = array()) {
		$this->api_service = get_option( 'ai_narration_service_vendor' )[0];
		if (empty($this->api_service) || $this->api_service === 'none') {
			$this->apply_response_and_exit(403, 'Forbidden. Missing service information.');
		}

		$this->api_key     = get_option( 'ai_narration_service_api_key' );
		$this->api_url     = AI_NARRATION_SERVICES[$this->api_service]['endpoint'];
		$this->model       = AI_NARRATION_SERVICES[$this->api_service]['models'][0]['model'];
		$this->voice       = get_option( 'ai_narration_voice' )[0];

		$this->debug = false;
		if (!empty($options)) {
			if (isset($options['debug'])) {
				$this->debug = true;
			}
		}
	}

	/**
	 *	Listen
 	 *
	 *	Public call to indicate which post should have audio generated
	 */
	public function listen() {
		if ( !$this->api_url ) {
			$this->apply_response_and_exit(403, 'Forbidden. Must set valid Narration Service.');
		}

		if ( !$this->api_key ) {
			$this->apply_response_and_exit(403, 'Forbidden. Invalid or missing API key.');
		}

		$post_data = $this->get_post_data();
		if ( !empty($post_data) ) {
			if ( isset($post_data['text']) && !empty($post_data['text']) ) {
				$audio_dir   = $this->get_directory($post_data);
				$audio_index = $post_data['segment'];
				$audio_text  = $post_data['text'];

				$response = $this->request_conversion($audio_text, $audio_index, $audio_dir);

				if ($response['status'] === 200) {
					$this->update_post_index($post_data, $audio_index, $audio_dir);
				}

				$this->apply_response_and_exit($response['status'], $response['message']);
			}
		}
	}

	/**
	 * Get narration directory
	 *
	 * Returns the path to the directory for a narration
	 * Each narration has its own directory given the number if audio files
	 * that coudl be generated.
	 *
	 * If the directory does not exits, it is created.
	 */
	private function get_directory($data) {
		$date = DateTime::createFromFormat('Y-m-d H:i:s', $data['date']);
		$year = $date->format('Y');
		$dir = "/$year/{$data['slug']}";
		if ( !is_dir(AI_NARRATION_PATH . $dir) ) {
			if ( !mkdir(AI_NARRATION_PATH . $dir, 0777, true) ) {
				$dir = false;
			}
		}
		return $dir;
	}

	/**
	 * Update Index
	 *
	 * The index contains all basic story meta data from a WP Post
	 * and references to all audio files required for a complete narration.
	 *
	 */
	private function update_post_index($data, $audio_index, $audio_dir) {
		$index_data = array();
		$index_file = AI_NARRATION_PATH . "{$audio_dir}/index.json";
		$audio_path = sprintf('%s/audio_%02d.mp3', $audio_dir, $audio_index);

		$curr_index_data = false;
		$query_in_progress = false;
		if ( file_exists($index_file) ) {
			$curr_index_data = json_decode(file_get_contents($index_file), true);
			if ( $curr_index_data['audio']['total'] > count($curr_index_data['audio']['tracks']) ) {
				$query_in_progress = true;
			}
		}

		$timestamp = microtime(true);
		$created = number_format($timestamp, 6, '.', '');

		$index_data = $data;
		$index_data['audio'] = array(
			'service'  => AI_NARRATION_SERVICES[$this->api_service]['name'],
			'model'    => $this->model,
			'voice'    => $this->voice,
			'created'  => $created,
			'total'    => $data['total'],
			'duration' => $query_in_progress ? $curr_index_data['audio']['duration'] : array(),
			'tracks'   => $query_in_progress ? $curr_index_data['audio']['tracks'] : array()
		);
		unset($index_data['text']);
		unset($index_data['total']);
		unset($index_data['segment']);

		//	Update: track path
		$index_data['audio']['tracks'][] = wp_normalize_path(AI_NARRATION_DIR . $audio_path);
		sort($index_data['audio']['tracks']);

		//	Update: durations
		$metadata = wp_read_audio_metadata( AI_NARRATION_PATH . $audio_path );
		$index_data['audio']['duration'][$audio_index - 1] = $metadata['length'];
		ksort($index_data['audio']['duration']);

		//	Save
		$result = file_put_contents($index_file, json_encode($index_data, JSON_PRETTY_PRINT));
		if ($result === false) {
			$this->apply_response_and_exit(500, 'Failed to create file.');
		}

		return $index_data;
	}

	private function apply_response_and_exit($code = 200, $message = 'Nothing happened.') {
		if ($this->debug) {
			$message .= ' Debug enabled.';
		}

		if ($code === 200) {
			wp_send_json_success([ 'message' => $message ]);
		} else {
			wp_send_json_error([ 'message' => $message ], $code);
		}
	}

	/**
	 *	Get POST data
 	 *
	 *	Examine incoming POST data and, if it is valid, pass it back.
	 */
	private function get_post_data() {
		$data = array();

		if ($this->debug || $_SERVER['REQUEST_METHOD'] === 'POST') {
			if ($this->debug) {
				$post_data = file_get_contents('last-request-post.txt');
			} else {
				$post_data = file_get_contents('php://input');
				file_put_contents('last-request-post.txt', $post_data);
			}
			if (strpos($post_data, AI_NARRATION_KEY) !== false) {
				$data = json_decode($post_data, true);
				unset($data['key']);
			} else {
				$this->apply_response_and_exit(403, 'Forbidden.');
			}
		} else {
			$this->apply_response_and_exit(405, 'Method Not Allowed. Only POST requests are supported.');
		}

		return $data;
	}

	/**
	 *	Request conversion
	 *
	 *	Request audio from TTS service. If successful, save it as a MP3.
	 */
	private function request_conversion($audio_text, $audio_index, $audio_dir) {

		$data = [
			'model' => $this->model,
			'input' => $audio_text,
			'voice' => $this->voice
		];

		$response = $this->send_request($data);

		if ($response['status'] === 200) {
			$file_name = AI_NARRATION_PATH . sprintf('%s/audio_%02d.mp3', $audio_dir, $audio_index);
			file_put_contents($file_name, $response['data']);
		}

		return $response;
	}

	/**
	 *	Send Request
	 */
	private function send_request($data) {

		if ($this->debug) {
			$sample_file = dirname(__DIR__) . '/assets/test/sample.mp3';
			$response = file_get_contents( $sample_file );
		} else {

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->api_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				'Authorization: Bearer ' . $this->api_key,
				'Content-Type: application/json',
			]);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

			$api_response = curl_exec($ch);

			// get the response code
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

			if ($http_code === 200) {
				$response = array(
					'status'  => 200,
					'message' => 'Success, young Padawan.',
					'data'    => $api_response
				);
			} else {
				$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
				$body = json_decode(substr($api_response, $header_size));
				$response = array(
					'status'  => $http_code,
					'message' => "Request to TTS service returned an error: {$body->error->type}: {$body->error->message}"
				);
			}

			curl_close($ch);
		}

		return $response;
	}

}
