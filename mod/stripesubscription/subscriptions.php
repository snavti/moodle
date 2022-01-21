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
require_once($CFG->libdir.'/enrollib.php');
require_once('Stripe/init.php');

global $CFG, $DB, $PAGE, $USER;

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title('Stripe Subscription Status');
$PAGE->set_heading('Stripe Subscription Status');
$PAGE->set_url($CFG->wwwroot.'/enrol/stripesubscription/subscriptions.php');
$PAGE->navigation->add('Stripe Subscription Status',
 new moodle_url('/enrol/stripesubscription/subscriptions.php'), navigation_node::TYPE_CONTAINER)->make_active();

// Page header.
echo $OUTPUT->header();
require_login();

$plugin = enrol_get_plugin('stripesubscription');
$counter = 1;

$islicenseactived = get_config('enrol_stripesubscription', 'stripesubscription_license_key_activated');
if ((!isset($islicenseactived) && empty($islicenseactived)) || ($islicenseactived == 0)) {
    redirect($CFG->wwwroot.'/enrol/stripesubscription/license.php', 'Please activate your license to continue');
}

?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css">
<?php

$stripesubscription = $DB->get_records_sql("SELECT * FROM {enrol_stripesubscription} ORDER BY id DESC");
// Checking and cancel existing subscriptions.
foreach ($stripesubscription as $stripesubscriptionkey => $stripesubscriptionvalue) {
    if ($stripesubscriptionvalue->payment_type != 'free') {
        \Stripe\Stripe::setApiKey($plugin->get_config('secretkey'));
        $checksubscription = \Stripe\Subscription::retrieve($stripesubscriptionvalue->subscription_id);
        if ($checksubscription->status == 'active' || $checksubscription->status == 'trialing') {
            $userdetail = $DB->get_record_sql("SELECT * FROM {user} WHERE id = {$stripesubscriptionvalue->userid}");
            $coursedetail = $DB->get_record_sql("SELECT * FROM {course} WHERE id = {$stripesubscriptionvalue->courseid}");
            if ($coursecontext = context_course::instance($coursedetail->id, IGNORE_MISSING)) {
                if (!is_enrolled($coursecontext, $userdetail, '', true)) {
                    $checksubscription->cancel();
                }
            }
        }
    }
}

if (is_siteadmin()) {

    $stripesubscription = $DB->get_records_sql("SELECT * FROM {enrol_stripesubscription} ORDER BY id DESC");

?>

<table id="dtBasicExample" class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
  <thead>
    <tr style="text-align: center;">
      <th class="th-sm"><?php echo get_string("stripeid", "enrol_stripesubscription"); ?></th>
      <th class="th-sm"><?php echo get_string("stripecoursename", "enrol_stripesubscription"); ?></th>
      <th class="th-sm"><?php echo get_string("stripestudentname", "enrol_stripesubscription"); ?></th>
      <th class="th-sm"><?php echo get_string("stripestudentemail", "enrol_stripesubscription"); ?></th>
      <th class="th-sm"><?php echo get_string("stripecreatedon", "enrol_stripesubscription"); ?></th>
      <th class="th-sm"><?php echo get_string("stripeamountinterval", "enrol_stripesubscription"); ?></th>
      <th class="th-sm"><?php echo get_string("stripesubscriptionstatus", "enrol_stripesubscription"); ?></th>
      <th class="th-sm"><?php echo get_string("stripeaction", "enrol_stripesubscription"); ?></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($stripesubscription as $key => $value) {
        if ($value->payment_type != 'free') {
            \Stripe\Stripe::setApiKey($plugin->get_config('secretkey'));

            $subscription = \Stripe\Subscription::retrieve($value->subscription_id);

            if ($subscription->status == 'active' || $subscription->status == 'trialing') {

                $userdetails = $DB->get_record_sql("SELECT * FROM {user} WHERE id = {$value->userid}");

                echo '<tr style="text-align: center;">
                    <td>'.$counter.'</td>
                    <td>'.$value->product_name.'</td>
                    <td>'.fullname($userdetails, true).'</td>
                    <td>'.$value->receiver_email.'</td>
                    <td>'.date("d-m-Y", $value->timeupdated).'</td>
                    <td>'.$value->payment_currency.' '.$value->amount.'/'.ucfirst($value->subscription_interval).'</td>';
                if ($subscription->status == 'trialing') {
                    echo '<td>'.get_string("stripetrialendson", "enrol_stripesubscription").' '.date("d-m-Y", $subscription->trial_end).'</td>';
                } else {
                    echo '<td>'.ucfirst($subscription->status).'</td>';
                }
                echo '<td style="color: #ec5a5b; text-decoration: none;"><a href="'.$CFG->wwwroot
                .'/enrol/stripesubscription/subscriptioncancel.php?id='.$value->subscription_id
                .'">'.get_string("stripecancelsubscription", "enrol_stripesubscription").'</a></td></tr>';
                $counter++;
            } else if ($subscription->status == 'canceled') {

                $userdetails = $DB->get_record_sql("SELECT * FROM {user} WHERE id = {$value->userid}");

                echo '<tr style="text-align: center;">
                    <td>'.$counter.'</td>
                    <td>'.$value->product_name.'</td>
                    <td>'.fullname($userdetails, true).'</td>
                    <td>'.$value->receiver_email.'</td>
                    <td>'.date("d/m/Y", $value->timeupdated).'</td>
                    <td>'.$value->payment_currency.' '.$value->amount.'/'.ucfirst($value->subscription_interval).'</td>
                    <td>Canceled</td>
                    <td style="color: #ec5a5b; text-decoration: none;"><a href="'.$CFG->wwwroot
                    .'/enrol/stripesubscription/subscriptionremrecord.php?id='.$value->subscription_id
                    .'">'.get_string("stripeclearrecord", "enrol_stripesubscription").'</a></td></tr>';
                $counter++;
            }
        }
    } ?>
  </tbody>
</table>

<?php } else {

    $stripesubscription = $DB->get_records_sql("SELECT * FROM {enrol_stripesubscription} WHERE
     userid = {$USER->id} ORDER BY id DESC");

?>

<table id="dtBasicExample" class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
  <thead>
    <tr style="text-align: center;">
      <th class="th-sm"><?php echo get_string("stripeid", "enrol_stripesubscription"); ?></th>
      <th class="th-sm"><?php echo get_string("stripecoursename", "enrol_stripesubscription"); ?></th>
      <th class="th-sm"><?php echo get_string("stripecreatedon", "enrol_stripesubscription"); ?></th>
      <th class="th-sm"><?php echo get_string("stripeamountinterval", "enrol_stripesubscription"); ?></th>
      <th class="th-sm"><?php echo get_string("stripesubscriptionstatus", "enrol_stripesubscription"); ?></th>
      <th class="th-sm"><?php echo get_string("stripeaction", "enrol_stripesubscription"); ?></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($stripesubscription as $key => $value) {
        if ($value->payment_type != 'free') {
            \Stripe\Stripe::setApiKey($plugin->get_config('secretkey'));

            $subscription = \Stripe\Subscription::retrieve($value->subscription_id);

            if ($subscription->status == 'active' || $subscription->status == 'trialing') {
                echo '<tr style="text-align: center;">
                    <td>'.$counter.'</td>
                    <td>'.$value->product_name.'</td>
                    <td>'.date("d/m/Y", $value->timeupdated).'</td>
                    <td>'.$value->payment_currency.' '.$value->amount.'/'.ucfirst($value->subscription_interval).'</td>';
                if ($subscription->status == 'trialing') {
                    echo '<td>'.get_string("stripetrialendson", "enrol_stripesubscription").' '.date("d-m-Y", $subscription->trial_end).'</td>';
                } else {
                    echo '<td>'.ucfirst($subscription->status).'</td>';
                }
                echo '<td style="color: #ec5a5b; text-decoration: none;"><a href="'.$CFG->wwwroot
                .'/enrol/stripesubscription/subscriptioncancel.php?id='.$value->subscription_id
                .'">'.get_string("stripecancelsubscription", "enrol_stripesubscription").'</a></td></tr>';
                $counter++;
            } else if ($subscription->status == 'canceled') {
                echo '<tr style="text-align: center;">
                    <td>'.$counter.'</td>
                    <td>'.$value->product_name.'</td>
                    <td>'.date("d/m/Y", $value->timeupdated).'</td>
                    <td>'.$value->payment_currency.' '.$value->amount.'/'.ucfirst($value->subscription_interval).'</td>
                    <td>Canceled</td>
                    <td style="color: #ec5a5b; text-decoration: none;"><a href="'.$CFG->wwwroot
                    .'/enrol/stripesubscription/subscriptionremrecord.php?id='.$value->subscription_id
                    .'">'.get_string("stripeclearrecord", "enrol_stripesubscription").'</a></td></tr>';
                $counter++;
            }
        }
    } ?>
  </tbody>
</table>

<?php } ?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js">
</script>
<script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js" type="text/javascript"></script>
<script type="text/javascript">
$(document).ready(function () {
    $('#dtBasicExample').DataTable();
    $('.dataTables_length').addClass('bs-select');
});
</script>
<?php

// Page footer.
echo $OUTPUT->footer();