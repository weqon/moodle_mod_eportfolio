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
 * Library of interface functions and constants.
 *
 * @package     mod_eportfolio
 * @copyright   2023 weQon UG <support@weqon.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function eportfolio_supports($feature) {
    switch ($feature) {
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_MOD_INTRO:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the mod_eportfolio into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $moduleinstance An object from the form.
 * @param mod_eportfolio_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function eportfolio_add_instance($moduleinstance, $mform = null) {
    global $DB;

    // Check, if already an instance for this course is available.
    $exists = $DB->get_record('eportfolio', array('course' => $moduleinstance->course));

    if ($exists) {

        // Currently only von activity per course is allowed!
        $url = new moodle_url('/course/view.php', ['id' => $moduleinstance->course]);

        redirect($url, get_string('eportfolio:create:activityalreadyavailable', 'local_eportfolio'),
                '', \core\output\notification::NOTIFY_ERROR);
    }

    $moduleinstance->timecreated = time();

    $id = $DB->insert_record('eportfolio', $moduleinstance);

    return $id;
}

/**
 * Updates an instance of the mod_eportfolio in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_eportfolio_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function eportfolio_update_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

    return $DB->update_record('eportfolio', $moduleinstance);
}

/**
 * Removes an instance of the mod_eportfolio from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function eportfolio_delete_instance($id) {
    global $DB;

    $exists = $DB->get_record('eportfolio', array('id' => $id));
    if (!$exists) {
        return false;
    }

    $moduleid = $DB->get_field('modules', 'id', ['name' => 'eportfolio']);

    // Get the course module.
    if (!$cm = $DB->get_record('course_modules', array('instance' => $id, 'module' => $moduleid))) {
        return false;
    }

    if (!$DB->delete_records('eportfolio', array('id' => $id))) {
        return false;
    }

    $result = true;

    // Get the module context.
    $modcontext = context_module::instance($cm->id);

    // Delete any dependent records here.

    // Delete all associated H5P files.
    $eportfoliofiles = $DB->get_records('files',
            ['contextid' => $modcontext->id, 'component' => 'mod_eportfolio', 'filearea' => 'eportfolio']);

    foreach ($eportfoliofiles as $eport) {

        // Get H5P files and delete them.
        if ($eport->filename != '.') {

            $h5pfile = $DB->get_record('h5p', ['pathnamehash' => $eport->pathnamehash]);

            if ($h5pfile) {
                $DB->delete_records('h5p', ['id' => $h5pfile->id]);
            }
        }

    }

    // Delete files associated with this ePortfolio.
    $fs = get_file_storage();
    if (!$fs->delete_area_files($modcontext->id, 'mod_eportfolio', 'eportfolio')) {
        $result = false;
    }

    // Delete entries from table local_eportfolio_share when shared for grading for this cm.
    if (!$DB->delete_records('local_eportfolio_share', ['courseid' => $cm->course, 'shareoption' => 'grade',
            'cmid' => $cm->id])) {
        $result = false;
    }

    // Delete entries from table eportfolio_grade.
    if (!$DB->delete_records('eportfolio_grade', ['courseid' => $cm->course, 'cmid' => $cm->id, 'instance' => $cm->instance])) {
        $result = false;
    }

    // Delete events.
    if (!$DB->delete_records('event', ['modulename' => 'eportfolio', 'instance' => $id])) {
        $result = false;
    }

    return $result;
}

/**
 * Is a given scale used by the instance of mod_eportfolio?
 *
 * This function returns if a scale is being used by one mod_eportfolio
 * if it has support for grading and scales.
 *
 * @param int $moduleinstanceid ID of an instance of this module.
 * @param int $scaleid ID of the scale.
 * @return bool True if the scale is used by the given mod_eportfolio instance.
 */
function eportfolio_scale_used($moduleinstanceid, $scaleid) {
    global $DB;

    if ($scaleid && $DB->record_exists('eportfolio', array('id' => $moduleinstanceid, 'grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if scale is being used by any instance of mod_eportfolio.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param int $scaleid ID of the scale.
 * @return bool True if the scale is used by any mod_eportfolio instance.
 */
function eportfolio_scale_used_anywhere($scaleid) {
    global $DB;

    if ($scaleid && $DB->record_exists('eportfolio', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Creates or updates grade item for the given mod_eportfolio instance.
 *
 * Needed by {@see grade_update_mod_grades()}.
 *
 * @param stdClass $moduleinstance Instance object with extra cmidnumber and modname property.
 * @param bool $reset Reset grades in the gradebook.
 * @return void.
 */
function eportfolio_grade_item_update($moduleinstance, $reset = false) {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    $item = array();
    $item['itemname'] = clean_param($moduleinstance->name, PARAM_NOTAGS);
    $item['gradetype'] = GRADE_TYPE_VALUE;

    if ($moduleinstance->grade > 0) {
        $item['gradetype'] = GRADE_TYPE_VALUE;
        $item['grademax'] = $moduleinstance->grade;
        $item['grademin'] = 0;
    } else if ($moduleinstance->grade < 0) {
        $item['gradetype'] = GRADE_TYPE_SCALE;
        $item['scaleid'] = -$moduleinstance->grade;
    } else {
        $item['gradetype'] = GRADE_TYPE_NONE;
    }
    if ($reset) {
        $item['reset'] = true;
    }

    grade_update('/mod/eportfolio', $moduleinstance->course, 'mod', 'mod_eportfolio', $moduleinstance->id, 0, null, $item);
}

/**
 * Delete grade item for given mod_eportfolio instance.
 *
 * @param stdClass $moduleinstance Instance object.
 * @return grade_item.
 */
function eportfolio_grade_item_delete($moduleinstance) {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    return grade_update('/mod/eportfolio', $moduleinstance->course, 'mod', 'eportfolio',
            $moduleinstance->id, 0, null, array('deleted' => 1));
}

/**
 * Update mod_eportfolio grades in the gradebook.
 *
 * Needed by {@see grade_update_mod_grades()}.
 *
 * @param stdClass $moduleinstance Instance object with extra cmidnumber and modname property.
 * @param int $userid Update grade of specific user only, 0 means all participants.
 */
function eportfolio_update_grades($moduleinstance, $userid = 0) {
    global $CFG, $DB;
    require_once($CFG->libdir . '/gradelib.php');

    // Populate array of grade objects indexed by userid.
    $grades = array();
    grade_update('/mod/eportfolio', $moduleinstance->course, 'mod', 'mod_eportfolio', $moduleinstance->id, 0, $grades);
}
