<?php

defined('MOODLE_INTERNAL') || die();
define('CLIENT_SECRET_PATH', __DIR__ . '/config/client_secret.json');

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

function obtener_notas_curso($courseid,  $selected_users = null) {
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
            WHERE c.id = :courseid AND ra.roleid = (SELECT id FROM {role} WHERE shortname = 'student')";
            
            // Si se pasaron usuarios seleccionados, añadir filtro por esos usuarios
            if (!empty($selected_users)) {
                $user_ids = implode(',', array_map('intval', $selected_users));
                $sql .= " AND u.id IN ($user_ids)";
            }

            $sql .= " GROUP BY u.id, c.id, gi.id, gg.finalgrade
             ORDER BY u.id, gi.itemname";

    return $DB->get_recordset_sql($sql, ['courseid' => $courseid]);
}

function export_selected_grades_to_csv($courseid, $selected_users = null) {
    global $DB;

    $grades = obtener_notas_curso($courseid, $selected_users);

    if (empty($grades)) {
        error_log("export_selected_grades_to_csv: No se encontraron notas para el curso ID: $courseid");
        return false;
    }

    $date = new DateTime();
    $datetime = $date->format('Ymd_His');
    $filename = "grades_course_{$courseid}_{$datetime}.csv";

    $temp_file = tempnam(sys_get_temp_dir(), 'export_grades_');
    $handle = fopen($temp_file, 'w');
    if (!$handle) {
        error_log("export_selected_grades_to_csv: No se pudo abrir el archivo temporal para escritura.");
        return false;
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
    error_log("export_selected_grades_to_csv: Archivo CSV generado en $temp_file con nombre: $filename");

    return ['temp_file' => $temp_file, 'filename' => $filename];
}


// Función para obtener la jerarquía completa del curso
function getCourseHierarchyForDrive($courseid) {
    global $DB;

    // Obtener el curso
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

    $hierarchy = [];

    // Agregar la jerarquía de categorías
    $categoryid = $course->category;
    while (!empty($categoryid)) {
        $category = $DB->get_record('course_categories', array('id' => $categoryid), '*', MUST_EXIST);
        $hierarchy[] = $category->name;
        $categoryid = $category->parent;
    }

    // Agregar el nombre del curso al final
    $hierarchy[] = $course->fullname;

    return implode(' > ', $hierarchy);
}



//subida al drive

function uploadToGoogleDrive($filePath, $fileName, $drive_service_account_credentials, $drive_folder_id,$course) {
    $client = new Google_Client();
    // Utilizar la constante definida para la ruta del client_secret.json
    $client_secret_path = CLIENT_SECRET_PATH;
      // Verificar si el archivo client_secret.json existe
    if (file_exists($client_secret_path)) {
        // Configurar el cliente con el archivo client_secret.json
        $client->setAuthConfig($client_secret_path);
        //return $client;
    } else {
        throw new \Exception("Error: archivo client_secret.json no encontrado en $client_secret_path");
    }
  
 
    $client->addScope(Google_Service_Drive::DRIVE_FILE);
    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');
     
    if (!file_exists($filePath) || !is_readable($filePath)) {
        throw new \Exception("El archivo no existe o no se puede leer: $filePath");
    }

    // Path to the token file
    
    $tokenPath = 'config/token.json';

if (file_exists($tokenPath)) {
        $accessToken = json_decode(file_get_contents($tokenPath), true);
        if (json_last_error() === JSON_ERROR_NONE && isset($accessToken['access_token']) && isset($accessToken['refresh_token'])) {
            $client->setAccessToken($accessToken);
        } else {
            // El token es inválido o no contiene los campos necesarios
            $accessToken = null;
        }
    } else {
        // El archivo token.json no existe
        $accessToken = null;
    }

    if (!$accessToken) {
        printf("Token inválido o no encontrado. Por favor, visita la siguiente URL y autoriza el acceso:\n%s\n", $client->createAuthUrl());
        $authCode = trim(fgets(STDIN));
        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
        $client->setAccessToken($accessToken);

        // Guarda el token en el archivo
        if (!file_exists(dirname($tokenPath))) {
            mkdir(dirname($tokenPath), 0700, true);
        }
        file_put_contents($tokenPath, json_encode($client->getAccessToken()));
    }
     




 
     // Verifica que el objeto $course contiene el campo fullname
    if (isset($course->fullname)) {
        printf("Nombre del curso: %s\n", $course->fullname);
    } else {
        throw new \Exception("El objeto \$course no contiene el campo fullname: $course");
    }

    // Obtener el id del curso y la jerarquia de carpetas
    $courseid = $course->id;
    printf("Id del curso: %s\n", $courseid);
    $courseHierarchy = getCourseHierarchyForDrive($course->id);
    if (isset($course->id)) {
    printf("Jerarquia del curso: %s\n", $courseHierarchy);
    }else {
        throw new \Exception("El objeto \$courseHierarchy no tiene contenido");
    }

    // Refresh the token if it's expired
    if ($client->isAccessTokenExpired()) {
        try {
            if ($client->getRefreshToken()) {
                $newAccessToken = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                $client->setAccessToken($newAccessToken);
            } else {
                throw new \Exception("No se encontró el token de actualización (refresh token).");
            }

            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        } catch (Exception $e) {
            throw new \Exception("Error al refrescar el token de acceso: " . $e->getMessage());
        }
    }

     // Verifica nuevamente el formato del token
    $tokenData = json_decode(file_get_contents($tokenPath), true);
    if (json_last_error() !== JSON_ERROR_NONE || !isset($tokenData['access_token'])) {
        throw new Exception('Formato de token inválido');
    }

    $service = new Google_Service_Drive($client);
    $currentFolderId = $drive_folder_id;

    // Separar la jerarquía por el separador '>'
    $folders = explode(' > ', $courseHierarchy);

    foreach ($folders as $folderName) {
        // Buscar si la carpeta ya existe
        $response = $service->files->listFiles(array(
            'q' => "name = '$folderName' and mimeType = 'application/vnd.google-apps.folder' and '$currentFolderId' in parents and trashed = false",
            'spaces' => 'drive',
            'fields' => 'files(id, name)',
        ));

        if (count($response->files) > 0) {
            // La carpeta ya existe, obtener su ID
            $currentFolderId = $response->files[0]->id;
            printf("La carpeta ya existe con ID: %s\n", $currentFolderId);
        } else {
            // Crear la carpeta si no existe
            $folderMetadata = new Google_Service_Drive_DriveFile(array(
                'name' => $folderName,
                'mimeType' => 'application/vnd.google-apps.folder',
                'parents' => array($currentFolderId)
            ));

            $folder = $service->files->create($folderMetadata, array(
                'fields' => 'id'
            ));

            $currentFolderId = $folder->id;
            printf("Nueva carpeta creada con ID: %s\n", $currentFolderId);
        }
    }

    // Crear la carpeta "Historico" dentro de la última carpeta del curso si no existe
    $historicFolderName = "Historico";
    $response = $service->files->listFiles(array(
        'q' => "name = '$historicFolderName' and mimeType = 'application/vnd.google-apps.folder' and '$currentFolderId' in parents and trashed = false",
        'spaces' => 'drive',
        'fields' => 'files(id, name)',
    ));

    $historicFolderId = null;
    if (count($response->files) > 0) {
        $historicFolderId = $response->files[0]->id;
        printf("La carpeta 'Historico' ya existe con ID: %s\n", $historicFolderId);
    } else {
        $folderMetadata = new Google_Service_Drive_DriveFile(array(
            'name' => $historicFolderName,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => array($currentFolderId)
        ));

        $folder = $service->files->create($folderMetadata, array(
            'fields' => 'id'
        ));

        $historicFolderId = $folder->id;
        printf("Nueva carpeta 'Historico' creada con ID: %s\n", $historicFolderId);
    }

    // Mover todos los archivos existentes a la carpeta "Historico"
    $response = $service->files->listFiles(array(
        'q' => "'$currentFolderId' in parents and mimeType != 'application/vnd.google-apps.folder' and trashed = false",
        'spaces' => 'drive',
        'fields' => 'files(id, name, parents)',
    ));

    foreach ($response->files as $file) {
        $fileId = $file->id;
        $service->files->update($fileId, new Google_Service_Drive_DriveFile(),array(
            'addParents' => $historicFolderId,
            'removeParents' => $currentFolderId,
            'fields' => 'id, parents'
        ));
        printf("Archivo movido a 'Historico': %s\n", $file->name);
    }



    // Subir el archivo a la carpeta final
    $fileMetadata = new Google_Service_Drive_DriveFile(array(
        'name' => $fileName,
        'parents' => array($currentFolderId)
    ));

    $content = file_get_contents($filePath);

    $file = $service->files->create($fileMetadata, array(
        'data' => $content,
        'mimeType' => 'text/csv',
        'uploadType' => 'multipart',
        'fields' => 'id'
    ));

    printf("File ID: %s\n", $file->id);

// --- Añadir el archivo a la carpeta del año actual ---

    // Obtener el año actual
    $currentYear = date("Y");

    // Buscar la carpeta del año actual
    $yearFolderId = null;

    $response = $service->files->listFiles(array(
        'q' => "name contains '$currentYear' and mimeType = 'application/vnd.google-apps.folder' and trashed = false",
        'spaces' => 'drive',
        'fields' => 'files(id, name)',
    ));

    if (count($response->files) > 0) {
        // La carpeta del año actual ya existe
        foreach ($response->files as $folder) {
            if (strpos($folder->name, (string)$currentYear) !== false) {
                $yearFolderId = $folder->id;
                break;
            }
        }
        printf("Carpeta del año actual encontrada con ID: %s\n", $yearFolderId);
    } else {
        // Crear la carpeta del año actual si no existe
        $folderMetadata = new Google_Service_Drive_DriveFile(array(
            'name' => $currentYear, // Nombre ejemplo para nueva carpeta
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => array($drive_folder_id) // Especificar que la carpeta del año actual sea un subdirectorio de la carpeta raíz
        ));

        $folder = $service->files->create($folderMetadata, array(
            'fields' => 'id'
        ));

        $yearFolderId = $folder->id;
        printf("Nueva carpeta del año actual creada con ID: %s\n", $yearFolderId);
    }

    // Buscar y eliminar archivos existentes del curso en la carpeta del año actual
    $response = $service->files->listFiles(array(
        'q' => "'$yearFolderId' in parents and mimeType != 'application/vnd.google-apps.folder' and trashed = false and name contains '$course->fullname'",
        'spaces' => 'drive',
        'fields' => 'files(id, name, parents)',
    ));

    foreach ($response->files as $file) {
        $fileId = $file->id;
        $service->files->delete($fileId);
        printf("Archivo del curso eliminado en la carpeta del año actual: %s\n", $file->name);
    }

    // Subir el nuevo archivo a la carpeta del año actual
    $fileMetadata = new Google_Service_Drive_DriveFile(array(
        'name' => $fileName,
        'parents' => array($yearFolderId)
    ));

    $file = $service->files->create($fileMetadata, array(
        'data' => $content,
        'mimeType' => 'text/csv',
        'uploadType' => 'multipart',
        'fields' => 'id'
    ));

    printf("Nuevo archivo subido a la carpeta del año actual. File ID: %s\n", $file->id);
}


//obtener jerarquia de cursos
/*
function get_course_hierarchy() {
    global $DB;

    // Obtener todas las categorías (carreras)
    $categories = $DB->get_records('course_categories');
    $courses = $DB->get_records('course', array('visible' => 1)); // Obtener todos los cursos visibles

    $hierarchy = array();

    foreach ($categories as $category) {
        $hierarchy[$category->id] = array(
            'name' => $category->name,
            'courses' => array()
        );
    }

    foreach ($courses as $course) {
        if (isset($hierarchy[$course->category])) {
            $hierarchy[$course->category]['courses'][$course->id] = $course->fullname;
        }
    }

    return $hierarchy;
}
*/

//GRUPOSSS

function get_all_groups_menu() {
    global $DB;

    // Obtener todos los registros de grupos
    $groups = $DB->get_records_menu('groups', [], '', 'id, name');

    // Formatear los registros para el menú desplegable (id => nombre)
    $group_options = [];
    foreach ($groups as $groupid => $groupname) {
        $group_options[$groupid] = format_string($groupname);
    }

    return $group_options;
}



//ALUMNOS SEGUN MATERIA Y GRUPO ELEGIDO
/*
function get_users_by_course_and_group($courseid, $groupid) {
    global $DB;

    // Consulta para obtener los usuarios del curso y grupo especificados
    $sql_alumnos = "SELECT u.id, u.username, u.firstname, u.lastname
            FROM {user} u
            INNER JOIN {user_enrolments} ue ON u.id = ue.userid
            INNER JOIN {enrol} e ON ue.enrolid = e.id
            WHERE ue.status = 0
            AND e.courseid = :courseid
            AND ue.groupid = :groupid
            ORDER BY u.lastname, u.firstname";

$params = [
    'status' => ENROL_USER_ACTIVE,
    'courseid' => $courseid,
    'groupid' => $groupid,
];

    return $DB->get_records_sql($sql_alumnos, $params);
}
*/

/*
function ajax_get_users_by_course_and_group() {
    global $DB;

    // Obtener los parámetros de la solicitud AJAX
    $courseid = required_param('courseid', PARAM_INT);
    $groupid = required_param('groupid', PARAM_INT);

    // Consulta para obtener los usuarios del curso y grupo especificados
    $sql = "SELECT u.id, CONCAT(u.firstname, ' ', u.lastname) AS fullname
            FROM {user} u
            INNER JOIN {groups_members} gm ON gm.userid = u.id
            INNER JOIN {groups} g ON g.id = gm.groupid
            WHERE gm.groupid = :groupid
            AND u.id IN (
                SELECT gm.userid
                FROM {groups_members} gm
                INNER JOIN {groups} g ON g.id = gm.groupid
                WHERE g.id = :groupid
            )
            ORDER BY u.lastname, u.firstname";

    $params = [
        'groupid' => $groupid,
    ];

    $users = $DB->get_records_sql_menu($sql, $params);

    // Construir las opciones para el desplegable de usuarios
    $user_options = [];
    foreach ($users as $userid => $fullname) {
        $user_options[$userid] = format_string($fullname);
    }

    // Devolver las opciones como HTML para el desplegable de usuarios
    echo html_writer::select($user_options, 'user', '', ['' => get_string('all')]);

    exit;
}
*/
function get_all_users_menu($courseid) {
    global $DB;

    // Obtener todos los usuarios matriculados en el curso
    $users = $DB->get_records_menu('user', array('deleted' => 0), '', 'id, CONCAT(firstname, " ", lastname) AS fullname');

    // Formatear los usuarios para el menú desplegable (id => nombre completo)
    $user_options = [];
    foreach ($users as $userid => $fullname) {
        $user_options[$userid] = format_string($fullname);
    }

    return $user_options;
}

function get_users_by_group($courseid, $groupid) {
    global $DB;

    if ($groupid > 0) {
        $groupmembers = groups_get_members($groupid);
        $userids = array_column($groupmembers, 'id');
        $users = $DB->get_records_list('user', 'id', $userids);
    } else {
        // Si no se selecciona un grupo, obtener todos los usuarios del curso
        $users = get_enrolled_users(context_course::instance($courseid), '', 0, 'u.id, u.firstname, u.lastname', 'u.lastname, u.firstname');
    }

    $user_options = [];
    foreach ($users as $user) {
        $user_options[$user->id] = fullname($user);
    }

    return $user_options;
}



?>

