<?php

namespace mod_exportgrades\task;

defined('MOODLE_INTERNAL') || die();

// class export_grades_task extends \core\task\scheduled_task {

//     public function get_name() {
//         return get_string('exportgradestask', 'mod_exportgrades');
//     }

//     public function execute() {
//         global $CFG, $DB;

//         require_once($CFG->dirroot . '/mod/exportgrades/lib.php');

//         // Obtén la configuración de frecuencia y otros ajustes
//         $frequency = get_config('mod_exportgrades', 'export_frequency');
//         $driveFolderId = get_config('mod_exportgrades', 'drive_folder_id');
//         $driveCredentials = get_config('mod_exportgrades', 'drive_service_account_credentials');

//         // Lógica para generar el archivo Excel
//         $courseid = 1; // Ajusta esto según sea necesario
//         $filePath = exportgrades_generate_excel($courseid, $driveFolderId, $frequency);

//         // Subir a Google Drive usando la API de Google
//         $client = new \Google_Client();
//         $client->setAuthConfig(json_decode($driveCredentials, true));
//         $client->addScope(\Google_Service_Drive::DRIVE_FILE);
//         $service = new \Google_Service_Drive($client);

//         $file = new \Google_Service_Drive_DriveFile();
//         $file->setName('exported_grades.xlsx');
//         $file->setParents(array($driveFolderId));

//         $content = file_get_contents($filePath);
//         $service->files->create($file, array(
//             'data' => $content,
//             'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
//             'uploadType' => 'multipart'
//         ));
//     
// }

class grade_export_task extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('grade_export_task', 'mod_exportgrades');
    }

    // public static function install() {
    //     // Obtener el objeto de gestión de tareas programadas
    //     $scheduler = \core\task\scheduled_task::get_scheduler();

    //     // Crear una instancia de la tarea programada
    //     $task = new \mod_exportgrades\task\grade_export_task();

    //     // Registrar la tarea programada en el planificador
    //     $scheduler->schedule_task($task);
    // }

    public function execute() {
        global $DB;

        require_once($CFG->dirroot . '/mod/exportgrades/lib.php');

        $export_frequency = get_config('mod_exportgrades', 'export_frequency');
        $last_export_time = get_config('mod_exportgrades', 'last_export_time');
        $current_time = time();

        $should_export = false;
        switch ($export_frequency) {
            case 'daily':
                $should_export = ($current_time - $last_export_time >= 86400); // 24 horas
                break;
            case 'weekly':
                $should_export = ($current_time - $last_export_time >= 604800); // 7 días
                break;
            case 'monthly':
                $should_export = ($current_time - $last_export_time >= 2592000); // 30 días
                break;
        }

        if ($should_export) {
            // Obtener todos los cursos
            $courses = $DB->get_records('course');

            foreach ($courses as $course) {
                export_selected_grades_to_csv($course->id);
            }

            // Actualizar el tiempo de la última exportación
            set_config('last_export_time', $current_time, 'mod_exportgrades');
        }
    }
}
