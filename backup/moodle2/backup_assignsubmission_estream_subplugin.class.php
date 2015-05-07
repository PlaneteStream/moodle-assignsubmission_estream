<?php
/**
 * backup class for the Planet eStream Assignment Submission Plugin
 * extends submission plugin base class
 *
 * @package	assignsubmission_estream
 * @copyright	Planet Enterprises Ltd
 * @license	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
defined('MOODLE_INTERNAL') || die();
class backup_assignsubmission_estream_subplugin extends backup_subplugin

	{
	protected function define_submission_subplugin_structure()
		{
		$subplugin = $this->get_subplugin_element();
		$subpluginwrapper = new backup_nested_element($this->get_recommended_name());
		$subpluginelement = new backup_nested_element('submission_estream', null, array(
			'widgettype',
			'submission'
		));
		$subplugin->add_child($subpluginwrapper);
		$subpluginwrapper->add_child($subpluginelement);
		$subpluginelement->set_source_table('assignsubmission_estream', array(
			'submission' => backup::VAR_PARENTID
		));
		$subpluginelement->annotate_files('assignsubmission_estream', 'submissions_estream', 'submission');
		return $subplugin;
		}
	}
