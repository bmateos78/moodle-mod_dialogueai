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
 * Strings for component 'dialogueai', language 'en'
 *
 * @package   mod_dialogueai
 * @copyright 2024 Your Name <your@email.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['modulename'] = 'DialogueAI';
$string['modulenameplural'] = 'DialogueAI Activities';
$string['pluginname'] = 'DialogueAI';
$string['pluginadministration'] = 'DialogueAI administration';

// Form field labels
$string['botname'] = 'Bot Name';
$string['botname_help'] = 'Enter the name for the AI bot that will interact with students';
$string['studentname'] = 'Student Name';
$string['studentname_help'] = 'Enter the name of the student for personalized interactions';
$string['welcomemessage'] = 'Welcome Message';
$string['welcomemessage_help'] = 'Enter a welcome message that the bot will automatically send when starting a new conversation';
$string['difficulty'] = 'Question Difficulty';
$string['difficulty_help'] = 'Select the difficulty level for questions the AI will ask';
$string['difficulty_1'] = '1 - Very Easy';
$string['difficulty_2'] = '2 - Easy';
$string['difficulty_3'] = '3 - Medium';
$string['difficulty_4'] = '4 - Difficult';
$string['difficulty_5'] = '5 - Very Difficult';
$string['numquestions'] = 'Number of Questions';
$string['numquestions_help'] = 'Select how many questions the assistant will ask before considering the conversation finished';
$string['documentation'] = 'Documentation';
$string['documentation_help'] = 'Upload documentation files that the AI will use for dialogue';
$string['systemprompt'] = 'System Prompt';
$string['systemprompt_help'] = 'Enter the system prompt that will guide the AI\'s behavior and responses';
$string['openaiapi'] = 'OpenAI API Key';
$string['openaiapi_help'] = 'Enter your OpenAI API key for AI functionality';

// Settings fieldset
$string['dialoguesettings'] = 'Dialogue Settings';

// Capabilities
$string['dialogueai:addinstance'] = 'Add a new DialogueAI activity';
$string['dialogueai:view'] = 'View DialogueAI activity';

// Activity interface
$string['startdialogue'] = 'Start Dialogue';
$string['dialoguemessage'] = 'Ready to start your AI-powered dialogue!';

// Admin settings
$string['defaultbotname'] = 'Default Bot Name';
$string['defaultbotname_desc'] = 'The default name for AI bots in new DialogueAI activities';
// Default system prompt removed - now configured per activity
$string['maxfilesize'] = 'Maximum File Size';
$string['maxfilesize_desc'] = 'Maximum size allowed for documentation file uploads';
$string['maxfiles'] = 'Maximum Files';
$string['maxfiles_desc'] = 'Maximum number of documentation files that can be uploaded per activity';
