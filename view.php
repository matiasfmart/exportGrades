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


// El archivo view.php en un plugin de Moodle generalmente se utiliza para mostrar el contenido principal
// o la vista principal de la actividad o recurso que el plugin proporciona.
//  En el contexto de tu plugin exportgrades, el archivo view.php podría usarse para mostrar la interfaz de usuario principal
// donde los usuarios interactúan con la funcionalidad de exportación de calificaciones.

// En view.php puedes incluir lógica para procesar datos, interactuar con la base de datos si es necesario,
// y generar la salida HTML que se mostrará a los usuarios.
//  También es común que este archivo maneje cualquier acción que el usuario realice en la interfaz,
// como enviar un formulario o hacer clic en un botón, y luego redirija o actualice la página según sea necesario.



/**
 * Prints an instance of mod_exportgrades.
 *
 * @package     mod_exportgrades
 * @copyright   2024 Your Name <you@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

// Course module id.
$id = optional_param('id', 0, PARAM_INT);

// Activity instance id.
$e = optional_param('e', 0, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('exportgrades', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('exportgrades', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    $moduleinstance = $DB->get_record('exportgrades', array('id' => $e), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('exportgrades', $moduleinstance->id, $course->id, false, MUST_EXIST);
}

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

$event = \mod_exportgrades\event\course_module_viewed::create(array(
    'objectid' => $moduleinstance->id,
    'context' => $modulecontext
));
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('exportgrades', $moduleinstance);
$event->trigger();

$PAGE->set_url('/mod/exportgrades/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

echo $OUTPUT->header();

echo $OUTPUT->footer();
