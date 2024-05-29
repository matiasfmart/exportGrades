<?php
// Este archivo lib.php proporciona funciones esenciales para la gestión de instancias del módulo mod_exportgrades, incluyendo la capacidad de agregar, actualizar y eliminar instancias en la base de datos de Moodle.
// IMPORTANTE - Se ha agregado la función exportgrades_generate_excel para generar el excel con las notas a Drive.

defined('MOODLE_INTERNAL') || die();

use Google\Client as Google_Client;
use Google\Service\Drive as Google_Service_Drive;
use Google\Service\Sheets as Google_Service_Sheets;

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

function exportgrades_generate_excel($courseid, $folderId, $exportFrequency, $exportTime) {
    global $DB;

    require_once(__DIR__ . '/vendor/autoload.php');

    $client = new Google_Client();
    $client->setAuthConfig(__DIR__ . '/config/client_secret_1036423208515-5v1f1ute6kvdppdf9tb9ni5ku36tj9uo.apps.googleusercontent.com.json');
    $client->addScope(Google_Service_Drive::DRIVE);
    $client->addScope(Google_Service_Sheets::SPREADSHEETS);

    $driveService = new Google_Service_Drive($client);
    $sheetsService = new Google_Service_Sheets($client);

    // Obtener información del curso
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

    // Obtener las calificaciones de los usuarios en el curso
    $sql = "SELECT u.id AS userid, u.firstname, u.lastname, u.email, gi.finalgrade
            FROM {user} u
            JOIN {grade_grades} gg ON gg.userid = u.id
            JOIN {grade_items} gi ON gi.id = gg.itemid
            WHERE gi.courseid = :courseid";
    $params = array('courseid' => $courseid);
    $grades = $DB->get_records_sql($sql, $params);

    // Crear el archivo Excel
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'ID de Usuario');
    $sheet->setCellValue('B1', 'Nombre');
    $sheet->setCellValue('C1', 'Apellido');
    $sheet->setCellValue('D1', 'Correo Electrónico');
    $sheet->setCellValue('E1', 'Calificación');

    $row = 2;
    foreach ($grades as $grade) {
        $sheet->setCellValue('A' . $row, $grade->userid);
        $sheet->setCellValue('B' . $row, $grade->firstname);
        $sheet->setCellValue('C' . $row, $grade->lastname);
        $sheet->setCellValue('D' . $row, $grade->email);
        $sheet->setCellValue('E' . $row, $grade->finalgrade);
        $row++;
    }

    // Guardar el archivo Excel en Google Drive
    $fileMetadata = new Google_Service_Drive_DriveFile(array(
        'name' => 'Calificaciones del Curso ' . $course->fullname . '.xlsx',
        'parents' => array($folderId),
        'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ));
    $content = file_get_contents('php://memory');
    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save($content);

    $file = $driveService->files->create($fileMetadata, array(
        'data' => $content,
        'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'uploadType' => 'multipart'
    ));

    printf("Archivo creado con ID: %s\n", $file->id);
}