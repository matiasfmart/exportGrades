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


// function export_selected_grades_to_csv($courseid) {
//     global $DB;

//     // Consulta para obtener los campos itemid, userid y finalgrade de la tabla mdl_grade_grades
//     $sql = "SELECT itemid, userid, finalgrade
//             FROM {grade_grades}
//             WHERE itemid IN (
//                 SELECT id FROM {grade_items} WHERE courseid = :courseid
//             )";
//     $params = ['courseid' => $courseid];
//     $grades = $DB->get_records_sql($sql, $params);

//     if (empty($grades)) {
//         return false;
//     }

//     // Nombre del archivo CSV
//     $filename = "grades_course_{$courseid}_" . date('Ymd_His') . ".csv";

//     // Obtener la ruta del directorio de exportación desde la configuración
//     $export_directory = get_string('mod_exportgrades', 'exportdirectory');
//     if (empty($export_directory)) {
//         // Si la configuración no está definida, usar una ruta predeterminada
//         $export_directory = '/Users/matiasmartinez/Downloads/';
//     }

//     // Crear el directorio de exportación si no existe
//     if (!file_exists($export_directory)) {
//         mkdir($export_directory, 0777, true);
//     }

//     // Combinar la ruta del directorio y el nombre de archivo para obtener la ruta completa
//     $filepath = $export_directory . $filename;

//     // Abrir el archivo CSV para escribir
//     $file = fopen($filepath, 'w');

//     // Escribir la cabecera del CSV
//     $header = ['itemid', 'userid', 'finalgrade'];
//     fputcsv($file, $header);

//     // Escribir los datos de las calificaciones
//     foreach ($grades as $grade) {
//         $row = [
//             $grade->itemid,
//             $grade->userid,
//             $grade->finalgrade
//         ];
//         fputcsv($file, $row);
//     }

//     // Cerrar el archivo CSV
//     fclose($file);

//     return $filepath;
// }

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
        $export_directory = '/Users/matiasmartinez/Downloads';
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
