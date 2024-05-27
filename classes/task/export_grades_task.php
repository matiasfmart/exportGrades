<?php
namespace mod_exportgrades\task;


// La clase export_grades_task en el archivo export_grades_task.php dentro de la carpeta classes/task de tu plugin
// exportgrades se utiliza para definir una tarea programada específica. En este caso, la tarea está destinada a realizar
// la exportación automática de calificaciones a Google Drive según la configuración de frecuencia establecida por el usuario.

// Dentro de la clase export_grades_task, se define el método get_name() para proporcionar el nombre de la tarea,
// que se utilizará para identificarla en la interfaz de administración de tareas programadas de Moodle.
// El método execute() contiene la lógica principal de la tarea, que incluye obtener la configuración de frecuencia y
// otros ajustes, generar el archivo Excel de las calificaciones y subirlo a Google Drive utilizando la API de Google Drive.

// Esta clase y su método execute() se ejecutarán periódicamente de acuerdo con la programación de tareas cron de Moodle,
// lo que permitirá que la exportación de calificaciones se realice de forma automática y programada.




defined('MOODLE_INTERNAL') || die();

class export_grades_task extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('exportgradestask', 'mod_exportgrades');
    }

    public function execute() {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/mod/exportgrades/lib.php');

        // Obtén la configuración de frecuencia y otros ajustes
        $frequency = get_config('mod_exportgrades', 'frequency');
        $driveFolderId = get_config('mod_exportgrades', 'drive_folder_id');
        $driveCredentials = get_config('mod_exportgrades', 'drive_service_account_credentials');

        // Lógica para generar el archivo Excel
        $courseid = 1; // Ajusta esto según sea necesario
        $filePath = exportgrades_generate_excel($courseid);

        // Subir a Google Drive usando la API de Google
        $client = new \Google_Client();
        $client->setAuthConfig(json_decode($driveCredentials, true));
        $client->addScope(\Google_Service_Drive::DRIVE_FILE);
        $service = new \Google_Service_Drive($client);

        $file = new \Google_Service_Drive_DriveFile();
        $file->setName('exported_grades.xlsx');
        $file->setParents(array($driveFolderId));

        $content = file_get_contents($filePath);
        $service->files->create($file, array(
            'data' => $content,
            'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'uploadType' => 'multipart'
        ));
    }
}
