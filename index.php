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


// El archivo index.php en un plugin de Moodle sirve como el punto de entrada principal para el plugin.
// Dependiendo del tipo de plugin y su funcionalidad, este archivo puede tener diferentes propósitos.
// En el contexto de un módulo de actividad, el index.php generalmente se utiliza para mostrar una lista de todas las instancias
// del módulo en un curso específico.

// En tu caso, para el plugin exportgrades, el index.php podría mostrar una lista de todas las instancias del plugin en un curso,
// permitiendo a los usuarios ver y acceder a cada una de ellas.







/**
 * Display information about all the mod_exportgrades modules in the requested course.
 *
 * @package     mod_exportgrades
 * @copyright   2024 Your Name <you@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');

require_once(__DIR__.'/lib.php');

$id = required_param('id', PARAM_INT);

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
require_course_login($course);

$coursecontext = context_course::instance($course->id);

$event = \mod_exportgrades\event\course_module_instance_list_viewed::create(array(
    'context' => $modulecontext
));
$event->add_record_snapshot('course', $course);
$event->trigger();

$PAGE->set_url('/mod/exportgrades/index.php', array('id' => $id));
$PAGE->set_title(format_string($course->fullname));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($coursecontext);

echo $OUTPUT->header();

$modulenameplural = get_string('modulenameplural', 'mod_exportgrades');
echo $OUTPUT->heading($modulenameplural);

$exportgradess = get_all_instances_in_course('exportgrades', $course);

if (empty($exportgradess)) {
    notice(get_string('no$exportgradesinstances', 'mod_exportgrades'), new moodle_url('/course/view.php', array('id' => $course->id)));
}

$table = new html_table();
$table->attributes['class'] = 'generaltable mod_index';

if ($course->format == 'weeks') {
    $table->head  = array(get_string('week'), get_string('name'));
    $table->align = array('center', 'left');
} else if ($course->format == 'topics') {
    $table->head  = array(get_string('topic'), get_string('name'));
    $table->align = array('center', 'left', 'left', 'left');
} else {
    $table->head  = array(get_string('name'));
    $table->align = array('left', 'left', 'left');
}

foreach ($exportgradess as $exportgrades) {
    if (!$exportgrades->visible) {
        $link = html_writer::link(
            new moodle_url('/mod/exportgrades/view.php', array('id' => $exportgrades->coursemodule)),
            format_string($exportgrades->name, true),
            array('class' => 'dimmed'));
    } else {
        $link = html_writer::link(
            new moodle_url('/mod/exportgrades/view.php', array('id' => $exportgrades->coursemodule)),
            format_string($exportgrades->name, true));
    }

    if ($course->format == 'weeks' or $course->format == 'topics') {
        $table->data[] = array($exportgrades->section, $link);
    } else {
        $table->data[] = array($link);
    }
}

echo html_writer::table($table);
echo $OUTPUT->footer();
