<?php
	/**
	* Plugin Name: Tracking Script Manager
	* Plugin URI: http://red8interactive.com
	* Description: A plugin that allows you to add tracking scripts to your site.
	* Version: 1.0.3
	* Author: Red8 Interactive
	* Author URI: http://red8interactive.com
	* License: GPL2
	*/
 
	/*  
		Copyright 2014 Red8 Interactive  (email : james@red8interactive.com) 
	
		This program is free software; you can redistribute it and/or
		modify it under the terms of the GNU General Public License
		as published by the Free Software Foundation; either version 2
		of the License, or (at your option) any later version.
		
		This program is distributed in the hope that it will be useful,
		but WITHOUT ANY WARRANTY; without even the implied warranty of
		MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
		GNU General Public License for more details.
		
		You should have received a copy of the GNU General Public License
		along with this program; if not, write to the Free Software
		Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
	*/
	
	if( !class_exists('Tracking_Scripts') ) {
		
		class Tracking_Scripts {
		
			function __construct() {
				self::define_constants();
				self::load_hooks();
			}
	
			/**
			* Defines plugin constants
			*/
			public static function define_constants() {
				define('TRACKING_SCRIPT_PATH', plugins_url( ' ', __FILE__ ) ); 
				
				define('WP_TRACKING_SCRIPTS_OPTION_GROUP', 'tracking_scripts_options' );
				define('WP_HEADER_TRACKING_SCRIPT', 'header_tracking_script_code' );
				define('WP_FOOTER_TRACKING_SCRIPT', 'footer_tracking_script_code' );
				define('WP_NEW_HEADER_TRACKING_SCRIPT', 'new_header_tracking_script_code' );
				define('WP_NEW_FOOTER_TRACKING_SCRIPT', 'new_footer_tracking_script_code' );
			}
			
			public static function load_hooks() {
				add_action('wp_head', array(__CLASS__, 'find_header_tracking_codes'));
				
				add_action('wp_footer', array(__CLASS__, 'find_footer_tracking_codes'));
				
				add_action('admin_menu', array(__CLASS__, 'tracking_scripts_create_menu'));
				
				add_action('admin_enqueue_scripts', array(__CLASS__, 'tracking_scripts_admin_scripts'));
				
				add_action('admin_init', array(__CLASS__, 'initialize_admin_posts'));
			}
	
			/*************************************************
			 * Front End
			**************************************************/
		
			/**
			 * 
			 *
			 * @param int $post_id The ID of the post being saved.
			 */
			public static function find_header_tracking_codes() {
				$header_scripts = unserialize(get_option(WP_HEADER_TRACKING_SCRIPT));
				
				if($header_scripts) {
					foreach($header_scripts as $script) {
						if($script->active) {
							echo iconv('cp1252', 'UTF-8', html_entity_decode(esc_attr($script->script_code), ENT_QUOTES, 'cp1252'));
						}
					}
				}
			}
			
			public static function find_footer_tracking_codes() {
				$footer_scripts = unserialize(get_option(WP_FOOTER_TRACKING_SCRIPT));
				
				if($footer_scripts) {
					foreach($footer_scripts as $script) {
						if($script->active) {
							echo iconv('cp1252', 'UTF-8', html_entity_decode(esc_attr($script->script_code), ENT_QUOTES, 'cp1252'));
						}
					}
				}
			}

			/*************************************************
			 * Admin Area
			**************************************************/
			
			public static function tracking_scripts_create_menu() {
				add_menu_page('Tracking Scripts', 'Tracking Scripts', 'administrator', __FILE__, array(__CLASS__, 'tracking_options'), '');
				add_action('admin_init', array(__CLASS__, 'register_tracking_scripts_settings'));
			}
			
			public static function register_tracking_scripts_settings() {
				register_setting( WP_TRACKING_SCRIPTS_OPTION_GROUP, WP_HEADER_TRACKING_SCRIPT, 'esc_textarea' );
				register_setting( WP_TRACKING_SCRIPTS_OPTION_GROUP, WP_FOOTER_TRACKING_SCRIPT, 'esc_textarea' );
			}
			
			public static function tracking_scripts_admin_tabs( $current = 'add_new' ) {
				$tabs = array('add_new' => 'Add New', 'existing' => 'Existing');
				echo '<div id="tracking_scripts_admin"><br></div>';
				echo '<h2 style="font-size: 22px; font-weight: bold; margin: 10px 0 40px;">Tracking Scripts</h2>';
				echo '<h2 class="nav-tab-wrapper">';
				foreach($tabs as $tab => $name) {
					$class = ($tab == $current) ? ' nav-tab-active' : '';
					echo "<a class='nav-tab$class' href='?page=tracking-script-manager/tracking-scripts.php&tab=$tab'>$name</a>";
				}
				echo '</h2>';
			}
			
			public static function tracking_options() {
				global $pagenow;
				$settings = get_option('tracking_scripts_settings');
				
				if(isset($_GET['tab'])) {
					self::tracking_scripts_admin_tabs($_GET['tab']);
					$pagenow = $_GET['tab']; 
				} else {
					self::tracking_scripts_admin_tabs('add_new'); 
					$pagenow = 'add_new';
				}
				?>
				<div class="wrap tracking_scripts_wrap">
					<form method="post" action="<?php echo get_admin_url(); ?>admin-post.php">
						<?php settings_fields(WP_TRACKING_SCRIPTS_OPTION_GROUP); ?>
						<?php do_settings_sections(WP_TRACKING_SCRIPTS_OPTION_GROUP); ?>
						<?php if($pagenow == 'add_new') { ?>
					        <div class="script_section">
						        <h2>Header Scripts</h2>
						        <div class="add_tracking_scripts">
						        	<label>Name:</label>
						        	<input type="text" name="new_header_script_name"/>
						        	<textarea rows="5" cols="40" name="<?php echo WP_NEW_HEADER_TRACKING_SCRIPT; ?>" style="font-weight: normal;"></textarea>
						        </div>
					        </div>
					        <div class="script_section">
						        <h2>Footer Scripts</h2>
						        <div class="add_tracking_scripts">
						        	<label>Name:</label>
						        	<input type="text" name="new_footer_script_name"/>
						        	<textarea rows="5" cols="40" name="<?php echo WP_NEW_FOOTER_TRACKING_SCRIPT; ?>" style="font-weight: normal;"></textarea>
						        </div>
					        </div>
						    <?php submit_button('Add Scripts', 'primary', 'save_new_tracking_codes'); ?>
						    <input type="hidden" name="action" value="save_new_tracking_codes">
					    <?php } else { ?>
					    	<div class="script_section">
						        <h2>Header Scripts</h2>
						        <?php $header_scripts = unserialize(get_option(WP_HEADER_TRACKING_SCRIPT)); $i = 1; ?>
						        <div class="tracking_scripts">
						        	<?php foreach($header_scripts as $script) { ?>
						        	<div class="tracking_script">
						        		<i class="fa fa-sort" title="Drag to Sort"></i>
						        		<p><?php echo $i; ?></p>
						        		<div class="script_info">
						        			<input type="text" name="header_script_<?php echo $i; ?>_name" value="<?php echo $script->script_name; ?>" readonly="readonly">
											<input type="text" name="header_script_<?php echo $i; ?>_code" value="<?php echo $script->script_code; ?>" readonly="readonly">
						        		</div>
						        		<i class="active_tracking fa <?php if($script->active === true) { echo 'fa-check-circle'; } else { echo 'fa-circle-o'; } ?>" title="<?php if($script->active === true) { echo 'Deactivate Script'; } else { echo 'Activate Script'; } ?>"></i>
						        		<i class="edit_tracking fa fa-edit" title="Edit Script"></i>
						        		<i class="delete_tracking fa fa-times" title="Delete Script"></i>
						        		<input type="hidden" class="script_order" name="header_script_<?php echo $i; ?>_order" value="<?php echo $i; ?>">
						        		<input type="hidden" class="script_active" name="header_script_<?php echo $i; ?>_active" value="<?php if($script->active === true) { echo 'true'; } else { echo 'false'; } ?>">
						        		<input type="hidden" class="script_exists" name="header_script_<?php echo $i; ?>_exists" value="true">
						        	</div>
						        	<?php $i++; } ?>
						        </div>
						    </div>
						    <div class="script_section">
						        <h2>Footer Scripts</h2>
						        <?php $footer_scripts = unserialize(get_option(WP_FOOTER_TRACKING_SCRIPT)); $i = 1; ?>
						        <div class="tracking_scripts">
						        	<?php foreach($footer_scripts as $script) { ?>
						        	<div class="tracking_script">
						        		<i class="fa fa-sort" title="Drag to Sort"></i>
						        		<p><?php echo $i; ?></p>
						        		<div class="script_info">
						        			<input type="text" name="footer_script_<?php echo $i; ?>_name" value="<?php echo $script->script_name; ?>" readonly="readonly">
											<input type="text" name="footer_script_<?php echo $i; ?>_code" value="<?php echo $script->script_code; ?>" readonly="readonly">
						        		</div>
						        		<i class="active_tracking fa <?php if($script->active === true) { echo 'fa-check-circle'; } else { echo 'fa-circle-o'; } ?>" title="<?php if($script->active === true) { echo 'Deactivate Script'; } else { echo 'Activate Script'; } ?>"></i>
						        		<i class="edit_tracking fa fa-edit" title="Edit Script"></i>
						        		<i class="delete_tracking fa fa-times" title="Delete Script"></i>
						        		<input type="hidden" class="script_order" name="footer_script_<?php echo $i; ?>_order" value="<?php echo $i; ?>">
						        		<input type="hidden" class="script_active" name="footer_script_<?php echo $i; ?>_active" value="<?php if($script->active === true) { echo 'true'; } else { echo 'false'; } ?>">
						        		<input type="hidden" class="script_exists" name="footer_script_<?php echo $i; ?>_exists" value="true">
						        	</div>
						        	<?php $i++; } ?>
						        </div>
						    </div>
						    <?php submit_button('Update Scripts', 'primary', 'update_tracking_codes'); ?>
						    <input type="hidden" name="action" value="update_tracking_codes">
					    <?php } ?>
					</form>
				</div>
				<?php 
				
			} 
			
			public static function tracking_scripts_admin_scripts() {
				wp_enqueue_script('jquery');
				wp_enqueue_script('jquery-ui-sortable');
				
				wp_register_style('tracking-scripts-main', plugins_url('/css/main.css', __FILE__));
				wp_enqueue_style('tracking-scripts-main');
				
				wp_enqueue_script( 'tracking_script_js', plugin_dir_url(__FILE__) . '/js/main.js', array(), '', true );
				
				
			}
			
			
			public static function initialize_admin_posts() {
				add_action('admin_post_save_new_tracking_codes', array(__CLASS__, 'save_new_tracking_codes')); // If the user is logged in
				add_action('admin_post_nopriv_save_new_tracking_codes', array(__CLASS__, 'save_new_tracking_codes')); // If the user in not logged in
				
				add_action('admin_post_update_tracking_codes', array(__CLASS__, 'update_tracking_codes')); // If the user is logged in
				add_action('admin_post_nopriv_update_tracking_codes', array(__CLASS__, 'update_tracking_codes')); // If the user in not logged in
			}
			
			
			public static function save_new_tracking_codes() {
				$header_scripts = unserialize(get_option(WP_HEADER_TRACKING_SCRIPT));
				$footer_scripts = unserialize(get_option(WP_FOOTER_TRACKING_SCRIPT));
				
				if(!$header_scripts) {
					$header_scripts = array();
				}
				
				if(!$footer_scripts) {
					$footer_scripts = array();
				}
				
				if($_POST['new_header_script_name']) {
					$tracking = new Tracking_Script();
					$tracking->script_name = sanitize_text_field($_POST['new_header_script_name']);
					$tracking->script_code = stripslashes(esc_textarea($_POST[WP_NEW_HEADER_TRACKING_SCRIPT]));
					$tracking->active = true;
					$tracking->order = count($header_scripts);
					$header_scripts[] = $tracking;
					update_option(WP_HEADER_TRACKING_SCRIPT, serialize($header_scripts));
				}
				
				if($_POST['new_footer_script_name']) {
					$tracking = new Tracking_Script();
					$tracking->script_name = sanitize_text_field($_POST['new_footer_script_name']);
					$tracking->script_code = stripslashes(esc_textarea($_POST[WP_NEW_FOOTER_TRACKING_SCRIPT]));
					$tracking->active = true;
					$tracking->order = count($footer_scripts);
					$footer_scripts[] = $tracking;
					update_option(WP_FOOTER_TRACKING_SCRIPT, serialize($footer_scripts));
				}
				
				wp_redirect(get_admin_url().'admin.php?page=tracking-script-manager/tracking-scripts.php&tab=existing');
				exit();
			}
			
			function update_tracking_codes() {
				$header_scripts = unserialize(get_option(WP_HEADER_TRACKING_SCRIPT));
				$footer_scripts = unserialize(get_option(WP_FOOTER_TRACKING_SCRIPT));
				
				$i = 1;
				foreach($header_scripts as $script) {
					if($_POST['header_script_'.$i.'_name']) {
						$script->script_name = sanitize_text_field($_POST['header_script_'.$i.'_name']);
					}
					if($_POST['header_script_'.$i.'_code']) {
						$script->script_code = stripslashes(esc_textarea($_POST['header_script_'.$i.'_code']));
					}
					if($_POST['header_script_'.$i.'_active']) {
						if($_POST['header_script_'.$i.'_active'] === 'false') {
							$script->active = false;
						} else {
							$script->active = true;
						}
					}
					if($_POST['header_script_'.$i.'_order']) {
						$order = filter_input(INPUT_POST, 'header_script_'.$i.'_order', FILTER_VALIDATE_INT);
						if(is_int($order)) { 
							$script->order = $order;
						}
					}
					if($_POST['header_script_'.$i.'_exists']) {
						if($_POST['header_script_'.$i.'_exists'] === 'false') {
							unset($header_scripts[$i-1]);
						}
					}
					
					$i++;
				}
				
				$i = 1;
				foreach($footer_scripts as $script) {
					if($_POST['footer_script_'.$i.'_name']) {
						$script->script_name = sanitize_text_field($_POST['footer_script_'.$i.'_name']);
					}
					if($_POST['footer_script_'.$i.'_code']) {
						$script->script_code = stripslashes(esc_textarea($_POST['footer_script_'.$i.'_code']));
					}
					if($_POST['footer_script_'.$i.'_active']) {
						if($_POST['footer_script_'.$i.'_active'] === 'false') {
							$script->active = false;
						} else {
							$script->active = true;
						}
					}
					if($_POST['footer_script_'.$i.'_order']) {
						$order = filter_input(INPUT_POST, 'footer_script_'.$i.'_order', FILTER_VALIDATE_INT);
						if(is_int($order)) { 
							$script->order = $order;
						}
					}
					if($_POST['footer_script_'.$i.'_exists']) {
						if($_POST['footer_script_'.$i.'_exists'] === 'false') {
							unset($footer_scripts[$i-1]);
						}
					}
					
					$i++;
				}
				
				usort($header_scripts, array(__CLASS__, 'compare_order'));
				usort($footer_scripts, array(__CLASS__, 'compare_order'));
				
				
				update_option(WP_HEADER_TRACKING_SCRIPT, serialize($header_scripts));
				update_option(WP_FOOTER_TRACKING_SCRIPT, serialize($footer_scripts));
				
				wp_redirect(get_admin_url().'admin.php?page=tracking-script-manager/tracking-scripts.php&tab=existing');
				exit();
			}	
			
			public static function compare_order($a, $b) {
				return ($a->order < $b->order) ? -1 : 1;
			}
		}

		$class['Tracking_Scripts'] = new Tracking_Scripts();
	}
		
	class Tracking_Script {
		public $script_name;
		public $script_code;
		public $active;
		public $order;
	
		function __construct() {
			
		}
	}
?>