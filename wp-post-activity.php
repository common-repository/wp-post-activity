<?php
/*
Plugin Name: WP-Post-Activity
Plugin URI: http://kovshenin.com/wordpress/plugins/wp-post-activity/
Description: Displays an icon indicating post activity (views/comments)
Author: Konstantin Kovshenin
Version: 0.1
Author URI: http://kovshenin.com/

*/

function wp_post_activity_init() {
	add_action("admin_menu", "wp_post_activity_menu");
}

function wp_post_activity_menu() {
	add_options_page('WP Post Activity Options', 'Post Activity', 8, __FILE__, 'wp_post_activity_options');
}

add_action("init", "wp_post_activity_init");

function wp_post_activity_options() {
	$options = get_option("wp_post_activity");
	
	if (!isset($options["highest"]) || !isset($options["lowest"]))
	{
		$options["highest"] = 0.5;
		$options["lowest"] = 0.01;
		update_option("wp_post_activity", $options);
	}
	
	if (isset($_POST["wp-post-activity-submit"]))
	{
		$options["highest"] = $_POST["highest"];
		$options["lowest"] = $_POST["lowest"];
		update_option("wp_post_activity", $options);
	}
	$highest = $options["highest"];
	$lowest = $options["lowest"];
?>
<div class="wrap">
<h2>WP Post Activity Options</h2>
<form method="post">
	<input type="hidden" value="1" name="wp-post-activity-submit"/>
	<table class="form-table">
	<tbody>
		<tr valign="top">
			<th scope="row"><label for="highest">Highest</label></th>
			<td>
				<input type="text" class="regular-text code" value="<?=$highest;?>" id="highest" name="highest"/>
				<span class="setting-description">Defaults to <code>0.5</code></span>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row"><label for="lowest">Lowest</label></th>
			<td>
				<input type="text" class="regular-text code" value="<?=$lowest;?>" id="lowest" name="lowest"/>
				<span class="setting-description">Defaults to <code>0.01</code></span>
			</td>
		</tr>
	</tbody>
	</table>
	<p class="submit">
		<input type="submit" value="Save Changes" class="button-primary" name="Submit"/>
	</p>
</form>
</div>
<?
}

// Function for use in themes.
function wp_post_activity($format = "image") {
	global $post;
	$id = intval($post->ID);
	$post_views = get_post_custom($id);
	$post_views = intval($post_views['wp_post_activity_views'][0]);
	$comment_count = $post->comment_count;
	
	$activity = @round(($comment_count / $post_views) * 100, 2);
	
	$options = get_option("wp_post_activity");
	$highest = $options["highest"];
	$lowest = $options["lowest"];
	
	if ($activity < $lowest) $activity = 0;
	elseif ($activity >= $highest) $activity = 5;
	else $activity = @round(($activity / $highest) * 5);
	
	switch($format)
	{
		case "image":
			$plugin_url = trailingslashit( get_bloginfo('wpurl') ).PLUGINDIR.'/'. dirname( plugin_basename(__FILE__) );
			echo "<img class=\"wp-post-activity\" src=\"{$plugin_url}/images/{$activity}.gif\" alt=\"{$activity}\" />";
			break;
		case "return_text":
			return array("views" => $post_views, "comments" => $comment_count);
		default:
			return false;
	}
}

function wp_post_activity_count() {
	global $user_ID, $post;
	if(is_single() || is_page()) {
		$id = intval($post->ID);
		$post_views = get_post_custom($id);
		$post_views = intval($post_views['wp_post_activity_views'][0]);
	
		if(!update_post_meta($id, 'wp_post_activity_views', ($post_views+1))) {
			add_post_meta($id, 'wp_post_activity_views', 1, true);
		}
	}
}

add_action('wp_head', 'wp_post_activity_count');