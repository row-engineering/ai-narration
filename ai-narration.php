<?php

/**
 * @link              https://https://github.com/row-engineering/
 * @since             1.0.0
 * @package           AI_Narration
 *
 * @wordpress-plugin
 * Plugin Name:       AI Narration
 * Plugin URI:        https://github.com/row-engineering/ai-narration
 * Description:       AI Narration plugin for news sites
 * Version:           1.0.0
 * Author:            Anna Rasshivkina
 * Author URI:        https://https://github.com/row-engineering
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
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'AI_NARRATION_VERSION', '1.1.16' );

define( 'AI_NARRATION_BASENAME', plugin_basename( __FILE__ ) );

include_once dirname(__FILE__) . '/configuration.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-ai-narration-activator.php
 */
function activate_ai_narration() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ai-narration-activator.php';
	AI_Narration_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-ai-narration-deactivator.php
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

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_ai_narration() {

	$plugin = new AI_Narration();
	$plugin->run();

}
run_ai_narration();
