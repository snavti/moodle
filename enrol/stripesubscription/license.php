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
 * Stripe enrolment plugin.
 *
 * This plugin allows you to set up paid courses.
 *
 * @package    enrol_stripesubscription
 * @copyright  2021 Digital Tricksters Software Solutions
 * @author     Trideep Das Modak <trideep@digital-tricksters.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once(__DIR__ . '/lib.php');

global $CFG, $DB, $PAGE, $USER;

require_login();

$PAGE->set_context(context_system::instance());
$PAGE->set_title('License Settings');
$PAGE->set_heading('Stripe Subscription License Settings');
$PAGE->set_url($CFG->wwwroot.'/enrol/stripesubscription/license.php');
$PAGE->navigation->add('Stripe Subscription License Settings',
 new moodle_url('/enrol/stripesubscription/license.php'), navigation_node::TYPE_CONTAINER)->make_active();

$cssfilename = '/enrol/stripesubscription/css/style.css';
$PAGE->requires->css($cssfilename);
$cssfilename = '/enrol/stripesubscription/css/owl.carousel.css';
$PAGE->requires->css($cssfilename);

if (!is_siteadmin()) {
    redirect($CFG->wwwroot.'/my', 'Somthing went wrong.');
}

// Page header.
echo $OUTPUT->header();

$itemid = 4616;
if (empty(get_config('enrol_stripesubscription', 'stripesubscription_license_key'))) {
  $licensekey = "Put your license key here...";
} else {
  $licensekey = get_config('enrol_stripesubscription', 'stripesubscription_license_key');
}

if (isset($_POST) && !empty($_POST)) {
    if (isset($_POST['license_activate'])) {
        $licensekey = $_POST['licensekey'];
        set_config("stripesubscription_license_key", $licensekey, "enrol_stripesubscription");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://digital-tricksters.com/?edd_action=activate_license&item_id='.$itemid.'&license='.$licensekey.'&url='.$CFG->wwwroot);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $licensereslr = json_decode($response);
        if (($licensereslr->success == true) && ($licensereslr->license == 'valid')) {
            set_config("stripesubscription_license_key_activated", 1, "enrol_stripesubscription");
            $licensekey = $_POST['licensekey'];
        }
    } else if (isset($_POST['license_deactivate'])) {
        $licensekey = get_config('enrol_stripesubscription', 'stripesubscription_license_key');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://digital-tricksters.com/?edd_action=deactivate_license&item_id='.$itemid.'&license='.$licensekey.'&url='.$CFG->wwwroot);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $licensereslr = json_decode($response);
        if (($licensereslr->success == true) && ($licensereslr->license == 'deactivated')) {
            set_config("stripesubscription_license_key_activated", 0, "enrol_stripesubscription");
            set_config("stripesubscription_license_key", "", "enrol_stripesubscription");
            $licensekey = "Put your license key here...";
        }
    }
}
if (!empty(get_config('enrol_stripesubscription', 'stripesubscription_license_key_activated'))) {
    $isstripesubscriptionlicensekey = get_config('enrol_stripesubscription', 'stripesubscription_license_key_activated');
} else {
    $isstripesubscriptionlicensekey = 0;
}

if (empty(get_config('enrol_stripesubscription', 'stripesubscription_license_key'))) {
  $licensekey = "Put your license key here...";
} else {
  $licensekey = get_config('enrol_stripesubscription', 'stripesubscription_license_key');
}

?>

<div class="licence-input-main-body-section">
    <div class="container">
      <div class="row">
        <div class="col-sm-12">
          <div class="licence-input-in-content-part">
            <form action="" method="POST">
              <h1>Stripe Subscription License</h1>
	            <div class="otp-code-div-cls licence-input-code-div-cls">
	              <input class="input-licence-valued" type="text" placeholder="<?php echo $licensekey; ?>" name="licensekey">
	            </div>
	            <div class="clearfix"></div>
	            <?php if (empty($isstripesubscriptionlicensekey) || ($isstripesubscriptionlicensekey == 0)) { ?>
              <input type="submit" class="active-cls" value="Activate Now" name="license_activate"></input>
	            <?php } else { ?>
	            <input type="submit" class="active-cls" value="Deactivate Now" name="license_deactivate"></input>
	            <?php } ?>
            </form>
          </div>
        </div>
      </div>
    </div>
</div>

<?php

// Page footer.
echo $OUTPUT->footer();