<?php
/**
 * restore class for the Planet eStream Assignment Submission Plugin
 * extends submission plugin base class
 *
 * @package	assignsubmission_estream
 * @copyright	Planet Enterprises Ltd
 * @license	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class restore_assignsubmission_estream_subplugin extends restore_subplugin

	{
	protected function define_submission_subplugin_structure()
		{
		$paths = array();
		$elename = $this->get_namefor('submission');
		$elepath = $this->get_pathfor('/submission_estream');
		$paths[] = new restore_path_element($elename, $elepath);
		return $paths;
		}

	public function process_assignsubmission_estream_submission($data)
		{
		global $DB;
		$data = (object)$data;
		$data->assignment = $this->get_new_parentid('assign');
		$oldsubmissionid = $data->submission;
		$data->submission = $this->get_mappingid('submission', $data->submission);
		$DB->insert_record('submissions_estream', $data);
		$this->add_related_files('submissions_estream', 'submissions_estream', 'submission', null, $oldsubmissionid);
		}
	}
