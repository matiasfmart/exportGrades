<?php

namespace mod_exportgrades;

defined('MOODLE_INTERNAL') || die();

class Observer
{

    /**
     * Función que se ejecuta cuando se guardan las configuraciones.
     *
     * @param \core\event\config_log_created $event El objeto del evento.
     */
    public static function config_updated(\core\event\config_log_created $event)
    {
        global $DB;

        // Verificar si las configuraciones de frecuencia han cambiado
        if ($event->other['plugin'] === 'mod_exportgrades' && $event->other['name'] === 'export_frequency') {
            // Obtener la frecuencia actual
            $export_frequency = get_config('mod_exportgrades', 'export_frequency');

            // Eliminar todas las instancias existentes de la tarea específica
            $DB->delete_records('task_scheduled', array('classname' => 'mod_exportgrades\task\grade_export_task'));

            // Definir la configuración por defecto de la tarea según la frecuencia
            switch ($export_frequency) {
                case 'daily':
                    $default_task = array(
                        'classname' => 'mod_exportgrades\task\grade_export_task',
                        'blocking' => 0,
                        'minute' => '0',
                        'hour' => '0',
                        'day' => '*/1',
                        'month' => '*',
                        'dayofweek' => '*',
                        'disabled' => 0
                    );
                    break;
                case 'weekly':
                    $default_task = array(
                        'classname' => 'mod_exportgrades\task\grade_export_task',
                        'blocking' => 0,
                        'minute' => '0',
                        'hour' => '0',
                        'day' => '*/7',
                        'month' => '*',
                        'dayofweek' => '0',
                        'disabled' => 0
                    );
                    break;
                case 'monthly':
                    $default_task = array(
                        'classname' => 'mod_exportgrades\task\grade_export_task',
                        'blocking' => 0,
                        'minute' => '0',
                        'hour' => '0',
                        'day' => '*/31',
                        'month' => '*',
                        'dayofweek' => '*',
                        'disabled' => 0
                    );
                    break;
                default:
                    $default_task = array(
                        'classname' => 'mod_exportgrades\task\grade_export_task',
                        'blocking' => 0,
                        'minute' => '0',
                        'hour' => '2',
                        'day' => '*/20',
                        'month' => '*',
                        'dayofweek' => '*',
                        'disabled' => 0
                    );
                    break;
            }

            // Insertar la nueva configuración de la tarea en la base de datos
            $DB->insert_record('task_scheduled', (object)$default_task);
        }
    }

}