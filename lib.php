<?php
//este archivo lib.php proporciona funciones esenciales para la gestión de instancias del módulo mod_exportgrades, incluyendo la capacidad de agregar, actualizar y eliminar instancias en la base de datos de Moodle.
//IMPORTANTE - Falta agrega funcion  exportgrades_generate_excel que se esta llamando desde el archivo export_grades_task.php para generar el excel con las notas a drive.

defined('MOODLE_INTERNAL') || die();

function exportgrades_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        default:
            return null;
    }
}

function exportgrades_add_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timecreated = time();

    $id = $DB->insert_record('exportgrades', $moduleinstance);

    return $id;
}

function exportgrades_update_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

    return $DB->update_record('exportgrades', $moduleinstance);
}

function exportgrades_delete_instance($id) {
    global $DB;

    $exists = $DB->get_record('exportgrades', array('id' => $id));
    if (!$exists) {
        return false;
    }

    $DB->delete_records('exportgrades', array('id' => $id));

    return true;
}
