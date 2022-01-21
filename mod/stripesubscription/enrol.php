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
defined('MOODLE_INTERNAL') || die();

require_once('lib.php');
global $CFG;

$islicenseactived = get_config('enrol_stripesubscription', 'stripesubscription_license_key_activated');
if ((!isset($islicenseactived) && empty($islicenseactived)) || ($islicenseactived == 0)) {
    redirect($CFG->wwwroot.'/enrol/stripesubscription/license.php', 'Please activate your license to continue');
}

?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js">
</script>
<script>
  $(document).ready(function() {
    $("#apply").click(function() {
      var txt = $("#coupon").val();
      $.post("<?php echo $CFG->wwwroot; ?>/enrol/stripesubscription/validate_coupon.php",
        {coupon_id: txt, courseid: '<?php echo $course->id; ?>'}, function(data, status) {

         if(data == 'wrong') {
           $("#coupon").focus();
           $("#new_coupon").html('<p style="color:red;"><b><?php
            echo get_string("invalidcouponcode", "enrol_stripesubscription"); ?>
             </b></p>');
         } else {

           $("#form_data_new_data").attr("value", data);
           $("#form_data_new_coupon_id").attr("value", txt);
           $( "#form_data_new" ).submit();

           $("#reload").load(location.href + " #reload");

           $("#coupon_id").attr("value", txt);
           $(".coupon_id").val(txt);
           if(data == 0.00) {
             $('#amountgreaterzero').css("display", "none");
             $('#amountequalzero').css("display", "block");
           } else {
             $('#amountgreaterzero').css("display", "block");
             $('#amountequalzero').css("display", "none");
           }
         }
       });
    });
  });
</script>

<div align="center" class="mainwrapper">
  <div class="stripe-img">
    <img src="<?php echo $CFG->wwwroot; ?>/enrol/stripesubscription/pix/stripe.png"></div>
    <p><?php print_string("paymentrequired") ?></p>

    <?php if ($instance->customchar1 != 0) {
        $a = new stdClass();
        $a->customchar1 = $instance->customchar1; ?>
        <p><b><?php echo get_string("freetrialdurationtext", "enrol_stripesubscription", $a); ?></b></p>
    <?php }
    if ($instance->customchar3 == 'yes') { ?>
        <p><b><?php echo get_string("cost").": {$instance->currency} {$cost} / ".ucfirst($instance->customchar2); ?></b></p>
        <div class="couponcode-wrap">
          <span class="couponcode-text"> <?php echo get_string("couponcode", "enrol_stripesubscription"); ?>: </span>
          <input type=text id="coupon"/>
          <button id="apply"><?php echo get_string("applycode", "enrol_stripesubscription"); ?></button>
        </div>
    <?php } ?>

    <form id="form_data_new" action="" method="post">
      <input id="form_data_new_data" type="hidden" name="data" value="" />
      <input id="form_data_new_coupon_id" type="hidden" name="coupon_id" value="" />
    </form>

    <div id="reload">
      <div id="new_coupon" style="margin-bottom:10px;"></div>
      <?php
        require('Stripe/init.php');
        $couponid = 0;
        $dataa = optional_param('data', null, PARAM_RAW);
        if ( isset($dataa) ) {
            $cost = $dataa;
            $couponid = required_param('coupon_id', PARAM_RAW);
            $_SESSION['amount'] = enrol_get_plugin('stripesubscription')->get_stripe_amount($cost, $_SESSION['currency'], false);
        }

        $_SESSION['description'] = $coursefullname;
        $_SESSION['courseid'] = $course->id;
        $_SESSION['currency'] = $instance->currency;
        $_SESSION['amount'] = enrol_get_plugin('stripesubscription')->get_stripe_amount($cost, $_SESSION['currency'], false);

        echo "<p><b> ".get_string("finalcost", "enrol_stripesubscription")." : $instance->currency $cost / ".ucfirst($instance->customchar2)." </b></p>";

      ?>

      <?php $costvalue = str_replace(".", "", $cost);
        if ($costvalue == 000) {  ?>
          <div id="amountequalzero">
            <button id="card-button-zero">
              <?php echo get_string("enrolnow", "enrol_stripesubscription"); ?>
            </button>
          </div>
          <br>

          <script type="text/javascript">
            $(document.body).on('click', '#card-button-zero' ,function(){
              var cost = "<?php echo str_replace(".", "", $cost); ?>";
              if (cost == 000) {
                document.getElementById("stripeformfree").submit();
              }
            });
          </script>

        <?php } else { ?>
        <script src="https://js.stripe.com/v3/"></script>

        <!-- placeholder for Elements -->

        <div id="amountgreaterzero">
          <strong>
            <div id="card-element"></div> <br>
            <button id="card-button">
              <?php echo get_string("submitpayment", "enrol_stripesubscription"); ?>
            </button>
            <div id="transaction-status">
              <center> <?php echo get_string("transactionprocessingtext", "enrol_stripesubscription"); ?> </center>
            </div>
          </strong>
        </div>

        <script type="text/javascript">
          var stripe = Stripe('<?php echo $publishablekey; ?>');
          var elements = stripe.elements();
          var style = {
            base: {
              fontSize:'15px',
              color:'#000',
              '::placeholder': {
                color: '#000',
              }
            },
          };

          var cardElement = elements.create('card', {style: style});
          cardElement.mount('#card-element');
          var cardholderName = "<?php echo $userfullname; ?>";
          var emailId = "<?php echo $USER->email; ?>";
          var cardButton = document.getElementById('card-button');
          var status = 0;
          var postal = null;

          cardElement.addEventListener('change', function(event) {

            postalCode = event.value['postalCode'];

          });

          cardButton.addEventListener('click', function(event) {

            if (event.error) {
              status = 0;
            } else {
              status = 1;
            }

            if (status == 0 || status == null) {
             $("#transaction-status").css("display", "none");
           } else {
             $("#transaction-status").css("display", "block");
             $("#card-button").attr("disabled", true);

             stripe.createToken(cardElement).then(function(result) {
              if (result.error) {
                // Inform the user if there was an error
                $("#transaction-status").html("<center> <?php echo get_string("transactioncanceltext", "enrol_stripesubscription"); ?> : "
                 + result.error.message + "</center>");

                $("#card-button").attr("disabled", false);

              } else {
                $("#transaction-status").html("<center> <?php echo get_string("transactionprocessingtext", "enrol_stripesubscription"); ?> </center>");
                // Send the token to your server
                document.getElementById("auth").value = result.token.id;
                document.getElementById("stripeform").submit();
              }
            });

           }
         });

       </script>

        <?php } ?>

     <form id="stripeform" action="<?php echo "$CFG->wwwroot/enrol/stripesubscription/charge.php"?>" method="post">
      <input id="coupon_id" type="hidden" name="coupon_id" value="<?php p($couponid) ?>" class="coupon_id" />
      <input type="hidden" name="cmd" value="_xclick" />
      <input type="hidden" name="charset" value="utf-8" />
      <input type="hidden" name="item_name" value="<?php p($coursefullname) ?>" />
      <input type="hidden" name="item_number" value="<?php p($courseshortname) ?>" />
      <input type="hidden" name="quantity" value="1" />
      <input type="hidden" name="on0" value="<?php print_string("user") ?>" />
      <input type="hidden" name="os0" value="<?php p($userfullname) ?>" />
      <input type="hidden" name="custom" value="<?php echo "{$USER->id}-{$course->id}-{$instance->id}" ?>" />
      <input type="hidden" name="customchar1" value="<?php echo "{$instance->customchar1}" ?>" />
      <input type="hidden" name="currency_code" value="<?php p($instance->currency) ?>" />
      <input type="hidden" name="amount" value="<?php p($cost) ?>" />
      <input type="hidden" name="for_auction" value="false" />
      <input type="hidden" name="no_note" value="1" />
      <input type="hidden" name="no_shipping" value="1" />
      <input type="hidden" name="rm" value="2" />
      <input type="hidden" name="cbt" value="<?php print_string("continuetocourse") ?>" />
      <input type="hidden" name="first_name" value="<?php p($userfirstname) ?>" />
      <input type="hidden" name="last_name" value="<?php p($userlastname) ?>" />
      <input type="hidden" name="address" value="<?php p($useraddress) ?>" />
      <input type="hidden" name="city" value="<?php p($usercity) ?>" />
      <input type="hidden" name="email" value="<?php p($USER->email) ?>" />
      <input type="hidden" name="country" value="<?php p($USER->country) ?>" />
      <input type="hidden" name="subscriptioninterval" value="<?php p($instance->customchar2) ?>" />
      <input id="cardholder-name" name="cname" type="hidden" value="<?php echo $userfullname; ?>">
      <input id="cardholder-email" type="hidden" name="email" value="<?php p($USER->email) ?>" />
      <input id="auth" name="auth" type="hidden" value="">
    </form>

    <form id="stripeformfree" action="<?php
    echo "$CFG->wwwroot/enrol/stripesubscription/free_enrol.php"?>" method="post">
    <input type="hidden" name="coupon_id" value="<?php p($couponid) ?>" class="coupon_id" />
    <input type="hidden" name="cmd" value="_xclick" />
    <input type="hidden" name="charset" value="utf-8" />
    <input type="hidden" name="item_name" value="<?php p($coursefullname) ?>" />
    <input type="hidden" name="item_number" value="<?php p($courseshortname) ?>" />
    <input type="hidden" name="quantity" value="1" />
    <input type="hidden" name="on0" value="<?php print_string("user") ?>" />
    <input type="hidden" name="os0" value="<?php p($userfullname) ?>" />
    <input type="hidden" name="custom" value="<?php echo "{$USER->id}-{$course->id}-{$instance->id}" ?>" />
    <input type="hidden" name="customchar1" value="0" />
    <input type="hidden" name="currency_code" value="<?php p($instance->currency) ?>" />
    <input type="hidden" name="amount" value="<?php p($cost) ?>" />
    <input type="hidden" name="for_auction" value="false" />
    <input type="hidden" name="no_note" value="1" />
    <input type="hidden" name="no_shipping" value="1" />
    <input type="hidden" name="rm" value="2" />
    <input type="hidden" name="cbt" value="<?php print_string("continuetocourse") ?>" />
    <input type="hidden" name="first_name" value="<?php p($userfirstname) ?>" />
    <input type="hidden" name="last_name" value="<?php p($userlastname) ?>" />
    <input type="hidden" name="address" value="<?php p($useraddress) ?>" />
    <input type="hidden" name="city" value="<?php p($usercity) ?>" />
    <input type="hidden" name="email" value="<?php p($USER->email) ?>" />
    <input type="hidden" name="country" value="<?php p($USER->country) ?>" />
    <input type="hidden" name="subscriptioninterval" value="<?php p($instance->customchar2) ?>" />

  </form>

</div>
</div>
