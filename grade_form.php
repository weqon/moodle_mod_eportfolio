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
 *
 * @package     mod_eportfolio
 * @copyright   2023 weQon UG {@link https://weqon.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("$CFG->libdir/formslib.php");
require_once($CFG->dirroot . "/mod/eportfolio/locallib.php");

class grade_form extends moodleform {

    public function definition() {
        global $DB;

        $mform = $this->_form; // Don't forget the underscore!

        $userid = $this->_customdata['userid'];
        $cmid = $this->_customdata['cmid'];
        $itemid = $this->_customdata['fileid'];
        $courseid = $this->_customdata['courseid'];

        $mform->addElement('hidden', 'userid', $userid);
        $mform->addElement('hidden', 'cmid', $cmid);
        $mform->addElement('hidden', 'fileid', $itemid);
        $mform->addElement('hidden', 'courseid', $courseid);

        $mform->addElement('html', '<h3>' . get_string('gradeform:header', 'mod_eportfolio') . '</h3><br>');

        $mform->addElement('text', 'grade', get_string('gradeform:grade', 'mod_eportfolio'), ['size' => '3']);
        $mform->setType('grade', PARAM_INT);
        $mform->addHelpButton('grade', 'gradeform:grade', 'mod_eportfolio');

        $mform->addElement('textarea', 'feedbacktext', get_string('gradeform:feedbacktext', 'mod_eportfolio'), 'wrap="virtual" rows="10" cols="30"');

        $mform->addElement('html', '<hr><hr>');

        // Add standard buttons.
        $this->add_action_buttons();

    }

}