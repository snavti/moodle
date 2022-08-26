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

defined('MOODLE_INTERNAL') || die();

/**
 * A login page layout for the space theme.
 *
 * @package   theme_space
 * @copyright 2022 Marcin Czaja (https://rosea.io)
 * @license   Commercial https://themeforest.net/licenses
 */

$siteurl = $CFG->wwwroot;

$loginlayout = theme_space_get_setting('setloginlayout');

$loginlayout1 = false;
$extraclasses[] = 'rui-login-page';

$loginlayout = theme_space_get_setting('setloginlayout');
if ($loginlayout == 1) {
    $extraclasses[] = 'rui-login-layout-1';
    $loginlayout1 = true;
}
if ($loginlayout == 2) {
    $extraclasses[] = 'rui-login-layout-2';
}
if ($loginlayout == 3) {
    $extraclasses[] = 'rui-login-layout-3';
}
if ($loginlayout == 4) {
    $extraclasses[] = 'rui-login-layout-4';
}
if ($loginlayout == 5) {
    $extraclasses[] = 'rui-login-layout-5';
}

$bodyattributes = $OUTPUT->body_attributes($extraclasses);

$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
    'output' => $OUTPUT,
    'bodyattributes' => $bodyattributes,
    'siteurl' => $siteurl,
    'loginlayout1' => $loginlayout1
];

$themesettings = new \theme_space\util\theme_settings();
$templatecontext = array_merge($templatecontext, $themesettings->global_settings());
$templatecontext = array_merge($templatecontext, $themesettings->footer_settings());
$templatecontext = array_merge($templatecontext, $themesettings->login_settings());

echo $OUTPUT->render_from_template('theme_space/tmpl-login', $templatecontext);