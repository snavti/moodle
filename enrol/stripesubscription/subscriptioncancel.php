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
$PAGE->set_title('Stripe Subscription Cancel');
$PAGE->set_heading('Stripe Subscription Cancel');
$PAGE->set_url($CFG->wwwroot.'/enrol/stripesubscription/subscriptioncancel.php');

$plugin = enrol_get_plugin('stripesubscription');

$stripesubid = required_param('id', PARAM_RAW);

$islicenseactived = get_config('enrol_stripesubscription', 'stripesubscription_license_key_activated');
if ((!isset($islicenseactived) && empty($islicenseactived)) || ($islicenseactived == 0)) {
    redirect($CFG->wwwroot.'/enrol/stripesubscription/license.php', 'Please activate your license to continue');
}

if (empty($stripesubid)) {
    redirect($CFG->wwwroot.'/enrol/stripesubscription/subscriptions.php');
}

try {

    require_once('Stripe/init.php');

    \Stripe\Stripe::setApiKey($plugin->get_config('secretkey'));

    $subscription = \Stripe\Subscription::retrieve($stripesubid);
    $subscriptioncancel = $subscription->cancel();

    if ($subscriptioncancel->status == 'canceled') {

        // Enrolment cancel.
        $stripesubscription = $DB->get_record('enrol_stripesubscription', array('subscription_id' => $stripesubid));
        if ($DB->record_exists('user_enrolments', array('id' => $stripesubscription->enrolmentid))) {
            $DB->delete_records('user_enrolments', array('id' => $stripesubscription->enrolmentid));
        }

        // Record delete.
        $DB->delete_records('enrol_stripesubscription', array('subscription_id' => $stripesubid));
        redirect($CFG->wwwroot.'/enrol/stripesubscription/subscriptions.php', get_string("subscriptionsuccessfullycanceled", "enrol_stripesubscription"));
    } else {
        redirect($CFG->wwwroot.'/enrol/stripesubscription/subscriptions.php', get_string("subscriptioncancellationfailed", "enrol_stripesubscription"));
    }
} catch (Stripe_CardError $e) {
    // Catch the errors in any way you like.
    echo 'Error';
} catch (Stripe_InvalidRequestError $e) {
    // Invalid parameters were supplied to Stripe's API.
    echo 'Invalid parameters were supplied to Stripe\'s API';
} catch (Stripe_AuthenticationError $e) {
    // Authentication with Stripe's API failed
    // (maybe you changed API keys recently).
    echo 'Authentication with Stripe\'s API failed';
} catch (Stripe_ApiConnectionError $e) {
    // Network communication with Stripe failed.
    echo 'Network communication with Stripe failed';
} catch (Stripe_Error $e) {
    // Display a very generic error to the user, and maybe send yourself an email.
    echo 'Stripe Error';
} catch (Exception $e) {
    // Something else happened, completely unrelated to Stripe.
    echo 'Something else happened, completely unrelated to Stripe';
}

redirect($CFG->wwwroot.'/enrol/stripesubscription/subscriptions.php', get_string("subscriptioncancellationfailed", "enrol_stripesubscription"));