<?php

/**
 * Provide an admin view for the plugin
 *
 * This file is used to mark up the admin-facing aspects of the plugin.
 *
 * @link       https://https://github.com/row-engineering/
 * @since      1.0.0
 *
 * @package    AI_Narration
 * @subpackage AI_Narration/admin/partials
 */

/*
	TO DO:
		- Exclusions
			- cut-off date for narrating old posts
			- custom tag or taxonomy flag for excluding a post from narration
		- Settings
			- skip adding CSS/JS?
			- custom intro & outro text
			- cut-off lengths (min & max word-count for a narration)
*/
?>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap limit-width">
	<form method="POST" action="options.php">
		<?php
			settings_fields( 'ain-settings' );
			do_settings_sections( 'ain-settings' );
			submit_button();
		?>
	</form>
</div>
