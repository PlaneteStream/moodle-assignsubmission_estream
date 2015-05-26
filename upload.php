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
 * Planet eStream Assignment Submission Plugin Upload code
 *
 * @package        assignsubmission_estream
 * @copyright        Planet Enterprises Ltd
 * @license        http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
require_once('../../../../config.php');
require_once($CFG->dirroot . '/mod/assign/locallib.php');
require_once($CFG->dirroot . '/mod/assign/submission/estream/locallib.php');
global $PAGE, $USER;
require_login();
$PAGE->set_context(context_user::instance($USER->id));
$PAGE->set_url($CFG->wwwroot . '/mod/assign/submission/estream/upload.php');
$itemtitle = optional_param('itemtitle', '', PARAM_TEXT);
$itemdesc = optional_param('itemdesc', '', PARAM_TEXT);
$itemcid = optional_param('itemcid', '', PARAM_TEXT);
$itemcdid = optional_param('itemcdid', '', PARAM_TEXT);
$itemaid = optional_param('itemaid', '', PARAM_TEXT);
$itemuid = optional_param('itemuid', '', PARAM_TEXT);
$cdid = optional_param('cdid', '', PARAM_TEXT);
$embedcode = optional_param('ec', '', PARAM_TEXT);
$error = optional_param('error', '', PARAM_TEXT);
$configerror = optional_param('configerror', '', PARAM_TEXT);
if (empty($itemtitle)) {
    if (empty($error) && empty($configerror) && !empty($cdid)) {
?>
<html>
    <head>
        <script type="text/javascript">
            function page_load() {
                parent.parent.document.getElementById('hdn_cdid').value = "<?php echo $cdid; ?>";
                parent.parent.document.getElementById('hdn_embedcode').value = "<?php echo $embedcode; ?>";
            }
        </script>
        <style type="text/css">
            * {
                font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            }
        </style>
    </head>
    <body onload="page_load()" >
            <h3><?php echo get_string('uploadok', 'assignsubmission_estream'); ?></h3>
    </body>
</html>
<?php
    } else {
?>
    <html>
        <head>
            <style type="text/css">
                * {
                    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
                }

            </style>
        </head>
        <body>
            <h3><?php
        if (empty($configerror)) {
            echo get_string('uploadfailed', 'assignsubmission_estream') . '<br />' . $error;
        } else {
            echo $configerror;
        } ?>    </h3>
        </body>
    </html>
<?php
    }
} else {
    $thissubmission = new assign_submission_estream(new assign(null, null, null) , null);
    if (empty($thissubmission)) {
        echo "Sorry, the submission could not be initiated.";
        die();
    }
    $baseurl = rtrim(get_config('assignsubmission_estream', 'url') , '/');
    if (empty($baseurl)) {
        $baseurl = rtrim(get_config('planetestream', 'url') , '/');
    }
    if (empty($baseurl)) {
?>
    <html>
        <head>
            <style type="text/css">
                * {
                    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
                }
            </style>
        </head>
        <body>
            <h3><?php echo get_string('notyetconfigured', 'assignsubmission_estream') ?></h3>
        </body>
     </html>
<?php
    } else {
?>
        <script type="text/javascript">

                // parent.parent.document.getElementById("id_submitbutton").disabled = true;

        </script>
        <div style="text-align: center;">
                <iframe width="100%" height="120" frameborder="0" src="<?php 
                echo $baseurl;?>/UploadSubmissionVLE.aspx?murl=<?php 
                echo $CFG->wwwroot.'/mod/assign/submission/estream/upload.php'
                .'&amp;title='.urlencode($itemtitle).'&amp;desc='.urlencode($itemdesc)
                .'&amp;cid='.urlencode($itemcid).'&amp;aid='.urlencode($itemaid)
                .'&amp;uid='.urlencode($itemuid).'&amp;cdid='
                .urlencode($itemcdid);?>"></iframe>
        </div>
<?php
    }
}