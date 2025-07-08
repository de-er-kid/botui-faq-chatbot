<?php
/**
 * The main plugin class.
 *
 * @since      1.0.0
 * @package    BotUI_FAQ_Chatbot
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * The main plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @since      1.0.0
 * @package    BotUI_FAQ_Chatbot
 */
class BotUI_FAQ_Chatbot {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      BotUI_FAQ_Chatbot    $instance    Maintains the singleton instance.
     */
    protected static $instance = null;

    /**
     * Define the core functionality of the plugin.
     *
     * @since    1.0.0
     */
    private function __construct() {
        // Register activation and deactivation hooks
        register_activation_hook(BOTUI_FAQ_CHATBOT_PLUGIN_DIR . 'botui-faq-chatbot.php', array($this, 'activate'));
        register_deactivation_hook(BOTUI_FAQ_CHATBOT_PLUGIN_DIR . 'botui-faq-chatbot.php', array($this, 'deactivate'));
    }

    /**
     * Main BotUI_FAQ_Chatbot Instance
     *
     * Ensures only one instance of BotUI_FAQ_Chatbot is loaded or can be loaded.
     *
     * @since    1.0.0
     * @return   BotUI_FAQ_Chatbot    Main instance
     */
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Fired when the plugin is activated.
     *
     * @since    1.0.0
     * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
     */
    public function activate($network_wide) {
        // Activation code here
        flush_rewrite_rules();
    }

    /**
     * Fired when the plugin is deactivated.
     *
     * @since    1.0.0
     * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Deactivate" action, false if WPMU is disabled or plugin is deactivated on an individual blog.
     */
    public function deactivate($network_wide) {
        // Deactivation code here
        flush_rewrite_rules();
    }
}

// Initialize the plugin
BotUI_FAQ_Chatbot::instance();