<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * The main dialogueai view page
 *
 * @package   mod_dialogueai
 * @copyright 2024 Your Name <your@email.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$id = required_param('id', PARAM_INT); // Course Module ID.

$cm = get_coursemodule_from_id('dialogueai', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$dialogueai = $DB->get_record('dialogueai', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);

$context = context_module::instance($cm->id);

// Print the page header.
$PAGE->set_url('/mod/dialogueai/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($dialogueai->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Add the page header to the page.
echo $OUTPUT->header();

// Add the activity name to the page.
echo $OUTPUT->heading(format_string($dialogueai->name));

// Display the DialogueAI chat interface.
echo html_writer::start_div('dialogueai-container');

// Chat header with configuration info
echo html_writer::start_div('chat-header');
$botname = !empty($dialogueai->botname) ? format_string($dialogueai->botname) : 'AI Assistant';
$studentname = !empty($dialogueai->studentname) ? format_string($dialogueai->studentname) : 'Student';
echo html_writer::tag('h3', 'Chat with ' . $botname);
echo html_writer::tag('p', 'You are chatting as: ' . $studentname, array('class' => 'student-info'));
echo html_writer::end_div();

// Chat messages container
echo html_writer::start_div('chat-messages', array('id' => 'chat-messages'));

// Load existing conversation history
$conversationid = dialogueai_get_current_conversation($dialogueai->id, $USER->id);
$history = dialogueai_get_conversation_history($conversationid, 50);
if (empty($history)) {
    // Show welcome message from bot if no conversation history exists
    $welcomemsg = !empty($dialogueai->welcomemessage) ? $dialogueai->welcomemessage : 
        'Welcome! Type a message below to start your conversation with ' . $botname . '.';
    echo html_writer::div($welcomemsg, 'welcome-message bot-welcome');
} else {
    // Display conversation history
    foreach ($history as $msg) {
        $isBot = ($msg->role === 'assistant' || $msg->role === 'bot');
        $messageClass = $isBot ? 'chat-message bot-message' : 'chat-message student-message';
        $messageName = $isBot ? $botname : $studentname;
        
        echo html_writer::start_div($messageClass);
        echo html_writer::tag('span', $messageName, array('class' => 'message-name'));
        echo html_writer::div($msg->message, 'message-content');
        echo html_writer::end_div();
    }
}

echo html_writer::end_div();

// Chat input area
echo html_writer::start_div('chat-input-container');
echo html_writer::start_tag('form', array('id' => 'chat-form'));
echo html_writer::start_div('input-group');
echo html_writer::tag('input', '', array(
    'type' => 'text',
    'id' => 'chat-input',
    'placeholder' => 'Type your message here...',
    'class' => 'form-control',
    'autocomplete' => 'off'
));
echo html_writer::tag('button', 'Send', array(
    'type' => 'submit',
    'id' => 'send-button',
    'class' => 'btn btn-primary'
));
echo html_writer::end_div();
echo html_writer::end_tag('form');
echo html_writer::end_div();

// Loading indicator
echo html_writer::div('AI is thinking...', 'loading-indicator', array('id' => 'loading-indicator', 'style' => 'display: none;'));

// Debug test button (removed for production)

echo html_writer::end_div();

// Add JavaScript for chat functionality
$PAGE->requires->js_init_code('
(function() {
    const chatForm = document.getElementById("chat-form");
    const chatInput = document.getElementById("chat-input");
    const chatMessages = document.getElementById("chat-messages");
    const sendButton = document.getElementById("send-button");
    const loadingIndicator = document.getElementById("loading-indicator");
    
    const botName = "' . addslashes($botname) . '";
    const studentName = "' . addslashes($studentname) . '";
    const cmId = ' . $cm->id . ';
    
    // Rate limiting variables
    let lastRequestTime = 0;
    const minRequestInterval = 2000; // 2 seconds between requests
    
    function addMessage(content, isBot = false) {
        const messageDiv = document.createElement("div");
        messageDiv.className = "chat-message " + (isBot ? "bot-message" : "student-message");
        
        const nameSpan = document.createElement("span");
        nameSpan.className = "message-name";
        nameSpan.textContent = isBot ? botName : studentName;
        
        const contentDiv = document.createElement("div");
        contentDiv.className = "message-content";
        contentDiv.textContent = content;
        
        messageDiv.appendChild(nameSpan);
        messageDiv.appendChild(contentDiv);
        chatMessages.appendChild(messageDiv);
        
        // Scroll to bottom
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    function showLoading(show, message = "AI is thinking...") {
        loadingIndicator.style.display = show ? "block" : "none";
        loadingIndicator.textContent = message;
        sendButton.disabled = show;
        chatInput.disabled = show;
        
        if (show) {
            sendButton.textContent = "...";
        } else {
            sendButton.textContent = "Send";
        }
    }
    
    function sendMessage(message) {
        if (!message.trim()) return;
        
        // Rate limiting check
        const currentTime = Date.now();
        const timeSinceLastRequest = currentTime - lastRequestTime;
        
        if (timeSinceLastRequest < minRequestInterval) {
            const waitTime = Math.ceil((minRequestInterval - timeSinceLastRequest) / 1000);
            addMessage("⏳ Please wait " + waitTime + " more second(s) before sending another message.", true);
            return;
        }
        
        lastRequestTime = currentTime;
        
        // Add user message
        addMessage(message, false);
        
        // Clear input
        chatInput.value = "";
        
        // Show loading
        showLoading(true);
        
        // Send to server
        const xhr = new XMLHttpRequest();
        const ajaxUrl = "' . $CFG->wwwroot . '/mod/dialogueai/ajax.php";
        
        console.log("Sending AJAX request to:", ajaxUrl);
        
        xhr.open("POST", ajaxUrl, true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                showLoading(false);
                
                console.log("AJAX Response Status:", xhr.status);
                console.log("AJAX Response Text:", xhr.responseText);
                
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        console.log("Parsed Response:", response);
                        
                        if (response.success) {
                            addMessage(response.response, true);
                        } else {
                            // Handle different types of errors with appropriate user feedback
                            let errorMsg = response.error;
                            let userFriendlyMsg = "";
                            
                            if (errorMsg.includes("rate limit")) {
                                userFriendlyMsg = "⏳ The AI is currently busy. Please wait a moment and try again.";
                            } else if (errorMsg.includes("API key")) {
                                userFriendlyMsg = "🔑 API key issue. Please contact your instructor.";
                            } else if (errorMsg.includes("server error")) {
                                userFriendlyMsg = "🔧 OpenAI service is temporarily unavailable. Please try again in a few minutes.";
                            } else if (errorMsg.includes("Network error")) {
                                userFriendlyMsg = "🌐 Connection issue. Please check your internet and try again.";
                            } else {
                                userFriendlyMsg = "❌ " + errorMsg;
                            }
                            
                            addMessage(userFriendlyMsg, true);
                            console.error("Server Error:", response.error);
                        }
                    } catch (e) {
                        console.error("JSON Parse Error:", e);
                        console.error("Raw Response:", xhr.responseText);
                        addMessage("Error: Failed to parse response - " + e.message, true);
                    }
                } else {
                    addMessage("Error: HTTP " + xhr.status + " - Failed to connect to server", true);
                }
            }
        };
        
        const params = "action=send_message&cmid=" + cmId + "&message=" + encodeURIComponent(message) + "&sesskey=' . sesskey() . '";
        console.log("Sending params:", params);
        xhr.send(params);
    }
    
    // Handle form submission
    chatForm.addEventListener("submit", function(e) {
        e.preventDefault();
        sendMessage(chatInput.value);
    });
    
    // Handle Enter key
    chatInput.addEventListener("keypress", function(e) {
        if (e.key === "Enter" && !e.shiftKey) {
            e.preventDefault();
            sendMessage(chatInput.value);
        }
    });
    
    // Initialize conversation on page load
    function initializeConversation() {
        const xhr = new XMLHttpRequest();
        const ajaxUrl = "' . $CFG->wwwroot . '/mod/dialogueai/ajax.php";
        
        xhr.open("POST", ajaxUrl, true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success && response.welcomemessage && !response.hasHistory) {
                        // Add welcome message if no conversation history exists
                        addMessage(response.welcomemessage, true);
                    }
                } catch (e) {
                    console.error("Init conversation error:", e);
                }
            }
        };
        
        const params = "action=init_conversation&cmid=" + cmId + "&sesskey=' . sesskey() . '";
        xhr.send(params);
    }
    
    // Initialize conversation when page loads
    initializeConversation();
    
    // Focus on input
    chatInput.focus();
})();
');

// Add comprehensive CSS for chat interface
echo '<style>
    .dialogueai-container {
        width: 100%;
        max-width: none;
        margin: 0;
        padding: 0;
        height: 67vh; /* 2/3 of viewport height */
        display: flex;
        flex-direction: column;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
        background: #fff;
    }
    
    .chat-header {
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        padding: 15px 20px;
        border-bottom: 1px solid #dee2e6;
        flex-shrink: 0;
    }
    
    .chat-header h3 {
        margin: 0 0 5px 0;
        font-size: 1.2em;
        font-weight: 600;
    }
    
    .student-info {
        margin: 0;
        opacity: 0.9;
        font-size: 0.9em;
    }
    
    .chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        background: #f8f9fa;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    
    .welcome-message {
        text-align: center;
        color: #6c757d;
        font-style: italic;
        padding: 20px;
        background: white;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }
    
    .bot-welcome {
        text-align: left;
        color: #333;
        font-style: normal;
        background: #f8f9fa;
        border: 1px solid #28a745;
        border-left: 4px solid #28a745;
    }
    
    .chat-message {
        display: flex;
        flex-direction: column;
        max-width: 80%;
        animation: fadeIn 0.3s ease-in;
    }
    
    .student-message {
        align-self: flex-end;
        align-items: flex-end;
    }
    
    .bot-message {
        align-self: flex-start;
        align-items: flex-start;
    }
    
    .message-name {
        font-size: 0.8em;
        font-weight: 600;
        margin-bottom: 5px;
        opacity: 0.8;
    }
    
    .student-message .message-name {
        color: #007bff;
    }
    
    .bot-message .message-name {
        color: #28a745;
    }
    
    .message-content {
        padding: 12px 16px;
        border-radius: 18px;
        word-wrap: break-word;
        line-height: 1.4;
    }
    
    .student-message .message-content {
        background: #007bff;
        color: white;
        border-bottom-right-radius: 4px;
    }
    
    .bot-message .message-content {
        background: white;
        color: #333;
        border: 1px solid #e9ecef;
        border-bottom-left-radius: 4px;
    }
    
    .chat-input-container {
        padding: 15px 20px;
        background: white;
        border-top: 1px solid #dee2e6;
        flex-shrink: 0;
    }
    
    .input-group {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    
    #chat-input {
        flex: 1;
        padding: 12px 16px;
        border: 1px solid #ced4da;
        border-radius: 25px;
        font-size: 14px;
        outline: none;
        transition: border-color 0.2s;
    }
    
    #chat-input:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
    }
    
    #send-button {
        padding: 12px 24px;
        background: #007bff;
        color: white;
        border: none;
        border-radius: 25px;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.2s;
        min-width: 80px;
    }
    
    #send-button:hover:not(:disabled) {
        background: #0056b3;
    }
    
    #send-button:disabled {
        background: #6c757d;
        cursor: not-allowed;
    }
    
    .loading-indicator {
        text-align: center;
        padding: 10px;
        color: #6c757d;
        font-style: italic;
        background: rgba(255, 255, 255, 0.9);
        margin: 10px 20px;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Responsive design */
    @media (max-width: 768px) {
        .dialogueai-container {
            height: 60vh;
        }
        
        .chat-message {
            max-width: 90%;
        }
        
        .chat-header {
            padding: 12px 15px;
        }
        
        .chat-messages {
            padding: 15px;
        }
        
        .chat-input-container {
            padding: 12px 15px;
        }
    }
    
    /* Scrollbar styling */
    .chat-messages::-webkit-scrollbar {
        width: 6px;
    }
    
    .chat-messages::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    
    .chat-messages::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }
    
    .chat-messages::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
</style>';

// Finish the page.
echo $OUTPUT->footer();
