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

$plugin->component = 'mod_dialogueai';
$plugin->version = 2024100901;  // Added OpenAI model selection with dynamic character limits (GPT-3.5-turbo: 50K, GPT-4-turbo/4o: 475K)
$plugin->requires = 2022112800; // Moodle 5.0.0
$plugin->maturity = MATURITY_STABLE;
$plugin->release = '1.0.0';     // DialogueAI release
$plugin->supported = [500, 599]; // Compatible with Moodle 5.0.0 and up
