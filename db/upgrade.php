<?php
/**
 * DB/upgrade stub for the Planet eStream Assignment Submission Plugin
 *
 * @package	assignsubmission_estream
 * @copyright	Planet Enterprises Ltd
 * @license	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

function xmldb_assignsubmission_estream_upgrade($oldversion)
	{
	global $CFG, $DB;
	$dbman = $DB->get_manager();
	return true;
	}
