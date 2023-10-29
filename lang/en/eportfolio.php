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
 * Plugin strings are defined here.
 *
 * @package     mod_eportfolio
 * @category    string
 * @copyright   2023 weQon UG <support@weqon.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'ePortfolio Grading';
$string['modulename'] = 'ePortfolio Grading';

$string['eportfolioname'] = 'Title';

// Overview table.
$string['overview:table:btn:grade'] = 'Add grading';
$string['overview:table:btn:view'] = 'View grading';
$string['overview:table:btn:delete'] = 'Allow new submission';
$string['overview:table:btn:delete:help'] = 'Clicking on "Allow new submission" will remove the current submission and delete the existing grade.
Course participants will be given the option to resubmit their submission, e.g. to provide a corrected version.';

// Grading form.
$string['gradeform:header'] = 'Grade & Feedback';
$string['gradeform:grade'] = 'Grade (in %)';
$string['gradeform:grade_help'] = 'Specify grading as a percentage.';
$string['gradeform:feedbacktext'] = 'Feedback as comment';
$string['gradeform:gradeview'] = 'Grade';
$string['gradeform:grader'] = 'Grading by';

// Insert & Update grading.
$string['grade:insert:success'] = 'Your grading has been successfully saved!';
$string['grade:insert:error'] = 'An error occurred while saving the grading! Please try again!';
$string['grade:update:success'] = 'Your grading has been successfully updated!';
$string['grade:update:error'] = 'An error occurred while updating the grading! Please try again!';

// Message provider.
$string['messageprovider:grading'] = 'Notification about new assessments for ePortfolio';
$string['message:emailmessage'] =
        '<p>A new grade has been added for you.<br>ePortfolio: {$a->filename}<br>Course: {$a->coursename}<br>
<br>Grading by: {$a->userfrom}<br>URL: {$a->viewurl}</p>';
$string['message:smallmessage'] =
        '<p>A new grade has been added for you.<br>ePortfolio: {$a->filename}<br>Course: {$a->coursename}<br>
<br>Grading by: {$a->userfrom}<br>URL: {$a->viewurl}</p>';
$string['message:subject'] = 'Notification about new assessments for ePortfolio';
$string['message:contexturlname'] = 'View grade for ePortfolio';

// Delete shared ePortfolio
$string['delete:header'] = 'Allow new submission?';
$string['delete:confirm'] = 'Confirm';
$string['delete:checkconfirm'] = '<b>Do you really want to allow a new submission for this file?</b><br><br>
Filename: {$a->filename}<br>Shared by: {$a->username}<br><br><b>The submitted file and any existing grades will also be deleted!</b>';
$string['delete:success'] = 'The selected file was deleted successfully!';
$string['delete:error'] = 'There was an error while deleting the file! Please try again!';

// Events.
$string['event:eportfolio:deleted:name'] = 'ePortfolio deleted';
$string['event:eportfolio:deleted'] =
        'The user with the id \'{$a->userid}\' deleted ePortfolio {$a->filename} (itemid: \'{$a->itemid}\')';
