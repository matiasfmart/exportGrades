<?php

namespace mod_exportgrades;

defined('MOODLE_INTERNAL') || die();

use core\task\manager; // Asegúrate de importar el namespace correcto

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
        if ($event->other['plugin'] === 'mod_exportgrades') {
            // Obtener la frecuencia actual
            $export_frequency = get_config('mod_exportgrades', 'export_frequency');
            $exportgrades_settings = get_config('mod_exportgrades');

            // Ejemplo de cómo acceder a la hora configurada
            $hour_setting = $exportgrades_settings->hour;

            // Definir la configuración por defecto de la tarea según la frecuencia
            switch ($export_frequency) {
                case 'daily':
                    $default_task = array(
                        'classname' => 'mod_exportgrades\task\grade_export_task',
                        'blocking' => 0,
                        'minute' => '*',
                        'hour' => $hour_setting,
                        'day' => '*',
                        'month' => '*',
                        'dayofweek' => '*',
                        'disabled' => 0
                    );
                    break;
                case 'weekly':
                    $default_task = array(
                        'classname' => 'mod_exportgrades\task\grade_export_task',
                        'blocking' => 0,
                        'minute' => '*',
                        'hour' => $hour_setting,
                        'day' => '*',
                        'month' => '*',
                        'dayofweek' => '0', // 0 representa el domingo
                        'disabled' => 0
                    );
                    break;
                case 'monthly':
                    $default_task = array(
                        'classname' => 'mod_exportgrades\task\grade_export_task',
                        'blocking' => 0,
                        'minute' => '*',
                        'hour' => $hour_setting,
                        'day' => '1',
                        'month' => '*',
                        'dayofweek' => '*',
                        'disabled' => 0
                    );
                    break;
            }

            // Buscar la tarea existente en la base de datos utilizando LIKE
            $existing_tasks = $DB->get_records_sql("SELECT * FROM {task_scheduled} WHERE classname LIKE ?", array('%grade_export_task%'));

            if ($existing_tasks) {
                // Si existe una tarea, actualizarla
                foreach ($existing_tasks as $task) {
                    $task->hour = $default_task['hour'];
                    $task->day = $default_task['day'];
                    $task->dayofweek = $default_task['dayofweek'];
                    $task->disabled = $default_task['disabled'];
                    $DB->update_record('task_scheduled', $task);
                }
            } else {
                // Crear una nueva tarea si no existe
                $DB->insert_record('task_scheduled', (object)$default_task);
            }
        }
    }
}
