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
 * Course wise edit form.
 *
 * Adds new instance of enrol_stripesubscription to specified course
 * or edits current instance.
 *
 * @package    enrol_stripesubscription
 * @copyright  2021 Digital Tricksters Software Solutions
 * @author     Trideep Das Modak <trideep@digital-tricksters.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once('lib.php');
/**
 * Sets up moodle edit form class methods.
 * @copyright  2021 Digital-Tricksters Software Solutions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_stripesubscription_edit_form extends moodleform {
    /**
     * Sets up moodle form.
     * @return void
     */
    public function definition() {
        $mform = $this->_form;

        list($instance, $plugin, $context) = $this->_customdata;

        $mform->addElement('header', 'header', get_string('pluginname', 'enrol_stripesubscription'));

        $mform->addElement('text', 'name', get_string('custominstancename', 'enrol'));
        $mform->setType('name', PARAM_TEXT);

        $options = array(ENROL_INSTANCE_ENABLED  => get_string('yes'),
           ENROL_INSTANCE_DISABLED => get_string('no'));
        $mform->addElement('select', 'status', get_string('status', 'enrol_stripesubscription'), $options);
        $mform->setDefault('status', $plugin->get_config('status'));

        $mform->addElement('text', 'cost', get_string('cost', 'enrol_stripesubscription'), array('size' => 4));
        $mform->setType('cost', PARAM_RAW); // Use unformat_float to get real value.
        $mform->setDefault('cost', format_float($plugin->get_config('cost'), 2, true));

        $stripecurrencies = $plugin->get_currencies();
        $mform->addElement('select', 'currency', get_string('currency', 'enrol_stripesubscription'), $stripecurrencies);
        $mform->setDefault('currency', $plugin->get_config('currency'));

        if ($instance->id) {
            $roles = get_default_enrol_roles($context, $instance->roleid);
        } else {
            $roles = get_default_enrol_roles($context, $plugin->get_config('roleid'));
        }
        $mform->addElement('select', 'roleid', get_string('assignrole', 'enrol_stripesubscription'), $roles);
        $mform->setDefault('roleid', $plugin->get_config('roleid'));

        $mform->addElement('text', 'customint3', get_string('maxenrolled', 'enrol_stripesubscription'));
        $mform->setDefault('maxenrolled', 'customint3');
        $mform->addHelpButton('customint3', 'maxenrolled', 'enrol_stripesubscription');
        $mform->setType('customint3', PARAM_INT);

        $mform->addElement('duration', 'enrolperiod', get_string('enrolperiod', 'enrol_stripesubscription'),
            array('optional' => true, 'defaultunit' => 86400));
        $mform->setDefault('enrolperiod', $plugin->get_config('enrolperiod'));
        $mform->addHelpButton('enrolperiod', 'enrolperiod', 'enrol_stripesubscription');

        $mform->addElement('date_time_selector', 'enrolstartdate', get_string('enrolstartdate', 'enrol_stripesubscription'),
            array('optional' => true));
        $mform->setDefault('enrolstartdate', 0);
        $mform->addHelpButton('enrolstartdate', 'enrolstartdate', 'enrol_stripesubscription');

        $mform->addElement('date_time_selector', 'enrolenddate', get_string('enrolenddate', 'enrol_stripesubscription'),
            array('optional' => true));
        $mform->setDefault('enrolenddate', 0);
        $mform->addHelpButton('enrolenddate', 'enrolenddate', 'enrol_stripesubscription');

        if ($instance->id) {
            $defaultfreetrialduration = $instance->customchar1;
            $defaultinterval = $instance->customchar2;
            $defaultcoupon = $instance->customchar3;
        } else {
            $defaultfreetrialduration = $plugin->get_config('freetrialduration');
            $defaultinterval = $plugin->get_config('subscriptioninterval');
            $defaultcoupon = $plugin->get_config('stripecoupon');
        }
        $options = array('month'  => 'Monthly',
           'year' => 'Yearly');
        $mform->addElement('select', 'subscriptioninterval', 'Subscription interval', $options);
        $mform->setDefault('subscriptioninterval', $defaultinterval);

        $mform->addElement('text', 'freetrialduration', get_string('freetrialduration',
         'enrol_stripesubscription'), array('size' => 3));
        $mform->setType('freetrialduration', PARAM_INT);
        $mform->setDefault('freetrialduration', $defaultfreetrialduration);

        $options = array('yes'  => 'Yes',
           'no' => 'No');
        $mform->addElement('select', 'stripecoupon', 'Stripe Coupon', $options);
        $mform->setDefault('stripecoupon', $defaultcoupon);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);

        if (enrol_accessing_via_instance($instance)) {
            $mform->addElement('static', 'selfwarn', get_string('instanceeditselfwarning', 'core_enrol'),
                get_string('instanceeditselfwarningtext', 'core_enrol'));
        }

        $this->add_action_buttons(true, ($instance->id ? null : get_string('addinstance', 'enrol')));

        $this->set_data($instance);
    }
    /**
     * Sets up moodle form validation.
     * @param stdClass $data
     * @param stdClass $files
     * @return $error error list
     */
    public function validation($data, $files) {
        global $DB, $CFG;
        $errors = parent::validation($data, $files);

        list($instance, $plugin, $context) = $this->_customdata;

        if (!empty($data['enrolenddate']) and $data['enrolenddate'] < $data['enrolstartdate']) {
            $errors['enrolenddate'] = get_string('enrolenddaterror', 'enrol_stripesubscription');
        }

        $cost = str_replace(get_string('decsep', 'langconfig'), '.', $data['cost']);
        if (!is_numeric($cost)) {
            $errors['cost'] = get_string('costerror', 'enrol_stripesubscription');
        }

        return $errors;
    }
}
