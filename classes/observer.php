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
 * Event observers implementation
 *
 * @copyright   ILD Fachhoschule Lübeck
 * @author      Eugen Ebel
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');

class enrol_ildpayone_observer {

    /**
     * Handle user enrolment created
     *
     * @param \core\event\user_enrolment_created
     */
    public static function user_enrolment_created($event) {
        global $DB, $CFG;

        $courseid = $event->get_data()['courseid'];
        $userid = $event->get_data()['relateduserid'];
        $enrol = $event->get_data()['other']['enrol'];

//        $logfile = $CFG->dirroot . '/enrol/ildpayone/classes/event.log';
//        file_put_contents($logfile, 'event: ' . $other . PHP_EOL . PHP_EOL, FILE_APPEND);
//        file_put_contents($logfile, 'userid: ' . $userid . PHP_EOL . PHP_EOL, FILE_APPEND);
//        file_put_contents($logfile, 'courseid: ' . $courseid . PHP_EOL . PHP_EOL, FILE_APPEND);

        if ($enrol != 'manual') {
            $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
            $completion = new \completion_info($course);

            if ($completion->is_enabled()) {
                $dbman = $DB->get_manager();
                $DB->delete_records_select('course_modules_completion',
                    'coursemoduleid IN (SELECT id FROM mdl_course_modules WHERE course=?) AND userid=?',
                    array($courseid, $userid));
                $DB->delete_records('course_completions', array('course' => $courseid, 'userid' => $userid));
                $DB->delete_records('course_completion_crit_compl', array('course' => $courseid, 'userid' => $userid));
                if ($dbman->table_exists('choice_answers')) {
                    $DB->delete_records_select('choice_answers',
                        'choiceid IN (SELECT id FROM mdl_choice WHERE course=?) AND userid=?',
                        array($courseid, $userid));
                }

                if ($dbman->table_exists('scorm_scoes_track')) {
                    $DB->delete_records_select('scorm_scoes_track',
                        'scormid IN (SELECT id FROM mdl_scorm WHERE course=?) AND userid=?',
                        array($courseid, $userid));
                }

                if ($dbman->table_exists('quiz')) {
                    $orphanedattempts = $DB->get_records_sql_menu("
                    SELECT id, uniqueid
                      FROM {quiz_attempts}
                    WHERE userid=$userid AND quiz IN (SELECT id FROM mdl_quiz WHERE course=$courseid)");

                    if ($orphanedattempts) {
                        foreach ($orphanedattempts as $attemptid => $usageid) {
                            \question_engine::delete_questions_usage_by_activity($usageid);
                            $DB->delete_records('quiz_attempts', array('id' => $attemptid));
                        }
                    }
                }

                \cache::make('core', 'completion')->purge();
            }
        }
    }
}
