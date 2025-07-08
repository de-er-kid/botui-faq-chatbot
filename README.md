# BotUI FAQ Chatbot

A WordPress plugin that uses the BotUI JavaScript library to display a chatbot interface for FAQs on your website.

## Description

BotUI FAQ Chatbot creates a conversational interface for your website's frequently asked questions. It uses the open-source BotUI library to create a chat-like experience where visitors can select questions and receive answers in a conversational format.

### Features

- Creates a custom post type `botui_faq` for managing FAQ items
- Displays a chatbot interface in the footer of your website
- Uses npm for dependency management (Vue.js and BotUI)
- Webpack for bundling and optimization
- Customizable styling via CSS
- Clean uninstallation that removes all plugin data

## Installation

1. Upload the `botui-faq-chatbot` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'FAQ Items' in your WordPress admin menu to add FAQ questions and answers

## Development

This plugin uses npm for dependency management and webpack for bundling.

### Setup

```bash
# Install dependencies
npm install
```

### Build

```bash
# Development build with watch
npm run dev

# Production build
npm run build
```

## Usage

### Adding FAQ Items

1. In your WordPress admin panel, go to 'FAQ Items' > 'Add New'
2. Enter the question as the title
3. Enter the answer as the content
4. Publish the FAQ item

The chatbot will automatically display all published FAQ items as clickable buttons in the chat interface.

### Customization

You can customize the appearance of the chatbot by modifying the `public/css/chatbot.css` file. The plugin includes basic styling, but you can enhance it to match your website's design.

## Frequently Asked Questions

### Where does the chatbot appear on my website?

The chatbot appears in the footer of your website on all non-admin pages.

### Can I change the position of the chatbot?

By default, the chatbot is added to the footer. If you want to change its position, you can modify the plugin code to use a different WordPress hook or use a shortcode implementation (which would require additional development).

### Does this plugin require an API key or external service?

No, this plugin is completely self-contained and doesn't require any external API keys or services. It uses the open-source BotUI library bundled with the plugin.

## Changelog

### 1.0.0
* Initial release

## Credits

* [BotUI](https://botui.org/) - A JavaScript framework for creating conversational UIs
* [Vue.js](https://vuejs.org/) - The progressive JavaScript framework

## License

This plugin is licensed under the GPL v2 or later.