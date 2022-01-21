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
 * enrol plugin Stripe payment subscription.
 *
 * @package    enrol_stripesubscription
 * @copyright  2021 Digital Tricksters Software Solutions
 * @author     Trideep Das Modak <trideep@digital-tricksters.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once("lib.php");

global $CFG, $DB, $PAGE, $USER;

require_login();

$PAGE->set_context(context_system::instance());
$PAGE->set_title('Stripe Subscription Record Clear');
$PAGE->set_heading('Stripe Subscription Record Clear');
$PAGE->set_url($CFG->wwwroot.'/enrol/stripesubscription/subscriptionremrecord.php');

$plugin = enrol_get_plugin('stripesubscription');

$stripesubid = required_param('id', PARAM_RAW);

$islicenseactived = get_config('enrol_stripesubscription', 'stripesubscription_license_key_activated');
if ((!isset($islicenseactived) && empty($islicenseactived)) || ($islicenseactived == 0)) {
    redirect($CFG->wwwroot.'/enrol/stripesubscription/license.php', 'Please activate your license to continue');
}

if (empty($stripesubid)) {
    redirect($CFG->wwwroot.'/enrol/stripesubscription/subscriptions.php');
} else {

    // Enrolment cancel.
    $stripesubscription = $DB->get_record('enrol_stripesubscription', array('subscription_id' => $stripesubid));
    if ($DB->record_exists('user_enrolments', array('id' => $stripesubscription->enrolmentid))) {
        $DB->delete_records('user_enrolments', array('id' => $stripesubscription->enrolmentid));
    }

    $DB->delete_records('enrol_stripesubscription', array('subscription_id' => $stripesubid));
    redirect($CFG->wwwroot.'/enrol/stripesubscription/subscriptions.php', get_string("subscriptionrecordsuccessfullycleared", "enrol_stripesubscription"));
}