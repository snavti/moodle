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
 * Strings for component 'enrol_stripesubscription', language 'en'.
 *
 * @package    enrol_stripesubscription
 * @copyright  2021 Digital Tricksters Software Solutions
 * @author     Trideep Das Modak <trideep@digital-tricksters.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['assignrole'] = 'Assign role';
$string['secretkey'] = 'Stripe Secret Key';
$string['publishablekey'] = 'Stripe Publishable Key';
$string['secretkey_desc'] = 'The API Secret Key of Stripe account';
$string['publishablekey_desc'] = 'The API Publishable Key of Stripe account';
$string['cost'] = 'Enrol cost';
$string['costerror'] = 'The enrolment cost is not numeric';
$string['costorkey'] = 'Please choose one of the following methods of enrolment.';
$string['currency'] = 'Currency';
$string['defaultrole'] = 'Default role assignment';
$string['defaultrole_desc'] = 'Select role which should be assigned to users during Stripe enrolments';
$string['enrolenddate'] = 'Course enrolment end date';
$string['enrolenddate_help'] = 'If enabled, users can be enrolled until this date only.';
$string['enrolenddaterror'] = 'Enrolment end date cannot be earlier than start date';
$string['enrolperiod'] = 'Enrolment duration';
$string['enrolperiod_desc'] = 'Default length of time that the enrolment is valid. If set to zero, the enrolment duration will be unlimited by default.';
$string['enrolperiod_help'] = 'Length of time that the enrolment is valid, starting with the moment the user is enrolled. If disabled, the enrolment duration will be unlimited.';
$string['enrolstartdate'] = 'Course enrolment start date';
$string['enrolstartdate_help'] = 'If enabled, users can be enrolled from this date onward only.';
$string['expiredaction'] = 'Enrolment expiration action';
$string['expiredaction_help'] = 'Select action to carry out when user enrolment expires. Please note that some user data and settings are purged from course during course unenrolment.';
$string['mailadmins'] = 'Notify admin';
$string['mailstudents'] = 'Notify students';
$string['mailteachers'] = 'Notify teachers';
$string['messageprovider:stripe_enrolment'] = 'Stripe enrolment messages';
$string['nocost'] = 'There is no cost associated with enrolling in this course!';
$string['stripe:config'] = 'Configure Stripe enrol instances';
$string['stripe:manage'] = 'Manage enrolled users';
$string['stripe:unenrol'] = 'Unenrol users from course';
$string['stripe:unenrolself'] = 'Unenrol self from the course';
$string['stripeaccepted'] = 'Stripe payments accepted';
$string['pluginname'] = 'Stripe Subscription';
$string['pluginname_desc'] = 'The Stripe module allows you to set up paid courses.  If the cost for any course is zero, then students are not asked to pay for entry.  There is a site-wide cost that you set here as a default for the whole site and then a course setting that you can set for each course individually. The course cost overrides the site cost.';
$string['sendpaymentbutton'] = 'Send payment via Stripe';
$string['status'] = 'Allow Stripe enrolments';
$string['status_desc'] = 'Allow users to use Stripe to enrol into a course by default.';
$string['unenrolselfconfirm'] = 'Do you really want to unenrol yourself from course "{$a}"?';
$string['messageprovider:stripesubscription_enrolment'] = 'Message Provider';

$string['maxenrolled'] = 'Max enrolled users';
$string['maxenrolled_help'] = 'Specifies the maximum number of users that can stripesubscription enrol. 0 means no limit.';
$string['maxenrolledreached'] = 'Maximum number of users allowed to stripesubscription-enrol was already reached.';

$string['canntenrol'] = 'Enrolment is disabled or inactive';
$string['stripesubscription:config'] = 'Configure stripesubscription';
$string['stripesubscription:manage'] = 'Manage stripesubscription';
$string['stripesubscription:unenrol'] = 'Unenrol stripesubscription';
$string['stripesubscription:unenrolself'] = 'Unenrolself stripesubscription';

$string['charge_description1'] = "create customer for email receipt";
$string['charge_description2'] = 'Charge for Course Enrolment Cost.';

$string['stripe_sorry'] = "Sorry, you can not use the script that way.";
$string['newcost'] = 'New Cost';
$string['couponcode'] = 'Coupon Code';
$string['applycode'] = 'Apply Code';
$string['invalidcouponcode'] = 'Invalid Coupon Code';
$string['invalidcouponcodevalue'] = 'Coupon Code {$a} is not valid!';
$string['enrol'] = 'Enrol';
$string['subscriptioninterval'] = 'Subscription interval';
$string['subscriptioninterval_desc'] = 'Select the subscription interval';
$string['stripecoupon'] = 'Stripe Coupon';
$string['stripecoupon_desc'] = 'Select no if you don\'t want to use stripe coupon';
$string['freetrialduration'] = 'Stripe Trial Duration';
$string['freetrialdurationtext'] = 'This course has {$a->customchar1}day(s) FREE trial';
$string['freetrialduration_desc'] = 'Default 0 if you don\'t want to use any free trial otherwise enter the no of days. Ex: 15. if you want a 15days free trial.';
$string['finalcost'] = 'Final Cost';
$string['enrolnow'] = 'Enrol Now';
$string['submitpayment'] = 'Submit Payment';
$string['transactionprocessingtext'] = 'Your transaction is processing. Please wait...';
$string['transactioncanceltext'] = 'Sorry! Your transaction is failed.<br>Error';
$string['stripeauthenticationerror'] = 'Stripe Authentication Error';
$string['stripepaymenterror'] = 'STRIPE PAYMENT ERROR';
$string['stripecouponerror'] = 'wrong';
$string['stripeid'] = 'Id';
$string['stripecoursename'] = 'Course Name';
$string['stripestudentname'] = 'Student Name';
$string['stripestudentemail'] = 'Student Email';
$string['stripecreatedon'] = 'Created On';
$string['stripeamountinterval'] = 'Amount / Interval';
$string['stripesubscriptionstatus'] = 'Subscription Status';
$string['stripeaction'] = 'Action';
$string['stripetrialendson'] = 'Trial ends on';
$string['stripecancelsubscription'] = 'Cancel Subscription';
$string['stripeclearrecord'] = 'Clear Record';
$string['subscriptionsuccessfullycanceled'] = 'Subscription successfully canceled.';
$string['subscriptioncancellationfailed'] = 'Subscription cancellation failed.';
$string['subscriptionrecordsuccessfullycleared'] = 'Subscription record successfully cleared.';
$string['licensesettings_desc'] = 'Activate your license to use this plugin. Click on <a href="'.$CFG->wwwroot.'/enrol/stripesubscription/license.php">License Settings</a> page to activate your license.';



