<?php

namespace mod_exportgrades;

defined('MOODLE_INTERNAL') || die();

class Observer
{

    /**
     * Funcion se ejecuta cuando se guardan las settings
     *
     * @param \core\event\config_log_created $event The event object.
     */
    public static function config_updated(\core\event\config_log_created $event)
    {
        global $DB;

        // Chequea si las settings the frecuencia es distinta
        if ($event->other['plugin'] === 'mod_exportgrades' && $event->other['name'] === 'export_frequency') {
           // Obtenemos la frecuencya actual
            $export_frequency = get_config('mod_exportgrades', 'export_frequency');

            // Definimos el comportamiento x default
            $default_task = array(
                'classname' => 'mod_exportgrades\task\grade_export_task',
                'blocking' => 0,
                'minute' => '*/1',
                'hour' => '*',
                'day' => '*',
                'month' => '*',
                'dayofweek' => '*',
                'disabled' => 0
            );

            // Obtenemos el task_scheduled de la DB
            $existing_task = $DB->get_record('task_scheduled', array('classname' => $default_task['classname']));


            if ($existing_task) {
            // Si ya existe una scheduled task la actualizamos y reseteamos el resto a default
                $existing_task->minute = $default_task['minute'];
                $existing_task->hour = $default_task['hour'];
                $existing_task->day = $default_task['day'];
                $existing_task->month = $default_task['month'];
                $existing_task->dayofweek = $default_task['dayofweek'];
                $existing_task->disabled = $default_task['disabled'];
                switch ($export_frequency) {
                    case 'daily':
                        $existing_task->day = '*/1';
                        break;
                    case 'weekly':
                        $existing_task->day = '*/7';
                        $existing_task->hour = '0';
                        break;
                    case 'monthly':
                        $existing_task->day = '*/31';  // cada 31 dias
                        $existing_task->hour = '0';  // a la noche
                        break;
                }

                $DB->update_record('task_scheduled', $existing_task);
        } else {
            // Insert a new task
            $DB->insert_record('task_scheduled', (object)$default_task);
        }

    }

}
}
