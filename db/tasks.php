<?php
defined('MOODLE_INTERNAL') || die();



// El archivo task.php en Moodle se utiliza para definir tareas programadas (también conocidas como tareas cron)
// que el plugin necesita ejecutar de forma periódica. Estas tareas pueden incluir operaciones como la actualización de datos,
// el envío de notificaciones o cualquier otra acción que deba realizarse en segundo plano de manera regular.

// En el contexto de tu plugin exportgrades, podrías utilizar el archivo task.php para programar la exportación
// automática de calificaciones a Google Drive según la configuración de frecuencia establecida por el usuario.
// Este archivo te permitiría definir una tarea cron que se ejecute periódicamente para realizar la exportación de manera 
// automática sin intervención manual.


$tasks = array(
    array(
        'classname' => 'mod_exportgrades\task\export_grades_task',
        'blocking' => 0,
        'minute' => 'R',
        'hour' => 'R',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    ),
);
