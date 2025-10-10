<?php

/**
 * @link              https://https://github.com/row-engineering/
 * @since             1.0.0
 * @package           AI_Narration
 *
 * @wordpress-plugin
 * Plugin Name:       AI Narration
 * Plugin URI:        https://github.com/row-engineering/ai-narration
 * Description:       Generate natural-sounding audio for your posts, making your content more accessible and engaging for readers.
 * Version:           1.0.0
 * Author:            Anna Rasshivkina, Michael Donohoe
 * Author URI:        https://github.com/row-engineering
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ai-audio
 * Domain Path:       /narrations
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 */
define( 'AI_NARRATION_VERSION', '1.1.7' );

define( 'AI_NARRATION_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Creates a unique verification token to prevent unauthorized API responses.
 * Narration generation can fail if this changes mid-request but that should be very very rare.
 */
function ain_get_secret_key() {
	$k = get_transient('ain_secret_key');
	if ($k) return $k;
	$k = bin2hex(random_bytes(32));
	set_transient('ain_secret_key', $k, DAY_IN_SECONDS * 14);
	return (string) $k;
}

include_once dirname(__FILE__) . '/configuration.php';

/**
 * Plugin activation.
 */
function activate_ai_narration() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ai-narration-activator.php';
	AI_Narration_Activator::activate();
}

/**
 * Plugin deactivation.
 */
function deactivate_ai_narration() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ai-narration-deactivator.php';
	AI_Narration_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_ai_narration' );
register_deactivation_hook( __FILE__, 'deactivate_ai_narration' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-ai-narration.php';

function run_ai_narration() {

	$plugin = new AI_Narration();
	$plugin->run();

}
run_ai_narration();
