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
 * Code that is executed before the tables and data are dropped during the plugin uninstallation.
 *
 * @package     mod_eportfolio
 * @category    upgrade
 * @copyright   2023 weQon UG <support@weqon.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Custom uninstallation procedure.
 */
function xmldb_eportfolio_uninstall() {
    global $DB;

    // Delete all associated H5P files.
    // First get all files.
    $eportfoliofiles = $DB->get_records('files',['component' => 'mod_eportfolio', 'filearea' => 'eportfolio']);

    foreach ($eportfoliofiles as $eport) {

        // Delete the H5P files based on the pathnamehash.
        if ($eport->filename != '.') {

            $h5pfile = $DB->get_record('h5p', ['pathnamehash' => $eport->pathnamehash]);

            if ($h5pfile) {
                $DB->delete_records('h5p', ['id' => $h5pfile->id]);
            }
        }

    }

    // Delete entries from table local_eportfolio_share where shared for grading.
    if (!$DB->delete_records('local_eportfolio_share', ['shareoption' => 'grade'])) {
        return false;
    }

    return true;
}
