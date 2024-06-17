<?php

namespace mod_exportgrades\task;

use core\task\scheduled_task;

class grade_export_task extends scheduled_task {

    public function get_name() {
        return get_string('grade_export_task', 'mod_exportgrades');
    }

    public function execute() {
        global $DB, $CFG;

        require_once($CFG->dirroot . '/mod/exportgrades/lib.php');

        $context = $this->determine_context();
        $courses = $this->get_courses($context);

        $export_frequency = get_config('mod_exportgrades', 'export_frequency');
        $last_export_time = get_config('mod_exportgrades', 'last_export_time');
        $current_time = time();

        if ($this->should_export($export_frequency, $last_export_time, $current_time)) {
            foreach ($courses as $course) {
                export_selected_grades_to_csv($course->id);
            }

            set_config('last_export_time', $current_time, 'mod_exportgrades');
        }
    }

    public function determine_context() {
        global $PAGE;

        if ($PAGE->context->contextlevel == CONTEXT_COURSE) {
            return (object)['type' => 'course', 'id' => $PAGE->context->instanceid];
        } else {
            return (object)['type' => 'site', 'id' => null];
        }
    }

    public function get_courses($context) {
        global $DB;

        if ($context->type == 'course') {
            return [$DB->get_record('course', ['id' => $context->id])];
        } elseif ($context->type == 'site') {
            return $DB->get_records('course', []);
        }

        return [];
    }

    private function should_export($export_frequency, $last_export_time, $current_time) {
        // Lógica para determinar si se debe exportar
        return ($current_time - $last_export_time) >= $export_frequency;
    }
}
