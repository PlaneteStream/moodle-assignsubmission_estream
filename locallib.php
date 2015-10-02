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
 * local library class for the Planet eStream Assignment Submission Plugin
 * extends submission plugin base class
 *
 * @package        assignsubmission_estream
 * @copyright        Planet Enterprises Ltd
 * @license        http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
defined('MOODLE_INTERNAL') || die();
class assign_submission_estream extends assign_submission_plugin
{
    /**
     * Get the name of the online text submission plugin
     * @return string
     */
    public function get_name() {
            return get_string('shortname', 'assignsubmission_estream');
    }
    /**
     * Save the submission plugin settings
     *
     * @param stdClass $data
     * @return bool
     */
    public function save_settings(stdClass $data) {
            return true;
    }
    /**
     * Read the submission information from the database
     *
     * @param  int $submissionid
     * @return mixed
     */
    private function funcgetsubmission($submissionid) {
            global $DB;
            return $DB->get_record('assignsubmission_estream', array(
                    'submission' => $submissionid
            ));
    }
    /**
     * Embed the player
     *
     * @return string
     */
    public function funcembedplayer($cdid, $embedcode) {
        $url = rtrim(get_config('assignsubmission_estream', 'url') , '/');
        if (empty($url)) {
                $url = rtrim(get_config('planetestream', 'url') , '/');
        }
        if ($cdid == "") {
            return "";
        } else {
            return "<iframe height=\"198\" width=\"352\" src=\"".$url."/Embed.aspx?id=".$cdid
            ."&amp;code=".$embedcode."&amp;wmode=opaque&amp;viewonestream=0\" frameborder=\"0\"></iframe>";
        }
    }
    /**
     * Save data to the database
     *
     * @param stdClass $submission
     * @param stdClass $data
     * @return bool
     */
    public function save(stdClass $submission, stdClass $data) {
        global $DB;
        $thissubmission = $this->funcgetsubmission($submission->id);
        if ($thissubmission) {
                $thissubmission->submission = $submission->id;
                $thissubmission->assignment = $this->assignment->get_instance()->id;
                $thissubmission->embedcode = $data->embedcode;
                $thissubmission->cdid = $data->cdid;
                return $DB->update_record('assignsubmission_estream', $thissubmission);
        } else {
                $thissubmission = new stdClass();
                $thissubmission->submission = $submission->id;
                $thissubmission->assignment = $this->assignment->get_instance()->id;
                $thissubmission->embedcode = $data->embedcode;
                $thissubmission->cdid = $data->cdid;
                return $DB->insert_record('assignsubmission_estream', $thissubmission) > 0;
        }
    }
    /**
     * Display the saved text content from the editor in the view table
     *
     * @param stdClass $submission
     * @return string
     */
    public function view(stdClass $submission) {
        $thissubmission = $this->funcgetsubmission($submission->id);
        if ($thissubmission) {
            return $this->funcembedplayer($thissubmission->cdid, $thissubmission->embedcode);
        } else {
            return "";
        }
    }
    /**
     * Display the list of files in the submission status table
     *
     * @param stdClass $submission
     * @param bool $showviewlink Set this to true if the list of files is long
     * @return string
     */
    public function view_summary(stdClass $submission, &$showviewlink) {
        return $this->view($submission);
    }
    /**
     * Return true if this plugin can upgrade an old Moodle 2.2 assignment of this type and version.
     *
     * @param string $type old assignment subtype
     * @param int $version old assignment version
     * @return bool True if upgrade is possible
     */
    public function can_upgrade($type, $version) {
        return false;
    }
    /**
     * Return submission log entry
     *
     * @param stdClass $submission The new submission
     * @return string
     */
    public function format_for_log(stdClass $submission) {
        return get_string('pluginname', 'assignsubmission_estream') . " added submission #" . $submission->id;
    }
    /**
     * The assignment has been deleted - cleanup
     *
     * @return bool
     */
    public function delete_instance() {
        global $DB;
        $DB->delete_records('assignsubmission_estream', array(
                'assignment' => $this->assignment->get_instance()->id
        ));
        try {
            $cs = ( float )(date('d') + date('m')) + (date('m') * date('d')) + (date('Y') * date('d'));
            $cs += $cs * (date('d') * 2.27409) * .689274;
            $url = rtrim(get_config('assignsubmission_estream', 'url') , '/');
            if (empty($url)) {
                $url = rtrim(get_config('planetestream', 'url') , '/');
            }
            $url = $url . "/UploadSubmissionVLE.aspx?mad=" . $this->assignment->get_instance()->id . "&checksum=" . md5(floor($cs));
            if (!$curl = curl_init($url)) {
                $this->log('curl init failed [187].');
                return false;
            }
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
            curl_setopt($curl, CURLOPT_TIMEOUT, 60);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $content = curl_exec($curl);
        } catch (Exception $e) {
            // Non-fatal exception!
        }
        return true;
    }
    /**
     * Is a submission present?
     *
     * @param stdClass $submission
     * @return bool
     */
    public function is_empty(stdClass $submission) {
        return $this->view($submission) == '';
    }
    /**
     * Add form elements
     *
     * @param mixed $submission can be null
     * @param MoodleQuickForm $mform
     * @param stdClass $data
     * @return true if elements were added to the form
     */
    public function get_form_elements($submission, MoodleQuickForm $mform, stdClass $data) {
        global $CFG, $USER, $PAGE, $COURSE;
        $cdid = 0;
        $embedcode = "";
        $url = $CFG->httpswwwroot . '/mod/assign/submission/estream/upload.php';
        $itemtitle = "Submission by " . fullname($USER);
        if (strlen($itemtitle > 120)) {
            $itemtitle = substr($itemtitle, 120);
        }
        $itemdesc = "Assignment : " . $this->assignment->get_instance()->name . "\r\n";
        $itemdesc .= "Course : " . $COURSE->fullname . "\r\n";
        $url .= '?itemtitle=' . urlencode($itemtitle);
        $url .= '&itemdesc=' . urlencode($itemdesc);
        $url .= '&itemaid=' . urlencode($this->assignment->get_instance()->id);
        $url .= '&itemuid=' . urlencode($USER->id);
        $url .= '&itemcid=' . urlencode($COURSE->id);
        if ($submission) {
            $thissubmission = $this->funcgetsubmission($submission->id);
            if ($thissubmission) {
                $cdid = $thissubmission->cdid;
                $embedcode = $thissubmission->embedcode;
                $iframehtml = $this->funcembedplayer($cdid, $embedcode);
                $mform->addElement('static', 'currentsubmission',
                get_string('currentsubmission', 'assignsubmission_estream') , $iframehtml);
                $url .= '&itemcdid=' . $cdid;
            }
        }
        $html = '<script type="text/javascript">';
        $html .= 'document.getElementById("hdn_cdid").value="' . $cdid . '";';
        $html .= 'document.getElementById("hdn_embedcode").value="' . $embedcode . '";';
        $html .= '</script>';
        $html .= '<div class="block" style="width: 60%; height: 300px;"><div class="header"><div class="title"><h2>'
        . get_string('upload', 'assignsubmission_estream') . '</h2></div></div>';
        $html .= '<div style="padding-left: 15px; padding-top: 8px; width: 95%; height: 90%; line-height: 160%;">';
        $html .= get_string('upload_help', 'assignsubmission_estream') . '<br />';
        $html .= '<div id="div_Loading" style="display: table-cell; width: 500px; height: 110px; padding-top: 16px; text'
        . '-align: center;">Loading..</br><img src="' . $CFG->wwwroot . '/mod/assign/submission/estream/pix/loading.gif" '
        . 'alt="loading.." /></div><iframe src="'.$url.'" width="100%" height="140" noresize frameborder="0" onload="'
        . 'document.getElementById(\'div_Loading\').style.display=\'none\';"></iframe></div>';
        $html .= '<div style="font-size: smaller; margin-top: 3px; text-align: right;">Powered by <img src="'
        . $CFG->wwwroot . '/mod/assign/submission/estream/pix/icon.png" alt="Planet eStream" />Planet eStream</div></div>';
        $mform->addElement('hidden', 'cdid', '', array('id' => 'hdn_cdid'));
        $mform->addElement('hidden', 'embedcode', '', array('id' => 'hdn_embedcode'));
        $mform->addElement('static', 'div_estream', '', $html);
        $mform->setType('cdid', PARAM_TEXT);
        $mform->setType('embedcode', PARAM_TEXT);
        return true;
    }
}
