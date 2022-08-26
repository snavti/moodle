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
 * Mustache helper to load a theme configuration.
 *
 * @package    theme_space
 * @copyright  Copyright © 2021 onwards, Marcin Czaja | RoseaThemes, rosea.io - Rosea Themes
 * @license    Commercial https://themeforest.net/licenses
 */

namespace theme_space\util;

use theme_config;
use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Helper to load a theme configuration.
 *
 * @package    theme_space
 * @copyright Copyright © 2018 onwards, Marcin Czaja | RoseaThemes, rosea.io - Rosea Themes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class theme_settings {

  /* --- GLOBAL SETTINGS ---*/
  public function global_settings() {
    $theme = theme_config::load('space');

    $templatecontext = [];
    $elements = [
      'googlefonturl', 'seothemecolor', 'themeauthor', 'displaycustomalert', 'closecustomalert', 'topbarlogoareaon', 'fontawesome', 'fontfiles'
    ];

    foreach ($elements as $setting) {
      if (!empty($theme->settings->$setting)) {
          $templatecontext[$setting] = $theme->settings->$setting;
      }
    }

    $elementshtml = [
      'stringmycourses', 'seometadesc', 'customalerthtml', 'customstcontent', 'customsmcontent', 'customsfcontent', 'customlogotxt', 'topbarcustomhtml', 'customnavitems'
    ];

    foreach ($elementshtml as $setting) {
      if (!empty($theme->settings->$setting)) {
          $templatecontext[$setting] = format_text(($theme->settings->$setting),FORMAT_HTML, array('noclean' => true));
      }
    }

    if (!empty($theme->setting_file_url('customlogo', 'customlogo'))) {
      $templatecontext['customlogo'] = $theme->setting_file_url('customlogo', 'customlogo');
    }
    
    if (!empty($theme->setting_file_url('customdmlogo', 'customdmlogo'))) {
      $templatecontext['customdmlogo'] = $theme->setting_file_url('customdmlogo', 'customdmlogo');
    }

    if (!empty($theme->setting_file_url('customsidebarlogo', 'customsidebarlogo'))) {
      $templatecontext['customsidebarlogo'] = $theme->setting_file_url('customsidebarlogo', 'customsidebarlogo');
    }

    if (!empty($theme->setting_file_url('customsidebardmlogo', 'customsidebardmlogo'))) {
      $templatecontext['customsidebardmlogo'] = $theme->setting_file_url('customsidebardmlogo', 'customsidebardmlogo');
    }

    if (!empty($theme->setting_file_url('seomanifestjson', 'seomanifestjson'))) {
      $templatecontext['seomanifestjson'] = $theme->setting_file_url('seomanifestjson', 'seomanifestjson');
    }

    if (!empty($theme->setting_file_url('seoappletouchicon', 'seoappletouchicon'))) {
      $templatecontext['seoappletouchicon'] = $theme->setting_file_url('seoappletouchicon', 'seoappletouchicon');
    }

    return $templatecontext;
  }



  /* --- DASHBOARD ---*/
  public function dashboard_settings() {
    $theme = theme_config::load('space');

    $templatecontext = [];

    if ($theme->settings->setdashboardlayout == 1) {
      $templatecontext['hasrightcolumn'] = false;
      $templatecontext['hasleftcolumn'] = false;
      $templatecontext['hastwocol'] = false;
      $templatecontext['hasonecol'] = true;
    } elseif ($theme->settings->setdashboardlayout == 2) {
      $templatecontext['hasrightcolumn'] = false;
      $templatecontext['hasleftcolumn'] = true;
      $templatecontext['hastwocol'] = true;
    } elseif ($theme->settings->setdashboardlayout == 3) {
      $templatecontext['hasrightcolumn'] = true;
      $templatecontext['hasleftcolumn'] = false;
      $templatecontext['hastwocol'] = true;
    }

    $elements = [
      'customdcolsize'
    ];
    foreach ($elements as $setting) {
      if (!empty($theme->settings->$setting)) {
        $templatecontext[$setting] = $theme->settings->$setting;
      }
    }

    return $templatecontext;
  }



  /* --- FOOTER ---*/
  public function footer_settings() {
    $theme = theme_config::load('space');

    $templatecontext = [];

    $elements = [
      'customalert', 'darkmodetheme', 'footercustomcss', 'showfooterbuttons', 'showsociallist', 'facebook', 'twitter', 'linkedin', 'youtube', 'instagram',
      'website', 'cwebsiteurl', 'mobile', 'mail', 'customsocialicon'
    ];

    foreach ($elements as $setting) {
      if (!empty($theme->settings->$setting)) {
          $templatecontext[$setting] = $theme->settings->$setting;
      }
    }

    $elementshtml = [
      'footerblock1', 'footerblock2', 'footerblock3', 'footercopy', 'block5slidesperrow', 'customalertcontent'
    ];

    foreach ($elementshtml as $setting) {
      if (!empty($theme->settings->$setting)) {
          $templatecontext[$setting] = format_text(($theme->settings->$setting),FORMAT_HTML, array('noclean' => true));
      }
    }

    if (!empty($theme->setting_file_url('footerbgimg', 'footerbgimg'))) {
      $templatecontext['footerbgimg'] = $theme->setting_file_url('footerbgimg', 'footerbgimg');
    }

    return $templatecontext;
  }


  /* --- LOGIN PAGE ---*/
  public function login_settings() {
    $theme = theme_config::load('space');

    $templatecontext = [];

    $elements = [
      'loginbg'
    ];

    foreach ($elements as $setting) {
      if (!empty($theme->setting_file_url($setting, $setting))) {
          $templatecontext[$setting] = $theme->setting_file_url($setting,$setting);
      }
    }
 
    return $templatecontext;
  }
}
