<?php
/**
 * Plugin Name: BotUI FAQ Chatbot
 * Plugin URI: https://github.com/de-er-kid/botui-faq-chatbot
 * Description: A WordPress plugin that uses BotUI to display a chatbot interface for FAQs.
 * Version: 1.0.1

 * Author: Mohammed Sinan P K
 * Author URI: mailto:mohammed.sinan@firstscreen.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: botui-faq-chatbot
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('BOTUI_FAQ_CHATBOT_VERSION', '1.0.0');
define('BOTUI_FAQ_CHATBOT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BOTUI_FAQ_CHATBOT_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Register the custom post type for FAQs.
 */
function botui_faq_chatbot_register_post_type() {
    $labels = array(
        'name'               => _x('FAQ Items', 'post type general name', 'botui-faq-chatbot'),
        'singular_name'      => _x('FAQ Item', 'post type singular name', 'botui-faq-chatbot'),
        'menu_name'          => _x('FAQ Chatbot', 'admin menu', 'botui-faq-chatbot'),
        'name_admin_bar'     => _x('FAQ Item', 'add new on admin bar', 'botui-faq-chatbot'),
        'add_new'            => _x('Add New', 'FAQ item', 'botui-faq-chatbot'),
        'add_new_item'       => __('Add New FAQ Item', 'botui-faq-chatbot'),
        'new_item'           => __('New FAQ Item', 'botui-faq-chatbot'),
        'edit_item'          => __('Edit FAQ Item', 'botui-faq-chatbot'),
        'view_item'          => __('View FAQ Item', 'botui-faq-chatbot'),
        'all_items'          => __('All FAQ Items', 'botui-faq-chatbot'),
        'search_items'       => __('Search FAQ Items', 'botui-faq-chatbot'),
        'parent_item_colon'  => __('Parent FAQ Items:', 'botui-faq-chatbot'),
        'not_found'          => __('No FAQ items found.', 'botui-faq-chatbot'),
        'not_found_in_trash' => __('No FAQ items found in Trash.', 'botui-faq-chatbot')
    );

    $args = array(
        'labels'             => $labels,
        'description'        => __('FAQ items for the BotUI chatbot', 'botui-faq-chatbot'),
        'public'             => true,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'botui-faq'),
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => null,
        'menu_icon'          => 'dashicons-format-chat',
        'supports'           => array('title', 'editor')
    );

    register_post_type('botui_faq', $args);
}
add_action('init', 'botui_faq_chatbot_register_post_type');

/**
 * Enqueue scripts and styles for the frontend.
 */
function botui_faq_chatbot_enqueue_scripts() {
    // Only load on frontend, not in admin
    if (is_admin()) {
        return;
    }

    // Enqueue Vue.js first (required by BotUI)
    wp_enqueue_script(
        'vue',
        'https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.min.js',
        array(),
        '2.6.14',
        true
    );

    // Enqueue bundled script with BotUI
    wp_enqueue_script(
        'botui-faq-chatbot-js',
        BOTUI_FAQ_CHATBOT_PLUGIN_URL . 'public/dist/js/botui-chatbot.js',
        array('vue'),
        BOTUI_FAQ_CHATBOT_VERSION,
        true
    );
    
    // Get all published FAQ items
    $args = array(
        'post_type'      => 'botui_faq',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'title',
        'order'          => 'ASC',
    );

    $faq_items = get_posts($args);

    // Prepare the FAQ data for JavaScript
    $faq_data = array();
    foreach ($faq_items as $faq) {
        $faq_data[] = array(
            'id'       => $faq->ID,
            'question' => htmlspecialchars($faq->post_title, ENT_QUOTES, 'UTF-8'),
            'answer'   => wp_json_encode(apply_filters('the_content', $faq->post_content)),
        );
    }
    
    // Localize the script with FAQ data
    wp_localize_script(
        'botui-faq-chatbot-js',
        'botuiFaqChatbot',
        array(
            'faqData' => $faq_data,
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('botui_faq_chatbot_nonce'),
        )
    );

    // Enqueue bundled CSS
    wp_enqueue_style(
        'botui-faq-chatbot-css',
        BOTUI_FAQ_CHATBOT_PLUGIN_URL . 'public/dist/css/botui-chatbot.css',
        array(),
        BOTUI_FAQ_CHATBOT_VERSION
    );
    
    // Enqueue Font Awesome for icons
    wp_enqueue_style(
        'font-awesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css',
        array(),
        '5.15.4'
    );
    
    // We're using the bundled CSS from webpack build
}
add_action('wp_enqueue_scripts', 'botui_faq_chatbot_enqueue_scripts');

/**
 * Add the BotUI container to the footer.
 */
function botui_faq_chatbot_footer() {
    // Only show on frontend, not in admin
    if (is_admin()) {
        return;
    }
    
    // Get saved options with defaults
    $options = get_option('botui_faq_chatbot_options', array());
    $chatbot_name = isset($options['chatbot_name']) ? $options['chatbot_name'] : get_bloginfo('name') . ' ' . __('Assistant', 'botui-faq-chatbot');
    $primary_color = isset($options['primary_color']) ? $options['primary_color'] : '#2196F3';
    $chat_position = isset($options['chat_position']) ? $options['chat_position'] : 'right';
    
    // Set position class based on settings
    $position_class = ($chat_position === 'left') ? 'chat-position-left' : 'chat-position-right';

    // Add the chat toggle button with Font Awesome icon
    echo '<div class="chat-toggle-button ' . esc_attr($position_class) . '" id="chat-toggle-button"><i class="fas fa-comment-dots"></i></div>';
    
    // Add the BotUI container with header
    echo '<div id="botui-app" class="chat-hidden ' . esc_attr($position_class) . '">';
    echo '<div class="chat-header" style="background-color: ' . esc_attr($primary_color) . ';"><h3>' . esc_html($chatbot_name) . '</h3><span class="chat-close" id="chat-close">×</span></div>';
    echo '<bot-ui></bot-ui>';
    echo '</div>';
    
    // Add custom CSS for primary color
    echo '<style>
        .botui-actions-buttons-button {
            background-color: ' . esc_attr($primary_color) . ' !important;
        }
        .botui-actions-buttons-button:hover {
            background-color: ' . esc_attr(adjustBrightness($primary_color, -20)) . ' !important;
        }
        .botui-message-content.human {
            background-color: ' . esc_attr($primary_color) . ' !important;
        }

        .chat-toggle-button {
            background-color: ' . esc_attr($primary_color) . ' !important;
        }
    </style>';
}
add_action('wp_footer', 'botui_faq_chatbot_footer');

/**
 * Helper function to adjust color brightness
 * 
 * @param string $hex Hex color code
 * @param int $steps Steps to adjust brightness (-255 to 255)
 * @return string Adjusted hex color
 */
function adjustBrightness($hex, $steps) {
    // Remove # if present
    $hex = ltrim($hex, '#');
    
    // Convert to RGB
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    // Adjust brightness
    $r = max(0, min(255, $r + $steps));
    $g = max(0, min(255, $g + $steps));
    $b = max(0, min(255, $b + $steps));
    
    // Convert back to hex
    return '#' . sprintf('%02x%02x%02x', $r, $g, $b);
}

/**
 * Load required files.
 */
require_once BOTUI_FAQ_CHATBOT_PLUGIN_DIR . 'includes/class-botui-faq-chatbot.php';

// Load admin functionality if in admin
if (is_admin()) {
    require_once BOTUI_FAQ_CHATBOT_PLUGIN_DIR . 'admin/class-botui-faq-chatbot-admin.php';
}

register_activation_hook(__FILE__, 'botui_faq_add_default_faqs');

function botui_faq_add_default_faqs() {
    // Check if there are any existing FAQs
    $existing_faqs = get_posts(array(
        'post_type' => 'botui_faq',
        'post_status' => 'publish',
        'numberposts' => 1
    ));

    if (!empty($existing_faqs)) {
        return; // FAQs already exist, do not add defaults
    }

    $default_faqs = array(
        array(
            'post_title'   => 'About Us',
            'post_content' => 'Discover the transformative power of fruits and veggies in your daily routine with Juicee World. From educational content to delicious recipes, we’ve got you covered.',
            'post_status'  => 'publish',
            'post_type'    => 'botui_faq'
        ),
        array(
            'post_title'   => 'Privacy',
            'post_content' => 'Your privacy is important to us. We do not share your account information with third parties. For more details, please refer to our Privacy Policy.',
            'post_status'  => 'publish',
            'post_type'    => 'botui_faq'
        ),
        array(
            'post_title'   => 'Cancellation',
            'post_content' => 'To cancel your subscription, please send us an email to support@juiceeworld.com or call us 0031202254833 (EU&UK) or 0018442345502 (US)',
            'post_status'  => 'publish',
            'post_type'    => 'botui_faq'
        ),
    );

    foreach ($default_faqs as $faq) {
        wp_insert_post($faq);
    }
}