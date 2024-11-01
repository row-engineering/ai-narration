ai-narration<?php

/**
 * Fired during plugin activation
 *
 * @link       https://https://github.com/row-engineering/
 * @since      1.0.0
 *
 * @package    AI_Narration
 * @subpackage AI_Narration/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    AI_Narration
 * @subpackage AI_Narration/includes
 */
class AI_Narration_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		/**
		 * Setup required directories
		 */
		$uploads_dir = AI_NARRATION_PATH;
		if (!file_exists($uploads_dir)) {
			mkdir($uploads_dir, 0755, true);
			$h = fopen($uploads_dir . '/index.html', 'w');
			fclose($h);
		}

	}

}
