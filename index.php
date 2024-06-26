<?php
// En resumen, esta clase index.php se encarga de mostrar una lista de todas las instancias del módulo exportgrades
// en el curso solicitado. Maneja la autenticación, la configuración de la página, la obtención de datos y el renderizado de la interfaz para que los usuarios
// puedan ver y acceder a cada instancia del módulo en el curso.
/*
require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require_once './vendor/autoload.php';

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

$modulenameplural = get_string('exportgrades', 'mod_exportgrades');
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
*/

// Este archivo index.php se encarga de mostrar una lista de todas las instancias del módulo exportgrades
// en el curso solicitado. Maneja la autenticación, la configuración de la página, la obtención de datos 
// y el renderizado de la interfaz para que los usuarios puedan ver y acceder a cada instancia del módulo en el curso.
require_once(__DIR__.'/vendor/autoload.php'); // Cambié la forma de requerir autoload.php para mantener la consistencia
require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');


$id = required_param('id', PARAM_INT);

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
require_course_login($course);

$coursecontext = context_course::instance($course->id);

// Corregido: El contexto del módulo se obtiene desde el curso
$event = \mod_exportgrades\event\course_module_instance_list_viewed::create(array(
    'context' => $coursecontext
));
$event->add_record_snapshot('course', $course);
$event->trigger();

$PAGE->set_url('/mod/exportgrades/index.php', array('id' => $id));
$PAGE->set_title(format_string($course->fullname));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($coursecontext);

echo $OUTPUT->header();

$modulenameplural = get_string('modulenameplural', 'mod_exportgrades'); // Asegúrate de que 'modulenameplural' esté definido en el archivo de idioma
echo $OUTPUT->heading($modulenameplural);

$exportgradess = get_all_instances_in_course('exportgrades', $course);

if (empty($exportgradess)) {
    notice(get_string('noexportgradesinstances', 'mod_exportgrades'), new moodle_url('/course/view.php', array('id' => $course->id))); // Corregido: Variable y cadena de idioma
    echo $OUTPUT->footer();
    exit; // Asegúrate de salir después de mostrar el aviso
}

$table = new html_table();
$table->attributes['class'] = 'generaltable mod_index';

if ($course->format == 'weeks') {
    $table->head  = array(get_string('week'), get_string('name'));
    $table->align = array('center', 'left');
} else if ($course->format == 'topics') {
    $table->head  = array(get_string('topic'), get_string('name'));
    $table->align = array('center', 'left');
} else {
    $table->head  = array(get_string('name'));
    $table->align = array('left');
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
