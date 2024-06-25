<?php
defined('MOODLE_INTERNAL') || die();
$plugin->component = 'mod_exportgrades';
$plugin->icon = 'mod/exportgrades/pix/icon.PNG';  // Cambia 'mod/exportgrades' según el tipo de tu plugin y estructura
$plugin->release = '0.1.0';
$plugin->version = 2024052600;
$plugin->requires = 2021051700;
$plugin->maturity = MATURITY_ALPHA;

$plugin->tasks = [
    [
        'classname' => 'mod_exportgrades\task\grade_export_task',
        'blocking' => 0,
        'minute' => '*',
        'hour' => '*', // Hora predeterminada para la tarea si no se especifica
        'day' => '*/20', // Día predeterminado para la tarea si no se especifica
        'month' => '*',
        'dayofweek' => '*',
    ],
];