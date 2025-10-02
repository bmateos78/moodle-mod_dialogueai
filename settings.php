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
 * DialogueAI module admin settings and defaults
 *
 * @package   mod_dialogueai
 * @copyright 2024 Your Name <your@email.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    // Default bot name setting
    $settings->add(new admin_setting_configtext(
        'mod_dialogueai/defaultbotname',
        get_string('defaultbotname', 'mod_dialogueai'),
        get_string('defaultbotname_desc', 'mod_dialogueai'),
        'AI Assistant',
        PARAM_TEXT
    ));

    // Note: System prompts are now configured individually for each activity

    // Maximum file upload size for documentation
    $settings->add(new admin_setting_configselect(
        'mod_dialogueai/maxfilesize',
        get_string('maxfilesize', 'mod_dialogueai'),
        get_string('maxfilesize_desc', 'mod_dialogueai'),
        1048576, // 1MB default
        array(
            262144 => '256KB',
            524288 => '512KB',
            1048576 => '1MB',
            2097152 => '2MB',
            5242880 => '5MB',
            10485760 => '10MB'
        )
    ));

    // Maximum number of files per activity
    $settings->add(new admin_setting_configtext(
        'mod_dialogueai/maxfiles',
        get_string('maxfiles', 'mod_dialogueai'),
        get_string('maxfiles_desc', 'mod_dialogueai'),
        10,
        PARAM_INT
    ));
}
