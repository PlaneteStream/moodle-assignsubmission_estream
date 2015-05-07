<?php
/**
 * Settings for the Planet eStream Assignment Submission Plugin
 *
 * @package	assignsubmission_estream
 * @copyright	Planet Enterprises Ltd
 * @license	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
$settings->add(new admin_setting_configcheckbox('assignsubmission_estream/default', new lang_string('default', 'assignsubmission_estream') , new lang_string('default_help', 'assignsubmission_estream') , 1));
$settings->add(new admin_setting_configtext('assignsubmission_estream/url', new lang_string('settingsurl', 'assignsubmission_estream') , new lang_string('settingsurl_help', 'assignsubmission_estream') , get_config('planetestream', 'url') , PARAM_URL));
