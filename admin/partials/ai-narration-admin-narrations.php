<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://https://github.com/row-engineering/
 * @since      1.0.0
 *
 * @package    AI_Narration
 * @subpackage AI_Narration/admin/partials
 */


$table = new Posts_Narration_List_Table();
$table->prepare_items();

?>

<div class="wrap">
	<h1>AI Narrations</h1>

	<div class="tablenav top">
		<div class="alignleft actions bulkactions">
			<button type="button" id="bulk-generate" class="button action">Generate Selected</button>
			<button type="button" id="bulk-delete" class="button action">Delete Selected</button>
		</div>
	</div>

	<form id="posts-narration-form" method="post">
		<?php
			$table->display();
		?>
	</form>
</div>
