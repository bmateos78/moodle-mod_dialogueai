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
$string['openaimodel'] = 'OpenAI Model';
$string['openaimodel_help'] = 'Select which OpenAI model to use for conversations. Different models have different capabilities and character limits.';
$string['model_gpt35turbo'] = 'GPT-3.5 Turbo (Fast, 50K chars max)';
$string['model_gpt4turbo'] = 'GPT-4 Turbo (Advanced, 475K chars max)';
$string['model_gpt4o'] = 'GPT-4o (Advanced & Efficient, 475K chars max)';
$string['documentation'] = 'Documentation';
$string['documentation_help'] = 'Upload documentation files that the AI will use for dialogue. Character limit depends on selected OpenAI model.';
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
$string['restartconversation'] = 'Restart Conversation';
$string['restartconversation_help'] = 'Delete current conversation and start fresh with welcome message';
$string['confirmrestart'] = 'Are you sure you want to restart the conversation? This will delete all current messages.';
$string['activitycompleted'] = 'Congratulations! You have completed this dialogue activity. Your progress has been recorded.';
$string['completionconversation'] = 'Student must complete the conversation';
$string['completionconversationgroup'] = 'Require conversation completion';
$string['completionconversationgroup_help'] = 'If enabled, the activity will be marked as complete when the student completes the required number of questions in the conversation.';
$string['documentationtolong'] = 'The uploaded documentation is too long. Current: {$a->current} characters, Maximum allowed: {$a->max} characters. Please reduce the content or split into multiple activities.';
$string['documentationlimitinfo'] = 'Character Limit: Maximum 50,000 characters total across all text files (.txt). Only text files are processed for AI context.';
$string['documentationlimitinfo_dynamic'] = 'Character Limit: Depends on selected OpenAI model. Only text files (.txt) are processed for AI context.';

// Admin settings
$string['defaultbotname'] = 'Default Bot Name';
$string['defaultbotname_desc'] = 'The default name for AI bots in new DialogueAI activities';
// Default system prompt removed - now configured per activity
$string['maxfilesize'] = 'Maximum File Size';
$string['maxfilesize_desc'] = 'Maximum size allowed for documentation file uploads';
$string['maxfiles'] = 'Maximum Files';
$string['maxfiles_desc'] = 'Maximum number of documentation files that can be uploaded per activity';
