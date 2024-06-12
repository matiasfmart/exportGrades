<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/exportgrades/vendor/autoload.php'); // Incluye el autoload de Composer

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

function export_selected_grades_to_csv($courseid) {
    global $DB;

    // Consulta para obtener los campos itemid, userid y finalgrade de la tabla mdl_grade_grades
    $sql = "SELECT itemid, userid, finalgrade
            FROM {grade_grades}
            WHERE itemid IN (
                SELECT id FROM {grade_items} WHERE courseid = :courseid
            )";
    $params = ['courseid' => $courseid];
    $grades = $DB->get_records_sql($sql, $params);

    if (empty($grades)) {
        return false;
    }

    // Nombre del archivo CSV
    $filename = "grades_course_{$courseid}_" . date('Ymd_His') . ".csv";

    // Obtener la ruta del directorio de exportación desde la configuración
    $export_directory = get_config('mod_exportgrades', 'exportdirectory');
    if (empty($export_directory)) {
        // Si la configuración no está definida, usar una ruta predeterminada
        $export_directory = 'C:/xampp/htdocs/MoodleWindowsInstaller-latest-401/server/moodle/mod/exportgrades/exports';//modificado mb
    }

    // Asegurarse de que el directorio termina con una barra
    if (substr($export_directory, -1) !== DIRECTORY_SEPARATOR) {
        $export_directory .= DIRECTORY_SEPARATOR;
    }

    // Crear el directorio de exportación si no existe
    if (!file_exists($export_directory)) {
        mkdir($export_directory, 0777, true);
    }

    // Combinar la ruta del directorio y el nombre de archivo para obtener la ruta completa
    $filepath = $export_directory . $filename;

    // Abrir el archivo CSV para escribir
    $file = fopen($filepath, 'w');

    // Escribir la cabecera del CSV
    $header = ['itemid', 'userid', 'finalgrade'];
    fputcsv($file, $header);

    // Escribir los datos de las calificaciones
    foreach ($grades as $grade) {
        $row = [
            $grade->itemid,
            $grade->userid,
            $grade->finalgrade
        ];
        fputcsv($file, $row);
    }

    // Cerrar el archivo CSV
    fclose($file);

    return $filepath;
}


//subida al drive

function uploadToGoogleDrive($filePath, $fileName) {
    $client = new Google_Client();
    $client->setAuthConfig('config/client_secret.json');
    $client->addScope(Google_Service_Drive::DRIVE_FILE);
    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');

    // Path to the token file
    $tokenPath = 'config/token.json';

    if (file_exists($tokenPath)) {
        $accessToken = json_decode(file_get_contents($tokenPath), true);
        $client->setAccessToken($accessToken);
    }

    // Refresh the token if it's expired
    if ($client->isAccessTokenExpired()) {
        if ($client->getRefreshToken()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        } else {
            // Request authorization from the user
            $authUrl = $client->createAuthUrl();
            printf("Open the following link in your browser:\n%s\n", $authUrl);
            print 'Enter verification code: ';
            $authCode = trim(fgets(STDIN));

            // Exchange authorization code for an access token
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
            $client->setAccessToken($accessToken);

            // Check to see if there was an error
            if (array_key_exists('error', $accessToken)) {
                throw new Exception(join(', ', $accessToken));
            }
        }
        // Save the token to a file
        if (!file_exists(dirname($tokenPath))) {
            mkdir(dirname($tokenPath), 0700, true);
        }
        file_put_contents($tokenPath, json_encode($client->getAccessToken()));
    }

    $service = new Google_Service_Drive($client);

    $fileMetadata = new Google_Service_Drive_DriveFile(array(
        'name' => $fileName,
        'parents' => array('1pqbk7AuZNdeWnJMValUAxpnMfndsH1Ao')
    ));

    $content = file_get_contents($filePath);

    $file = $service->files->create($fileMetadata, array(
        'data' => $content,
        'mimeType' => 'text/csv',
        'uploadType' => 'multipart',
        'fields' => 'id'
    ));

    printf("File ID: %s\n", $file->id);
}
