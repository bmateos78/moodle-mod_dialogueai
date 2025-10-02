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
 * Form for editing DialogueAI instances.
 *
 * @package   mod_dialogueai
 * @copyright 2024 Your Name <your@email.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form.
 *
 * @package   mod_dialogueai
 * @copyright 2024 Your Name <your@email.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_dialogueai_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are shown.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('name'), array('size' => '64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('required'), 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        // Adding DialogueAI specific settings fieldset.
        $mform->addElement('header', 'dialoguesettings', get_string('dialoguesettings', 'dialogueai'));

        // Bot Name field
        $mform->addElement('text', 'botname', get_string('botname', 'dialogueai'), array('size' => '64'));
        $mform->setType('botname', PARAM_TEXT);
        $mform->addHelpButton('botname', 'botname', 'dialogueai');
        // Student Name field
        $mform->addElement('text', 'studentname', get_string('studentname', 'dialogueai'), array('size' => '64'));
        $mform->setType('studentname', PARAM_TEXT);
        $mform->addHelpButton('studentname', 'studentname', 'dialogueai');

        // Welcome message field
        $mform->addElement('textarea', 'welcomemessage', get_string('welcomemessage', 'dialogueai'), 
            array('rows' => 4, 'cols' => 80));
        $mform->setType('welcomemessage', PARAM_TEXT);
        $mform->addHelpButton('welcomemessage', 'welcomemessage', 'dialogueai');

        // Difficulty level dropdown
        $difficulty_options = array(
            1 => get_string('difficulty_1', 'dialogueai'),
            2 => get_string('difficulty_2', 'dialogueai'),
            3 => get_string('difficulty_3', 'dialogueai'),
            4 => get_string('difficulty_4', 'dialogueai'),
            5 => get_string('difficulty_5', 'dialogueai')
        );
        $mform->addElement('select', 'difficulty', get_string('difficulty', 'dialogueai'), $difficulty_options);
        $mform->setDefault('difficulty', 3);
        $mform->addHelpButton('difficulty', 'difficulty', 'dialogueai');

        // Number of questions dropdown
        $numquestions_options = array();
        for ($i = 1; $i <= 20; $i++) {
            $numquestions_options[$i] = $i;
        }
        $mform->addElement('select', 'numquestions', get_string('numquestions', 'dialogueai'), $numquestions_options);
        $mform->setDefault('numquestions', 5);
        $mform->addHelpButton('numquestions', 'numquestions', 'dialogueai');

        // Documentation upload field
        $mform->addElement('filemanager', 'documentation', get_string('documentation', 'dialogueai'), null,
            array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 10, 'accepted_types' => array('.pdf', '.txt', '.doc', '.docx')));
        $mform->addHelpButton('documentation', 'documentation', 'dialogueai');

        // System Prompt field
        $mform->addElement('textarea', 'systemprompt', get_string('systemprompt', 'dialogueai'), 
            array('rows' => 10, 'cols' => 80));
        $mform->setType('systemprompt', PARAM_TEXT);
        $mform->addHelpButton('systemprompt', 'systemprompt', 'dialogueai');

        // OpenAI API Key field
        $mform->addElement('passwordunmask', 'openaiapi', get_string('openaiapi', 'dialogueai'), array('size' => '64'));
        $mform->setType('openaiapi', PARAM_TEXT);
        $mform->addHelpButton('openaiapi', 'openaiapi', 'dialogueai');

        // Add standard elements.
        $this->standard_coursemodule_elements();

        // Add standard buttons.
        $this->add_action_buttons();
    }

    /**
     * Prepares the form before data are set
     *
     * @param array $defaultvalues
     */
    public function data_preprocessing(&$defaultvalues) {
        if ($this->current->instance) {
            // Prepare file manager for documentation
            $draftitemid = file_get_submitted_draft_itemid('documentation');
            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_dialogueai', 'documentation', 0,
                array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 10));
            $defaultvalues['documentation'] = $draftitemid;
        }
    }

    /**
     * Add any custom completion rules.
     *
     * @return array Array of string IDs of added items, empty array if none
     */
    public function add_completion_rules() {
        return [];
    }

    /**
     * Determines if completion is enabled for this module.
     *
     * @param array $data Data from the form
     * @return bool True if completion is enabled
     */
    public function completion_rule_enabled($data) {
        return false;
    }

    /**
     * Overriding the parent method to avoid PEAR library calls
     */
    public function definition_after_data() {
        // Intentionally empty to avoid PEAR library calls
    }
}
