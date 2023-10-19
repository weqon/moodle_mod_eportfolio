<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Prints an instance of mod_eportfolio.
 *
 * @package     mod_eportfolio
 * @copyright   2023 weQon UG <support@weqon.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once('locallib.php');
require_once('lib.php');
require_once('grade_form.php');
require_once($CFG->dirroot . "/local/eportfolio/locallib.php");

// Course module id.
$id = optional_param('id', 0, PARAM_INT);

// Activity instance id.
$e = optional_param('e', 0, PARAM_INT);

$action = optional_param('action', 0, PARAM_ALPHA);
$fileid = optional_param('fileid', 0, PARAM_INT);
$userid = optional_param('userid', 0, PARAM_INT);

// We need this in case an ePortfolio will be deleted.
$confirm = optional_param('confirm', '', PARAM_ALPHANUM);

if ($id) {
    $cm = get_coursemodule_from_id('eportfolio', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('eportfolio', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    $moduleinstance = $DB->get_record('eportfolio', array('id' => $e), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('eportfolio', $moduleinstance->id, $course->id, false, MUST_EXIST);
}

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

$url = new moodle_url('/mod/eportfolio/view.php', array('id' => $cm->id));

$event = \mod_eportfolio\event\course_module_viewed::create(array(
        'objectid' => $moduleinstance->id,
        'context' => $modulecontext
));
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('eportfolio', $moduleinstance);
$event->trigger();

$PAGE->set_url($url);
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

echo $OUTPUT->header();

// Check if this course is marked as eportfolio course.
if (check_current_eportfolio_course($course->id)) {
    // Get all shared ePortfolios for this course.
    // ToDo: Check for specifc roles, groups or users.
    // Own Function in localllib.
    // Mustache Template.
    // Icon Table -> Grading - if activity is set.

    $coursecontext = context_course::instance($course->id);
    if ($action === 'grade' && has_capability('mod/eportfolio:grade_eport', $coursecontext)) {

        if (!$fileid) {
            die('no file found');
        }
        if (!$userid) {
            die('no user found');
        }

        // Check, if a grade exists.
        $gradeexists = $DB->get_record('eportfolio_grade',
                ['userid' => $userid, 'itemid' => $fileid, 'cmid' => $cm->id]);

        if ($gradeexists) {

            $setdata = array(
                    'grade' => $gradeexists->grade,
                    'feedbacktext' => $gradeexists->feedbacktext,
            );

        }

        $customdata = array(
                'userid' => $userid,
                'cmid' => $cm->id,
                'fileid' => $fileid,
                'courseid' => $course->id,
        );

        $gradeurl = new moodle_url('/mod/eportfolio/view.php', array('id' => $cm->id, 'action' => 'grade'));

        $mform = new grade_form($gradeurl, $customdata);
        $mform->set_data($setdata);

        if ($formdata = $mform->is_cancelled()) {

            redirect(new moodle_url('/mod/eportfolio/view.php', ['id' => $cm->id]));

        } else if ($formdata = $mform->get_data()) {

            // Get activity instance id from table eportfolio.
            $instanceid = $DB->get_record('eportfolio', ['course' => $course->id]);

            $data = new stdClass();

            $data->userid = $formdata->userid;
            $data->cmid = $formdata->cmid;
            $data->itemid = $formdata->fileid;
            $data->courseid = $formdata->courseid;
            $data->graderid = $USER->id;
            $data->instance = $instanceid->id;
            $data->grade = $formdata->grade;
            $data->feedbacktext = $formdata->feedbacktext;

            // Send message to inform user about new or updated grade.
            $fs = get_file_storage();
            $file = $fs->get_file_by_id($data->itemid);

            $message = eportfolio_send_grading_message($data->courseid, $data->graderid, $data->userid,
                    $file->get_filename(), $data->itemid, $data->cmid);

            if ($gradeexists) {

                $data->id = $gradeexists->id;
                $data->timemodified = time();

                if ($DB->update_record('eportfolio_grade', $data)) {

                    redirect(new moodle_url('/mod/eportfolio/view.php', ['id' => $cm->id]),
                            get_string('grade:update:success', 'mod_eportfolio'),
                            null, \core\output\notification::NOTIFY_SUCCESS);

                } else {

                    redirect(new moodle_url('/mod/eportfolio/view.php', ['id' => $cm->id]),
                            get_string('grade:update:error', 'mod_eportfolio'),
                            null, \core\output\notification::NOTIFY_ERROR);

                }

            } else {

                $data->timecreated = time();

                if ($DB->insert_record('eportfolio_grade', $data)) {

                    redirect(new moodle_url('/mod/eportfolio/view.php', ['id' => $cm->id]),
                            get_string('grade:insert:success', 'mod_eportfolio'),
                            null, \core\output\notification::NOTIFY_SUCCESS);

                } else {

                    redirect(new moodle_url('/mod/eportfolio/view.php', ['id' => $cm->id]),
                            get_string('grade:insert:error', 'mod_eportfolio'),
                            null, \core\output\notification::NOTIFY_ERROR);

                }

            }

        } else {

            // Convert display options to a valid object.
            $factory = new \core_h5p\factory();
            $core = $factory->get_core();
            $config = core_h5p\helper::decode_display_options($core, $modulecontext->displayoptions);

            $fs = get_file_storage();
            $file = $fs->get_file_by_id($fileid);

            $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(),
                    $file->get_filearea(), $file->get_itemid(), $file->get_filepath(),
                    $file->get_filename(), false);

            // Get the real times for created and modified.
            $pathnamehash = $file->get_pathnamehash();
            $h5pfile = $DB->get_record('h5p', ['pathnamehash' => $pathnamehash]);

            // Get the user who shared the ePortfolio for grading.
            $user = $DB->get_record('user', ['id' => $userid]);

            $data = new stdClass();

            $data->userfullname = fullname($user);
            $data->timecreated = date('d.m.Y', $h5pfile->timecreated);
            $data->timemodified = date('d.m.Y', $h5pfile->timemodified);
            $data->h5pplayer = \core_h5p\player::display($fileurl, $config, false, 'local_eportfolio', false);
            $data->gradeform = $mform->render();

            echo $OUTPUT->render_from_template('mod_eportfolio/eportfolio_grading', $data);

        }

    } else if ($action == 'view') {

        // Convert display options to a valid object.
        $factory = new \core_h5p\factory();
        $core = $factory->get_core();
        $config = core_h5p\helper::decode_display_options($core, $modulecontext->displayoptions);

        $fs = get_file_storage();
        $file = $fs->get_file_by_id($fileid);

        $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(),
                $file->get_filearea(), $file->get_itemid(), $file->get_filepath(),
                $file->get_filename(), false);

        // Get the real times for created and modified.
        $pathnamehash = $file->get_pathnamehash();
        $h5pfile = $DB->get_record('h5p', ['pathnamehash' => $pathnamehash]);

        // Get the user who shared the ePortfolio for grading.
        $user = $DB->get_record('user', ['id' => $userid]);
        $grade = $DB->get_record('eportfolio_grade', ['cmid' => $cm->id, 'itemid' => $fileid]);

        $data = new stdClass();

        $data->userfullname = fullname($user);
        $data->timecreated = date('d.m.Y', $h5pfile->timecreated);
        $data->timemodified = date('d.m.Y', $h5pfile->timemodified);
        $data->h5pplayer = \core_h5p\player::display($fileurl, $config, false, 'local_eportfolio', false);

        $grader = $DB->get_record('user', ['id' => $grade->graderid]);

        $data->grade = $grade->grade . ' %';
        $data->gradetext = s($grade->feedbacktext);
        $data->grader = fullname($grader);

        echo $OUTPUT->render_from_template('mod_eportfolio/eportfolio_view', $data);

    } else if ($action == 'delete') {

        if ($confirm != md5($fileid)) {

            // Get filename.
            $fs = get_file_storage();
            $file = $fs->get_file_by_id($fileid);
            $pathnamehash = $file->get_pathnamehash();

            $filename = get_h5p_title($pathnamehash);

            // Get username.
            $user = $DB->get_record('user', ['id' => $userid]);

            $userfullname = fullname($user);

            $optionsyes = array(
                    'id' => $id,
                    'fileid' => $fileid,
                    'action' => 'delete',
                    'delete' => $fileid,
                    'userid' => $userid,
                    'confirm' => md5($fileid),
            );

            echo $OUTPUT->heading(get_string('delete:header', 'mod_eportfolio'));

            $deleteurl = new moodle_url('view.php', $optionsyes);
            $deletebutton = new single_button($deleteurl,
                    get_string('delete:confirm', 'mod_eportfolio'), 'post');

            $stringparams = array(
                    'filename' => $filename,
                    'username' => $userfullname,
            );

            echo $OUTPUT->confirm(get_string('delete:checkconfirm', 'mod_eportfolio', $stringparams), $deletebutton, $deleteurl);
            echo $OUTPUT->footer();
            die;

        } else if (data_submitted()) {

            $data = data_submitted();

            // Get file storage for further processing.
            $fs = get_file_storage();
            $file = $fs->get_file_by_id($data->fileid);

            // Get entry from local_eportfolio_share.
            $eportfolioshare = $DB->get_record('local_eportfolio_share', ['fileidcontext' => $data->fileid,
                    'shareoption' => 'grade', 'userid' => $data->userid]);

            // Delete the entry in eportfolio_share table.
            $DB->delete_records('local_eportfolio_share', ['id' => $eportfolioshare->id]);

            // We use the pathnamehash to get the H5P file
            $pathnamehash = $file->get_pathnamehash();

            $h5pfile = $DB->get_record('h5p', ['pathnamehash' => $pathnamehash]);

            // If H5P, delete it from the H5P table as well.
            if ($h5pfile) {

                $DB->delete_records('h5p', ['id' => $h5pfile->id]);
                // Also delete from files where context = 1, itemid = h5p id component core_h5p, filearea content
                $fs->delete_area_files('1', 'core_h5p', 'content', $h5pfile->id);

            }

            if ($file->delete()) {

                // Trigger event for withdrawing sharing of ePortfolio.
                \local_eportfolio\event\eportfolio_deleted::create([
                        'other' => [
                                'description' => get_string('event:eportfolio:deleted', 'mod_eportfolio',
                                        array('userid' => $USER->id, 'filename' => $file->get_filename(),
                                                'itemid' => $file->get_id())),
                        ],
                ])->trigger();

                redirect(new moodle_url('/mod/eportfolio/view.php', ['id' => $id]),
                        get_string('delete:success', 'mod_eportfolio'),
                        null, \core\output\notification::NOTIFY_SUCCESS);

            }

        } else {

            redirect(new moodle_url('/mod/eportfolio/view.php', ['id' => $id]),
                    get_string('delete:error', 'local_eportfolio'),
                    null, \core\output\notification::NOTIFY_ERROR);
        }
    } else {
        // Generate table with all eportfolios shared for grading for this course.
        eportfolio_render_overview_table($course->id, $cm->id, $url);

    }

} else {
    echo "Dieser Kurs ist kein ePortfolio Kurs!";
}

echo $OUTPUT->footer();
