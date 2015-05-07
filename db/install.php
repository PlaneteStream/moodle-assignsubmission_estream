<?php
/**
 * Post-install code for the Planet eStream Assignment Submission Plugin
 *
 * @package	assignsubmission_estream
 * @copyright	Planet Enterprises Ltd
 * @license	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
defined('MOODLE_INTERNAL') || die();

function xmldb_assignsubmission_estream_install()
	{
	global $CFG;
	require_once ($CFG->dirroot . '/mod/assign/adminlib.php');

	$pluginmanager = new assign_plugin_manager('assignsubmission');
	$pluginmanager->move_plugin('estream', 'up');
	$pluginmanager->move_plugin('estream', 'up');
	return true;
	}