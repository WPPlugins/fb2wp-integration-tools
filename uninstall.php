<?php
/*
 * plugin should create a file named ‘uninstall.php’ in the base plugin folder. This file will be called, if it exists,
 * during the uninstall process bypassing the uninstall hook.
 * ref: https://developer.wordpress.org/reference/functions/register_uninstall_hook/
 */
if (!defined('WP_UNINSTALL_PLUGIN')) {
	die;
}
if (get_option("mxp_complete_remove", "no") == "yes") {
	global $wpdb;
	$wpdb->query("DROP TABLE {$wpdb->prefix}fb2wp_debug");
	delete_option("mxp_fb2wp_db_version");
	delete_option("mxp_fb_app_id");
	delete_option("mxp_fb_secret");
	delete_option("mxp_fb_app_access_token");
	delete_option("mxp_fb_enable_jssdk");
	delete_option("mxp_fb_jssdk_local");
	delete_option("mxp_fb_api_version");
	delete_option("mxp_enable_debug");
	delete_option("mxp_fb2wp_callback_url");
	delete_option("mxp_messenger_msglist");
	delete_option("mxp_messenger_default_reply");
	delete_option("mxp_fb2wp_post_enable");
	delete_option("mxp_fb2wp_post_author");
	delete_option("mxp_fb2wp_post_category");
	delete_option("mxp_fb2wp_post_status");
	delete_option("mxp_fb2wp_post_comment_status");
	delete_option("mxp_fb2wp_post_ping_status");
	delete_option("mxp_fb2wp_post_type");
	delete_option("mxp_fb2wp_auth_users");
	delete_option("mxp_fb2wp_default_title");
	delete_option("mxp_fb2wp_post_tags");
	delete_option("mxp_fb2wp_default_display_attachment");
	delete_option("mxp_fb2wp_default_display_embed");
	delete_option("mxp_fb2wp_image_width");
	delete_option("mxp_fb2wp_image_height");
	delete_option("mxp_fb2wp_video_width");
	delete_option("mxp_fb2wp_video_height");
	delete_option("mxp_fb2wp_post_footer");
	delete_option("mxp_fb2wp_no_post_tag");
	delete_option("mxp_fb_quote_enable");
	delete_option("mxp_fb_save_enable");
	delete_option("mxp_fb_send_enable");
	delete_option("mxp_fb_comments_enable");
	delete_option("mxp_complete_remove");
	delete_option("mxp_fb_page_id"); //add from 1.4.4
	delete_option("mxp_fb_functions_section_title"); //add from 1.4.5
}