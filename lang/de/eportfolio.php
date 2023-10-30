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

$string['pluginname'] = 'ePortfolio Bewertung';
$string['modulename'] = 'ePortfolio Bewertung';

$string['eportfolioname'] = 'Titel';

// Overview table.
$string['overview:table:btn:grade'] = 'Bewerten';
$string['overview:table:btn:view'] = 'Anzeigen';
$string['overview:table:btn:delete'] = 'Neue Freigabe erlauben';
$string['overview:table:btn:delete:help'] = 'Mit Klick auf "Neue Freigabe erlauben" wird die aktuelle Einreichung entfernt und die bisher gesetzten Bewertungen gelöscht.
Die Kursteilnehmer/innen erhalten die Möglichkeit, ihre Einreichung erneut durchzuführen, z. B. um eine korrigierte Version bereitzustellen.';

// Grading form.
$string['gradeform:header'] = 'Benotung & Feedback';
$string['gradeform:grade'] = 'Benotung (in %)';
$string['gradeform:grade_help'] = 'Benotung in Prozent angeben.';
$string['gradeform:feedbacktext'] = 'Feedback als Kommentar';
$string['gradeform:gradeview'] = 'Benotung';
$string['gradeform:grader'] = 'Bewertet durch';
$string['gradeform:backbtn'] = 'Zurück zur Übersicht';

// Insert & Update grading.
$string['grade:insert:success'] = 'Ihre Bewertung wurde erfolgreich gespeichert!';
$string['grade:insert:error'] = 'Beim Speichern der Benotung ist ein Fehler aufgetreten! Bitte versuchen Sie es erneut!';
$string['grade:update:success'] = 'Ihre Bewertung wurde erfolgreich aktualisiert!';
$string['grade:update:error'] = 'Beim Aktualisieren der Benotung ist ein Fehler aufgetreten! Bitte versuchen Sie es erneut!';

// Message provider.
$string['messageprovider:grading'] = 'Mitteilung über neue Bewertungen für ePortfolio';
$string['message:emailmessage'] =
        '<p>Für Sie wurde eine neue Bewertung hinterlegt.<br>Eingereichtes ePortfolio: {$a->filename}<br>Kurs: {$a->coursename}<br>
<br>Bewertet durch: {$a->userfrom}<br>URL zur Einreichung: {$a->viewurl}</p>';
$string['message:smallmessage'] =
        '<p>Für Sie wurde eine neue Bewertung hinterlegt.<br>Eingereichtes ePortfolio: {$a->filename}<br>Kurs: {$a->coursename}<br>
<br>Bewertet durch: {$a->userfrom}<br>URL zur Einreichung: {$a->viewurl}</p>';
$string['message:subject'] = 'Mitteilung über eine neue Bewertung für Ihr ePortfolio';
$string['message:contexturlname'] = 'Bewertung für ePortfolio anzeigen';

// Delete shared ePortfolio
$string['delete:header'] = 'Neue Freigabe erlauben?';
$string['delete:confirm'] = 'Löschen bestätigen';
$string['delete:checkconfirm'] = '<b>Möchten Sie für die ausgewählte Datei wirklich eine neue Freigabe erlauben?</b><br><br>
Dateiname: {$a->filename}<br>Eingereicht von: {$a->username}<br><br><b>Die eingereichte Datei und bestehende Bewertungen werden gelöscht!</b>';
$string['delete:success'] = 'Datei wurde erfolgreich gelöscht!';
$string['delete:error'] = 'Beim Löschen der Datei ist ein Fehler aufgetreten! Bitte versuchen Sie es erneut!';

// Events.
$string['event:eportfolio:deleted:name'] = 'ePortfolio aus Bewertung gelöscht';
$string['event:eportfolio:deleted'] =
        'The user with the id \'{$a->userid}\' deleted ePortfolio {$a->filename} (itemid: \'{$a->itemid}\')';
