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
 * Settings for the Planet eStream Assignment Submission Plugin
 *
 * @package        assignsubmission_estream
 * @copyright        Planet Enterprises Ltd
 * @license        http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
$settings->add(new admin_setting_configcheckbox('assignsubmission_estream/default',
new lang_string('default', 'assignsubmission_estream'),
new lang_string('default_help', 'assignsubmission_estream') , 1));
$settings->add(new admin_setting_configtext('assignsubmission_estream/url',
new lang_string('settingsurl', 'assignsubmission_estream'),
new lang_string('settingsurl_help', 'assignsubmission_estream'),
get_config('planetestream', 'url') , PARAM_URL));
