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
        if ($cdid == "0") {
            return "";
        } else {
			
			   if(strpos($cdid, '¬') !== false){ // Multiple uploads
			   
	$CDIDs = explode("¬", $cdid);
				 $Codes = explode("¬", $embedcode);	
				 $strReturn = '';

				  for($i = 0;$i<count($CDIDs);$i++){

			 $strReturn .="<iframe allowfullscreen height=\"198\" width=\"352\" src=\"".$url."/Embed.aspx?id=".$CDIDs[$i]
            ."&amp;code=".$Codes[$i]."&amp;wmode=opaque&amp;viewonestream=0\" frameborder=\"0\"></iframe>&nbsp;";
					 
				}
				
				return $strReturn;
				
				      return "<iframe allowfullscreen height=\"198\" width=\"352\" src=\"".$url."/Embed.aspx?id=123&amp;code=123&amp;wmode=opaque&amp;viewonestream=0\" frameborder=\"0\"></iframe>";
 
			   } else {
				   
				        return "<iframe allowfullscreen height=\"198\" width=\"352\" src=\"".$url."/Embed.aspx?id=".$cdid
            ."&amp;code=".$embedcode."&amp;wmode=opaque&amp;viewonestream=0\" frameborder=\"0\"></iframe>";
				   
			   }
			       
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
	    try {			
           // if ($data->cdid > 0) {
			  if(empty($data->cdid)) {
	         global $DB; // beng
                $thissubmission = $this->funcgetsubmission($submission->id);
                if ($thissubmission) {
                    $thissubmission->submission = $submission->id;
                    $thissubmission->assignment = $this->assignment->get_instance()->id;
                    $thissubmission->embedcode = "";
                    $thissubmission->cdid = "";
                    return $DB->update_record('assignsubmission_estream', $thissubmission);
                } else {
                    $thissubmission = new stdClass();
                    $thissubmission->submission = $submission->id;
                    $thissubmission->assignment = $this->assignment->get_instance()->id;
                    $thissubmission->embedcode = "";
                    $thissubmission->cdid = "";
                    return $DB->insert_record('assignsubmission_estream', $thissubmission) > 0;
                }	
            } else {
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
			
        } catch (Exception $e) {
			
            // Non-fatal exception!
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
	 
	 function atto_planetestream_obfuscate($strx) {
    $strbase64chars = '0123456789aAbBcCDdEeFfgGHhiIJjKklLmMNnoOpPQqRrsSTtuUvVwWXxyYZz/+=';
    $strbase64string = base64_encode($strx);
    if ($strbase64string == '') {
        return '';
    }
    $strobfuscated = '';
    for ($i = 0; $i < strlen ($strbase64string); $i ++) {
        $intpos = strpos($strbase64chars, substr($strbase64string, $i, 1));
        if ($intpos == - 1) {
            return '';
        }
        $intpos += strlen($strbase64string ) + $i;
        $intpos = $intpos % strlen($strbase64chars);
        $strobfuscated .= substr($strbase64chars, $intpos, 1);
    }
    return urlencode($strobfuscated);
}
	 
	 
	 function atto_planetestream_getchecksum() {
    $decchecksum = (float)(date('d') + date('m')) + (date('m') * date('d')) + (date('Y') * date('d'));
    $decchecksum += $decchecksum * (date('d') * 2.27409) * .689274;
    return md5(floor($decchecksum));
}


function atto_planetestream_getauthticket($url, $checksum, $delta, $userip, &$params) {
    $return = '';
	
	//$return = $url . "~~~" . $checksum . "~~~~" . $delta . "~~~~" . $userip;
    try {
        $url .= '/VLE/Moodle/Auth/?source=1&checksum=' . $checksum . '&delta=' . $delta . '&u=' . $userip;
        if (!$curl = curl_init($url)) {
           return '';
		   //return $return;
        }
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 4);
        curl_setopt($curl, CURLOPT_FORBID_REUSE, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($curl);
        if (strpos($response, '{"ticket":') === 0) {
            $jobj = json_decode($response);
            $return = $jobj->ticket;
            $params['estream_height'] = $jobj->height;
            $params['estream_width'] = $jobj->width;
        }
    } catch (Exception $e) {
        // ... non-fatal ...
    }
    return $return;
}

	 
    public function delete_instance() {
        global $DB;
		global $PAGE, $USER;
		profile_load_data($USER);
		
		$params = array();
    $params['usercontextid'] = $usercontextid;
if (isset($USER->profile_field_planetestreamusername) && !empty($USER->profile_field_planetestreamusername)) {
    $delta = atto_planetestream_obfuscate($USER->profile_field_planetestreamusername);
	} else {
	$delta = atto_planetestream_obfuscate($USER->username);
	}
	$userip = atto_planetestream_obfuscate(getremoteaddr());
	$url = rtrim(get_config('assignsubmission_estream', 'url') , '/');
	$params['estream_url'] = $url;
	$checksum = atto_planetestream_getchecksum();
	$authticket = atto_planetestream_getauthticket($url, $checksum, $delta, $userip, $params);
	if ($authticket == '') {
       $params['disabled'] = true; //may not need
    }
		
        $DB->delete_records('assignsubmission_estream', array(
                'assignment' => $this->assignment->get_instance()->id
        ));
		

        try {
            $cs = ( float )(date('d') + date('m')) + (date('m') * date('d')) + (date('Y') * date('d'));
            $cs += $cs * (date('d') * 2.27409) * .689274;
          //  $url = rtrim(get_config('assignsubmission_estream', 'url') , '/');
            if (empty($url)) {
                $url = rtrim(get_config('planetestream', 'url') , '/');
            }
			
            $url = $url . "/VLE/Moodle/Default.aspx?sourceid=11&inlinemode=moodle";
			$url = $url . "&mad=" . $this->assignment->get_instance()->id; 
			$url = $url . "&delta=" . $delta; 
			$url = $url . "&assign=" . ((string)$PAGE->pagetype == 'mod-assign-editsubmission' ? "true" : "false");
			$url = $url . "&ticket=" . $authticket; 
			//$url = $url . "&checksum=" . md5(floor($cs));
			$url = $url . "&checksum=" . $checksum;
			
			
            if (!$curl = curl_init($url)) {
                $this->log('curl init failed [187].');
                return false;
            }
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
            curl_setopt($curl, CURLOPT_TIMEOUT, 60);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
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
	 /* Return false does not work. Return true 'works' in the sense that it doesn't regardless of file  */
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
        $cdid = "";
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
        $html .= '<iframe src="'.$url.'" width="100%" height="720" noresize frameborder="0"></iframe></div>';
        $mform->addElement('hidden', 'cdid', '', array('id' => 'hdn_cdid'));
        $mform->addElement('hidden', 'embedcode', '', array('id' => 'hdn_embedcode')); 
	  $mform->addElement('html', $html);
        $mform->setType('cdid', PARAM_TEXT);
        $mform->setType('embedcode', PARAM_TEXT);
        return true;
    }
}
