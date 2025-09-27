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

<div class="wrap" id="ain-page-narrations">
	<div class="tablenav top hide-if-no-js">
		<!-- <div class="alignleft actions bulkactions">
			<button type="button" id="bulk-generate" class="button action">Generate Selected</button>
			<button type="button" id="bulk-delete" class="button action">Delete Selected</button>
		</div> -->

		<div class="alignleft actions bulkactions">
			<label for="bulk-action-selector" class="screen-reader-text">Select bulk action</label>
			<select name="action" id="bulk-action-selector">
				<option value="-1">Bulk actions</option>
				<option value="generate">Generate</option>
				<option value="delete">Delete</option>
			</select>
			<button type="button" id="bulk-apply" class="button action">Apply</button>
		</div>

		<div class="alignright">
			<span id="ain-status">&nbsp;</span>
		</div>
	</div>

	<form id="posts-narration-form" method="post">
		<?php
			$table->display();
		?>
	</form>
</div>
