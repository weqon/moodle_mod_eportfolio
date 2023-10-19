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
 * Locallib eportfolio
 *
 * @package mod_eportfolio
 * @copyright 2023 weQon UG {@link https://weqon.net}
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/eportfolio/locallib.php');
require_once($CFG->libdir . '/tablelib.php');

function check_current_eportfolio_course($courseid) {
    global $DB, $USER;

    // Get the field id to identify the custm field data.
    $customfield = $DB->get_record('customfield_field', ['shortname' => 'eportfolio_course']);

    // Get the value for custom field id.
    $customfielddata = $DB->get_records('customfield_data', ['fieldid' => $customfield->id]);

    foreach ($customfielddata as $cd) {

        // True, if eportfolio course.
        if ($cd->value == '1') {

            if ($cd->instanceid === $courseid) {
                return true;
            }
        }
    }

    return false;

}

function eportfolio_render_overview_table($courseid, $cmid, $url) {
    global $DB, $USER;

    $coursecontext = context_course::instance($courseid);
    $coursemodulecontext = context_module::instance($cmid);

    $actionsallowed = false;

    // First we have to check, if current user is editingteacher.
    if (is_enrolled($coursecontext, $USER, 'mod/eportfolio:grade_eport')) {

        $actionsallowed = true;

        $entry = get_shared_eportfolios('grade', $courseid);

    } else {
        $entry = get_my_shared_eportfolios($coursemodulecontext, 'grade', $courseid);
    }

    // View all ePortfolios shared for grading.
    if (!empty($entry)) {

        // Create overview table.
        $table = new flexible_table('eportfolio:overview');
        $table->define_columns(array(
                'filename',
                'filetimemodified',
                'userfullname',
                'sharestart',
                'grade',
                'actions',
        ));
        $table->define_headers(array(
                get_string('overview:table:filename', 'local_eportfolio'),
                get_string('overview:table:filetimemodified', 'local_eportfolio'),
                get_string('overview:table:sharedby', 'local_eportfolio'),
                get_string('overview:table:sharestart', 'local_eportfolio'),
                get_string('overview:table:grading', 'local_eportfolio'),
                get_string('overview:table:actions', 'local_eportfolio'),
        ));
        $table->define_baseurl($url);
        $table->set_attribute('class', 'table-hover');
        $table->sortable(true, 'fullusername', SORT_ASC);
        $table->initialbars(true);
        $table->no_sorting('actions');
        $table->setup();

        foreach ($entry as $ent) {

            // ToDo: Get existing grades/feedback.
            $getgrade = $DB->get_record('eportfolio_grade', ['courseid' => $courseid, 'cmid' => $cmid,
                    'userid' => $ent['userid'], 'itemid' => $ent['fileitemid']]);

            $grade = './';

            if ($USER->id === $ent['userid'] || has_capability('mod/eportfolio:grade_eport', $coursecontext)) {
                $grade = ($getgrade->grade) ? $getgrade->grade . ' %' : './.';
            }

            if ($actionsallowed) {

                // Add grade button for teacher.
                $actionbtn = html_writer::link(new moodle_url('/mod/eportfolio/view.php',
                        array('id' => $cmid, 'fileid' => $ent['fileitemid'], 'userid' => $ent['userid'],
                                'action' => 'grade')), get_string('overview:table:btn:grade', 'mod_eportfolio'),
                        array('class' => 'btn btn-primary'));

                $deletebtn .= html_writer::link(new moodle_url('/mod/eportfolio/view.php',
                        array('id' => $cmid, 'fileid' => $ent['fileitemid'], 'userid' => $ent['userid'],
                                'action' => 'delete')), '', array('class' => 'btn btn-danger fa fa-trash ml-3',
                        'title' => get_string('overview:table:actions:delete', 'local_eportfolio')));

            } else {

                // Add view button for students.
                $actionbtn = html_writer::link(new moodle_url('/mod/eportfolio/view.php',
                        array('id' => $cmid, 'fileid' => $ent['fileitemid'], 'userid' => $ent['userid'],
                                'action' => 'view')), get_string('overview:table:btn:view', 'mod_eportfolio'),
                        array('class' => 'btn btn-primary'));
            }

            $table->add_data(
                    array(
                            $ent['filename'],
                            $ent['filetimemodified'],
                            $ent['userfullname'],
                            $ent['sharestart'],
                            $grade,
                            $actionbtn . $deletebtn,
                    )
            );
        }

        $table->finish_html();

    } else {

        echo html_writer::start_tag('p', array('class' => 'alert alert-info'));
        echo html_writer::tag('i', '', array('class' => 'fa fa-info-circle mr-1'));
        echo "Aktuell liegen keine ePortfolios vor!";
        echo html_writer::end_tag('p');

    }

}

function eportfolio_send_grading_message($courseid, $userfrom, $userto, $filename, $itemid, $cmid) {
    global $DB;

    $contexturl = new moodle_url('/mod/eportfolio/view.php', array('id' => $cmid));

    // Holds values for the string for the email message.
    $a = new stdClass;

    $userfromdata = $DB->get_record('user', ['id' => $userfrom]);
    $a->userfrom = fullname($userfromdata);
    $a->filename = $filename;
    $a->viewurl = (string) $contexturl;
    
    $course = $DB->get_record('course', ['id' => $courseid]);
    $a->coursename = $course->fullname;

    // Fetch message HTML and plain text formats
    $messagehtml = get_string('message:emailmessage', 'mod_eportfolio', $a);
    $plaintext = format_text_email($messagehtml, FORMAT_HTML);

    $smallmessage = get_string('message:smallmessage', 'mod_eportfolio', $a);
    $smallmessage = format_text_email($smallmessage, FORMAT_HTML);

    // Subject
    $subject = get_string('message:subject', 'mod_eportfolio');

    $message = new \core\message\message();

    $message->courseid = $courseid;
    $message->component = 'mod_eportfolio'; // Your plugin's name
    $message->name = 'grading'; // Your notification name from message.php

    $message->userfrom = core_user::get_noreply_user();

    $usertodata = $DB->get_record('user', ['id' => $userto]);
    $message->userto = $usertodata;

    $message->subject = $subject;
    $message->smallmessage = $smallmessage;
    $message->fullmessage = $plaintext;
    $message->fullmessageformat = FORMAT_PLAIN;
    $message->fullmessagehtml = $messagehtml;
    $message->notification = 1; // Because this is a notification generated from Moodle, not a user-to-user message.
    $message->contexturl = $contexturl->out(false);
    $message->contexturlname = get_string('message:contexturlname', 'mod_eportfolio');

    // Finally send the message
    message_send($message);

}