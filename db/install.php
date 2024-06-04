<?php

defined('MOODLE_INTERNAL') || die();

/**
 * Custom code to be run on installing the plugin.
 */
function xmldb_exportgrades_install() {
    global $CFG;

    // Registrar la tarea programada
    //\mod_exportgrades\task\grade_export_task::install();

    return true;
}

