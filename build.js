/**
 * Build script for BotUI FAQ Chatbot
 * 
 * This script handles the build process for the BotUI FAQ Chatbot plugin:
 * 1. Cleans the dist directory
 * 2. Creates necessary directories
 * 3. Runs webpack in production mode
 */

const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

// Define paths
const distDir = path.join(__dirname, 'public', 'dist');
const distJsDir = path.join(distDir, 'js');
const distCssDir = path.join(distDir, 'css');

/**
 * Clean dist directory if it exists
 */
function cleanDistDirectory() {
  console.log('Cleaning dist directory...');
  if (fs.existsSync(distDir)) {
    try {
      // Remove all files in dist directory
      fs.readdirSync(distDir, { withFileTypes: true }).forEach(entry => {
        const entryPath = path.join(distDir, entry.name);
        if (entry.isDirectory()) {
          fs.rmSync(entryPath, { recursive: true, force: true });
        } else {
          fs.unlinkSync(entryPath);
        }
      });
      console.log('Dist directory cleaned successfully.');
    } catch (error) {
      console.error('Error cleaning dist directory:', error.message);
      process.exit(1);
    }
  }
}

/**
 * Create necessary directories
 */
function createDirectories() {
  console.log('Creating necessary directories...');
  try {
    if (!fs.existsSync(distDir)) {
      fs.mkdirSync(distDir, { recursive: true });
    }

    if (!fs.existsSync(distJsDir)) {
      fs.mkdirSync(distJsDir, { recursive: true });
    }

    if (!fs.existsSync(distCssDir)) {
      fs.mkdirSync(distCssDir, { recursive: true });
    }
    console.log('Directories created successfully.');
  } catch (error) {
    console.error('Error creating directories:', error.message);
    process.exit(1);
  }
}

/**
 * Run webpack build
 */
function runWebpackBuild() {
  console.log('Building assets with webpack...');
  try {
    execSync('npx webpack --mode production', { stdio: 'inherit' });
    console.log('Build completed successfully!');
  } catch (error) {
    console.error('Build failed:', error.message);
    process.exit(1);
  }
}

// Execute build process
try {
  cleanDistDirectory();
  createDirectories();
  runWebpackBuild();
  console.log('Build process completed successfully!');
} catch (error) {
  console.error('Build process failed:', error.message);
  process.exit(1);
}