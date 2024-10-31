<?php
/*
Plugin Name: Related External Links
Plugin URI: http://kovshenin.com/wordpress/plugins/related-external-links/
Description: Display related external links with posts
Author: Konstantin Kovshenin
Version: 1.0.2
Author URI: http://kovshenin.com/

Copyright (C) 2009 Konstantin Kovshenin (http://kovshenin.com)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see http://www.gnu.org/licenses/gpl.html
*/

function related_exlinks_custom_box($post_id) {
	if (function_exists("add_meta_box")) {
		add_meta_box("related_exlinks_sectionid", "Related external links", 
                "related_exlinks_inner_custom_box", "post", "normal");
	}
	else {
		add_action("dbx_post_advanced", "related_exlinks_old_custom_box");
	}
}
   
function related_exlinks_inner_custom_box($post) {
	$post_id = $post->ID;
	for ($i = 1; $i <= 5; $i++)	{
		$link[$i] = get_post_meta($post_id, "related_exlink_$i", true);
		$desc[$i] = __(get_post_meta($post_id, "related_exlink_$i"."_desc", true));
	}
	echo '<input type="hidden" name="related_exlinks_noncename" id="related_exlinks_noncename" value="' . 
	wp_create_nonce(plugin_basename(__FILE__)) . '" />';
    
	// The actual fields for data entry
	echo <<<HTML
	<table class="widefat">
	<thead>
		<tr>
			<th>Anchor</th>
			<th>URL</th>
		</tr>
	</thead>
HTML;

	for ($i = 1; $i <= 5; $i++)	{
		echo '
		<tr>
	  		<td><input id="related_exlinks_link_'.$i.'_desc" class="widefat" type="text" name="related_exlinks_link_'.$i.'_desc" value="'.$desc[$i].'" /></td>
	  		<td><input id="related_exlinks_link_'.$i.'" class="widefat" type="text" name="related_exlinks_link_'.$i.'" value="'.$link[$i].'" /></td>
		</tr>
		';
	}
	echo '</table>';
}

function related_exlinks_old_custom_box() {
	echo '<div class="dbx-b-ox-wrapper">' . "\n";
	echo '<fieldset id="related_exlinks_fieldsetid" class="dbx-box">' . "\n";
	echo '<div class="dbx-h-andle-wrapper"><h3 class="dbx-handle">' . 
	      "Related external links" . "</h3></div>";   
	echo '<div class="dbx-c-ontent-wrapper"><div class="dbx-content">';
	related_exlinks_inner_custom_box();
	echo "</div></div></fieldset></div>\n";
}

function related_exlinks_save_postdata($post_id) {
	if ( !wp_verify_nonce( $_POST['related_exlinks_noncename'], plugin_basename(__FILE__) )) {
		return $post_id;
	}

	if ( 'page' == $_POST['post_type'] ) {
		if ( !current_user_can( 'edit_page', $post_id ))
			return $post_id;
	}
	else {
		if ( !current_user_can( 'edit_post', $post_id ))
			return $post_id;
	}

	for ($i = 1; $i <= 5; $i++)
	{
		$link = $_POST['related_exlinks_link_'.$i];
		$desc = $_POST['related_exlinks_link_'.$i.'_desc'];
		add_post_meta($post_id, "related_exlink_$i", $link[$i], true) or update_post_meta($post_id, "related_exlink_$i", $link);
		add_post_meta($post_id, "related_exlink_$i"."_desc", $desc[$i], true) or update_post_meta($post_id, "related_exlink_$i"."_desc", $desc);
	}
}

function related_exlinks_widget($args) {
	extract($args);

	$options = get_option("related_exlinks");
	if( $options == false ) {
		$options["title"] = "Related external links";
	}
	
	$title = $options["title"];

	if (is_single())
	{
		global $post;
		$post_id = $post->ID;

		$out = "<ul class=\"related_exlinks\">";
		
		for ($i = 1; $i <= 5; $i++)
			if (get_post_meta($post_id, "related_exlink_$i", true) != "" && __(get_post_meta($post_id, "related_exlink_$i"."_desc", true)) != "")
			{
				$link = get_post_meta($post_id, "related_exlink_$i", true);
				$desc = __(get_post_meta($post_id, "related_exlink_$i"."_desc", true));
				$out .= "<li><a href=\"$link\">$desc</a></li>\n";
			}

		$out .= "</ul>";
	}
	else $out = false;
	?>
<!-- Related external links start -->
	<?php if ($out) { echo $before_widget; ?>
		<?php if(!empty($title)) echo $before_title . $title . $after_title; ?>
		<?php echo $out ?>
	<?php echo $after_widget; } ?>
<!-- Related external links end -->
<?php
}

function related_exlinks_shortcode_output() {
	if (is_single())
	{
		global $post;
		$post_id = $post->ID;

		$out = "<ul class=\"related_exlinks\">";
		
		for ($i = 1; $i <= 5; $i++)
			if (get_post_meta($post_id, "related_exlink_$i", true) != "" && __(get_post_meta($post_id, "related_exlink_$i"."_desc", true)) != "")
			{
				$link = get_post_meta($post_id, "related_exlink_$i", true);
				$desc = __(get_post_meta($post_id, "related_exlink_$i"."_desc", true));
				$out .= "<li><a href=\"$link\">$desc</a></li>\n";
			}

		$out .= "</ul>";
		return $out;
	}
	return false;
}


function related_exlinks_control() {
	$options = $newoptions = get_option("related_exlinks");
	if( $options == false ) {
		$newoptions["title"] = "Related external links";
	}
	if ( $_POST["related-exlinks-submit"] ) {
		$newoptions["title"] = strip_tags(stripslashes($_POST["related-exlinks-title"]));
	}
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option("related_exlinks", $options);
	}
	$title = wp_specialchars($options["title"]);
	?>
	<p><label for="related-exlinks-title"><?php _e("Title:"); ?> <input class="widefat" id="related-exlinks-title" name="related-exlinks-title" type="text" value="<?php echo $title; ?>" /></label></p>
	<input type="hidden" id="related-exlinks-submit" name="related-exlinks-submit" value="1" />
	<?php
}

function related_exlinks_shortcode($atts, $content = null) {
	return related_exlinks_shortcode_output();
}

function related_exlinks_init() {
	register_widget_control("Related External Links", "related_exlinks_control");
	register_sidebar_widget("Related External Links", "related_exlinks_widget");
	add_shortcode('related-links', 'related_exlinks_shortcode');
}

add_action("init", "related_exlinks_init");
add_action('admin_menu', 'related_exlinks_custom_box');
add_action('save_post', 'related_exlinks_save_postdata');
?>