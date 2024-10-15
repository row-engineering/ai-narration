<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://https://github.com/row-engineering/
 * @since             1.0.0
 * @package           AI_Narration
 *
 * @wordpress-plugin
 * Plugin Name:       AI Narration
 * Plugin URI:        https://https://github.com/row-engineering/ai-audio
 * Description:       AI Narration plugin for news sites
 * Version:           1.0.0
 * Author:            Anna Rasshivkina
 * Author URI:        https://https://github.com/annafractuous/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ai-audio
 * Domain Path:       /narrations
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'AI_NARRATION_VERSION', '1.0.0' );

define( 'AI_NARRATION_BASENAME', plugin_basename( __FILE__ ) );
define( 'AI_NARRATION_PATH', plugin_dir_path(__FILE__) );

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
