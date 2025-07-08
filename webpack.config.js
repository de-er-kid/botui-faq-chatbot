/**
 * Webpack configuration for BotUI FAQ Chatbot
 */

const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = (env, argv) => {
  const isProduction = argv.mode === 'production';
  
  return {
    // Define entry points
    entry: {
      'botui-chatbot': ['./src/js/chatbot.js', './src/css/chatbot.css'],
    },
    // Output configuration
    output: {
      path: path.resolve(__dirname, 'public/dist'),
      filename: 'js/[name].js',
      // Expose Vue globally so BotUI can find it
      library: {
        name: 'BotUIChatbot',
        type: 'var',
      },
    },
    // Development tools
    devtool: isProduction ? false : 'source-map',
    
    // Module rules for processing different file types
    module: {
      rules: [
        {
          test: /\.js$/,
          exclude: /node_modules/,
          use: {
            loader: 'babel-loader',
            options: {
              presets: ['@babel/preset-env']
            }
          }
        },
        {
          test: /\.css$/,
          use: [
            MiniCssExtractPlugin.loader,
            'css-loader'
          ]
        }
      ]
    },
    // Plugins
    plugins: [
      new MiniCssExtractPlugin({
        filename: 'css/[name].css'
      }),
      // Add more plugins as needed
    ],
    
    // Optimization settings
    optimization: {
      minimize: isProduction,
      // Add more optimization settings as needed
    },
    
    // Make Vue available globally
    externals: {
      vue: 'Vue'
    }
  };
};