<?php
/*
Plugin Name: Lnk Juice Tracking
Plugin URI: hhttp://www.liljedahl.me/projekt/wp-lnkjuice/
Description: Simple plugin that adds your Lnk Juice tracking code to the head tag. When activated you must <a href="plugins.php?page=lnkjuice-config">enter your Lnk Juice Tracking Code</a> under the Plugins menu for it to work. If you do not yet have an account go get one at <a href="http://www.lnkjuice.com/">LnkJuice.com</a>!
Author: Markus Liljedahl 
Version: 1.2.1
Author URI: http://www.liljedahl.me/
*/

// For backwards compatibility, esc_attr_e was added in 2.8 and attribute_escape is from 2.8 marked as deprecated.
if (! function_exists('esc_attr_e')) {
	function esc_attr_e( $text ) {
		return attribute_escape( $text );
	}
}

// The html code that goes in to the header
function add_LnkJuice_footer() {
	$code = get_option('lnkjuice_cached_code');
	
	if(!is_admin() && strlen($code) >= 32) {
		echo '<script type="text/javascript" src="http://stat.lnkjuice.com/lt.js"></script>
<script type="text/javascript">
<!--
   roza_hrefReplace('.$code.');
// -->
</script>' . PHP_EOL;
		echo PHP_EOL;
	}
}

// Prints the admin menu where it is possible to add the tracking code
function print_LnkJuice_management() {
	if (isset($_POST['submit'])) {
		if (!current_user_can('manage_options'))
			wp_die(__('You do not have sufficient permissions to manage options for this blog.'));
		
		$code = trim($_POST['lnkjuice_tracking_code']);
		
		if (strlen($code) > 0) {
			
			$identifyer = trim($_POST['lnkjuice_detailed_statistics']);
			$subdomain = $_POST['lnkjuice_disable_subdomain'];
	
			if ( empty($code) ) {
				delete_option('lnkjuice_tracking_code');
			} else {
				update_option('lnkjuice_tracking_code', $code);
			}
			
			if ( empty($identifyer) ) {
				delete_option('lnkjuice_detailed_statistics');
			} else {
				update_option('lnkjuice_detailed_statistics', $identifyer);
			}
			
			if ( empty($subdomain) ) {
				delete_option('lnkjuice_disable_subdomain');
			} else {
				update_option('lnkjuice_disable_subdomain', $subdomain);
			}

			cache_LnkJuice_code();

?>
<div id="message" class="updated fade"><p><strong><?php esc_attr_e('Options saved.'); ?></strong></p></div>
<?php
		
		}
	}
?>
<div class=wrap>
<?php screen_icon(); ?>
	<h2><?php esc_attr_e('Lnk Juice Settings', 'lnkjuice'); ?></h2>
	
	<p><?php _e('You need to enter your Lnk Juice Tracking Code here to get the plugin to work. If you do not yet have an account go get one at <a href="http://www.lnkjuice.com/">LnkJuice.com</a>!', 'lnkjuice'); ?></p>
	
	<p><?php _e('When you login at Lnk Juice go to the control panel and then get your <strong>Tracking ID</strong> wich is located under <strong>My tracking code</strong>. It should look similar this code:', 'lnkjuice'); ?></p>
	
	<p><code>abcdefghijklmnopqrstuvwxyz123456</code></p>
		
	<form method="post" action="">
	
		<table class="form-table">
			<tr valign="top">
			<th scope="row"><label for="lnkjuice_tracking_code"><?php esc_attr_e('Your Tracking ID', 'lnkjuice'); ?></label></th>
			<td><input name="lnkjuice_tracking_code" type="text" id="lnkjuice_tracking_code" value="<?php echo get_option('lnkjuice_tracking_code'); ?>" class="regular-text" maxlength="32" /><br /><span class="description"><?php esc_attr_e('Leave empty to remove', 'lnkjuice'); ?></span></td>
			<tr>

			<tr valign="top">
			<th scope="row"><label for=""><?php esc_attr_e('Detailed statistics identifier', 'lnkjuice'); ?></label></th>
			<td><input name="lnkjuice_detailed_statistics" type="text" id="lnkjuice_detailed_statistics" value="<?php echo get_option('lnkjuice_detailed_statistics'); ?>" class="regular-text" maxlength="100" /><br /><span class="description"><?php esc_attr_e('Use up to 100 characters, (a-z, 0-9 and .)', 'lnkjuice'); ?></span></td>
			</tr>

			<tr valign="top">
			<th scope="row"><label for=""><?php esc_attr_e('Disable subdomain conversion', 'lnkjuice'); ?></label></th>
			<td><input name="lnkjuice_disable_subdomain" type="checkbox" id="lnkjuice_disable_subdomain" value="false" <?php if ( get_option('lnkjuice_disable_subdomain') ) echo "checked=\"checked\""?>></td>
			</tr>
		</table>
		
		<p class="submit">
			<input type="submit" name="submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
		</p>

	</form>

</div>
<?php
}

function cache_LnkJuice_code()
{
	$code = get_option('lnkjuice_tracking_code');
	$identifyer = get_option('lnkjuice_detailed_statistics');
	$subdomain = get_option('lnkjuice_disable_subdomain');

	if ($code && $identifyer && $subdomain) {
		$fullCode = '"'.$code.'+'.$identifyer.'", '.$subdomain;
		update_option('lnkjuice_cached_code', $fullCode);
	} elseif ($code && $identifyer) {
		$fullCode = '"'.$code.'+'.$identifyer.'"';
		update_option('lnkjuice_cached_code', $fullCode);
	} elseif ($code) {
		$fullCode = '"'.$code.'"';
		update_option('lnkjuice_cached_code', $fullCode);
	} else {
		delete_option('lnkjuice_cached_code');
	}
}

function add_LnkJuice_admin_page() 
{
	if ( function_exists('add_submenu_page') )
		add_submenu_page('plugins.php', __('Lnk Juice Settings', 'lnkjuice'), __('Lnk Juice Tracking'), 'manage_options', 'lnkjuice-config', 'print_LnkJuice_management');
}

load_plugin_textdomain('lnkjuice', 'wp-content/plugins/' . dirname(plugin_basename(__FILE__)) . '/i18n');
add_action('wp_footer', 'add_LnkJuice_footer');
add_action('admin_menu', 'add_LnkJuice_admin_page');
?>
