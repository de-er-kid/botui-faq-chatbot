<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 * @package    BotUI_FAQ_Chatbot
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for the admin area.
 *
 * @since      1.0.0
 * @package    BotUI_FAQ_Chatbot
 */
class BotUI_FAQ_Chatbot_Admin {

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        // Add admin-specific hooks
        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('admin_menu', array($this, 'add_plugin_admin_menu'));
        add_action('admin_notices', array($this, 'admin_notices'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_filter('manage_botui_faq_posts_columns', array($this, 'set_custom_columns'));
        add_action('manage_botui_faq_posts_custom_column', array($this, 'custom_column_content'), 10, 2);
        
        // Register settings
        add_action('admin_init', array($this, 'register_settings'));
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        // Only load on our custom post type screens
        $screen = get_current_screen();
        if ($screen && ($screen->post_type === 'botui_faq' || $screen->id === 'botui_faq_page_botui-faq-settings')) {
            wp_enqueue_style(
                'botui-faq-chatbot-admin',
                BOTUI_FAQ_CHATBOT_PLUGIN_URL . 'admin/css/admin.css',
                array(),
                BOTUI_FAQ_CHATBOT_VERSION,
                'all'
            );
        }
    }

    /**
     * Add menu items to the admin area.
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {
        // Add a settings page for this plugin
        add_submenu_page(
            'edit.php?post_type=botui_faq',
            __('BotUI FAQ Chatbot Settings', 'botui-faq-chatbot'),
            __('Settings', 'botui-faq-chatbot'),
            'manage_options',
            'botui-faq-settings',
            array($this, 'display_plugin_settings_page')
        );
    }

    /**
     * Render the settings page for this plugin.
     *
     * @since    1.0.0
     */
    public function display_plugin_settings_page() {
        include_once BOTUI_FAQ_CHATBOT_PLUGIN_DIR . 'admin/partials/botui-faq-chatbot-admin-settings.php';
    }

    /**
     * Display admin notices.
     *
     * @since    1.0.0
     */
    public function admin_notices() {
        // Check if we need to show any notices
        if (isset($_GET['post_type']) && $_GET['post_type'] === 'botui_faq' && !isset($_GET['post'])) {
            // Count how many FAQ items we have
            $faq_count = wp_count_posts('botui_faq');
            
            // If no published FAQs, show a notice
            if ($faq_count->publish < 1) {
                echo '<div class="notice notice-info botui-faq-admin-notice"><p>';
                echo esc_html__('You don\'t have any published FAQ items yet. Add some questions and answers to get started!', 'botui-faq-chatbot');
                echo '</p></div>';
            }
        }
    }

    /**
     * Add meta boxes to the FAQ edit screen.
     *
     * @since    1.0.0
     */
    public function add_meta_boxes() {
        add_meta_box(
            'botui_faq_help',
            __('FAQ Chatbot Help', 'botui-faq-chatbot'),
            array($this, 'render_help_meta_box'),
            'botui_faq',
            'side',
            'high'
        );
    }

    /**
     * Render the help meta box.
     *
     * @since    1.0.0
     * @param    WP_Post    $post    The post object.
     */
    public function render_help_meta_box($post) {
        echo '<div class="botui-faq-help-text">';
        echo '<p><strong>' . esc_html__('How to use:', 'botui-faq-chatbot') . '</strong></p>';
        echo '<p>' . esc_html__('Enter the question as the title and the answer as the content.', 'botui-faq-chatbot') . '</p>';
        echo '<p>' . esc_html__('Keep answers concise and to the point for the best user experience.', 'botui-faq-chatbot') . '</p>';
        echo '</div>';
    }

    /**
     * Define custom columns for the FAQ list table.
     *
     * @since    1.0.0
     * @param    array    $columns    The existing columns.
     * @return   array                The modified columns.
     */
    public function set_custom_columns($columns) {
        $new_columns = array();
        
        // Insert columns at specific positions
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            
            // Add our custom column after the title
            if ($key === 'title') {
                $new_columns['answer_preview'] = __('Answer Preview', 'botui-faq-chatbot');
            }
        }
        
        return $new_columns;
    }

    /**
     * Display content for custom columns.
     *
     * @since    1.0.0
     * @param    string    $column     The column name.
     * @param    int       $post_id    The post ID.
     */
    public function custom_column_content($column, $post_id) {
        switch ($column) {
            case 'answer_preview':
                $content = get_post_field('post_content', $post_id);
                echo wp_trim_words(wp_strip_all_tags($content), 15, '...');
                break;
        }
    }
    
    /**
     * Register settings for the plugin.
     *
     * @since    1.0.0
     */
    public function register_settings() {
        // Register a new setting for the settings page
        register_setting(
            'botui_faq_chatbot_settings',
            'botui_faq_chatbot_options',
            array(
                'sanitize_callback' => array($this, 'sanitize_settings'),
                'default' => array(
                    'chatbot_name' => get_bloginfo('name') . ' ' . __('Assistant', 'botui-faq-chatbot'),
                    'primary_color' => '#2196F3',
                    'chat_position' => 'right',
                ),
            )
        );
    }
    
    /**
     * Sanitize the settings before saving.
     *
     * @since    1.0.0
     * @param    array    $input    The input array to sanitize.
     * @return   array              The sanitized input.
     */
    public function sanitize_settings($input) {
        $sanitized_input = array();
        
        // Sanitize chatbot name
        if (isset($input['chatbot_name'])) {
            $sanitized_input['chatbot_name'] = sanitize_text_field($input['chatbot_name']);
        } else {
            $sanitized_input['chatbot_name'] = get_bloginfo('name') . ' ' . __('Assistant', 'botui-faq-chatbot');
        }
        
        // Sanitize primary color (ensure it's a valid hex color)
        if (isset($input['primary_color']) && preg_match('/^#[a-f0-9]{6}$/i', $input['primary_color'])) {
            $sanitized_input['primary_color'] = $input['primary_color'];
        } else {
            $sanitized_input['primary_color'] = '#2196F3';
        }
        
        // Sanitize chat position
        if (isset($input['chat_position']) && in_array($input['chat_position'], array('left', 'right'))) {
            $sanitized_input['chat_position'] = $input['chat_position'];
        } else {
            $sanitized_input['chat_position'] = 'right';
        }
        
        return $sanitized_input;
    }
}

// Initialize the admin class
new BotUI_FAQ_Chatbot_Admin();