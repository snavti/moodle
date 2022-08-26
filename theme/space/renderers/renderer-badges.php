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

// BADGES
require_once($CFG->dirroot . "/badges/renderer.php");
class theme_space_core_badges_renderer extends core_badges_renderer {

        // Outputs badges list.
        public function print_badges_list($badges, $userid, $profile = false, $external = false) {
            global $USER, $CFG;
            foreach ($badges as $badge) {
                if (!$external) {
                    $context = ($badge->type == BADGE_TYPE_SITE) ? context_system::instance() : context_course::instance($badge->courseid);
                    $bname = $badge->name;
                    $imageurl = moodle_url::make_pluginfile_url($context->id, 'badges', 'badgeimage', $badge->id, '/', 'f3', false);
                } else {
                    $bname = '';
                    $imageurl = '';
                    if (!empty($badge->name)) {
                        $bname = s($badge->name);
                    }
                    if (!empty($badge->image)) {
                        $imageurl = $badge->image;
                    }
                    if (isset($badge->assertion->badge->name)) {
                        $bname = s($badge->assertion->badge->name);
                    }
                    if (isset($badge->imageUrl)) {
                        $imageurl = $badge->imageUrl;
                    }
                }

                $name = html_writer::tag('span', $bname, array('class' => 'badge-name'));

                $image = html_writer::empty_tag('img', array('src' => $imageurl, 'class' => 'badge-image'));
                if (!empty($badge->dateexpire) && $badge->dateexpire < time()) {
                    $image .= $this->output->pix_icon('i/expired',
                            get_string('expireddate', 'badges', userdate($badge->dateexpire)),
                            'moodle',
                            array('class' => 'expireimage'));
                    $name .= '(' . get_string('expired', 'badges') . ')';
                }

                $download = $status = $push = '';
                if (($userid == $USER->id) && !$profile) {
                    $params = array(
                        'download' => $badge->id,
                        'hash' => $badge->uniquehash,
                        'sesskey' => sesskey()
                    );
                    $url = new moodle_url(
                        'mybadges.php',
                        $params
                    );
                    $notexpiredbadge = (empty($badge->dateexpire) || $badge->dateexpire > time());
                    $userbackpack = badges_get_user_backpack();
                    if (!empty($CFG->badges_allowexternalbackpack) && $notexpiredbadge && $userbackpack) {
                        $assertion = new moodle_url('/badges/assertion.php', array('b' => $badge->uniquehash));
                        $icon = new pix_icon('t/backpack', get_string('addtobackpack', 'badges'));
                        if (badges_open_badges_backpack_api($userbackpack->id) == OPEN_BADGES_V2) {
                            $addurl = new moodle_url('/badges/backpack-add.php', array('hash' => $badge->uniquehash));
                            $push = $this->output->action_icon($addurl, $icon);
                        } else if (badges_open_badges_backpack_api($userbackpack->id) == OPEN_BADGES_V2P1) {
                            $addurl = new moodle_url('/badges/backpack-export.php', array('hash' => $badge->uniquehash));
                            $push = $this->output->action_icon($addurl, $icon);
                        }
                    }

                    $downloadIcon = '<svg width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.75 14.75V16.25C4.75 17.9069 6.09315 19.25 7.75 19.25H16.25C17.9069 19.25 19.25 17.9069 19.25 16.25V14.75"></path><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14.25L12 4.75"></path><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.75 10.75L12 14.25L15.25 10.75"></path></svg>';
                    $download = '<a class="btn btn-sm btn-secondary" href="' . $url . '">'. $downloadIcon . '</a>';
                    if ($badge->visible) {
                        $url = new moodle_url('mybadges.php', array('hide' => $badge->issuedid, 'sesskey' => sesskey()));
                        $hideIcon = '<svg width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.6247 10C19.0646 10.8986 19.25 11.6745 19.25 12C19.25 13 17.5 18.25 12 18.25C11.2686 18.25 10.6035 18.1572 10 17.9938M7 16.2686C5.36209 14.6693 4.75 12.5914 4.75 12C4.75 11 6.5 5.75 12 5.75C13.7947 5.75 15.1901 6.30902 16.2558 7.09698"></path><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.25 4.75L4.75 19.25"></path><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.409 13.591C9.53033 12.7123 9.53033 11.2877 10.409 10.409C11.2877 9.5303 12.7123 9.5303 13.591 10.409"></path></svg>';
                        $status = '<a class="btn btn-sm btn-danger" href="' . $url . '" title="' . get_string('makeprivate', 'badges') . '">'. $hideIcon . '</a>';
                    } else {
                        $url = new moodle_url('mybadges.php', array('show' => $badge->issuedid, 'sesskey' => sesskey()));
                        $showIcon = '<svg width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.25 12C19.25 13 17.5 18.25 12 18.25C6.5 18.25 4.75 13 4.75 12C4.75 11 6.5 5.75 12 5.75C17.5 5.75 19.25 11 19.25 12Z"></path><circle cx="12" cy="12" r="2.25" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></circle></svg>';
                        $status = '<a class="btn btn-sm btn-success" href="' . $url . '" title="' . get_string('makepublic', 'badges') . '">'. $showIcon . '</a>';
                    }
                }

                if (!$profile) {
                    $url = new moodle_url('badge.php', array('hash' => $badge->uniquehash));
                } else {
                    if (!$external) {
                        $url = new moodle_url('/badges/badge.php', array('hash' => $badge->uniquehash));
                    } else {
                        $hash = hash('md5', $badge->hostedUrl);
                        $url = new moodle_url('/badges/external.php', array('hash' => $hash, 'user' => $userid));
                    }
                }
                $actions = html_writer::tag('div', $push . $download . $status, array('class' => 'rui-badge-actions'));
                $items[] = html_writer::link($url, $image . $name . $actions, array('title' => $bname));
            }

            return html_writer::alist($items, array('class' => 'badges rui-list-group'));
        }

    // Prints action icons for the badge.
    public function print_badge_table_actions($badge, $context) {
        $actions = "";

        if (has_capability('moodle/badges:configuredetails', $context) && $badge->has_criteria()) {
            // Activate/deactivate badge.
            if ($badge->status == BADGE_STATUS_INACTIVE || $badge->status == BADGE_STATUS_INACTIVE_LOCKED) {
                // "Activate" will go to another page and ask for confirmation.
                $url = new moodle_url('/badges/action.php');
                $url->param('id', $badge->id);
                $url->param('activate', true);
                $url->param('sesskey', sesskey());
                $return = new moodle_url(qualified_me());
                $url->param('return', $return->out_as_local_url(false));
                $actions .= '<a class="btn btn-sm btn-outline-secondary mr-1" href="' . $url . '" title="'. get_string('activate', 'badges') .'">
                <svg width="24" height="24" fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.25 12C19.25 13 17.5 18.25 12 18.25C6.5 18.25 4.75 13 4.75 12C4.75 11 6.5 5.75 12 5.75C17.5 5.75 19.25 11 19.25 12Z"></path>
                <circle cx="12" cy="12" r="2.25" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></circle>
                </svg>
                </a>';
            } else {
                $url = new moodle_url(qualified_me());
                $url->param('lock', $badge->id);
                $url->param('sesskey', sesskey());
                $actions .= '<a class="btn btn-sm btn-outline-secondary mr-1" href="' . $url . '" title="'. get_string('deactivate', 'badges').'">
                <svg width="24" height="24" fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.6247 10C19.0646 10.8986 19.25 11.6745 19.25 12C19.25 13 17.5 18.25 12 18.25C11.2686 18.25 10.6035 18.1572 10 17.9938M7 16.2686C5.36209 14.6693 4.75 12.5914 4.75 12C4.75 11 6.5 5.75 12 5.75C13.7947 5.75 15.1901 6.30902 16.2558 7.09698"></path>
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.25 4.75L4.75 19.25"></path>
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.409 13.591C9.53033 12.7123 9.53033 11.2877 10.409 10.409C11.2877 9.5303 12.7123 9.5303 13.591 10.409"></path>
                </svg>
                </a>';
            }
        }

        // Award badge manually.
        if ($badge->has_manual_award_criteria() &&
                has_capability('moodle/badges:awardbadge', $context) &&
                $badge->is_active()) {
            $url = new moodle_url('/badges/award.php', array('id' => $badge->id));
            $actions .= '<a class="btn btn-sm btn-outline-secondary mr-1" href="' . $url . '" title="'. get_string('award', 'badges') .'">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M17.25 10C17.25 12.8995 14.8995 15.25 12 15.25C9.10051 15.25 6.75 12.8995 6.75 10C6.75 7.10051 9.10051 4.75 12 4.75C14.8995 4.75 17.25 7.10051 17.25 10Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
            <path d="M8.75 14.75L7.75 19.25L12 17.75L16.25 19.25L15.25 14.75" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            </a>';
        }

        // Edit badge.
        if (has_capability('moodle/badges:configuredetails', $context)) {
            $url = new moodle_url('/badges/edit.php', array('id' => $badge->id, 'action' => 'badge'));
            $actions .= '<a class="btn btn-sm btn-outline-secondary mr-1" href="' . $url . '" title="'. get_string('edit') .'">
            <svg width="24" height="24" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.75 19.25L9 18.25L18.2929 8.95711C18.6834 8.56658 18.6834 7.93342 18.2929 7.54289L16.4571 5.70711C16.0666 5.31658 15.4334 5.31658 15.0429 5.70711L5.75 15L4.75 19.25Z"></path>
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.25 19.25H13.75"></path>
            </svg>
            </a>';
        }

        // Duplicate badge.
        if (has_capability('moodle/badges:createbadge', $context)) {
            $url = new moodle_url('/badges/action.php', array('copy' => '1', 'id' => $badge->id, 'sesskey' => sesskey()));
            $actions .= '<a class="btn btn-sm btn-outline-secondary mr-1" href="' . $url . '" title="'. get_string('copy') .'">
            <svg width="24" height="24" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.5 15.25V15.25C5.5335 15.25 4.75 14.4665 4.75 13.5V6.75C4.75 5.64543 5.64543 4.75 6.75 4.75H13.5C14.4665 4.75 15.25 5.5335 15.25 6.5V6.5"></path>
            <rect width="10.5" height="10.5" x="8.75" y="8.75" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" rx="2"></rect>
            </svg>
            </a>';
        }

        // Delete badge.
        if (has_capability('moodle/badges:deletebadge', $context)) {
            $url = new moodle_url(qualified_me());
            $url->param('delete', $badge->id);
            $actions .= '<a class="btn btn-sm btn-outline-danger mr-1" href="' . $url . '" title="'.get_string('delete').'">
            <svg width="24" height="24" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 7.75L7.59115 17.4233C7.68102 18.4568 8.54622 19.25 9.58363 19.25H14.4164C15.4538 19.25 16.319 18.4568 16.4088 17.4233L17.25 7.75"></path>
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 7.5V6.75C9.75 5.64543 10.6454 4.75 11.75 4.75H12.25C13.3546 4.75 14.25 5.64543 14.25 6.75V7.5"></path>
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 7.75H19"></path>
            </svg></a>';
        }

        return $actions;
    }

    /**
     * Render an issued badge.
     *
     * @param \core_badges\output\issued_badge $ibadge
     * @return string
     */
    protected function render_issued_badge(\core_badges\output\issued_badge $ibadge) {
        global $USER, $CFG, $DB, $SITE;
        $issued = $ibadge->issued;
        $userinfo = $ibadge->recipient;
        $badgeclass = $ibadge->badgeclass;
        $badge = new badge($ibadge->badgeid);
        $now = time();
        if (isset($issued['expires'])) {
            if (!is_numeric($issued['expires'])) {
                $issued['expires'] = strtotime($issued['expires']);
            }
            $expiration = $issued['expires'];
        } else {
            $expiration = $now + 86400;
        }

        $badgeimage = is_array($badgeclass['image']) ? $badgeclass['image']['id'] : $badgeclass['image'];
        $languages = get_string_manager()->get_list_of_languages();

        $output = '';
        $output .= html_writer::start_tag('div', array('id' => 'badges'));

        $output .= html_writer::start_tag('div', array('class' => 'rui-badge-overview-wrapper border rounded p-6 mt-2 mb-2 d-inline-flex align-items-start w-100'));

            $output .= html_writer::start_tag('div', array('class' => 'border rounded p-4 mr-6 mt-6'));
                $output .= html_writer::empty_tag('img', array('src' => $badgeimage, 'alt' => $badge->name, 'class' => 'activatebadge', 'width' => '100'));
                if ($expiration < $now) {
                    $output .= $this->output->pix_icon('i/expired',
                    get_string('expireddate', 'badges', userdate($issued['expires'])),
                        'moodle',
                        array('class' => 'expireimage'));
                }

                if ($USER->id == $userinfo->id && !empty($CFG->enablebadges)) {
                    $output .= '<div class="mt-4">' . $this->output->single_button(
                                new moodle_url('/badges/badge.php', array('hash' => $ibadge->hash, 'bake' => true)),
                                get_string('download'),
                                'POST') . '</div>';
                    if (!empty($CFG->badges_allowexternalbackpack) && ($expiration > $now)
                        && $userbackpack = badges_get_user_backpack($USER->id)) {

                        if (badges_open_badges_backpack_api($userbackpack->id) == OPEN_BADGES_V2P1) {
                            $assertion = new moodle_url('/badges/backpack-export.php', array('hash' => $ibadge->hash));
                        } else {
                            $assertion = new moodle_url('/badges/backpack-add.php', array('hash' => $ibadge->hash));
                        }

                        $attributes = ['class' => 'btn btn-sm btn-secondary mt-4', 'role' => 'button'];
                        $tobackpack = html_writer::link($assertion, get_string('addtobackpack', 'badges'), $attributes);
                        $output .= $tobackpack;
                    }
                }
            $output .= html_writer::end_tag('div'); // #badge-image

            $output .= html_writer::start_tag('div', array('class' => 'rui-badge-overview'));
                // Badge details.

                if ($badge->type == BADGE_TYPE_COURSE && isset($badge->courseid)) {
                    $coursename = $DB->get_field('course', 'fullname', array('id' => $badge->courseid));
                    $output .= html_writer::start_tag('span', array('class' => 'badge badge-info'));
                        $output .= '<span class="mr-1 font-weight-bold">' . get_string('course') . ': </span>' . $coursename;
                    $output .= html_writer::end_tag('span');
                }

                if (!empty($badge->version)) {
                    $output .= html_writer::start_tag('span', array('class' => 'badge badge-secondary'));
                        $output .= '<span class="mr-1 font-weight-bold">' . get_string('version', 'badges') . ': </span>' . $badge->version;
                    $output .= html_writer::end_tag('span');
                }

                if (!empty($languages[$badge->language])) {
                    $output .= html_writer::start_tag('span', array('class' => 'badge badge-secondary'));
                        $output .= '<span class="mr-1 font-weight-bold">' . get_string('language') . ': </span>' . $languages[$badge->language];
                    $output .= html_writer::end_tag('span');
                }

                $output .= html_writer::start_tag('h4', array('class' => 'rui-badge-name'));
                $output .= $badge->name;
                $output .= html_writer::end_tag('h4');

                $output .= html_writer::start_tag('div', array('class' => 'rui-badge-desc mt-3'));

                $output .= html_writer::start_tag('h5', array('class' => 'd-inline-flex align-items-center w-100'));
                $output .= get_string('description', 'badges');
                $output .= html_writer::end_tag('h5');

                $output .= $badge->description;

                $output .= html_writer::start_tag('ul', array('class' => 'rui-badge-desc-list mt-3 ml-3'));

                    if (!empty(userdate($badge->imageauthorname))) {
                        $output .= html_writer::start_tag('li');
                            $output .= '<span class="mr-1 font-weight-bold">' . get_string('imageauthorname', 'badges') . ': </span>' . $badge->imageauthorname;
                        $output .= html_writer::end_tag('li');
                    }

                    if (!empty(userdate($badge->imageauthoremail))) {
                        $output .= html_writer::start_tag('li');
                            $output .= '<span class="mr-1 font-weight-bold">' . get_string('imageauthoremail', 'badges') . ': </span>' . html_writer::tag('a', $badge->imageauthoremail, array('href' => 'mailto:' . $badge->imageauthoremail));
                        $output .= html_writer::end_tag('li');
                    }
                    if (!empty(userdate($badge->imageauthorurl))) {
                        $output .= html_writer::start_tag('li');
                            $output .= '<span class="mr-1 font-weight-bold">' . get_string('imageauthorurl', 'badges') . ': </span>' . html_writer::link($badge->imageauthorurl, $badge->imageauthorurl, array('target' => '_blank'));
                        $output .= html_writer::end_tag('li');
                    }
                    if (!empty($badge->imagecaption)) {
                        $output .= html_writer::start_tag('li');
                            $output .= '<span class="mr-1 font-weight-bold">' . get_string('imagecaption', 'badges') . ': </span>' . $badge->imagecaption;
                        $output .= html_writer::end_tag('li');
                    }
                $output .= html_writer::end_tag('div');

                $output .= '<hr />';

                if (!is_numeric($issued['issuedOn'])) {
                    $issued['issuedOn'] = strtotime($issued['issuedOn']);
                    $output .= html_writer::start_tag('span', array('class' => 'badge badge-secondary'));
                        $output .= '<span class="mr-1 font-weight-bold">' . get_string('dateawarded', 'badges') . ': </span>' . userdate($issued['issuedOn']);
                    $output .= html_writer::end_tag('span');
                }

                if (isset($issued['expires'])) {
                    if ($issued['expires'] < $now) {
                        $output .= html_writer::start_tag('span', array('class' => 'badge badge-danger'));
                            $output .= '<span class="mr-1 font-weight-bold">' . get_string('expirydate', 'badges') . ': </span>' . userdate($issued['expires']) . get_string('warnexpired', 'badges');
                        $output .= html_writer::end_tag('span');
                    } else {
                        $output .= html_writer::start_tag('span', array('class' => 'badge badge-warning'));
                            $output .= '<span class="mr-1 font-weight-bold">' . get_string('expirydate', 'badges') . ': </span>' . userdate($issued['expires']);
                        $output .= html_writer::end_tag('span');
                    }
                }

            $output .= html_writer::end_tag('div'); // rui-badge-desc


            $output .= html_writer::end_tag('div'); // .rui-badge-overview

        $output .= html_writer::end_tag('div'); // .rui-badge-ovrview-wrapper

        $output .= html_writer::start_tag('div', array('class' => 'rui-columns-wrapper d-inline-flex justify-content-end w-100'));
            // Issuer information.
            if (!empty($badge->issuername)|| !empty($badge->issuercontact)) {
                $output .= html_writer::start_tag('div', array('class' => 'alert alert-secondary d-block mr-2'));
                    $output .=  '<span class="mr-1 font-weight-bold">' . get_string('issuername', 'badges') . ': </span>' . $badge->issuername;

                    if (!empty($badge->issuername)|| !empty($badge->issuercontact)) {
                        $output .=  html_writer::tag('a', $badge->issuercontact, array('href' => 'mailto:' . $badge->issuercontact));
                    }

                $output .= html_writer::end_tag('div');
            }

            // Recipient information.
            if ($userinfo->deleted) {
                $strdata = new stdClass();
                $strdata->user = fullname($userinfo);
                $strdata->site = format_string($SITE->fullname, true, array('context' => context_system::instance()));
                $output .= html_writer::start_tag('div', array('class' => 'alert alert-danger d-block'));
                    $output .=  '<span class="mr-1 font-weight-bold">' . get_string('recipientdetails', 'badges') . ': </span>' . get_string('error:userdeleted', 'badges', $strdata);
                $output .= html_writer::end_tag('div');
            } else {
                $output .= html_writer::start_tag('div', array('class' => 'alert alert-info d-block'));
                    $output .=  '<span class="mr-1 font-weight-bold">' . get_string('recipientdetails', 'badges') . ': </span>' . fullname($userinfo);
                $output .= html_writer::end_tag('div');
            }
        $output .= html_writer::end_tag('div'); // .rui-columns-wrapper

        $output .= html_writer::start_tag('div', array('class' => 'wrapper-fw'));

            // Criteria details if any.
            $output .= html_writer::start_tag('div', array('class' => 'rui-badge-criteria my-4'));
                $output .= html_writer::start_tag('h5', array('class' => 'd-inline-flex align-items-center w-100'));
                $output .= '<svg class="mr-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.75 13.25H6.75L13.25 4.75V10.75H17.25L10.75 19.25V13.25Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>' . get_string('bcriteria', 'badges');
                $output .= html_writer::end_tag('h5');

                if ($badge->has_criteria()) {
                    $output .= '<div class="ml-4">' . self::print_badge_criteria($badge) . '</div>';
                } else {
                    $output .= '<p class="ml-4">' . get_string('nocriteria', 'badges') . '</p>';
                    if (has_capability('moodle/badges:configurecriteria', $context)) {
                        $output .= $this->output->single_button(
                            new moodle_url('/badges/criteria.php', array('id' => $badge->id)),
                            get_string('addcriteria', 'badges'), 'POST', array('class' => 'activatebadge ml-4'));
                    }
                }

            $output .= html_writer::end_tag('div'); // .rui-badge-criteria

            // Evidence.
            $agg = $badge->get_aggregation_methods();
            $evidence = $badge->get_criteria_completions($userinfo->id);
            $eids = array_map(function($o) {
                return $o->critid;
            }, $evidence);
            unset($badge->criteria[BADGE_CRITERIA_TYPE_OVERALL]);

            $items = array();
            foreach ($badge->criteria as $type => $c) {
                if (in_array($c->id, $eids)) {
                    if (count($c->params) == 1) {
                        $items[] = get_string('criteria_descr_single_' . $type , 'badges') . $c->get_details();
                    } else {
                        $items[] = get_string('criteria_descr_' . $type , 'badges',
                                core_text::strtoupper($agg[$badge->get_aggregation_method($type)])) . $c->get_details();
                    }
                }
            }

            $output .= html_writer::start_tag('div', array('class' => 'rui-evidence-criteria my-5'));
                    $output .= html_writer::start_tag('h5', array('class' => 'd-inline-flex align-items-center w-100'));
                    $output .= '<svg class="mr-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.5 6C6.5 6.27614 6.27614 6.5 6 6.5C5.72386 6.5 5.5 6.27614 5.5 6C5.5 5.72386 5.72386 5.5 6 5.5C6.27614 5.5 6.5 5.72386 6.5 6Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path><path d="M6.5 12C6.5 12.2761 6.27614 12.5 6 12.5C5.72386 12.5 5.5 12.2761 5.5 12C5.5 11.7239 5.72386 11.5 6 11.5C6.27614 11.5 6.5 11.7239 6.5 12Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path><path d="M6.5 18C6.5 18.2761 6.27614 18.5 6 18.5C5.72386 18.5 5.5 18.2761 5.5 18C5.5 17.7239 5.72386 17.5 6 17.5C6.27614 17.5 6.5 17.7239 6.5 18Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path><path d="M9.75 6H18.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M9.75 12H18.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M9.75 18H18.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>' . get_string('evidence', 'badges');
                    $output .= html_writer::end_tag('h5');

                    $output .= '<div class="ml-4">' . get_string('completioninfo', 'badges') . html_writer::alist($items, array(), 'ul') . '</div>';

            $output .= html_writer::end_tag('div'); // .rui-evidence-criteria

            $endorsement = $badge->get_endorsement();
            if (!empty($endorsement)) {
                $output .= self::print_badge_endorsement($badge);
            }

            $output .= html_writer::start_tag('div', array('class' => 'rui-badge-related my-5'));
                $output .= self::print_badge_related($badge);
            $output .= html_writer::end_tag('div');

            $alignments = $badge->get_alignments();
            if (!empty($alignments)) {
                $output .= self::print_badge_alignments($badge);
            }
            $output .= html_writer::end_tag('div');

        $output .= html_writer::end_tag('div'); // .wrapper-fw

        return $output;
    }

    /**
     * Render a collection of user badges.
     *
     * @param \core_badges\output\badge_user_collection $badges
     * @return string
     */
    protected function render_badge_user_collection(\core_badges\output\badge_user_collection $badges) {
        global $CFG, $USER, $SITE;
        $backpack = $badges->backpack;
        $mybackpack = new moodle_url('/badges/mybackpack.php');

        $paging = new paging_bar($badges->totalcount, $badges->page, $badges->perpage, $this->page->url, 'page');
        $htmlpagingbar = $this->render($paging);

        // Set backpack connection string.
        $backpackconnect = '';
        if (!empty($CFG->badges_allowexternalbackpack) && is_null($backpack)) {
            $backpackconnect = $this->output->box(get_string('localconnectto', 'badges', $mybackpack->out()), 'noticebox');
        }
        // Search box.
        $searchform = $this->output->container($this->helper_search_form($badges->search));

        // Download all button.
        $actionhtml = $this->output->single_button(
                    new moodle_url('/badges/mybadges.php', array('downloadall' => true, 'sesskey' => sesskey())),
                    get_string('downloadall'), 'POST', array('class' => 'activatebadge'));

        $downloadall = $this->output->container($actionhtml, 'rui-downloadall text-right');
        $downloadall = $this->output->container($downloadall, 'rui-downloadall-wrapper mt-3');

        // Local badges.
        $localhtml = html_writer::start_tag('div', array('id' => 'issued-badge-table', 'class' => 'wrapper-fw mb-5'));
        $sitename = format_string($SITE->fullname, true, array('context' => context_system::instance()));
        $heading = get_string('localbadges', 'badges', $sitename);
        $localhtml .= $this->output->heading_with_help($heading, 'localbadgesh', 'badges','','', 2, $classnames = 'mb-3');
        if ($badges->badges) {
            $countmessage = '<hr /><svg class="mr-2" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.25 8.75L18.25 4.75H5.75L9.75 8.75"></path><circle cx="12" cy="14" r="5.25" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></circle></svg>' . get_string('badgesearned', 'badges', $badges->totalcount) . '<hr />';

            $htmllist = $this->print_badges_list($badges->badges, $USER->id);
            $localhtml .= $backpackconnect . $countmessage . $searchform;
            $localhtml .= $htmlpagingbar . $htmllist . $htmlpagingbar . $downloadall;
        } else {
            $localhtml .= $searchform . $this->output->notification(get_string('nobadges', 'badges'));
        }
        $localhtml .= html_writer::end_tag('div');

        // External badges.
        $externalhtml = "";
        if (!empty($CFG->badges_allowexternalbackpack)) {
            $externalhtml .= html_writer::start_tag('div', array('class' => 'wrapper-fw mt-4'));
            $externalhtml .= $this->output->heading_with_help(get_string('externalbadges', 'badges'), 'externalbadges', 'badges', '', '', 5, 'mb-3');
            if (!is_null($backpack)) {
                if ($backpack->totalcollections == 0) {
                    $externalhtml .= get_string('nobackpackcollectionssummary', 'badges', $backpack);
                } else {
                    if ($backpack->totalbadges == 0) {
                        $externalhtml .= get_string('nobackpackbadgessummary', 'badges', $backpack);
                    } else {
                        $externalhtml .= get_string('backpackbadgessummary', 'badges', $backpack);
                        $externalhtml .= '<br/><br/>' . $this->print_badges_list($backpack->badges, $USER->id, true, true);
                    }
                }
            } else {
                $externalhtml .= get_string('externalconnectto', 'badges', $mybackpack->out());
            }

            $externalhtml .= html_writer::end_tag('div');
            $attr = ['class' => 'btn btn-sm btn-info'];
            $label = get_string('backpackbadgessettings', 'badges');
            $backpacksettings = html_writer::link(new moodle_url('/badges/mybackpack.php'), $label, $attr);
            $actionshtml = $this->output->container($backpacksettings, 'rui-backpacksettings');
            $actionshtml = $this->output->container($actionshtml, 'rui-backpacksettings-wrapper wrapper-fw mt-3');
            $externalhtml .= $actionshtml;
        }

        return $localhtml . $externalhtml;
    }

    /**
     * Render a collection of badges.
     *
     * @param \core_badges\output\badge_collection $badges
     * @return string
     */
    protected function render_badge_collection(\core_badges\output\badge_collection $badges) {
        $output = '';
        foreach ($badges->badges as $badge) {
            $badgeimage = print_badge_image($badge, $this->page->context, 'large');
            $name = $badge->name;
            $description = $badge->description;
            $criteria = self::print_badge_criteria($badge);
            if ($badge->dateissued) {
                $iconCheck = '<svg class="mr-2 width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.75 12C4.75 7.99594 7.99594 4.75 12 4.75V4.75C16.0041 4.75 19.25 7.99594 19.25 12V12C19.25 16.0041 16.0041 19.25 12 19.25V19.25C7.99594 19.25 4.75 16.0041 4.75 12V12Z"></path><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 12.75L10.1837 13.6744C10.5275 14.407 11.5536 14.4492 11.9564 13.7473L14.25 9.75"></path></svg>';
                $criteriaTxt = $iconCheck . get_string('dateearned', 'badges', userdate($badge->dateissued, get_string('strftimedatefullshort', 'core_langconfig')));
                $badgeurl = new moodle_url('/badges/badge.php', array('hash' => $badge->uniquehash));
                $awarded = '<a class="d-inline-flex align-items-center w-100" href="' . $badgeurl . '">' . $criteriaTxt . '</a>';
            } else {
                $awarded = "";
            }

            $output .= '<div class="m-0">';
                $output .= '<div class="rui-badge-overview-wrapper border rounded w-100 p-4 mt-2 mb-2 d-inline-flex align-items-start">';
                    $output .= '<div class="border rounded p-4 mr-6">' . $badgeimage . '</div>';
                    $output .= html_writer::start_tag('div', array('class' => 'rui-badge-overview'));
                        $output .= html_writer::start_tag('h4', array('class' => 'rui-badge-name mt-2'));
                            $output .= $name;
                        $output .= html_writer::end_tag('h4');
                        $output .= html_writer::start_tag('div', array('class' => 'rui-badge-desc mt-3'));
                            $output .= html_writer::start_tag('h5', array('class' => 'd-inline-flex align-items-center w-100'));
                            $output .= get_string('description', 'badges');
                            $output .= html_writer::end_tag('h5');
                            $output .= $description;

                            $output .= html_writer::start_tag('h5', array('class' => 'd-inline-flex align-items-center w-100 mt-4'));
                            $output .= '<svg class="mr-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.75 13.25H6.75L13.25 4.75V10.75H17.25L10.75 19.25V13.25Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>' . get_string('bcriteria', 'badges');
                            $output .= html_writer::end_tag('h5');
                            $output .= '<div class="ml-4">' . $criteria . '</div>';

                            if(!empty($awarded)) {
                                $output .= html_writer::start_tag('h5', array('class' => 'd-inline-flex align-items-center w-100 mt-4'));
                                $output .= '<svg class="mr-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M17.25 10C17.25 12.8995 14.8995 15.25 12 15.25C9.10051 15.25 6.75 12.8995 6.75 10C6.75 7.10051 9.10051 4.75 12 4.75C14.8995 4.75 17.25 7.10051 17.25 10Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M8.75 14.75L7.75 19.25L12 17.75L16.25 19.25L15.25 14.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>' . get_string('awardedtoyou', 'badges');
                                $output .= html_writer::end_tag('h5');
                                $output .= '<div class="ml-4">' . $awarded . '</div>';
                            }

                        $output .= html_writer::end_tag('div');
                    $output .= html_writer::end_tag('div');
                $output .= '</div>';
            $output .= '</div>';
        }

        return $output;
    }

    /**
     * Render a table of badges.
     *
     * @param \core_badges\output\badge_management $badges
     * @return string
     */
    protected function render_badge_management(\core_badges\output\badge_management $badges) {
        // New badge button.
        $htmlnew = '';
        if (has_capability('moodle/badges:createbadge', $this->page->context)) {
            $n['type'] = $this->page->url->get_param('type');
            $n['id'] = $this->page->url->get_param('id');
            $btn = $this->output->single_button(new moodle_url('newbadge.php', $n), get_string('newbadge', 'badges'));
            $htmlnew = $this->output->box($btn);
        }

        $output = '';

        $output .= '<div class="rui-badge-wrapper">';
        foreach ($badges->badges as $b) {
            $style = !$b->is_active() ? 'rui-badge-not-available' : '';
            $badgeimage = print_badge_image($b, $this->page->context, 'large');
            $forlink =  html_writer::start_tag('span') . $b->name . html_writer::end_tag('span');
            $name = html_writer::link(new moodle_url('/badges/overview.php', array('id' => $b->id)), $forlink);

            if (!$b->is_active()) {
                $status = '<span class="badge badge-danger">' . $b->statstring . '</span>';
            } else {
                $status = '<span class="badge badge-success">' . $b->statstring . '</span>';
            }

            $criteria = self::print_badge_criteria($b, 'short');

            $output .= '<div class="rui-badge-box w-100 ' . $style . '">';
                $output .= '<div class="rui-badge-overview-actions d-flex justify-content-between"><div>'. self::print_badge_table_actions($b, $this->page->context) . '</div>' . $status . '</div>';

                $output .= '<div class="d-inline-flex align-items-start w-100">';
                    $output .= '<div class="border rounded p-5 mr-6">' . $badgeimage . '</div>';
                    $output .= html_writer::start_tag('div', array('class' => 'rui-badge-overview'));

                        $output .= html_writer::start_tag('h4', array('class' => 'rui-badge-name mt-2'));
                            $output .= $name;
                        $output .= html_writer::end_tag('h4');
                        $output .= html_writer::start_tag('div', array('class' => 'rui-badge-desc'));

                            $output .= html_writer::start_tag('h5', array('class' => 'd-inline-flex align-items-center w-100 mt-2'));
                            $output .= '<svg class="mr-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.75 13.25H6.75L13.25 4.75V10.75H17.25L10.75 19.25V13.25Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>' . get_string('bcriteria', 'badges');
                            $output .= html_writer::end_tag('h5');
                            $output .= '<div class="ml-4">' . $criteria . '</div>';

                            if (has_capability('moodle/badges:viewawarded', $this->page->context)) {
                                $awards = html_writer::link(new moodle_url('/badges/recipients.php', array('id' => $b->id)), $b->awards);
                                $output .= html_writer::start_tag('h5', array('class' => 'd-inline-flex align-items-center w-100 mt-4'));
                                $output .= '<svg class="mr-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M17.25 10C17.25 12.8995 14.8995 15.25 12 15.25C9.10051 15.25 6.75 12.8995 6.75 10C6.75 7.10051 9.10051 4.75 12 4.75C14.8995 4.75 17.25 7.10051 17.25 10Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M8.75 14.75L7.75 19.25L12 17.75L16.25 19.25L15.25 14.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>' . get_string('awards', 'badges');
                                $output .= html_writer::end_tag('h5');
                                $output .= '<div class="ml-4">' . $awards . '</div>';
                            } else {
                                $output .= html_writer::start_tag('h5', array('class' => 'd-inline-flex align-items-center w-100 mt-4'));
                                $output .= '<svg class="mr-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M17.25 10C17.25 12.8995 14.8995 15.25 12 15.25C9.10051 15.25 6.75 12.8995 6.75 10C6.75 7.10051 9.10051 4.75 12 4.75C14.8995 4.75 17.25 7.10051 17.25 10Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M8.75 14.75L7.75 19.25L12 17.75L16.25 19.25L15.25 14.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>' . get_string('awards', 'badges');
                                $output .= html_writer::end_tag('h5');
                                $output .= '<div class="ml-4">' . $b->awards . '</div>';
                            }

                            $output .= html_writer::end_tag('div');
                        $output .= html_writer::end_tag('div');
                    $output .= '</div>';
                $output .= '</div>';

        }
        $output .= '</div>';
        return $output;

    }

    /**
     * Prints badge status box.
     *
     * @param badge $badge
     * @return Either the status box html as a string or null
     */
    public function print_badge_status_box(badge $badge) {
        if (has_capability('moodle/badges:configurecriteria', $badge->get_context())) {

            if (!$badge->has_criteria()) {
                $criteriaurl = new moodle_url('/badges/criteria.php', array('id' => $badge->id));
                $status = get_string('nocriteria', 'badges');
                if ($this->page->url != $criteriaurl) {
                    $action = $this->output->single_button(
                        $criteriaurl,
                        get_string('addcriteria', 'badges'), 'POST', array('class' => 'activatebadge'));
                } else {
                    $action = '';
                }

                $message = '<p class="py-3 m-0">' . $status . $action . '</p>';
            } else {
                $status = get_string('statusmessage_' . $badge->status, 'badges');
                if ($badge->is_active()) {
                    $action = $this->output->single_button(new moodle_url('/badges/action.php',
                                array('id' => $badge->id, 'lock' => 1, 'sesskey' => sesskey(),
                                      'return' => $this->page->url->out_as_local_url(false))),
                            get_string('deactivate', 'badges'), 'POST', array('class' => 'activatebadge'));
                } else {
                    $action = $this->output->single_button(new moodle_url('/badges/action.php',
                                array('id' => $badge->id, 'activate' => 1, 'sesskey' => sesskey(),
                                      'return' => $this->page->url->out_as_local_url(false))),
                            get_string('activate', 'badges'), 'POST', array('class' => 'activatebadge'));
                }

                $message = '<p class="py-3 m-0">' . $status . $this->output->help_icon('status', 'badges') . $action . '</p>';

            }

            $style = $badge->is_active() ? 'wrapper-fw d-flex align-items-center justify-content-between rui-badge-alert rui-statusbox active' : 'wrapper-fw d-flex align-items-center justify-content-between rui-badge-alert rui-statusbox inactive';
            return $this->output->container($message, $style);
        }

        return null;
    }

    /**
     * Outputs list en badges.
     *
     * @param badge $badge Badge object.
     * @return string $output content endorsement to output.
     */
    protected function print_badge_endorsement(badge $badge) {
        $output = '';
        $endorsement = $badge->get_endorsement();
        $dl = array();

        $output .= html_writer::start_tag('h5', array('class' => 'd-inline-flex align-items-center w-100'));
            $output .= '<svg class="mr-2" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 13V15"></path><circle cx="12" cy="9" r="1" fill="currentColor"></circle><circle cx="12" cy="12" r="7.25" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></circle></svg>' . get_string('endorsement', 'badges');
        $output .= html_writer::end_tag('h5');

        if (!empty($endorsement)) {
            $output .= html_writer::start_tag('div', array('class' => 'ml-4'));

                if (!empty(userdate($endorsement->dateissued))) {
                    $output .= html_writer::start_tag('span', array('class' => 'badge badge-light'));
                        $output .= '<span class="mr-1 font-weight-bold">' . get_string('dateawarded', 'badges') . ': </span>' . userdate($endorsement->dateissued);
                    $output .= html_writer::end_tag('span');
                }

                $output .= html_writer::start_tag('div', array('class' => 'rui-badge-comment w-100 rounded p-4 my-2'));
                    $output .= '<label>' . get_string('claimcomment', 'badges') . '</label>';
                    $output .= html_writer::start_tag('p', array('class' => 'd-inline-flex align-items-center w-100 mb-0'));
                        $output .= $endorsement->claimcomment;
                    $output .= html_writer::end_tag('p');
                $output .= html_writer::end_tag('div');

                if (!empty($endorsement->issuername) || !empty($endorsement->issueremail)) {
                    $output .= html_writer::start_tag('span', array('class' => 'alert alert-secondary d-block'));
                        $output .=  $endorsement->issuername . html_writer::tag('a', $endorsement->issueremail, array('class' => 'ml-3'), array('href' => 'mailto:' . $endorsement->issueremail))
                                    . html_writer::link($endorsement->issuerurl, $endorsement->issuerurl, array('class' => 'ml-3'), array('target' => '_blank'));
                    $output .= html_writer::end_tag('span');
                }

            $output .= html_writer::end_tag('div');

        } else {
            $output .= html_writer::start_tag('div', array('class' => 'ml-4'));
                $output .= get_string('noendorsement', 'badges');
            $output .= html_writer::end_tag('div');
        }
        return $output;
    }

    /**
     * Print list badges related.
     *
     * @param badge $badge Badge objects.
     * @return string $output List related badges to output.
     */
    protected function print_badge_related(badge $badge) {
        $output = '';
        $relatedbadges = $badge->get_related_badges();
        $output .= html_writer::start_tag('h5', array('class' => 'd-inline-flex align-items-center w-100'));
        $output .= '<svg class="mr-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M17.25 10C17.25 12.8995 14.8995 15.25 12 15.25C9.10051 15.25 6.75 12.8995 6.75 10C6.75 7.10051 9.10051 4.75 12 4.75C14.8995 4.75 17.25 7.10051 17.25 10Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M8.75 14.75L7.75 19.25L12 17.75L16.25 19.25L15.25 14.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>' . get_string('relatedbages', 'badges');
        $output .= html_writer::end_tag('h5');
        if (!empty($relatedbadges)) {
            $items = array();
            foreach ($relatedbadges as $related) {
                $relatedurl = new moodle_url('/badges/overview.php', array('id' => $related->id));
                $items[] = html_writer::link($relatedurl->out(), $related->name, array('target' => '_blank'));
            }
            $output .= html_writer::alist($items, array(), 'ul');
        } else {
            $output .= html_writer::start_tag('div', array('class' => 'ml-4'));
                $output .= get_string('norelated', 'badges');
            $output .= html_writer::end_tag('div');
        }
        return $output;
    }

    /**
     * Print list badge alignments.
     *
     * @param badge $badge Badge objects.
     * @return string $output List alignments to output.
     */
    protected function print_badge_alignments(badge $badge) {
        $output = '';

        $output .= html_writer::start_tag('h5', array('class' => 'd-inline-flex align-items-center w-100'));
        $output .= '<svg class="mr-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.75 5.75H16.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M7.75 18.25H16.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M4.75 12H19.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>' . get_string('alignment', 'badges');
        $output .= html_writer::end_tag('h5');

        $alignments = $badge->get_alignments();
        if (!empty($alignments)) {
            $items = array();
            foreach ($alignments as $alignment) {
                $urlaligment = new moodle_url('alignment.php',
                    array('id' => $badge->id, 'alignmentid' => $alignment->id)
                );
                $items[] = html_writer::link($urlaligment, $alignment->targetname, array('target' => '_blank'));
            }
            $output .= html_writer::alist($items, array('class' => 'ml-4'), 'ul');
        } else {
            $output .= html_writer::start_tag('div', array('class' => 'ml-4'));
                $output .= get_string('noalignment', 'badges');
            $output .= html_writer::end_tag('div');
        }
        return $output;
    }

    // Prints a badge overview infomation.
    public function print_badge_overview($badge, $context) {
        $languages = get_string_manager()->get_list_of_languages();

        $output = '';
        $output .= html_writer::start_tag('div', array('class' => 'mt-4'));

            $output .= html_writer::start_tag('div', array('class' => 'rui-badge-overview-wrapper border rounded w-100 p-6 mt-2 mb-2 d-inline-flex align-items-start'));
                $output .= '<div class="border rounded p-4 mr-6 mt-6">' . print_badge_image($badge, $context, 'large') . '</div>';

                $output .= html_writer::start_tag('div', array('class' => 'rui-badge-overview'));
                    // Badge details.

                    if (!empty($badge->version)) {
                        $output .= html_writer::start_tag('span', array('class' => 'badge badge-secondary'));
                            $output .= '<span class="mr-1 font-weight-bold">' . get_string('version', 'badges') . ': </span>' . $badge->version;
                        $output .= html_writer::end_tag('span');
                    }

                    if (!empty($languages[$badge->language])) {
                        $output .= html_writer::start_tag('span', array('class' => 'badge badge-secondary'));
                            $output .= '<span class="mr-1 font-weight-bold">' . get_string('language') . ': </span>' . $languages[$badge->language];
                        $output .= html_writer::end_tag('span');
                    }

                    if (!empty(userdate($badge->timecreated))) {
                        $output .= html_writer::start_tag('span', array('class' => 'badge badge-info'));
                            $output .= '<span class="mr-1 font-weight-bold">' . get_string('createdon', 'search') . ': </span>' . userdate($badge->timecreated);
                        $output .= html_writer::end_tag('span');
                    }

                    $output .= html_writer::start_tag('h4', array('class' => 'rui-badge-name'));
                    $output .= $badge->name;
                    $output .= html_writer::end_tag('h4');


                    // Issuance details if any.
                    if ($badge->can_expire()) {
                        if ($badge->expiredate) {
                            $output .= html_writer::start_tag('span', array('class' => 'rui-badge-expires-info'));
                                $output .= '<svg class="mr-2" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.9522 16.3536L10.2152 5.85658C10.9531 4.38481 13.0539 4.3852 13.7913 5.85723L19.0495 16.3543C19.7156 17.6841 18.7487 19.25 17.2613 19.25H6.74007C5.25234 19.25 4.2854 17.6835 4.9522 16.3536Z"></path><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10V12"></path><circle cx="12" cy="16" r="1" fill="currentColor"></circle></svg>' . get_string('expiredate', 'badges', userdate($badge->expiredate));
                            $output .= html_writer::end_tag('span');
                        } else if ($badge->expireperiod) {
                            if ($badge->expireperiod < 60) {
                                $output .= html_writer::start_tag('span', array('class' => 'rui-badge-expires-info'));
                                    $output .= '<svg class="mr-2" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.9522 16.3536L10.2152 5.85658C10.9531 4.38481 13.0539 4.3852 13.7913 5.85723L19.0495 16.3543C19.7156 17.6841 18.7487 19.25 17.2613 19.25H6.74007C5.25234 19.25 4.2854 17.6835 4.9522 16.3536Z"></path><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10V12"></path><circle cx="12" cy="16" r="1" fill="currentColor"></circle></svg>' . get_string('expireperiods', 'badges', round($badge->expireperiod, 2)) ;
                                $output .= html_writer::end_tag('span');
                            } else if ($badge->expireperiod < 60 * 60) {
                                $output .= html_writer::start_tag('span', array('class' => 'rui-badge-expires-info'));
                                    $output .= '<svg class="mr-2" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.9522 16.3536L10.2152 5.85658C10.9531 4.38481 13.0539 4.3852 13.7913 5.85723L19.0495 16.3543C19.7156 17.6841 18.7487 19.25 17.2613 19.25H6.74007C5.25234 19.25 4.2854 17.6835 4.9522 16.3536Z"></path><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10V12"></path><circle cx="12" cy="16" r="1" fill="currentColor"></circle></svg>' . get_string('expireperiodm', 'badges', round($badge->expireperiod / 60, 2));
                                $output .= html_writer::end_tag('span');
                            } else if ($badge->expireperiod < 60 * 60 * 24) {
                                $output .= html_writer::start_tag('span', array('class' => 'rui-badge-expires-info'));
                                    $output .= '<svg class="mr-2" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.9522 16.3536L10.2152 5.85658C10.9531 4.38481 13.0539 4.3852 13.7913 5.85723L19.0495 16.3543C19.7156 17.6841 18.7487 19.25 17.2613 19.25H6.74007C5.25234 19.25 4.2854 17.6835 4.9522 16.3536Z"></path><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10V12"></path><circle cx="12" cy="16" r="1" fill="currentColor"></circle></svg>' . get_string('expireperiodh', 'badges', round($badge->expireperiod / 60 / 60, 2));
                                $output .= html_writer::end_tag('span');
                            } else {
                                $output .= html_writer::start_tag('span', array('class' => 'badge badge-danger'));
                                    $output .= '<svg class="mr-2" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.9522 16.3536L10.2152 5.85658C10.9531 4.38481 13.0539 4.3852 13.7913 5.85723L19.0495 16.3543C19.7156 17.6841 18.7487 19.25 17.2613 19.25H6.74007C5.25234 19.25 4.2854 17.6835 4.9522 16.3536Z"></path><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10V12"></path><circle cx="12" cy="16" r="1" fill="currentColor"></circle></svg>' . get_string('expireperiod', 'badges', round($badge->expireperiod / 60 / 60 * 0.54, 2));
                                $output .= html_writer::end_tag('span');
                            }
                        }
                    } else {
                        $output .= html_writer::start_tag('span', array('class' => 'badge badge-success'));
                            $output .= '<span class="mr-1 font-weight-bold">' . get_string('noexpiry', 'badges') . '</span>';
                        $output .= html_writer::end_tag('span');
                    }


                    $output .= html_writer::start_tag('div', array('class' => 'rui-badge-desc mt-3'));

                        $output .= html_writer::start_tag('h5', array('class' => 'd-inline-flex align-items-center w-100'));
                        $output .= get_string('description', 'badges');
                        $output .= html_writer::end_tag('h5');

                        $output .= $badge->description;

                        $output .= html_writer::start_tag('ul', array('class' => 'rui-badge-desc-list mt-3 ml-3'));

                            if (!empty(userdate($badge->imageauthorname))) {
                                $output .= html_writer::start_tag('li');
                                    $output .= '<span class="mr-1 font-weight-bold">' . get_string('imageauthorname', 'badges') . ': </span>' . $badge->imageauthorname;
                                $output .= html_writer::end_tag('li');
                            }

                            if (!empty(userdate($badge->imageauthoremail))) {
                                $output .= html_writer::start_tag('li');
                                    $output .= '<span class="mr-1 font-weight-bold">' . get_string('imageauthoremail', 'badges') . ': </span>' . html_writer::tag('a', $badge->imageauthoremail, array('href' => 'mailto:' . $badge->imageauthoremail));
                                $output .= html_writer::end_tag('li');
                            }
                            if (!empty(userdate($badge->imageauthorurl))) {
                                $output .= html_writer::start_tag('li');
                                    $output .= '<span class="mr-1 font-weight-bold">' . get_string('imageauthorurl', 'badges') . ': </span>' . html_writer::link($badge->imageauthorurl, $badge->imageauthorurl, array('target' => '_blank'));
                                $output .= html_writer::end_tag('li');
                            }
                            if (!empty($badge->imagecaption)) {
                                $output .= html_writer::start_tag('li');
                                    $output .= '<span class="mr-1 font-weight-bold">' . get_string('imagecaption', 'badges') . ': </span>' . $badge->imagecaption;
                                $output .= html_writer::end_tag('li');
                            }
                        $output .= html_writer::end_tag('div');


                    $output .= html_writer::end_tag('div'); // rui-badge-desc
                $output .= html_writer::end_tag('div'); // .rui-badge-overview

                if (!empty($badge->issuername)|| !empty($badge->issuercontact)) {
                    $output .= html_writer::start_tag('span', array('class' => 'alert alert-secondary d-block'));
                        $output .=  '<span class="mr-1 font-weight-bold">' . get_string('issuername', 'badges') . ': </span>' . $badge->issuername;

                        if (!empty($badge->issuername)|| !empty($badge->issuercontact)) {
                            $output .=  html_writer::tag('a', $badge->issuercontact, array('href' => 'mailto:' . $badge->issuercontact));
                        }

                    $output .= html_writer::end_tag('span');
                }


                    $output .= html_writer::start_tag('div', array('class' => 'wrapper-fw'));

                        // Criteria details if any.
                        $output .= html_writer::start_tag('div', array('class' => 'rui-badge-criteria my-4'));

                            $output .= html_writer::start_tag('h5', array('class' => 'd-inline-flex align-items-center w-100'));
                            $output .= '<svg class="mr-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.75 13.25H6.75L13.25 4.75V10.75H17.25L10.75 19.25V13.25Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>' . get_string('bcriteria', 'badges');
                            $output .= html_writer::end_tag('h5');

                            if ($badge->has_criteria()) {
                                $output .= '<div class="ml-4">' . self::print_badge_criteria($badge) . '</div>';
                            } else {
                                $output .= '<p class="ml-4">' . get_string('nocriteria', 'badges') . '</p>';
                                if (has_capability('moodle/badges:configurecriteria', $context)) {
                                    $output .= $this->output->single_button(
                                        new moodle_url('/badges/criteria.php', array('id' => $badge->id)),
                                        get_string('addcriteria', 'badges'), 'POST', array('class' => 'activatebadge ml-4'));
                                }
                            }
                        $output .= html_writer::end_tag('div');

                        // Awards details if any.
                        $output .= html_writer::start_tag('div', array('class' => 'rui-badge-awards my-4'));
                            if (has_capability('moodle/badges:viewawarded', $context)) {
                                $output .= html_writer::start_tag('h5', array('class' => 'd-inline-flex align-items-center w-100'));
                                $output .= '<svg class="mr-2" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5.78168 19.25H13.2183C13.7828 19.25 14.227 18.7817 14.1145 18.2285C13.804 16.7012 12.7897 14 9.5 14C6.21031 14 5.19605 16.7012 4.88549 18.2285C4.773 18.7817 5.21718 19.25 5.78168 19.25Z"></path><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 14C17.8288 14 18.6802 16.1479 19.0239 17.696C19.2095 18.532 18.5333 19.25 17.6769 19.25H16.75"></path><circle cx="9.5" cy="7.5" r="2.75" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></circle><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.75 10.25C16.2688 10.25 17.25 9.01878 17.25 7.5C17.25 5.98122 16.2688 4.75 14.75 4.75"></path></svg>' . get_string('awards', 'badges');
                                $output .= html_writer::end_tag('h5');

                                if ($badge->has_awards()) {
                                    $url = new moodle_url('/badges/recipients.php', array('id' => $badge->id));
                                    $a = new stdClass();
                                    $a->link = $url->out();
                                    $a->count = count($badge->get_awards());
                                    $output .= get_string('numawards', 'badges', $a);
                                } else {
                                    $output .= html_writer::start_tag('div', array('class' => 'ml-4'));
                                        $output .= get_string('noawards', 'badges');
                                    $output .= html_writer::end_tag('div');
                                }

                                if (has_capability('moodle/badges:awardbadge', $context) &&
                                    $badge->has_manual_award_criteria() &&
                                    $badge->is_active()) {
                                        $output .= html_writer::start_tag('div', array('class' => 'mt-3 ml-4'));
                                            $output .= $this->output->single_button(
                                            new moodle_url('/badges/award.php', array('id' => $badge->id)),
                                            get_string('award', 'badges'), 'POST', array('class' => 'activatebadge'));
                                        $output .= html_writer::end_tag('div');
                                }
                            }
                        $output .= html_writer::end_tag('div');

                        $output .= html_writer::start_tag('div', array('class' => 'rui-badge-endorsement my-4'));
                            $output .= self::print_badge_endorsement($badge);
                        $output .= html_writer::end_tag('div');

                        $output .= html_writer::start_tag('div', array('class' => 'rui-badge-related my-4'));
                            $output .= self::print_badge_related($badge);
                        $output .= html_writer::end_tag('div');

                        $output .= html_writer::start_tag('div', array('class' => 'rui-badge-alignments my-4'));
                            $output .= self::print_badge_alignments($badge);
                        $output .= html_writer::end_tag('div');

                    $output .= html_writer::end_tag('div'); // .wrapper-fw

            $output .= html_writer::end_tag('div'); // .badge-ovrview-wrapper

        $output .= html_writer::end_tag('div');
        return $output;

        //return html_writer::div($display, null, array('id' => 'badge-overview', 'class' => 'rui-badge-overview'));
    }

    /**
     * Renders a search form
     *
     * @param string $search Search string
     * @return string HTML
     */
    protected function helper_search_form($search) {
        global $CFG;
        require_once($CFG->libdir . '/formslib.php');

        $mform = new MoodleQuickForm('searchform', 'POST', $this->page->url);

        $mform->addElement('hidden', 'sesskey', sesskey());

        $el[] = $mform->createElement('text', 'search', get_string('search'), array('size' => 30));
        $mform->setDefault('search', $search);
        $el[] = $mform->createElement('submit', 'submitsearch', get_string('search'));
        $el[] = $mform->createElement('submit', 'clearsearch', get_string('clear'));
        $mform->addGroup($el, 'searchgroup', ' ', ' ', false);

        ob_start();
        $mform->display();
        $out = ob_get_clean();

        return $out;
    }

}