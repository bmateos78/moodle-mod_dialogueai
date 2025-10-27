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
 * This file keeps track of upgrades to the dialogueai module
 *
 * @package   mod_dialogueai
 * @copyright 2024 Your Name <your@email.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute dialogueai upgrade from the given old version
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_dialogueai_upgrade($oldversion) {
    global $DB;
    
    $dbman = $DB->get_manager();
    
    // Add welcome message field to dialogueai table
    if ($oldversion < 2024092602) {
        $table = new xmldb_table('dialogueai');
        $field = new xmldb_field('welcomemessage', XMLDB_TYPE_TEXT, null, null, null, null, null, 'studentname');
        
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        upgrade_mod_savepoint(true, 2024092602, 'dialogueai');
    }
    
    // Create conversation table
    if ($oldversion < 2024092603) {
        $table = new xmldb_table('dialogueai_conversations');
        
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('dialogueaiid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('role', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('message', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('dialogueaiid', XMLDB_KEY_FOREIGN, array('dialogueaiid'), 'dialogueai', array('id'));
        $table->add_key('userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));
        
        $table->add_index('dialogueai_user_time', XMLDB_INDEX_NOTUNIQUE, array('dialogueaiid', 'userid', 'timecreated'));
        
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        
        upgrade_mod_savepoint(true, 2024092603, 'dialogueai');
    }
    
    // Add conversationid field to existing conversation table
    if ($oldversion < 2024092604) {
        $table = new xmldb_table('dialogueai_conversations');
        
        // Add conversationid field
        $field = new xmldb_field('conversationid', XMLDB_TYPE_CHAR, '40', null, XMLDB_NOTNULL, null, null, 'userid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Generate conversation IDs for existing records
        // Group existing messages by user and activity, assign same conversation ID to each group
        $sql = "SELECT DISTINCT dialogueaiid, userid FROM {dialogueai_conversations} WHERE conversationid = '' OR conversationid IS NULL";
        $usergroups = $DB->get_records_sql($sql);
        
        foreach ($usergroups as $group) {
            $conversationid = uniqid('conv_', true);
            $DB->set_field('dialogueai_conversations', 'conversationid', $conversationid, 
                array('dialogueaiid' => $group->dialogueaiid, 'userid' => $group->userid));
        }
        
        // Update indexes
        $index = new xmldb_index('dialogueai_user_time', XMLDB_INDEX_NOTUNIQUE, array('dialogueaiid', 'userid', 'timecreated'));
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }
        
        // Add new indexes
        $index1 = new xmldb_index('dialogueai_user_conversation', XMLDB_INDEX_NOTUNIQUE, array('dialogueaiid', 'userid', 'conversationid'));
        if (!$dbman->index_exists($table, $index1)) {
            $dbman->add_index($table, $index1);
        }
        
        $index2 = new xmldb_index('conversation_time', XMLDB_INDEX_NOTUNIQUE, array('conversationid', 'timecreated'));
        if (!$dbman->index_exists($table, $index2)) {
            $dbman->add_index($table, $index2);
        }
        
        upgrade_mod_savepoint(true, 2024092604, 'dialogueai');
    }
    
    // Add difficulty and numquestions fields
    if ($oldversion < 2024093002) {
        $table = new xmldb_table('dialogueai');
        
        // Add difficulty field
        $field = new xmldb_field('difficulty', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '3', 'welcomemessage');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Add numquestions field
        $field = new xmldb_field('numquestions', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '5', 'difficulty');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        upgrade_mod_savepoint(true, 2024093002, 'dialogueai');
    }
    
    // Add completion conversation field
    if ($oldversion < 2024100602) {
        $table = new xmldb_table('dialogueai');
        
        // Add completionconversation field
        $field = new xmldb_field('completionconversation', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'numquestions');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        upgrade_mod_savepoint(true, 2024100602, 'dialogueai');
    }
    
    // Add OpenAI model selection field
    if ($oldversion < 2024100901) {
        $table = new xmldb_table('dialogueai');
        
        // Add openaimodel field
        $field = new xmldb_field('openaimodel', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, 'gpt-3.5-turbo', 'completionconversation');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        upgrade_mod_savepoint(true, 2024100901, 'dialogueai');
    }
    
    // Remove systemprompt field for production version
    if ($oldversion < 2024101001) {
        $table = new xmldb_table('dialogueai');
        
        // Drop systemprompt field if it exists
        $field = new xmldb_field('systemprompt');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        
        upgrade_mod_savepoint(true, 2024101001, 'dialogueai');
    }
    
    return true;
}
