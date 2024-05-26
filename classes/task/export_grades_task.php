<?php
namespace mod_exportgrades\task;

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
