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
$block15introtitle = format_text(theme_space_get_setting('block15introtitle'),FORMAT_HTML, array('noclean' => true));
$block15introcontent = format_text(theme_space_get_setting('block15introcontent'),FORMAT_HTML, array('noclean' => true));
$block15html = format_text(theme_space_get_setting('block15htmlcontent'),FORMAT_HTML, array('noclean' => true));
$block15footer = format_text(theme_space_get_setting('block15footercontent'),FORMAT_HTML, array('noclean' => true));
$block15class = theme_space_get_setting('block15class');

echo '<!-- Start Block 15 -->';
echo '<div class="wrapper-xl rui-fp-block--15 '.$block15class.'">';
          if(!empty($block15introtitle) || !empty($block15introcontent)) {
          echo '<div class="wrapper-md">';
          }
          if(!empty($block15introtitle)) {
          echo '<h3 class="rui-block-title">'.$block15introtitle.'</h3>';
          }
          if(!empty($block15introcontent)) {
          echo '<div class="rui-block-desc">'.$block15introcontent.'</div>';
          }
          if(!empty($block15introtitle) || !empty($block15introcontent)) {
          echo '</div>';
          }
          echo $block15html;
          if(!empty($block15footer)) {
          echo '<div class="rui-block-footer wrapper-fw">'.$block15footer.'</div>';
          }
echo '</div>';
if(theme_space_get_setting("displayhrblock15") == '1') {
          echo '<hr class="rui-block-hr" />';
}
echo '<!-- End Block 15 -->';