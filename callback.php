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
 * Planet eStream Assignment Submission Plugin callback.
 *
 * Receives the completed Planet eStream upload details and passes them
 * back to the already-loaded Moodle assignment submission form.
 *
 * This page intentionally does not require a Moodle login session because
 * it is loaded inside the Planet eStream iframe, where the Moodle
 * SameSite=Lax session cookie may not be available.
 *
 * @package        assignsubmission_estream
 * @copyright      Planet Enterprises Ltd
 * @license        http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_MOODLE_COOKIES', true);

require_once('../../../../config.php');


$strCDID = optional_param('cdid', '', PARAM_RAW_TRIMMED);
$strEmbedCode = optional_param('ec', '', PARAM_RAW_TRIMMED);
$strError = optional_param('error', '', PARAM_RAW_TRIMMED);
$strConfigError = optional_param('configerror', '', PARAM_RAW_TRIMMED);

$strEStreamURL = trim((string)get_config('assignsubmission_estream', 'url'));
$strEStreamOrigin = '';

$arrEStreamURLX = parse_url($strEStreamURL);

if (is_array($arrEStreamURLX)
    && isset($arrEStreamURLX['scheme'])
    && isset($arrEStreamURLX['host'])
    && ($arrEStreamURLX['scheme'] === 'https' || $arrEStreamURLX['scheme'] === 'http')) {

    $strEStreamOrigin = $arrEStreamURLX['scheme'] . '://' . $arrEStreamURLX['host'];

    if (isset($arrEStreamURLX['port'])) {

        $strEStreamOrigin .= ':' . $arrEStreamURLX['port'];

    }

}


// The callback is deliberately loaded inside the eStream iframe.
//
// Some Moodle installations return frame-ancestors 'self', which would
// prevent this callback from loading because the immediate parent is eStream.
// Permit the configured eStream origin for this callback only.

header_remove('Content-Security-Policy');
header_remove('X-Frame-Options');

if ($strEStreamOrigin !== '') {

    header(
        "Content-Security-Policy: "
        . "default-src 'none'; "
        . "script-src 'unsafe-inline'; "
        . "style-src 'unsafe-inline'; "
        . "frame-ancestors 'self' " . $strEStreamOrigin . ";"
    );

} else {

    header(
        "Content-Security-Policy: "
        . "default-src 'none'; "
        . "script-src 'unsafe-inline'; "
        . "style-src 'unsafe-inline'; "
        . "frame-ancestors 'self';"
    );

}

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
header('Referrer-Policy: no-referrer');


function funcValidateCDIDList($strX) {

    if ($strX === '' || strlen($strX) > 4096) {

        return false;

    }

    $arrCDIDX = explode('¬', $strX);

    if (count($arrCDIDX) < 1 || count($arrCDIDX) > 100) {

        return false;

    }

    foreach ($arrCDIDX as $strCDIDX) {

        if ($strCDIDX === ''
            || !ctype_digit($strCDIDX)
            || (int)$strCDIDX < 1) {

            return false;

        }

    }

    return true;

}


function funcValidateEmbedCodeList($strX, $intExpectedCountX) {

    if ($strX === '' || strlen($strX) > 16384) {

        return false;

    }

    $arrEmbedCodeX = explode('¬', $strX);

    if (count($arrEmbedCodeX) !== $intExpectedCountX) {

        return false;

    }

    foreach ($arrEmbedCodeX as $strEmbedCodeX) {

        if ($strEmbedCodeX === ''
            || strlen($strEmbedCodeX) > 512
            || !preg_match('/\A[A-Za-z0-9~_.-]+\z/D', $strEmbedCodeX)) {

            return false;

        }

    }

    return true;

}


$boolSuccess = false;
$strMessage = '';

if ($strConfigError !== '') {

    $strMessage = $strConfigError;

} elseif ($strError !== '') {

    $strMessage = get_string('uploadfailed', 'assignsubmission_estream') . ' ' . $strError;

} elseif (!funcValidateCDIDList($strCDID)) {

    $strMessage = get_string('uploadfailed', 'assignsubmission_estream') . ' Invalid media ID returned by Planet eStream.';

} else {

    $arrCDIDX = explode('¬', $strCDID);

    if (!funcValidateEmbedCodeList($strEmbedCode, count($arrCDIDX))) {

        $strMessage = get_string('uploadfailed', 'assignsubmission_estream') . ' Invalid embed code returned by Planet eStream.';

    } else {

        $boolSuccess = true;
        $strMessage = get_string('uploadok', 'assignsubmission_estream');

    }

}


$strCDIDJSON = json_encode(
    $strCDID,
    JSON_HEX_TAG
    | JSON_HEX_AMP
    | JSON_HEX_APOS
    | JSON_HEX_QUOT
    | JSON_UNESCAPED_UNICODE
);

$strEmbedCodeJSON = json_encode(
    $strEmbedCode,
    JSON_HEX_TAG
    | JSON_HEX_AMP
    | JSON_HEX_APOS
    | JSON_HEX_QUOT
    | JSON_UNESCAPED_UNICODE
);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <title>Planet eStream Assignment Submission</title>

    <style type="text/css">

        * {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        }

    </style>

<?php if ($boolSuccess) { ?>

    <script type="text/javascript">

        function page_load() {

            try {

                var docAssignmentX = parent.parent.document;

                var elCDIDX = docAssignmentX.getElementById('hdn_cdid');
                var elEmbedCodeX = docAssignmentX.getElementById('hdn_embedcode');

                if (!elCDIDX || !elEmbedCodeX) {

                    throw new Error('Could not find the Planet eStream assignment fields.');

                }

                elCDIDX.value = <?php echo $strCDIDJSON; ?>;
                elEmbedCodeX.value = <?php echo $strEmbedCodeJSON; ?>;

            } catch (errorX) {

                var elStatusX = document.getElementById('div_Status');

                if (elStatusX) {

                    elStatusX.innerText = 'The upload completed, but the Moodle assignment form could not be updated.';

                }

                if (window.console) {

                    console.error(errorX);

                }

            }

        }

    </script>

<?php } ?>

</head>

<body<?php echo $boolSuccess ? ' onload="page_load()"' : ''; ?>>

    <h3 id="div_Status"><?php echo s($strMessage); ?></h3>

</body>
</html>