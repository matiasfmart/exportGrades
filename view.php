<?php
require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

$id = optional_param('id', 0, PARAM_INT);
$e = optional_param('e', 0, PARAM_INT);

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
$export_frequency = get_config('mod_exportgrades', 'export_frequency');
$drive_folder_id = get_config('mod_exportgrades', 'drive_folder_id');
$drive_service_account_credentials = get_config('mod_exportgrades', 'drive_service_account_credentials');

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $export_frequency = optional_param('export_frequency', 'daily', PARAM_TEXT);
    $drive_folder_id = optional_param('drive_folder_id', '', PARAM_TEXT);
    $drive_service_account_credentials = optional_param('drive_service_account_credentials', '', PARAM_RAW);

    set_config('export_frequency', $export_frequency, 'mod_exportgrades');
    set_config('drive_folder_id', $drive_folder_id, 'mod_exportgrades');
    set_config('drive_service_account_credentials', $drive_service_account_credentials, 'mod_exportgrades');

    // Generar el archivo CSV
    $filepath = export_selected_grades_to_csv($course->id);

    if ($filepath) {
        // Subir el archivo CSV a Google Drive
        uploadToGoogleDrive($filepath, basename($filepath), $drive_service_account_credentials, $drive_folder_id);

        // Redirigir al usuario al script de descarga con el nombre del archivo
        redirect('download_csv.php?file=' . urlencode(basename($filepath)));
        exit();
    } else {
        echo $OUTPUT->notification(get_string('no_grades_found', 'mod_exportgrades'), 'notifyproblem');
    }

    echo $OUTPUT->notification(get_string('changessaved'), 'notifysuccess');
}

// Mostrar formulario
echo '<form method="post">';
echo '<div>';
echo '<label for="export_frequency">' . get_string('frequency', 'mod_exportgrades') . '</label>';
echo '<select id="export_frequency" name="export_frequency">';
echo '<option value="daily"' . ($export_frequency === 'daily' ? ' selected' : '') . '>' . get_string('daily', 'mod_exportgrades') . '</option>';
echo '<option value="weekly"' . ($export_frequency === 'weekly' ? ' selected' : '') . '>' . get_string('weekly', 'mod_exportgrades') . '</option>';
echo '<option value="monthly"' . ($export_frequency === 'monthly' ? ' selected' : '') . '>' . get_string('monthly', 'mod_exportgrades') . '</option>';
echo '</select>';
echo '</div>';
echo '<div>';
echo '<label for="drive_folder_id">' . get_string('drivefolderid', 'mod_exportgrades') . '</label>';
echo '<input type="text" id="drive_folder_id" name="drive_folder_id" value="' . s($drive_folder_id) . '">';
echo '</div>';
echo '<div>';
echo '<label for="drive_service_account_credentials">' . get_string('drivecredentials', 'mod_exportgrades') . '</label>';
echo '<textarea id="drive_service_account_credentials" name="drive_service_account_credentials">' . s($drive_service_account_credentials) . '</textarea>';
echo '</div>';
echo '<div>';
echo '<input type="submit" value="' . get_string('savechanges') . '">';
echo '</div>';
echo '</form>';

echo $OUTPUT->footer();
