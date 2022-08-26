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
 *
 * @package   theme_space
 * @copyright 2022 Marcin Czaja (https://rosea.io)
 * @license   Commercial https://themeforest.net/licenses
 *
 */

defined('MOODLE_INTERNAL') || die();

// Variables - Settings
$block17introtitle = format_text(theme_space_get_setting('block17introtitle'),FORMAT_HTML, array('noclean' => true));
$block17introcontent = format_text(theme_space_get_setting('block17introcontent'),FORMAT_HTML, array('noclean' => true));
$block17html = format_text(theme_space_get_setting('block17htmlcontent'),FORMAT_HTML, array('noclean' => true));
$block17footer = format_text(theme_space_get_setting('block17footercontent'),FORMAT_HTML, array('noclean' => true));
$block17class = theme_space_get_setting('block17class');

echo '<!-- Start Block 17 -->';
echo '<div class="wrapper-xl rui-fp-block--17 '.$block17class.'">';
          if(!empty($block17introtitle) || !empty($block17introcontent)) {
          echo '<div class="wrapper-md">';
          }
          if(!empty($block17introtitle)) {
          echo '<h3 class="rui-block-title">'.$block17introtitle.'</h3>';
          }
          if(!empty($block17introcontent)) {
          echo '<div class="rui-block-desc">'.$block17introcontent.'</div>';
          }
          if(!empty($block17introtitle) || !empty($block17introcontent)) {
          echo '</div>';
          }
          echo $block17html;
          if(!empty($block17footer)) {
          echo '<div class="rui-block-footer wrapper-fw">'.$block17footer.'</div>';
          }
echo '</div>';
if(theme_space_get_setting("displayhrblock17") == '1') {
          echo '<hr class="rui-block-hr" />';
}
echo '<!-- End Block 17 -->';