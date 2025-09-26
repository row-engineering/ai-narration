<?php

$page_type = 'admin.php';
$menu_nested = get_option( 'ai_narration_menu_nested' );
if ($menu_nested) {
	$page_type = 'options-general.php';
}

$submenu_html = '';
foreach($this->pages as $key => $page) {
	$activeClass = ($page_name === $key) ? ' is-active' : '';
	$submenu_html .= implode("", array(
		"\n<a class=\"ain-tab {$activeClass}\" href=\"/wp-admin/{$page_type}?page={$this->plugin_name}-{$key}\">",
			"{$page['title']}",
		"</a>"
	));
}

?>
<style>


</style>

<div class="ain-admin-page">

	<div class="ain-admin-toolbar">
		<div class="ain-admin-toolbar-inner">
			<div class="ain-nav-wrap">
				<a href="/wp-admin/admin.php?page=ain-settings" class="ain-logo">
					<img src="/wp-content/plugins/<?= $this->plugin_name; ?>/assets/images/ain-logo.svg" alt="AI Narration plugin logo" title="AI Narration">
					<!-- <div class="ain-pro-label">PRO</div> -->
				</a>
				<h2>AI Narration</h2>
				<?= $submenu_html; ?>
			</div>
			<div class="ain-nav-promo-wrap">
				<a href="https://restofworld.org/?utm_source=acf_plugin&amp;utm_medium=referral&amp;utm_campaign=bx_prod_referral&amp;utm_content=acf_pro_plugin_topbar_logo" target="_blank" class="ain-nav-row-logo">
					<span>Brought to you by</span>
					<img src="/wp-content/plugins/<?= $this->plugin_name; ?>/assets/images/row-logo.svg" alt="Rest of World">
					<!-- <span>and many contributors!</span> -->
				</a>
			</div>
		</div>
	</div>

	<div class="ain-headerbar">
		<h1 class="ain-page-title"><?= $this->pages[$page_name]['title']; ?></h1>
	</div>

	<div class="wrap">
		<?php
		if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ){
			$this->admin_notice();
		} ?>
	</div>