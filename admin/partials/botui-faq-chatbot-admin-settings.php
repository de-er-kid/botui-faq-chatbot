<?php
/**
 * Provide a admin area view for the plugin settings
 *
 * @since      1.0.0
 * @package    BotUI_FAQ_Chatbot
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <form method="post" action="options.php" class="botui-faq-settings-form">
        <?php
        // Register settings
        settings_fields('botui_faq_chatbot_settings');
        do_settings_sections('botui_faq_chatbot_settings');
        
        // Get saved options with defaults
        $options = get_option('botui_faq_chatbot_options', array());
        $chatbot_name = isset($options['chatbot_name']) ? $options['chatbot_name'] : get_bloginfo('name') . ' ' . __('Assistant', 'botui-faq-chatbot');
        $primary_color = isset($options['primary_color']) ? $options['primary_color'] : '#2196F3';
        $chat_position = isset($options['chat_position']) ? $options['chat_position'] : 'right';
        ?>
        
        <div class="botui-faq-metabox">
            <h3><?php esc_html_e('Chatbot Settings', 'botui-faq-chatbot'); ?></h3>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="botui_chatbot_name"><?php esc_html_e('Chatbot Name', 'botui-faq-chatbot'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="botui_chatbot_name" name="botui_faq_chatbot_options[chatbot_name]" value="<?php echo esc_attr($chatbot_name); ?>" class="regular-text">
                        <p class="description"><?php esc_html_e('The name displayed in the chat header.', 'botui-faq-chatbot'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="botui_primary_color"><?php esc_html_e('Primary Color', 'botui-faq-chatbot'); ?></label>
                    </th>
                    <td>
                        <input type="color" id="botui_primary_color" name="botui_faq_chatbot_options[primary_color]" value="<?php echo esc_attr($primary_color); ?>">
                        <p class="description"><?php esc_html_e('The main color used for the chatbot interface.', 'botui-faq-chatbot'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="botui_chat_position"><?php esc_html_e('Chat Icon Position', 'botui-faq-chatbot'); ?></label>
                    </th>
                    <td>
                        <select id="botui_chat_position" name="botui_faq_chatbot_options[chat_position]">
                            <option value="right" <?php selected($chat_position, 'right'); ?>><?php esc_html_e('Bottom Right', 'botui-faq-chatbot'); ?></option>
                            <option value="left" <?php selected($chat_position, 'left'); ?>><?php esc_html_e('Bottom Left', 'botui-faq-chatbot'); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e('The position of the chat icon on the screen.', 'botui-faq-chatbot'); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        
        <?php submit_button(__('Save Settings', 'botui-faq-chatbot')); ?>
    </form>
    
    <div class="botui-faq-metabox">
        <h3><?php esc_html_e('FAQ Statistics', 'botui-faq-chatbot'); ?></h3>
        <?php
        $faq_count = wp_count_posts('botui_faq');
        $published = $faq_count->publish;
        $draft = $faq_count->draft;
        $total = $published + $draft;
        ?>
        <p>
            <?php echo sprintf(
                esc_html__('You have %1$s published FAQ items and %2$s drafts (total: %3$s).', 'botui-faq-chatbot'),
                '<strong>' . esc_html($published) . '</strong>',
                '<strong>' . esc_html($draft) . '</strong>',
                '<strong>' . esc_html($total) . '</strong>'
            ); ?>
        </p>
        <?php if ($published < 1) : ?>
            <p class="botui-faq-help-text">
                <?php esc_html_e('You don\'t have any published FAQ items yet. Add some questions and answers to get started!', 'botui-faq-chatbot'); ?>
            </p>
        <?php endif; ?>
    </div>
    
    <div class="botui-faq-metabox">
        <h3><?php esc_html_e('Quick Help', 'botui-faq-chatbot'); ?></h3>
        <ol>
            <li><?php esc_html_e('Create FAQ items by going to FAQ Items > Add New in the WordPress admin menu.', 'botui-faq-chatbot'); ?></li>
            <li><?php esc_html_e('Enter the question as the title and the answer as the content.', 'botui-faq-chatbot'); ?></li>
            <li><?php esc_html_e('Publish your FAQ items to make them appear in the chatbot.', 'botui-faq-chatbot'); ?></li>
            <li><?php esc_html_e('The chatbot will automatically appear in the footer of your website.', 'botui-faq-chatbot'); ?></li>
        </ol>
    </div>
</div>