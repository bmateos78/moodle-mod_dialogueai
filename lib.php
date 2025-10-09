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

defined('MOODLE_INTERNAL') || die();

/**
 * List of features supported in DialogueAI module
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if unknown
 */
function dialogueai_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_OTHER;
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_MOD_INTRO:               return false;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return false;
        case FEATURE_COMPLETION_HAS_RULES:    return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;
        case FEATURE_MOD_PURPOSE:             return MOD_PURPOSE_CONTENT;
        default: return null;
    }
}

/**
 * Saves a new instance of the dialogueai into the database
 *
 * @param stdClass $dialogueai An object from the form in mod_form.php
 * @param mod_dialogueai_mod_form $mform The form instance
 * @return int The id of the newly inserted dialogueai record
 */
function dialogueai_add_instance($dialogueai, $mform = null) {
    global $DB;

    $dialogueai->timecreated = time();
    $dialogueai->timemodified = $dialogueai->timecreated;

    // Handle file uploads for documentation
    if ($mform) {
        $context = context_module::instance($dialogueai->coursemodule);
        if ($draftitemid = $dialogueai->documentation) {
            file_save_draft_area_files($draftitemid, $context->id, 'mod_dialogueai', 'documentation', 0,
                array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 10));
        }
    }

    $id = $DB->insert_record('dialogueai', $dialogueai);
    return $id;
}

/**
 * Updates an instance of the dialogueai in the database
 *
 * @param stdClass $dialogueai An object from the form in mod_form.php
 * @param mod_dialogueai_mod_form $mform The form instance
 * @return boolean Success/Fail
 */
function dialogueai_update_instance($dialogueai, $mform = null) {
    global $DB;

    $dialogueai->timemodified = time();
    $dialogueai->id = $dialogueai->instance;

    // Handle file uploads for documentation
    if ($mform) {
        $context = context_module::instance($dialogueai->coursemodule);
        if ($draftitemid = $dialogueai->documentation) {
            file_save_draft_area_files($draftitemid, $context->id, 'mod_dialogueai', 'documentation', 0,
                array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 10));
        }
    }

    return $DB->update_record('dialogueai', $dialogueai);
}

/**
 * Removes an instance of the dialogueai from the database
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function dialogueai_delete_instance($id) {
    global $DB;

    if (!$dialogueai = $DB->get_record('dialogueai', array('id' => $id))) {
        return false;
    }

    // Delete any files associated with this instance
    $cm = get_coursemodule_from_instance('dialogueai', $id);
    if ($cm) {
        $context = context_module::instance($cm->id);
        $fs = get_file_storage();
        $fs->delete_area_files($context->id, 'mod_dialogueai');
    }

    $DB->delete_records('dialogueai', array('id' => $dialogueai->id));
    return true;
}

/**
 * Returns the information on whether the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function dialogueai_get_file_areas($course, $cm, $context) {
    return array(
        'documentation' => get_string('documentation', 'dialogueai'),
    );
}

/**
 * File browsing support for dialogueai file areas
 *
 * @package mod_dialogueai
 * @category files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info instance or null if not found
 */
function dialogueai_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Store a conversation message in the database
 *
 * @param int $dialogueaiid The dialogueai instance ID
 * @param int $userid The user ID
 * @param string $conversationid The conversation session ID
 * @param string $role The role (user, assistant, system)
 * @param string $message The message content
 * @return int The ID of the stored message
 */
function dialogueai_store_conversation_message($dialogueaiid, $userid, $conversationid, $role, $message) {
    global $DB;
    
    $record = new stdClass();
    $record->dialogueaiid = $dialogueaiid;
    $record->userid = $userid;
    $record->conversationid = $conversationid;
    $record->role = $role;
    $record->message = $message;
    $record->timecreated = time();
    
    return $DB->insert_record('dialogueai_conversations', $record);
}

/**
 * Get conversation history for a specific conversation
 *
 * @param string $conversationid The conversation ID
 * @param int $limit Maximum number of messages to retrieve (default 10)
 * @return array Array of conversation messages
 */
function dialogueai_get_conversation_history($conversationid, $limit = 10) {
    global $DB;
    
    // Ensure limit is an integer and safe
    $limit = (int)$limit;
    if ($limit <= 0) {
        $limit = 10;
    }
    
    $sql = "SELECT * FROM {dialogueai_conversations} 
            WHERE conversationid = ? 
            ORDER BY timecreated DESC";
    
    $messages = $DB->get_records_sql($sql, array($conversationid), 0, $limit);
    
    // Reverse the array to get chronological order (oldest first)
    return array_reverse($messages, true);
}

/**
 * Get or create current conversation for a user and dialogueai instance
 *
 * @param int $dialogueaiid The dialogueai instance ID
 * @param int $userid The user ID
 * @return string The current conversation ID
 */
function dialogueai_get_current_conversation($dialogueaiid, $userid) {
    global $DB;
    
    // Try to get the most recent conversation for this user and activity
    $sql = "SELECT conversationid FROM {dialogueai_conversations} 
            WHERE dialogueaiid = ? AND userid = ? 
            ORDER BY timecreated DESC 
            LIMIT 1";
    
    $result = $DB->get_record_sql($sql, array($dialogueaiid, $userid));
    
    if ($result) {
        return $result->conversationid;
    }
    
    // No existing conversation, create new one
    return dialogueai_start_new_conversation($dialogueaiid, $userid);
}

/**
 * Start a new conversation session
 *
 * @param int $dialogueaiid The dialogueai instance ID
 * @param int $userid The user ID
 * @return string The new conversation ID
 */
function dialogueai_start_new_conversation($dialogueaiid, $userid) {
    // Generate unique conversation ID
    return uniqid('conv_' . $dialogueaiid . '_' . $userid . '_', true);
}

/**
 * Get all conversations for a user and dialogueai instance
 *
 * @param int $dialogueaiid The dialogueai instance ID
 * @param int $userid The user ID
 * @return array Array of conversation IDs with metadata
 */
function dialogueai_get_user_conversations($dialogueaiid, $userid) {
    global $DB;
    
    $sql = "SELECT conversationid, MIN(timecreated) as started, MAX(timecreated) as updated, COUNT(*) as messagecount
            FROM {dialogueai_conversations} 
            WHERE dialogueaiid = ? AND userid = ? 
            GROUP BY conversationid 
            ORDER BY updated DESC";
    
    return $DB->get_records_sql($sql, array($dialogueaiid, $userid));
}

/**
 * Clear conversation history for a specific conversation
 *
 * @param string $conversationid The conversation ID
 * @return bool Success status
 */
function dialogueai_clear_conversation_history($conversationid) {
    global $DB;
    
    return $DB->delete_records('dialogueai_conversations', array(
        'conversationid' => $conversationid
    ));
}

/**
 * Clear all conversations for a user and dialogueai instance
 *
 * @param int $dialogueaiid The dialogueai instance ID
 * @param int $userid The user ID
 * @return bool Success status
 */
function dialogueai_clear_user_conversations($dialogueaiid, $userid) {
    global $DB;
    
    return $DB->delete_records('dialogueai_conversations', array(
        'dialogueaiid' => $dialogueaiid,
        'userid' => $userid
    ));
}

/**
 * Format conversation history for OpenAI API messages format
 *
 * @param array $history Array of conversation messages from database
 * @return array Array formatted for OpenAI API
 */
function dialogueai_format_conversation_for_api($history) {
    $messages = array();
    
    foreach ($history as $message) {
        // Map database roles to OpenAI API roles
        $role = $message->role;
        if ($role === 'bot' || $role === 'assistant') {
            $role = 'assistant';
        } else if ($role === 'user') {
            $role = 'user';
        } else if ($role === 'system') {
            $role = 'system';
        }
        
        $messages[] = array(
            'role' => $role,
            'content' => $message->message
        );
    }
    
    return $messages;
}

/**
 * Check if conversation should be marked as complete
 *
 * @param int $dialogueaiid The dialogueai instance ID
 * @param int $userid The user ID
 * @param string $conversationid The conversation ID
 * @return bool True if conversation should be completed
 */
function dialogueai_should_complete_conversation($dialogueaiid, $userid, $conversationid) {
    global $DB;
    
    // Get the dialogueai instance to check number of questions setting
    $dialogueai = $DB->get_record('dialogueai', array('id' => $dialogueaiid));
    if (!$dialogueai) {
        return false;
    }
    
    // Count bot messages (questions) in this conversation
    $bot_message_count = $DB->count_records('dialogueai_conversations', array(
        'conversationid' => $conversationid,
        'role' => 'assistant'
    ));
    
    // Complete if we've reached the target number of questions (adjusted by +2 for timing)
    return $bot_message_count >= ($dialogueai->numquestions + 2);
}

/**
 * Check if this is the exact moment when conversation should be completed
 * (i.e., we just reached the target number of questions)
 *
 * @param int $dialogueaiid The dialogueai instance ID
 * @param int $userid The user ID
 * @param string $conversationid The conversation ID
 * @return bool True if this is the completion moment
 */
function dialogueai_is_completion_moment($dialogueaiid, $userid, $conversationid) {
    global $DB;
    
    // Get the dialogueai instance to check number of questions setting
    $dialogueai = $DB->get_record('dialogueai', array('id' => $dialogueaiid));
    if (!$dialogueai) {
        return false;
    }
    
    // Count bot messages (questions) in this conversation
    $bot_message_count = $DB->count_records('dialogueai_conversations', array(
        'conversationid' => $conversationid,
        'role' => 'assistant'
    ));
    
    // Return true only if we exactly reached the target (adjusted by +2 for timing)
    return $bot_message_count == ($dialogueai->numquestions + 2);
}

/**
 * Mark activity as complete for a user
 *
 * @param int $cmid The course module ID
 * @param int $userid The user ID
 * @return bool True if completion was marked successfully
 */
function dialogueai_mark_activity_complete($cmid, $userid) {
    global $CFG;
    
    // Check if completion is enabled
    if (!$CFG->enablecompletion) {
        return false;
    }
    
    require_once($CFG->libdir . '/completionlib.php');
    
    $course = get_course_by_courseid_from_cmid($cmid);
    $cm = get_coursemodule_from_id('dialogueai', $cmid);
    
    if (!$course || !$cm) {
        return false;
    }
    
    $completion = new completion_info($course);
    
    if ($completion->is_enabled($cm)) {
        $completion->update_state($cm, COMPLETION_COMPLETE, $userid);
        return true;
    }
    
    return false;
}

/**
 * Helper function to get course from course module ID
 *
 * @param int $cmid The course module ID
 * @return object|false Course object or false
 */
function get_course_by_courseid_from_cmid($cmid) {
    global $DB;
    
    $cm = $DB->get_record('course_modules', array('id' => $cmid));
    if (!$cm) {
        return false;
    }
    
    return $DB->get_record('course', array('id' => $cm->course));
}

/**
 * Obtains the automatic completion state for this dialogueai based on any conditions
 * in dialogueai settings.
 *
 * @param object $course Course
 * @param object $cm Course-module
 * @param int $userid User ID
 * @param bool $type Type of comparison (or/and; can be used as return value if no conditions)
 * @return bool True if completed, false if not, $type if conditions not set.
 */
function dialogueai_get_completion_state($course, $cm, $userid, $type) {
    global $DB;

    // Get dialogueai details
    $dialogueai = $DB->get_record('dialogueai', array('id' => $cm->instance), '*', MUST_EXIST);

    // If completion isn't enabled for this activity, or there's no custom completion criteria
    if (!$dialogueai->completionconversation || !$dialogueai->numquestions) {
        return $type;
    }

    // Get current conversation for this user
    $conversationid = dialogueai_get_current_conversation($dialogueai->id, $userid);
    if (!$conversationid) {
        return false;
    }

    // Check if user has completed the required number of questions
    return dialogueai_should_complete_conversation($dialogueai->id, $userid, $conversationid);
}
