/**
 * BotUI FAQ Chatbot
 * 
 * Main JavaScript file for the chatbot functionality.
 * This file handles the initialization of the BotUI chatbot,
 * displays FAQ questions as buttons, and manages the chat window animations.
 * Includes state management to remember user's chat open/close preference.
 * 
 * @package BotUI-FAQ-Chatbot
 * @version 1.1.0
 */

// Use Vue from global scope (loaded via WordPress)
// const Vue = window.Vue;
import BotUI from 'botui';

// Import BotUI styles
import 'botui/build/botui.min.css';
import 'botui/build/botui-theme-default.css';

(function() {
    // Constants
    const ANIMATION_DURATION = {
        IN: 500,
        OUT: 300
    };
    const DELAY = {
        WELCOME: 500,
        BUTTONS: 1000,
        ANSWER: 500,
        FOLLOW_UP: 1000
    };
    
    // LocalStorage key for chat state
    const CHAT_STATE_KEY = 'botuiFaqChatState';
    
    // Variables to track initialization
    let botui = null;
    let isInitialized = false;
    let isChatOpen = false; // Track chat state
    
    /**
     * Get chat state from localStorage
     * @returns {object} Chat state object with isOpen property
     */
    function getChatState() {
        try {
            const state = localStorage.getItem(CHAT_STATE_KEY);
            return state ? JSON.parse(state) : { isOpen: true }; // Default to open for first-time users
        } catch (error) {
            console.warn('Error reading chat state from localStorage:', error);
            return { isOpen: true }; // Default fallback
        }
    }
    
    /**
     * Save chat state to localStorage
     * @param {boolean} isOpen - Whether the chat is open
     */
    function saveChatState(isOpen) {
        try {
            const state = { isOpen: isOpen };
            localStorage.setItem(CHAT_STATE_KEY, JSON.stringify(state));
            console.log('Chat state saved:', state);
        } catch (error) {
            console.warn('Error saving chat state to localStorage:', error);
        }
    }
    
    /**
     * Initialize the chatbot interface
     * Creates a new BotUI instance and displays welcome message and FAQ buttons
     */
    function initializeChatbot() {
        // Prevent multiple initializations
        if (isInitialized) return;
        
        try {
            // Initialize BotUI
            botui = new BotUI('botui-app', {
                vue: {
                    // Add this configuration
                    unsafeHTML: true
                }
            });
            isInitialized = true;
            
            // Display welcome message
            botui.message.add({
                content: 'Hello! I can help answer your questions. What would you like to know?',
                delay: DELAY.WELCOME
            }).then(function() {
                // Debug: Log the FAQ data
                console.log('=== FAQ DEBUG INFO ===');
                console.log('botuiFaqChatbot exists:', typeof botuiFaqChatbot !== 'undefined');
                if (typeof botuiFaqChatbot !== 'undefined') {
                    console.log('botuiFaqChatbot object:', botuiFaqChatbot);
                    console.log('faqData exists:', !!botuiFaqChatbot.faqData);
                    console.log('faqData is array:', Array.isArray(botuiFaqChatbot.faqData));
                    if (botuiFaqChatbot.faqData) {
                        console.log('faqData length:', botuiFaqChatbot.faqData.length);
                        console.log('faqData content:', botuiFaqChatbot.faqData);
                        botuiFaqChatbot.faqData.forEach((faq, index) => {
                            console.log(`FAQ ${index}:`, {
                                id: faq?.id,
                                question: faq?.question,
                                answer: faq?.answer,
                                hasId: !!faq?.id
                            });
                        });
                        console.log('Has valid FAQ with ID:', botuiFaqChatbot.faqData.some(faq => faq?.id));
                    }
                }
                console.log('=== END FAQ DEBUG ===');
                
                // Check if FAQ data exists and is properly formatted
                if (typeof botuiFaqChatbot !== 'undefined' && 
                    botuiFaqChatbot.faqData && 
                    Array.isArray(botuiFaqChatbot.faqData) && 
                    botuiFaqChatbot.faqData.some(faq => faq?.id)) {
                    return showQuestions(true, false);
                } else {
                    console.warn('FAQ data is missing or empty');
                    return botui.message.add({
                        content: 'Sorry, no FAQ items are available at the moment.',
                        delay: DELAY.WELCOME
                    });
                }
            }).catch(function(error) {
                console.error('Error initializing chatbot:', error);
            });
        } catch (error) {
            console.error('Failed to initialize BotUI:', error);
        }
    }
    
    /**
     * Display FAQ questions as buttons or just the "Ask another question" button
     * 
     * @param {boolean} isInitial - Whether this is the initial display of questions
     * @param {boolean} showOnlyAskButton - Whether to show only the "Ask another question" button
     * @returns {Promise} - Promise that resolves when user selects a button
     */
    function showQuestions(isInitial = true, showOnlyAskButton = false) {
        let buttons = [];
        
        try {
            // If we only want to show the "Ask another question" button
            if (showOnlyAskButton) {
                buttons.push({
                    text: 'Ask another question',
                    value: 'more'
                });
            } else {
                // Create a button for each FAQ item
                botuiFaqChatbot.faqData.forEach(function(faq) {
                    if (faq && faq.question && faq.id) {
                        buttons.push({
                            text: faq.question,
                            value: faq.id
                        });
                    }
                });
                
                // Add cancellation button
                buttons.push({
                    text: 'Cancellation',
                    value: 'cancellation'
                });
                
                // If no valid buttons were created, show an error
                if (buttons.length === 0) {
                    return botui.message.add({
                        content: 'Sorry, there was a problem loading your questions.',
                        delay: DELAY.WELCOME
                    });
                }
            }
            
            return botui.action.button({
                delay: DELAY.BUTTONS,
                action: buttons
            }).then(function(res) {
                // If user wants to ask another question
                if (res.value === 'more') {
                    return showQuestions(true, false); // Show all questions
                }
                
                // Handle cancellation button
                if (res.value === 'cancellation') {
                    return handleCancellation();
                }
                
                // Find the selected FAQ item
                const selectedFaq = botuiFaqChatbot.faqData.find(faq => faq.id === res.value);
                
                // Show the answer
                if (selectedFaq) {
                    // Decode the base64 encoded answer
                    const decodedAnswer = atob(selectedFaq.answer);
                    
                    return botui.message.add({
                        content: decodedAnswer,
                        delay: DELAY.ANSWER,
                        loading: true,
                        type: 'html'
                    }).then(function() {
                        return botui.message.add({
                            content: 'Is there anything else you would like to know?',
                            delay: DELAY.FOLLOW_UP
                        }).then(function() {
                            return showQuestions(false, true);
                        });
                    });
                } else {
                    console.error('Selected FAQ item not found:', res.value);
                    return botui.message.add({
                        content: 'Sorry, I couldn\'t find the answer to that question.',
                        delay: DELAY.ANSWER
                    }).then(() => showQuestions(false, true));
                }
            }).catch(function(error) {
                console.error('Error displaying buttons:', error);
            });
        } catch (error) {
            console.error('Error in showQuestions:', error);
            return Promise.reject(error);
        }
    }
    
    /**
     * Handle cancellation button click
     * Implements different flows for signed-in and signed-out users
     */
    function handleCancellation() {
        // Check if user is logged in (WordPress adds logged-in class to body)
        const isLoggedIn = document.body.classList.contains('logged-in');
        
        if (isLoggedIn) {
            // Flow for signed-in user
            return botui.message.add({
                content: 'Are you sure you want to unsubscribe?',
                delay: DELAY.ANSWER
            }).then(() => {
                return botui.action.button({
                    delay: DELAY.BUTTONS,
                    action: [
                        { text: 'Yes, unsubscribe me', value: 'yes' },
                        { text: 'No, cancel', value: 'no' }
                    ]
                });
            }).then((res) => {
                if (res.value === 'yes') {
                    // User confirmed unsubscription
                    return processUnsubscribe();
                } else {
                    // User cancelled unsubscription
                    return botui.message.add({
                        content: 'Unsubscription cancelled.',
                        delay: DELAY.ANSWER
                    }).then(() => showQuestions(false, true));
                }
            });
        } else {
            // Flow for signed-out user
            return botui.message.add({
                content: 'Please enter your email address to unsubscribe:',
                delay: DELAY.ANSWER
            }).then(() => {
                return botui.action.text({
                    delay: DELAY.BUTTONS,
                    action: {
                        placeholder: 'Your email address'
                    }
                });
            }).then((res) => {
                const email = res.value;
                // Validate email format
                if (!validateEmail(email)) {
                    return botui.message.add({
                        content: 'Please enter a valid email address.',
                        delay: DELAY.ANSWER
                    }).then(() => handleCancellation());
                }
                
                return processUnsubscribe(email);
            });
        }
    }
    
    /**
     * Process unsubscription request
     * @param {string} email - Email address for non-logged in users
     */
    function processUnsubscribe(email = null) {
        return botui.message.add({
            content: 'Processing your unsubscription request...',
            delay: DELAY.ANSWER,
            loading: true
        }).then(() => {
            // Prepare form data
            const formData = new FormData();
            
            if (email) {
                formData.append('email', email);
            } else {
                formData.append('email', 'current_user');
            }
            
            // Send AJAX request
            return fetch(botuiFaqChatbot.ajaxUrl + '?action=botui_unsubscribe', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    return botui.message.add({
                        content: 'You have been successfully unsubscribed.',
                        delay: DELAY.ANSWER
                    }).then(() => showQuestions(false, true));
                } else {
                    return botui.message.add({
                        content: 'An error occurred: ' + data.data.message + '<br><br>Please contact customer support.',
                        delay: DELAY.ANSWER,
                        type: 'html'
                    }).then(() => showQuestions(false, true));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                return botui.message.add({
                    content: 'An unexpected error occurred. Please contact customer support.',
                    delay: DELAY.ANSWER
                }).then(() => showQuestions(false, true));
            });
        });
    }
    
    /**
     * Validate email format
     * @param {string} email - Email to validate
     * @returns {boolean} - Whether the email is valid
     */
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(String(email).toLowerCase());
    }
    
    /**
     * Open the chat window
     */
    function openChat() {
        const chatWindow = document.getElementById('botui-app');
        chatWindow.classList.remove('chat-hidden', 'chat-animate-out');
        chatWindow.classList.add('chat-animate-in');
        isChatOpen = true;
        
        // Save the open state to localStorage
        saveChatState(true);
        
        setTimeout(() => {
            chatWindow.classList.remove('chat-animate-in');
            // Initialize chatbot if not already initialized
            if (!isInitialized) {
                initializeChatbot();
            }
        }, ANIMATION_DURATION.IN);
    }
    
    /**
     * Close the chat window
     */
    function closeChat() {
        const chatWindow = document.getElementById('botui-app');
        chatWindow.classList.add('chat-animate-out');
        isChatOpen = false;
        
        // Save the closed state to localStorage
        saveChatState(false);
        
        setTimeout(() => {
            chatWindow.classList.add('chat-hidden');
            chatWindow.classList.remove('chat-animate-out');
        }, ANIMATION_DURATION.OUT);
    }
    
    /**
     * Initialize close button functionality using event delegation
     * This will work even if the close button is created dynamically
     */
    function initializeCloseButton() {
        // Method 1: Direct event listener (if element exists)
        const closeButton = document.getElementById('chat-close');
        
        if (closeButton) {
            console.log('Close button found, attaching direct event listener');
            
            // Remove any existing event listeners to avoid duplicates
            closeButton.removeEventListener('click', handleCloseClick);
            
            // Add the close event listener
            closeButton.addEventListener('click', handleCloseClick);
            
            // Also add it using onclick as backup
            closeButton.onclick = handleCloseClick;
            
        } else {
            console.log('Close button not found, will use event delegation');
        }
        
        // Method 2: Event delegation on document (works for dynamic elements)
        document.removeEventListener('click', handleDocumentClick);
        document.addEventListener('click', handleDocumentClick);
        
        console.log('Close button event listeners setup complete');
    }
    
    /**
     * Handle close button click
     */
    function handleCloseClick(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Close button clicked - closing chat');
        closeChat();
    }
    
    /**
     * Handle document clicks to catch dynamically created close buttons
     */
    function handleDocumentClick(e) {
        // Check if the clicked element is the close button
        if (e.target && e.target.id === 'chat-close') {
            console.log('Close button clicked via event delegation');
            handleCloseClick(e);
        }
    }
    
    /**
     * Apply initial chat state based on localStorage
     */
    function applyInitialChatState() {
        const chatState = getChatState();
        const chatWindow = document.getElementById('botui-app');
        
        if (!chatWindow) {
            console.error('Chat window element not found');
            return;
        }
        
        console.log('Applying initial chat state:', chatState);
        
        if (chatState.isOpen) {
            // Chat should be open
            chatWindow.classList.remove('chat-hidden');
            isChatOpen = true;
            
            // Initialize the chatbot
            if (!isInitialized) {
                setTimeout(() => {
                    initializeChatbot();
                }, 300);
            }
        } else {
            // Chat should be closed
            chatWindow.classList.add('chat-hidden');
            isChatOpen = false;
        }
    }
    
    /**
     * Initialize chat toggle functionality
     * Sets up event listeners for the chat toggle button and close button
     */
    function initializeChatControls() {
        const toggleButton = document.getElementById('chat-toggle-button');
        const chatWindow = document.getElementById('botui-app');
        
        // Ensure required elements exist
        if (!toggleButton || !chatWindow) {
            console.error('Required chat elements not found:', {
                toggleButton: !!toggleButton,
                chatWindow: !!chatWindow
            });
            return;
        }
        
        // Apply the saved state instead of defaulting to open
        applyInitialChatState();
        
        // Use toggle button as open/close toggle
        toggleButton.addEventListener('click', function() {
            if (isChatOpen) {
                closeChat();
            } else {
                openChat();
            }
        });
        
        // Initialize close button separately
        initializeCloseButton();
    }
    
    // Initialize everything when the DOM is fully loaded
    document.addEventListener('DOMContentLoaded', function() {
        try {
            initializeChatControls();
            
            // Also try to initialize close button after a short delay
            // in case the element is dynamically created
            setTimeout(function() {
                initializeCloseButton();
            }, 100);
            
            // Keep trying to find the close button for up to 5 seconds
            let attempts = 0;
            const maxAttempts = 50; // 50 attempts × 100ms = 5 seconds
            
            const findCloseButton = setInterval(function() {
                attempts++;
                const closeButton = document.getElementById('chat-close');
                
                if (closeButton) {
                    console.log('Close button found after', attempts * 100, 'ms');
                    initializeCloseButton();
                    clearInterval(findCloseButton);
                } else if (attempts >= maxAttempts) {
                    console.log('Close button not found after 5 seconds, using event delegation only');
                    clearInterval(findCloseButton);
                }
            }, 100);
            
        } catch (error) {
            console.error('Error initializing chat controls:', error);
        }
    });
    
    // Also try to initialize when the window loads (backup)
    window.addEventListener('load', function() {
        setTimeout(function() {
            initializeCloseButton();
        }, 200);
    });
})();