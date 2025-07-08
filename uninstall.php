<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @since      1.0.0
 * @package    BotUI_FAQ_Chatbot
 */

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete all the FAQ posts
$faq_posts = get_posts(array(
    'post_type'      => 'botui_faq',
    'posts_per_page' => -1,
    'post_status'    => 'any',
));

foreach ($faq_posts as $post) {
    wp_delete_post($post->ID, true); // Set to true to force delete (bypass trash)
}

// Delete post meta related to the custom post type
global $wpdb;
$wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE post_id NOT IN (SELECT id FROM {$wpdb->posts})");

// Clear any cached data that has been removed
wp_cache_flush();