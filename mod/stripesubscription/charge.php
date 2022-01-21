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
 * Listens for Instant Payment Notification from Stripe
 *
 * This script waits for Payment notification from Stripe,
 * then double checks that data by sending it back to Stripe.
 * If Stripe verifies this then it sets up the enrolment for that
 * user.
 *
 * @package    enrol_stripesubscription
 * @copyright  2021 Digital Tricksters Software Solutions
 * @author     Trideep Das Modak <trideep@digital-tricksters.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Disable moodle specific debug messages and any errors in output,
// comment out when debugging or better look into error log!
define('NO_DEBUG_DISPLAY', true);

require("../../config.php");
require_once("lib.php");
require_once($CFG->libdir.'/enrollib.php');
require_once($CFG->libdir . '/filelib.php');

require_login();

$islicenseactived = get_config('enrol_stripesubscription', 'stripesubscription_license_key_activated');
if ((!isset($islicenseactived) && empty($islicenseactived)) || ($islicenseactived == 0)) {
    redirect($CFG->wwwroot.'/enrol/stripesubscription/license.php', 'Please activate your license to continue');
}

// Keep out casual intruders.
$tid = required_param('auth', PARAM_RAW);
$subscriptioninterval = required_param('subscriptioninterval', PARAM_RAW);
$data = new stdClass();

$data->coupon_id = required_param('coupon_id', PARAM_RAW);
$data->cmd = required_param('cmd', PARAM_RAW);
$data->charset = required_param('charset', PARAM_RAW);
$data->item_name = required_param('item_name', PARAM_TEXT);
$data->item_number = required_param('item_number', PARAM_TEXT);
$data->quantity = required_param('quantity', PARAM_INT);
$data->on0 = optional_param('on0', array(), PARAM_TEXT);
$data->os0 = optional_param('os0', array(), PARAM_TEXT);
$data->custom = optional_param('custom', array(), PARAM_RAW);
$data->currency_code = required_param('currency_code', PARAM_RAW);
$data->amount = required_param('amount', PARAM_RAW);
$data->for_auction = required_param('for_auction', PARAM_BOOL);
$data->customchar1 = required_param('customchar1', PARAM_INT);
$data->no_note = required_param('no_note', PARAM_INT);
$data->no_shipping = required_param('no_shipping', PARAM_INT);
$data->rm = required_param('rm', PARAM_RAW);
$data->cbt = optional_param('cbt', array(), PARAM_TEXT);
$data->first_name = required_param('first_name', PARAM_TEXT);
$data->last_name = required_param('last_name', PARAM_TEXT);
$data->address = optional_param('address', array(), PARAM_TEXT);
$data->city = optional_param('city', array(), PARAM_TEXT);
$data->email = required_param('email', PARAM_EMAIL);
$data->country = optional_param('country', array(), PARAM_TEXT);
$data->stripeToken = $tid;
$data->stripeTokenType = 'card';
$data->stripeEmail = required_param('email', PARAM_EMAIL);

$custom = explode('-', $data->custom);
$data->userid           = (int)$custom[0];
$data->courseid         = (int)$custom[1];
$data->instanceid       = (int)$custom[2];
$data->payment_gross    = $data->amount;
$data->payment_currency = $data->currency_code;
$data->timeupdated      = time();
// Get the user and course records.

if (! $user = $DB->get_record("user", array("id" => $data->userid))) {
    message_stripesubscription_error_to_admin("Not a valid user id", $data);
    redirect($CFG->wwwroot);
}

if (! $course = $DB->get_record("course", array("id" => $data->courseid))) {
    message_stripesubscription_error_to_admin("Not a valid course id", $data);
    redirect($CFG->wwwroot);
}

if (! $context = context_course::instance($course->id, IGNORE_MISSING)) {
    message_stripesubscription_error_to_admin("Not a valid context id", $data);
    redirect($CFG->wwwroot);
}

$PAGE->set_context($context);

if (! $plugininstance = $DB->get_record("enrol", array("id" => $data->instanceid, "status" => 0))) {
    message_stripesubscription_error_to_admin("Not a valid instance id", $data);
    redirect($CFG->wwwroot);
}

 // If currency is incorrectly set then someone maybe trying to cheat the system.

if ($data->courseid != $plugininstance->courseid) {
    message_stripesubscription_error_to_admin("Course Id does not match to the course settings, received: ".$data->courseid, $data);
    redirect($CFG->wwwroot);
}

$plugin = enrol_get_plugin('stripesubscription');

// Check that amount paid is the correct amount.
if ( (float) $plugininstance->cost <= 0 ) {
    $cost = (float) $plugin->get_config('cost');
} else {
    $cost = (float) $plugininstance->cost;
}

// Use the same rounding of floats as on the enrol form.
$cost = format_float($cost, 2, false);

// Let's say each article costs 15.00 bucks.

try {

    require_once('Stripe/init.php');

    \Stripe\Stripe::setApiKey($plugin->get_config('secretkey'));

    $iscoupon = false;
    if ($data->coupon_id && $data->coupon_id != 0) {
        $coupon = \Stripe\Coupon::retrieve($data->coupon_id);
        if (!$coupon->valid) {
            redirect($CFG->wwwroot.'/enrol/index.php?id='.$data->courseid, get_string("invalidcouponcodevalue",
                "enrol_stripesubscription", $data->coupon_id));
        } else {
            $iscoupon = true;
            if (isset($coupon->percent_off)) {
                $cost = $cost - ( $cost * ( $coupon->percent_off / 100 ) );
            } else if (isset($coupon->amount_off)) {
                $cost = (($cost * 100) - $coupon->amount_off) / 100;
            }
        }
    }

    $customer = \Stripe\Customer::create(array(
        'email' => $data->stripeEmail,
        'source'  => $tid
    ));
    $data->receiver_id = $customer->id;

    $checkproduct = $DB->get_records('enrol_stripesubscription', array('product_name' => $data->item_name));
    foreach ($checkproduct as $keydata => $valuedata) {
        $checkproduct = $valuedata;
    }
    if (!$checkproduct) {
        // Product create.
        $product = \Stripe\Product::create([
          'name' => $data->item_name,
          'type' => 'service',
        ]);
        $data->product_id = $product->id;
        $data->product_name = $data->item_name;
        $data->product_type = 'service';
    } else {
        $data->product_id = $checkproduct->product_id;
        $data->product_name = $checkproduct->product_name;
        $data->product_type = $checkproduct->product_type;
    }

    // Create the subscription.
    if ($data->customchar1 != 0 || !empty($data->customchar1)) {
        $subscription = \Stripe\Subscription::create([
          'customer' => $data->receiver_id,
          'items' => [[
            'price_data' => [
              'unit_amount' => $_SESSION['amount'],
              'currency' => strtolower($data->currency_code),
              'product' => $data->product_id,
              'recurring' => [
                'interval' => $subscriptioninterval,
              ],
            ],
          ]],
          'trial_period_days' => $data->customchar1
        ]);
    } else {
        $subscription = \Stripe\Subscription::create([
          'customer' => $data->receiver_id,
          'items' => [[
            'price_data' => [
              'unit_amount' => $_SESSION['amount'],
              'currency' => strtolower($data->currency_code),
              'product' => $data->product_id,
              'recurring' => [
                'interval' => $subscriptioninterval,
              ],
            ],
          ]],
        ]);
    }
    if (!empty($subscription)) {
        $data->subscription_id = $subscription->id;
        $data->subscription_interval = $subscriptioninterval;
    }

    $data->receiver_email = $data->stripeEmail;
    $data->txn_id = $tid;
    $data->amount = $_SESSION['amount'] / 100;
    $data->subscription_status = $subscription->status;
    $data->collection_method = $subscription->collection_method;
    $data->payment_type = 'card';

    // Stripe Authentication Checking.
    $destination = "$CFG->wwwroot/course/view.php?id=$course->id";

    if ($subscription->status != 'active') {
        if ($subscription->status != 'trialing') {
            redirect($destination, get_string("stripeauthenticationerror", "enrol_stripesubscription"));
        }
    } else if ($data->stripeEmail != $USER->email) {
        redirect($destination, get_string("stripeauthenticationerror", "enrol_stripesubscription"));
    }

    if ($plugininstance->enrolperiod) {
        $timestart = time();
        $timeend   = $timestart + $plugininstance->enrolperiod;
    } else {
        $timestart = 0;
        $timeend   = 0;
    }

    // Enrol user.
    $plugin->enrol_user($plugininstance, $user->id, $plugininstance->roleid, $timestart, $timeend);

    $enroldetails = $DB->get_record('enrol', array('enrol' => 'stripesubscription', 'courseid' => $data->courseid));
    $enrolmentdetails = $DB->get_records_sql("SELECT * FROM {user_enrolments} WHERE enrolid = {$enroldetails->id}
     AND userid = {$USER->id} AND timestart = {$timestart} AND timeend = {$timeend} ORDER BY id DESC");
    foreach ($enrolmentdetails as $key => $enrolvalue) {
        $data->enrolmentid = $enrolvalue->id;
        break;
    }

    // ALL CLEAR !
    $DB->insert_record("enrol_stripesubscription", $data);

    // Pass $view=true to filter hidden caps if the user cannot see them.
    if ($users = get_users_by_capability($context, 'moodle/course:update', 'u.*', 'u.id ASC', '', '', '', '', false, true)) {
        $users = sort_by_roleassignment_authority($users, $context);
        $teacher = array_shift($users);
    } else {
        $teacher = false;
    }

    $mailstudents = $plugin->get_config('mailstudents');
    $mailteachers = $plugin->get_config('mailteachers');
    $mailadmins   = $plugin->get_config('mailadmins');
    $shortname = format_string($course->shortname, true, array('context' => $context));

    $coursecontext = context_course::instance($course->id);

    if (!empty($mailstudents)) {
        $a = new stdClass();
        $a->coursename = format_string($course->fullname, true, array('context' => $coursecontext));
        $a->profileurl = "$CFG->wwwroot/user/view.php?id=$user->id";

        $userfrom = empty($teacher) ? core_user::get_support_user() : $teacher;
        $subject = get_string("enrolmentnew", 'enrol', $shortname);
        $fullmessage = get_string('welcometocoursetext', '', $a);
        $fullmessagehtml = html_to_text('<p>'.get_string('welcometocoursetext', '', $a).'</p>');

        // Send test email.
        ob_start();
        $success = email_to_user($user, $userfrom, $subject, $fullmessage, $fullmessagehtml);
        $smtplog = ob_get_contents();
        ob_end_clean();
    }

    if (!empty($mailteachers) && !empty($teacher)) {
        $a->course = format_string($course->fullname, true, array('context' => $coursecontext));
        $a->user = fullname($user);

        $subject = get_string("enrolmentnew", 'enrol', $shortname);
        $fullmessage = get_string('enrolmentnewuser', 'enrol', $a);
        $fullmessagehtml = html_to_text('<p>'.get_string('enrolmentnewuser', 'enrol', $a).'</p>');

        // Send test email.
        ob_start();
        $success = email_to_user($teacher, $user, $subject, $fullmessage, $fullmessagehtml);
        $smtplog = ob_get_contents();
        ob_end_clean();
    }

    if (!empty($mailadmins)) {
        $a->course = format_string($course->fullname, true, array('context' => $coursecontext));
        $a->user = fullname($user);
        $admins = get_admins();
        foreach ($admins as $admin) {
            $subject = get_string("enrolmentnew", 'enrol', $shortname);
            $fullmessage = get_string('enrolmentnewuser', 'enrol', $a);
            $fullmessagehtml = html_to_text('<p>'.get_string('enrolmentnewuser', 'enrol', $a).'</p>');

            // Send test email.
            ob_start();
            $success = email_to_user($admin, $user, $subject, $fullmessage, $fullmessagehtml);
            $smtplog = ob_get_contents();
            ob_end_clean();
        }
    }

    $destination = "$CFG->wwwroot/course/view.php?id=$course->id";

    $fullname = format_string($course->fullname, true, array('context' => $context));

    if (is_enrolled($context, null, '', true)) { // TODO: use real stripe check.
        redirect($destination, get_string('paymentthanks', '', $fullname));

    } else {   // Somehow they aren't enrolled yet!
        $PAGE->set_url($destination);
        echo $OUTPUT->header();
        $a = new stdClass();
        $a->teacher = get_string('defaultcourseteacher');
        $a->fullname = $fullname;
        notice(get_string('paymentsorry', '', $a), $destination);
    }
} catch (Stripe_CardError $e) {
    // Catch the errors in any way you like.
    echo 'Error';
}

// Catch the errors in any way you like.

catch (Stripe_InvalidRequestError $e) {
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

    // Display a very generic error to the user, and maybe send
    // yourself an email.
    echo 'Stripe Error';
} catch (Exception $e) {

    // Something else happened, completely unrelated to Stripe.
    echo 'Something else happened, completely unrelated to Stripe';
}


/**
 * Send payment error message to the admin.
 *
 * @param string $subject
 * @param stdClass $data
 */
function message_stripesubscription_error_to_admin($subject, $data) {
    $admin = get_admin();
    $site = get_site();

    $message = "$site->fullname:  Transaction failed.\n\n$subject\n\n";

    foreach ($data as $key => $value) {
        $message .= s($key) ." => ". s($value)."\n";
    }

    $subject = get_string("stripepaymenterror", "enrol_stripesubscription").": ".$subject;
    $fullmessage = $message;
    $fullmessagehtml = html_to_text('<p>'.$message.'</p>');

    // Send test email.
    ob_start();
    $success = email_to_user($admin, $admin, $subject, $fullmessage, $fullmessagehtml);
    $smtplog = ob_get_contents();
    ob_end_clean();
}