<?php
require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require_once($CFG->dirroot . '/group/lib.php');


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

// Incluir CSS de Select2
echo '<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />';

// Incluir jQuery (si no está incluido ya) y Select2 JS
echo '<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>';
echo '<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>';

// Script para inicializar Select2
echo '<script>
$(document).ready(function() {
    $("#selected_users").select2({
        placeholder: "Select users",
        allowClear: true
    });

    $("#groupid").change(function() {
        var groupid = $(this).val();
        $.ajax({
            url: "get_users_by_group.php",
            method: "GET",
            data: {groupid: groupid, courseid: ' . $course->id . '},
            success: function(data) {
                var users = JSON.parse(data);
                var options = "";
                for (var userid in users) {
                    options += "<option value=\"" + userid + "\">" + users[userid] + "</option>";
                }
                $("#selected_users").html(options);
                $("#selected_users").trigger("change"); // Refresh Select2
            }
        });
    });
});
</script>';

// Obtener configuraciones
$drive_folder_id = get_config('mod_exportgrades', 'drive_folder_id');
$drive_service_account_credentials = get_config('mod_exportgrades', 'drive_service_account_credentials');
$export_directory = get_config('mod_exportgrades', 'exportdirectory'); 

// Obtener los grupos existentes
$groups = get_all_groups_menu();

// Obtener el grupo seleccionado 
$selected_groupid = optional_param('groupid', 0, PARAM_INT);

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $export_frequency = optional_param('export_frequency', 'daily', PARAM_TEXT);
    $drive_folder_id = optional_param('drive_folder_id', '', PARAM_TEXT);
    $selected_groupid = optional_param('groupid', 0, PARAM_INT);
     $selected_users = optional_param_array('selected_users', [], PARAM_INT);
     $export_directory = optional_param('export_directory', '', PARAM_TEXT);

   // Llama a la función con los usuarios seleccionados
    set_config('drive_folder_id', $drive_folder_id, 'mod_exportgrades');
  

    // Generar y redirigir al script de descarga
    $file_info = export_selected_grades_to_csv($course->id, $selected_users);
    $filepath = $file_info['temp_file'];
    $filename = $file_info['filename'];

        // Validar si export_directory está vacío
    if (empty($export_directory)) {
        // Redirigir directamente a la carga a Google Drive
        uploadToGoogleDrive($filepath, basename($filepath), $drive_service_account_credentials, $drive_folder_id, $course);
        redirect(new moodle_url('/mod/exportgrades/view.php', array('id' => $cm->id)));
        exit();
    }

        // Subir el archivo CSV a Google Drive si está configurado
    if (!empty($drive_folder_id)) {
        uploadToGoogleDrive($filepath, basename($filepath), $drive_service_account_credentials, $drive_folder_id, $course);
    }


    redirect(new moodle_url('/mod/exportgrades/download_csv.php', array('file' => urlencode($filepath), 'filename' => $filename)));
   
    

    ///validar este punto si me lo realiza igual a pesar de tener vacio el path
    if ($result) {
        $temp_file = $result['temp_file'];
        $filename = $result['filename'];
        // Redirigir al usuario al script de descarga con el nombre del archivo
        redirect(new moodle_url('/mod/exportgrades/download_csv.php', array('file' => $temp_file, 'filename' => $filename)));
       //redirect(new moodle_url('/mod/exportgrades/view.php', array('id' => $cm->id, 'file' => urlencode($filepath), 'filename' => $filename)));

        exit();
    } else {
        echo $OUTPUT->notification(get_string('no_grades_found', 'mod_exportgrades'), 'notifyproblem');
    }
    echo $OUTPUT->notification(get_string('changessaved'), 'notifysuccess');
}

// Obtener las opciones de usuario para el desplegable
$user_options = get_users_by_group($course->id, $selected_groupid);



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
    /* Estilo específico para el desplegable de grupos */
    select#groupid {
        max-width: 150px; /* Reducir la anchura máxima del desplegable */
    }

</style>';

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


//Desplegable de grupos

echo '<div class="form-group">';
echo '<label for="groupid">' . get_string('selectgroup', 'mod_exportgrades') . '</label>';
echo html_writer::select($groups, 'groupid', $selected_groupid, ['' => get_string('selectgroup', 'mod_exportgrades')], ['id' => 'groupid']);
echo '</div>';

// Desplegable para seleccionar usuarios/alumnos múltiples
echo '<div class="form-group">';
echo '<label for="selected_users">' . get_string('selectusers', 'mod_exportgrades') . '</label>';
echo '<select id="selected_users" name="selected_users[]" multiple class="form-control">';
foreach ($user_options as $userid => $username) {
    echo '<option value="' . $userid . '">' . $username . '</option>';
}
echo '</select>';
echo '</div>';


echo '<div class="form-group">';
echo '<input type="submit" value="' . get_string('savechanges', 'mod_exportgrades') . '">';
echo '</div>';

echo '</form>';

echo $OUTPUT->footer();




echo '<script>
$(document).ready(function() {
    $("#groupid").change(function() {
        var groupid = $(this).val();
        $.ajax({
            url: "get_users_by_group.php",
            method: "GET",
            data: {groupid: groupid, courseid: ' . $course->id . '},
            success: function(data) {
                var users = JSON.parse(data);
                var options = "";
                for (var userid in users) {
                    options += "<option value=\"" + userid + "\">" + users[userid] + "</option>";
                }
                $("#selected_users").html(options);
            }
        });
    });
});
</script>';

?>