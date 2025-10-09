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
 * AJAX endpoint for DialogueAI chat functionality
 *
 * @package   mod_dialogueai
 * @copyright 2024 Your Name <your@email.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require_once('../../config.php');
require_once($CFG->dirroot . '/mod/dialogueai/lib.php');

// Debug logging (remove in production)
// error_log('AJAX Debug - POST data: ' . print_r($_POST, true));

// Try different parameter extraction methods
$action = optional_param('action', '', PARAM_ALPHANUMEXT); // Allow underscores
if (empty($action)) {
    // Fallback: try raw parameter extraction
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $action = clean_param($action, PARAM_ALPHANUMEXT);
}

$cmid = optional_param('cmid', 0, PARAM_INT);

// Debug: Log extracted parameters (remove in production)
// error_log('AJAX Debug - Action: "' . $action . '"');

if (empty($action)) {
    echo json_encode(['success' => false, 'error' => 'No action parameter received']);
    exit;
}

if (empty($cmid)) {
    echo json_encode(['success' => false, 'error' => 'No cmid parameter received']);
    exit;
}

$cm = get_coursemodule_from_id('dialogueai', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$dialogueai = $DB->get_record('dialogueai', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

// Validate sesskey for security
require_sesskey();

header('Content-Type: application/json');

switch ($action) {
    case 'send_message':
    case 'sendmessage': // Handle both formats due to potential parameter processing
        $message = optional_param('message', '', PARAM_TEXT);
        
        if (empty($message)) {
            echo json_encode(['success' => false, 'error' => 'No message provided']);
            exit;
        }
        
        try {
            // Get or create current conversation
            $conversationid = dialogueai_get_current_conversation($dialogueai->id, $USER->id);
            
            // Store user message in conversation history
            dialogueai_store_conversation_message($dialogueai->id, $USER->id, $conversationid, 'user', $message);
            
            // Use Moodle's AI subsystem if available, fallback to direct OpenAI
            if (class_exists('\core\ai\manager')) {
                $response = dialogueai_send_via_moodle_ai($dialogueai, $message, $context, $conversationid);
            } else {
                $response = dialogueai_send_to_openai($dialogueai, $message, $conversationid);
            }
            
            // Store AI response in conversation history
            dialogueai_store_conversation_message($dialogueai->id, $USER->id, $conversationid, 'assistant', $response);
            
            // Check if this is the completion moment (exactly reached target questions)
            $is_completion_moment = false;
            $is_complete = false;
            
            // Only check completion if the activity has completion enabled
            if ($dialogueai->completionconversation) {
                $is_completion_moment = dialogueai_is_completion_moment($dialogueai->id, $USER->id, $conversationid);
                if ($is_completion_moment) {
                    $is_complete = dialogueai_mark_activity_complete($cm->id, $USER->id);
                }
            }
            
            echo json_encode([
                'success' => true,
                'response' => $response,
                'botname' => $dialogueai->botname ?: 'AI Assistant',
                'studentname' => $dialogueai->studentname ?: 'Student',
                'completed' => $is_complete
            ]);
        } catch (Exception $e) {
            error_log('AJAX Error: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        break;
        
    case 'load_history':
        try {
            // Get current conversation ID
            $conversationid = dialogueai_get_current_conversation($dialogueai->id, $USER->id);
            $history = dialogueai_get_conversation_history($conversationid, 50);
            $messages = array();
            
            foreach ($history as $msg) {
                $isBot = ($msg->role === 'assistant' || $msg->role === 'bot');
                $messages[] = array(
                    'content' => $msg->message,
                    'isBot' => $isBot,
                    'timestamp' => $msg->timecreated
                );
            }
            
            echo json_encode([
                'success' => true,
                'messages' => $messages,
                'conversationid' => $conversationid,
                'botname' => $dialogueai->botname ?: 'AI Assistant',
                'studentname' => $dialogueai->studentname ?: 'Student'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        break;
        
    case 'init_conversation':
        try {
            // Get or create current conversation
            $conversationid = dialogueai_get_current_conversation($dialogueai->id, $USER->id);
            
            // Check if conversation already has messages
            $history = dialogueai_get_conversation_history($conversationid, 1);
            
            if (empty($history) && !empty($dialogueai->welcomemessage)) {
                // Store welcome message as first bot message
                dialogueai_store_conversation_message($dialogueai->id, $USER->id, $conversationid, 'assistant', $dialogueai->welcomemessage);
            }
            
            echo json_encode([
                'success' => true,
                'conversationid' => $conversationid,
                'welcomemessage' => $dialogueai->welcomemessage ?: '',
                'hasHistory' => !empty($history)
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        break;
        
    case 'test':
        echo json_encode([
            'success' => true,
            'message' => 'AJAX endpoint is working!',
            'received_action' => $action,
            'timestamp' => time()
        ]);
        break;
        
    case 'restart_conversation':
        try {
            // Get current conversation ID
            $current_conversationid = dialogueai_get_current_conversation($dialogueai->id, $USER->id);
            
            // Delete existing conversation messages
            dialogueai_clear_conversation_history($current_conversationid);
            
            // Generate new conversation ID
            $new_conversationid = dialogueai_start_new_conversation($dialogueai->id, $USER->id);
            
            // Store welcome message if available
            if (!empty($dialogueai->welcomemessage)) {
                dialogueai_store_conversation_message($dialogueai->id, $USER->id, $new_conversationid, 'assistant', $dialogueai->welcomemessage);
            }
            
            echo json_encode([
                'success' => true,
                'conversationid' => $new_conversationid,
                'welcomemessage' => $dialogueai->welcomemessage ?: '',
                'message' => 'Conversation restarted successfully'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'error' => 'Invalid action: "' . $action . '" (length: ' . strlen($action) . ')',
            'available_actions' => ['send_message', 'sendmessage', 'load_history', 'init_conversation', 'restart_conversation', 'test']
        ]);
        break;
}

/**
 * Send message using Moodle's AI subsystem
 *
 * @param stdClass $dialogueai The dialogueai instance
 * @param string $message The user message
 * @param context $context The module context
 * @return string The AI response
 * @throws Exception If AI call fails
 */
function dialogueai_send_via_moodle_ai($dialogueai, $message, $context, $conversationid) {
    global $USER;
    
    try {
        // Get AI manager
        $aimanager = \core\ai\manager::get_manager();
        
        // Prepare system prompt
        $systemprompt = !empty($dialogueai->systemprompt) ? $dialogueai->systemprompt : 
            'You are a helpful AI assistant designed to help students learn. Be encouraging, clear, and educational in your responses.';
        
        // Replace parameters in system prompt
        $difficulty_labels = array(
            1 => 'very easy',
            2 => 'easy', 
            3 => 'medium',
            4 => 'difficult',
            5 => 'very difficult'
        );
        $difficulty_text = isset($difficulty_labels[$dialogueai->difficulty]) ? $difficulty_labels[$dialogueai->difficulty] : 'medium';
        $systemprompt = str_replace('[difficulty]', $difficulty_text, $systemprompt);
        $systemprompt = str_replace('[number_of_questions]', $dialogueai->numquestions, $systemprompt);
        
        // Get documentation context if available
        $doccontext = dialogueai_get_documentation_context($dialogueai);
        if (!empty($doccontext)) {
            $systemprompt .= "\n\nAdditional context from uploaded documentation:\n" . $doccontext;
        }
        
        // Get conversation history and format for API
        $history = dialogueai_get_conversation_history($conversationid, 10);
        $conversation_messages = dialogueai_format_conversation_for_api($history);
        
        // Create AI request with conversation history
        // Note: Moodle AI API might not support full conversation history in chat_request
        // This is a simplified implementation - you may need to concatenate history into the message
        $full_message = $message;
        if (!empty($conversation_messages)) {
            $context_text = "Previous conversation context:\n";
            foreach ($conversation_messages as $msg) {
                $context_text .= $msg['role'] . ": " . $msg['content'] . "\n";
            }
            $full_message = $context_text . "\nCurrent message: " . $message;
        }
        
        $request = new \core\ai\aiapi\request\chat_request(
            $systemprompt,
            $full_message,
            $context->id,
            'mod_dialogueai'
        );
        
        // Get AI provider (try to get configured provider or use default)
        $providers = $aimanager->get_providers_for_placement('core_ai\placement\generate_text');
        if (empty($providers)) {
            throw new Exception('No AI providers configured in Moodle');
        }
        
        $provider = reset($providers); // Use first available provider
        
        // Send request
        $response = $provider->chat($request);
        
        if (!$response || !$response->get_content()) {
            throw new Exception('Empty response from AI provider');
        }
        
        return trim($response->get_content());
        
    } catch (Exception $e) {
        error_log('Moodle AI Error: ' . $e->getMessage());
        // Fallback to direct OpenAI if Moodle AI fails
        return dialogueai_send_to_openai($dialogueai, $message, $conversationid);
    }
}

/**
 * Send message to OpenAI API and get response with retry logic
 *
 * @param stdClass $dialogueai The dialogueai instance
 * @param string $message The user message
 * @return string The AI response
 * @throws Exception If API call fails after retries
 */
function dialogueai_send_to_openai($dialogueai, $message, $conversationid) {
    global $CFG, $USER;
    
    // Get API key from activity settings
    $apikey = $dialogueai->openaiapi;
    
    if (empty($apikey)) {
        throw new Exception('OpenAI API key not configured. Please add your API key in the activity settings.');
    }
    
    // Prepare system prompt
    $systemprompt = !empty($dialogueai->systemprompt) ? $dialogueai->systemprompt : 
        'You are a helpful AI assistant designed to help students learn. Be encouraging, clear, and educational in your responses.';
    
    // Replace parameters in system prompt
    $difficulty_labels = array(
        1 => 'very easy',
        2 => 'easy', 
        3 => 'medium',
        4 => 'difficult',
        5 => 'very difficult'
    );
    $difficulty_text = isset($difficulty_labels[$dialogueai->difficulty]) ? $difficulty_labels[$dialogueai->difficulty] : 'medium';
    $systemprompt = str_replace('[difficulty]', $difficulty_text, $systemprompt);
    $systemprompt = str_replace('[number_of_questions]', $dialogueai->numquestions, $systemprompt);
    
    // Get documentation context if available
    $context = dialogueai_get_documentation_context($dialogueai);
    if (!empty($context)) {
        $systemprompt .= "\n\nAdditional context from uploaded documentation:\n" . $context;
    }
    
    // Get conversation history and build messages array
    $history = dialogueai_get_conversation_history($conversationid, 10);
    $conversation_messages = dialogueai_format_conversation_for_api($history);
    
    // Build messages array starting with system prompt
    $messages = [
        [
            'role' => 'system',
            'content' => $systemprompt
        ]
    ];
    
    // Add conversation history
    $messages = array_merge($messages, $conversation_messages);
    
    // Add current user message
    $messages[] = [
        'role' => 'user',
        'content' => $message
    ];
    
    // Prepare API request
    $data = [
        'model' => $dialogueai->openaimodel ?: 'gpt-3.5-turbo',
        'messages' => $messages,
        'max_tokens' => 500,
        'temperature' => 0.7
    ];
    
    // Retry logic with exponential backoff
    $maxRetries = 3;
    $baseDelay = 1; // Start with 1 second
    
    for ($attempt = 0; $attempt <= $maxRetries; $attempt++) {
        try {
            $result = dialogueai_make_openai_request($data, $apikey);
            return $result; // Success, return immediately
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            
            // Check if this is a rate limit error (429) or server error (5xx)
            if (preg_match('/HTTP (\d+)/', $errorMessage, $matches)) {
                $httpCode = (int)$matches[1];
                
                // Rate limit (429) or server errors (5xx) - retry with backoff
                if ($httpCode === 429 || ($httpCode >= 500 && $httpCode < 600)) {
                    if ($attempt < $maxRetries) {
                        $delay = $baseDelay * pow(2, $attempt); // Exponential backoff: 1s, 2s, 4s
                        error_log("OpenAI API rate limit/server error (HTTP $httpCode), retrying in {$delay}s (attempt " . ($attempt + 1) . "/$maxRetries)");
                        sleep($delay);
                        continue;
                    } else {
                        // Max retries reached
                        if ($httpCode === 429) {
                            throw new Exception('OpenAI API rate limit exceeded. Please wait a moment and try again. If this persists, check your API usage limits.');
                        } else {
                            throw new Exception('OpenAI API server error (HTTP ' . $httpCode . '). Please try again later.');
                        }
                    }
                } else {
                    // Other HTTP errors (4xx except 429) - don't retry
                    throw $e;
                }
            } else {
                // Non-HTTP errors (network, etc.) - don't retry
                throw $e;
            }
        }
    }
}

/**
 * Make a single request to OpenAI API
 *
 * @param array $data The request data
 * @param string $apikey The API key
 * @return string The AI response
 * @throws Exception If the request fails
 */
function dialogueai_make_openai_request($data, $apikey) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apikey
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        throw new Exception('Network error: Unable to connect to OpenAI. Please check your internet connection.');
    }
    
    // Handle different HTTP status codes with specific messages
    if ($httpcode !== 200) {
        $errorMessage = 'OpenAI API error: HTTP ' . $httpcode;
        
        // Try to get more specific error from response
        if ($response) {
            $errorData = json_decode($response, true);
            if (isset($errorData['error']['message'])) {
                $errorMessage .= ' - ' . $errorData['error']['message'];
            }
        }
        
        // Add user-friendly explanations
        switch ($httpcode) {
            case 401:
                $errorMessage .= ' (Invalid API key)';
                break;
            case 403:
                $errorMessage .= ' (Access forbidden - check your API key permissions)';
                break;
            case 429:
                $errorMessage .= ' (Rate limit exceeded)';
                break;
            case 500:
            case 502:
            case 503:
            case 504:
                $errorMessage .= ' (OpenAI server error)';
                break;
        }
        
        throw new Exception($errorMessage);
    }
    
    $responseData = json_decode($response, true);
    
    if (!$responseData) {
        throw new Exception('Invalid JSON response from OpenAI API');
    }
    
    if (isset($responseData['error'])) {
        throw new Exception('OpenAI API error: ' . $responseData['error']['message']);
    }
    
    if (!isset($responseData['choices'][0]['message']['content'])) {
        throw new Exception('Unexpected response format from OpenAI API');
    }
    
    return trim($responseData['choices'][0]['message']['content']);
}

/**
 * Get documentation context from uploaded files
 *
 * @param stdClass $dialogueai The dialogueai instance
 * @return string The documentation content
 */
function dialogueai_get_documentation_context($dialogueai) {
    global $DB;
    
    $cm = get_coursemodule_from_instance('dialogueai', $dialogueai->id);
    if (!$cm) {
        return '';
    }
    
    $context = context_module::instance($cm->id);
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'mod_dialogueai', 'documentation', 0, 'filename', false);
    
    $content = '';
    $total_chars = 0;
    $max_chars = dialogueai_get_model_char_limit($dialogueai->openaimodel ?: 'gpt-3.5-turbo');
    
    foreach ($files as $file) {
        if ($file->get_mimetype() === 'text/plain') {
            $file_content = $file->get_content();
            $file_chars = strlen($file_content);
            
            // Check if adding this file would exceed the limit
            if ($total_chars + $file_chars > $max_chars) {
                // Log warning but continue with truncated content
                error_log('DialogueAI: Documentation exceeds ' . number_format($max_chars) . ' character limit. Total: ' . ($total_chars + $file_chars) . ' chars');
                
                // Add as much as we can from this file
                $remaining_chars = $max_chars - $total_chars;
                if ($remaining_chars > 0) {
                    $content .= "\n\nFile: " . $file->get_filename() . " (truncated)\n";
                    $content .= substr($file_content, 0, $remaining_chars);
                }
                break; // Stop processing more files
            }
            
            $content .= "\n\nFile: " . $file->get_filename() . "\n";
            $content .= $file_content;
            $total_chars += $file_chars;
        }
    }
    
    return $content;
}

/**
 * Validate documentation character count
 *
 * @param int $contextid The context ID
 * @return array Array with 'valid' boolean and 'char_count' integer
 */
function dialogueai_validate_documentation_length($contextid, $model = 'gpt-3.5-turbo') {
    $fs = get_file_storage();
    $files = $fs->get_area_files($contextid, 'mod_dialogueai', 'documentation', 0, 'filename', false);
    
    $total_chars = 0;
    $max_chars = dialogueai_get_model_char_limit($model);
    
    foreach ($files as $file) {
        if ($file->get_mimetype() === 'text/plain') {
            $total_chars += strlen($file->get_content());
        }
    }
    
    return [
        'valid' => $total_chars <= $max_chars,
        'char_count' => $total_chars,
        'max_chars' => $max_chars
    ];
}

/**
 * Get character limit based on OpenAI model
 *
 * @param string $model The OpenAI model name
 * @return int Character limit for the model
 */
function dialogueai_get_model_char_limit($model) {
    switch ($model) {
        case 'gpt-3.5-turbo':
            return 50000;
        case 'gpt-4-turbo':
        case 'gpt-4o':
            return 475000;
        default:
            return 50000; // Default to most restrictive
    }
}
