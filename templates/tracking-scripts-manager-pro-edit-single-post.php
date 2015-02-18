<?php 
	if ( ! defined( 'ABSPATH' ) ) { 
    	exit; // Exit if accessed directly
	}
?>
<div class="script_section">
<h2>Page/Post Scripts</h2>
<?php $page_scripts = unserialize(get_option(WP_PAGE_TRACKING_SCRIPT)); $i = 1; ?>
<?php
	$page_scripts_array = array();
	foreach($page_scripts as $script) {
		$page_scripts_array[$script->page_id][$script->location][] = $script;
	}
?>
<div class="tracking_scripts_page_right_side">
	<select id="tracking_script_single_page">
		<option value="none">Choose Page/Post</option>
		<?php foreach($page_scripts_array as $page => $scripts) { ?>
			<?php $script_page = get_post($page); ?>
			<option value="<?php echo $page; ?>"><?php echo $script_page->post_title; ?></option>
		<?php } ?>
	?>
	</select>
	<div class="tracking_scripts_page_content">
    	
	</div>
</div>
</div>
<?php submit_button('Update Scripts', 'primary', 'update_page_tracking_codes'); ?>
<input type="hidden" name="action" value="update_page_tracking_codes">