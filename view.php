<?php
require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

global $DB, $OUTPUT, $PAGE;

$id = optional_param('id', 0, PARAM_INT);
$e = optional_param('e', 0, PARAM_INT);


$group = $DB->get_record('groups', array('id' => $instance->groupid), 'name');

if ($id) {
    $cm = get_coursemodule_from_id('exportgrades', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('exportgrades', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    $moduleinstance = $DB->get_record('exportgrades', array('id' => $e), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('exportgrades', $moduleinstance->id, $course->id, false, MUST_EXIST);
}

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

$event = \mod_exportgrades\event\course_module_viewed::create(array(
    'objectid' => $moduleinstance->id,
    'context' => $modulecontext
));
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('exportgrades', $moduleinstance);
$event->trigger();

$PAGE->set_url('/mod/exportgrades/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

echo $OUTPUT->header();

// Obtener configuraciones
$drive_folder_id = get_config('mod_exportgrades', 'drive_folder_id');
$drive_service_account_credentials = get_config('mod_exportgrades', 'drive_service_account_credentials');
$export_directory = get_config('mod_exportgrades', 'exportdirectory'); 

// Obtener los grupos existentes
$groups = $DB->get_records_menu('groups', array('courseid' => $course->id), '', 'id, name');

// Obtener el grupo seleccionado 
$selected_groupid = optional_param('groupid', 0, PARAM_INT);

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $export_frequency = optional_param('export_frequency', 'daily', PARAM_TEXT);
    $drive_folder_id = optional_param('drive_folder_id', '', PARAM_TEXT);
    $drive_service_account_credentials = optional_param('drive_service_account_credentials', '', PARAM_RAW);
    $selected_groupid = optional_param('groupid', 0, PARAM_INT);

    set_config('drive_folder_id', $drive_folder_id, 'mod_exportgrades');
    set_config('drive_service_account_credentials', $drive_service_account_credentials, 'mod_exportgrades');
  
    //set_config('groupid', $selected_groupid, 'mod_exportgrades');

    // Generar y redirigir al script de descarga
    $file_info = export_selected_grades_to_csv($course->id);
    $filepath = $file_info['temp_file'];
    $filename = $file_info['filename'];


    // Verificar el contenido del objeto $course
echo '<pre>';
print_r($course);
echo '</pre>';

    // Subir el archivo CSV a Google Drive
    uploadToGoogleDrive($filepath, basename($filepath), $drive_service_account_credentials, $drive_folder_id, $course);
  

    redirect(new moodle_url('/mod/exportgrades/download_csv.php', array('file' => urlencode($filepath), 'filename' => $filename)));

    if ($result) {
        $temp_file = $result['temp_file'];
        $filename = $result['filename'];
        // Redirigir al usuario al script de descarga con el nombre del archivo
        redirect(new moodle_url('/mod/exportgrades/download_csv.php', array('file' => $temp_file, 'filename' => $filename)));
        exit();
    } else {
        echo $OUTPUT->notification(get_string('no_grades_found', 'mod_exportgrades'), 'notifyproblem');
    }
    echo $OUTPUT->notification(get_string('changessaved'), 'notifysuccess');
}

// Incluye CSS personalizado para mejorar el formulario
echo '<style>
    form.custom-form {
        background-color: #f7f7f7;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .custom-form div {
        margin-bottom: 10px;
    }
    .custom-form label {
        font-weight: bold;
    }
    .custom-form input[type="text"], .custom-form select, .custom-form textarea {
        width: 100%;
        padding: 8px;
        border-radius: 4px;
        border: 1px solid #ccc;
    }
    .custom-form input[type="submit"] {
        background-color: #0056b3;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 4px;
        cursor: pointer;
    }
    .custom-form input[type="submit"]:hover {
        background-color: #004494;
    }
    .form-group {
        margin-bottom: 15px; /* Espaciado entre grupos de formularios */
    }
    label, .form-control {
        display: inline-block; /* Hacer que los elementos sean en línea para gestionar mejor su anchura */
        vertical-align: top;
    }
    label {
        width: 20%; /* Anchura del label */
        max-width: 180px; /* Anchura máxima para evitar que sea demasiado grande */
        min-width: 100px; /* Anchura mínima para mantener la consistencia */
        margin-right: 10px; /* Espacio entre el label y el control de entrada */
    }
    .form-control {
        width: calc(80% - 10px); /* Ocupar el resto del espacio disponible */
        max-width: 300px; /* Anchura máxima del input */
    }
    .hidden {
        display: none;
    }
</style>';

//Include jQuery
echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>';


// Formulario
echo '<form method="post" class="custom-form">';

echo '<div class="form-group">';
echo '<label for="export_directory">' . get_string('exportdirectory', 'mod_exportgrades') . '</label>';
echo '<input type="text" id="export_directory" name="export_directory" class="form-control" value="' . s($export_directory) . '">';
echo '</div>';

//Google Drive
echo '<div class="form-group">';
echo '<label for="drive_folder_id">' . get_string('drivefolderid', 'mod_exportgrades') . '</label>';
echo '<input type="text" id="drive_folder_id" name="drive_folder_id" class="form-control" value="' . s($drive_folder_id) . '">';
echo '</div>';

echo '<div>';
echo '<label for="drive_service_account_credentials">' . get_string('drivecredentials', 'mod_exportgrades') . '</label>';
echo '<input type="file" id="drive_service_account_credentials" name="drive_service_account_credentials" class="form-control">';
echo '</div>';

echo '<div class="form-group">';
echo '<input type="submit" value="' . get_string('savechanges', 'mod_exportgrades') . '">';
echo '</div>';

echo '</form>';

echo $OUTPUT->footer();