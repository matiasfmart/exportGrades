<?php

require_once($CFG->dirroot . '/mod/exportgrades/vendor/autoload.php');

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

function obtener_notas_curso($courseid) {
    global $DB;

    $sql = "SELECT u.id AS id_alumno, CONCAT(u.firstname, ' ', u.lastname) AS nombre_completo, c.id AS id_curso, c.fullname AS curso,
            gi.itemname AS item, COALESCE(gg.finalgrade, 'Sin calificar') AS nota_item,
            AVG(gg.finalgrade) OVER (PARTITION BY u.id, c.id) AS nota_final
            FROM {user} u
            JOIN {role_assignments} ra ON ra.userid = u.id
            JOIN {context} ctx ON ctx.id = ra.contextid AND ctx.contextlevel = 50
            JOIN {course} c ON c.id = ctx.instanceid
            JOIN {grade_items} gi ON gi.courseid = c.id AND gi.itemtype != 'course'
            LEFT JOIN {grade_grades} gg ON gg.itemid = gi.id AND gg.userid = u.id
            WHERE c.id = :courseid AND ra.roleid = (SELECT id FROM {role} WHERE shortname = 'student')
            GROUP BY u.id, c.id, gi.id, gg.finalgrade
            ORDER BY u.id, gi.itemname";

    // Use get_recordset_sql() para manejar registros sin clave única.
    return $DB->get_recordset_sql($sql, ['courseid' => $courseid]);
}


function get_course_categories_tree($courseid) {
    global $DB;
    $categories = [];

    if ($courseid <= 0) {
        throw new moodle_exception('Invalid course ID');
    }

    $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
    if (!$course) {
        throw new moodle_exception('No se puede encontrar el curso en la base de datos');
    }

    // Obtener la categoría del curso
    $category = $DB->get_record('course_categories', ['id' => $course->category], '*', MUST_EXIST);
    if (!$category) {
        throw new moodle_exception('No se puede encontrar la categoría del curso en la base de datos. ID de categoría: ' . $course->category);
    }

    // Recorrer las categorías hasta la raíz
    while ($category) {
        $categories[] = format_string($category->name);
        if ($category->parent == 0) break;
        $category = $DB->get_record('course_categories', ['id' => $category->parent], '*', MUST_EXIST);
    }

    if (empty($categories)) {
        throw new moodle_exception('No se puede encontrar registro de datos en la tabla course_categories de la base de datos.');
    }

    return array_reverse($categories); // Esto asegura que la raíz esté primero
}

function export_selected_grades_to_csv($courseid) {
    global $DB;

    $grades = obtener_notas_curso($courseid);

    if (empty($grades)) {
        return false;
    }

    $categories = get_course_categories_tree($courseid);
    $category_path = implode('_', $categories);

    $date = new DateTime();
    $datetime = $date->format('Ymd_His');
    $filename = "{$category_path}_{$datetime}.csv";

    $temp_file = tempnam(sys_get_temp_dir(), 'export_grades_');
    $handle = fopen($temp_file, 'w');
    if (!$handle) {
        die('No se pudo abrir el archivo temporal para escritura.');
    }

    $headers = ['ID de Alumno', 'Nombre y Apellido', 'ID del Curso', 'Curso', 'Tarea', 'Nota', 'Nota Final'];
    fputcsv($handle, $headers);

    $current_alumno_id = null;
    $current_curso_id = null;
    foreach ($grades as $grade) {
        if ($current_alumno_id !== $grade->id_alumno || $current_curso_id !== $grade->id_curso) {
            if ($current_alumno_id !== null) {
                // Agregar fila de promedio del alumno anterior
                fputcsv($handle, [$current_alumno_id, $prev_nombre, $current_curso_id, $prev_curso, 'Promedio', '', $prev_nota_final]);
            }
            $current_alumno_id = $grade->id_alumno;
            $current_curso_id = $grade->id_curso;
        }

        $data = [
            $grade->id_alumno,
            $grade->nombre_completo,
            $grade->id_curso,
            $grade->curso,
            $grade->item,
            $grade->nota_item,
            ''  // La nota final se agrega al final de cada grupo de estudiantes
        ];
        fputcsv($handle, $data);
        $prev_nombre = $grade->nombre_completo;
        $prev_curso = $grade->curso;
        $prev_nota_final = $grade->nota_final;
    }

    // Última fila para el último alumno
    if ($current_alumno_id !== null) {
        fputcsv($handle, [$current_alumno_id, $prev_nombre, $current_curso_id, $prev_curso, 'Promedio', '', $prev_nota_final]);
    }

    fclose($handle);
    error_log("Archivo CSV generado: $temp_file con nombre: $filename");

    return ['temp_file' => $temp_file, 'filename' => $filename];
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


?>
