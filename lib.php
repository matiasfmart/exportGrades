<?php
require_once (__DIR__ . '/vendor/autoload.php');
require_once($CFG->libdir.'/filelib.php');
defined('MOODLE_INTERNAL') || die();
define('CLIENT_SECRET_PATH', __DIR__ . '/config/client_secret.json');



function exportgrades_supports($feature)
{
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        default:
            return null;
    }
}

function exportgrades_add_instance($moduleinstance, $mform = null)
{
    global $DB;

    $moduleinstance->timecreated = time();

    $id = $DB->insert_record('exportgrades', $moduleinstance);

    return $id;
}

function exportgrades_update_instance($moduleinstance, $mform = null)
{
    global $DB;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

    return $DB->update_record('exportgrades', $moduleinstance);
}

function exportgrades_delete_instance($id)
{
    global $DB;

    $exists = $DB->get_record('exportgrades', array('id' => $id));
    if (!$exists) {
        return false;
    }

    $DB->delete_records('exportgrades', array('id' => $id));

    return true;
}

function obtener_notas_curso($courseid, $selected_users = null)
{
    global $DB;

    // Construcción de la consulta SQL
    
    $sql = "
        SELECT 
    u.id AS userid,
    u.lastname AS 'apellidos',
    u.firstname AS 'nombre',
    u.username AS 'nombre de usuario',
    u.institution AS 'institucion',
    u.department AS 'departamento',
    u.institution AS 'sede',
    u.department AS 'carrera',
   COALESCE(NULLIF(GROUP_CONCAT(class.data SEPARATOR ','), ''), '')  AS 'class',
 g.id AS groupid,
    g.name AS 'grupo',
    '10' AS 'asistencia',
    (SELECT gg.finalgrade 
     FROM mdl_grade_items gi 
     JOIN mdl_grade_grades gg ON gi.id = gg.itemid 
     WHERE gi.itemname = 'Carpeta Final del Proyecto (Documentación)' AND gg.userid = u.id) AS 'carpeta Final del Proyecto (Documentación)',
    (SELECT gg.finalgrade 
     FROM mdl_grade_items gi 
     JOIN mdl_grade_grades gg ON gi.id = gg.itemid 
     WHERE gi.itemname = 'Carpeta Final del Proyecto (Documentación) - Recuperatorio' AND gg.userid = u.id) AS 'carpeta Final del Proyecto (Documentación) - Recuperatorio',
    (SELECT gg.finalgrade 
     FROM mdl_grade_items gi 
     JOIN mdl_grade_grades gg ON gi.id = gg.itemid 
     WHERE gi.itemname = 'Carpeta del Programador' AND gg.userid = u.id) AS 'carpeta del Programador',
    (SELECT gg.finalgrade 
     FROM mdl_grade_items gi 
     JOIN mdl_grade_grades gg ON gi.id = gg.itemid 
     WHERE gi.itemname = 'Carpeta del Proyecto' AND gg.userid = u.id) AS 'carpeta del Proyecto',
    (SELECT gg.finalgrade 
     FROM mdl_grade_items gi 
     JOIN mdl_grade_grades gg ON gi.id = gg.itemid 
     WHERE gi.itemname = 'Conformación de los Grupos y Elección de 2 posibles Proyectos' AND gg.userid = u.id) AS 'conformación de los Grupos y Elección de 2 posibles Proyectos',
    c.fullname AS 'Curso',
    (SELECT gg.finalgrade 
     FROM mdl_grade_items gi 
     JOIN mdl_grade_grades gg ON gi.id = gg.itemid 
     WHERE gi.itemname = 'Entrega de Aplicativo (Entrega)' AND gg.userid = u.id) AS 'entrega de Aplicativo (Entrega)',
    (SELECT gg.finalgrade 
     FROM mdl_grade_items gi 
     JOIN mdl_grade_grades gg ON gi.id = gg.itemid 
     WHERE gi.itemname = 'Entrega de Aplicativo (Recuperatorio)' AND gg.userid = u.id) AS 'entrega de Aplicativo (Recuperatorio)',
    (SELECT gg.finalgrade 
     FROM mdl_grade_items gi 
     JOIN mdl_grade_grades gg ON gi.id = gg.itemid 
     WHERE gi.itemname = 'Nota Final 1°Llamado Diciembre' AND gg.userid = u.id) AS 'nota Final 1°Llamado Diciembre',
    (SELECT gg.finalgrade 
     FROM mdl_grade_items gi 
     JOIN mdl_grade_grades gg ON gi.id = gg.itemid 
     WHERE gi.itemname = 'Nota Final 1°Llamado Febrero' AND gg.userid = u.id) AS 'nota Final 1°Llamado Febrero',
    (SELECT gg.finalgrade 
     FROM mdl_grade_items gi 
     JOIN mdl_grade_grades gg ON gi.id = gg.itemid 
     WHERE gi.itemname = 'Nota Final 2°Llamado Diciembre' AND gg.userid = u.id) AS 'nota Final 2°Llamado Diciembre',
    (SELECT gg.finalgrade 
     FROM mdl_grade_items gi 
     JOIN mdl_grade_grades gg ON gi.id = gg.itemid 
     WHERE gi.itemname = 'Nota Final 2°Llamado Febrero' AND gg.userid = u.id) AS 'nota Final 2°Llamado Febrero',
    (SELECT gg.finalgrade 
     FROM mdl_grade_items gi 
     JOIN mdl_grade_grades gg ON gi.id = gg.itemid 
     WHERE gi.itemname = 'Nota Final Cursada (REQUERIDO POR LA COORDINACIÓN)' AND gg.userid = u.id) AS 'nota Final Cursada (REQUERIDO POR LA COORDINACIÓN)',
    (SELECT gg.finalgrade 
     FROM mdl_grade_items gi 
     JOIN mdl_grade_grades gg ON gi.id = gg.itemid 
     WHERE gi.itemname = 'Nota Final Llamado Julio' AND gg.userid = u.id) AS 'nota Final Llamado Julio',
    (SELECT gg.finalgrade 
     FROM mdl_grade_items gi 
     JOIN mdl_grade_grades gg ON gi.id = gg.itemid 
     WHERE gi.itemname = 'Presentacion Proyecto Belgrano' AND gg.userid = u.id) AS 'presentacion Proyecto Belgrano'
FROM 
    mdl_user u
LEFT JOIN 
    mdl_user_enrolments ue ON ue.userid = u.id
LEFT JOIN 
    mdl_enrol e ON e.id = ue.enrolid
LEFT JOIN 
    mdl_course c ON c.id = e.courseid
LEFT JOIN 
    mdl_groups_members gm ON gm.userid = u.id
LEFT JOIN 
    mdl_groups g ON g.id = gm.groupid
LEFT JOIN 
    mdl_user_info_data class ON class.userid = u.id
LEFT JOIN 
    mdl_user_info_field class_field ON class.fieldid = class_field.id AND class_field.shortname = 'class'
LEFT JOIN 
    {role_assignments} ra ON ra.userid = u.id
LEFT JOIN 
    {context} ctx ON ctx.id = ra.contextid AND ctx.contextlevel = 50
WHERE 
    u.deleted = 0 
    AND c.id = :courseid 
    AND ra.roleid = (SELECT id FROM {role} WHERE shortname = 'student')";

    // Si se pasaron usuarios seleccionados, añadir filtro por esos usuarios
  
    if (!empty($selected_users)) {
        $user_ids = implode(',', array_map('intval', $selected_users));
        $sql .= " AND u.id IN ($user_ids)";
    }
     $sql .= " 
        GROUP BY u.id, c.id
        ORDER BY u.id
    ";
   

    return $DB->get_recordset_sql($sql, ['courseid' => $courseid]);





}


function export_selected_grades_to_csv($courseid, $selected_users = null)
{
    global $DB;

    $grades = obtener_notas_curso($courseid, $selected_users);

    if (empty($grades)) {
        error_log("export_selected_grades_to_csv: No se encontraron notas para el curso ID: $courseid");
        return false;
    }

    

    // Obtener el curso
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);


    $date = new DateTime();
    $datetime = $date->format('Ymd_His');
   
    $filename = "{$course->shortname}_{$course->fullname}_{$datetime}.csv";

    $temp_file = tempnam(sys_get_temp_dir(), 'export_grades_');
    $handle = fopen($temp_file, 'w');
    if (!$handle) {
        error_log("export_selected_grades_to_csv: No se pudo abrir el archivo temporal para escritura.");
        return false;
    }

    $headers = [
        'userid',
        'apellidos',
        'nombre',
        'nombre de usuario',
        'institucion',
        'departamento',
        'sede',
        'carrera',
        'class',
        'groupid',
        'grupo',
        'asistencia',
        'carpeta Final del Proyecto (Documentación)',
        'carpeta Final del Proyecto (Documentación) - Recuperatorio',
        'carpeta del Programador',
        'carpeta del Proyecto',
        'conformación de los Grupos y Elección de 2 posibles Proyectos',
        'entrega de Aplicativo (Entrega)',
        'entrega de Aplicativo (Recuperatorio)',
        'nota Final 1°Llamado Diciembre',
        'nota Final 1°Llamado Febrero',
        'nota Final 2°Llamado Diciembre',
        'nota Final 2°Llamado Febrero',
        'nota Final Cursada (REQUERIDO POR LA COORDINACIÓN)',
        'nota Final Llamado Julio',
        'presentacion Proyecto Belgrano'
    ];

    fputcsv($handle, $headers);


    foreach ($grades as $grade) {
        $data = [
            $grade->{'userid'},
            $grade->{'apellidos'},
            $grade->{'nombre'},
            $grade->{'nombre de usuario'},
            $grade->{'institucion'},
            $grade->{'departamento'},
            $grade->{'sede'},
            $grade->{'carrera'},
            $grade->{'class'},
            $grade->{'groupid'},
            $grade->{'grupo'},
            $grade->{'asistencia'},
            isset($grade->{'carpeta Final del Proyecto (Documentación)'}) ? $grade->{'carpeta Final del Proyecto (Documentación)'} : '-',
            isset($grade->{'carpeta Final del Proyecto (Documentación) - Recuperatorio'}) ? $grade->{'carpeta Final del Proyecto (Documentación) - Recuperatorio'} : '-',
            isset($grade->{'carpeta del Programador'}) ? $grade->{'carpeta del Programador'} : '-',
            isset($grade->{'carpeta del Proyecto'}) ? $grade->{'carpeta del Proyecto'} : '-',
            isset($grade->{'conformación de los Grupos y Elección de 2 posibles Proyectos'}) ? $grade->{'conformación de los Grupos y Elección de 2 posibles Proyectos'} : '-',
            isset($grade->{'entrega de Aplicativo (Entrega)'}) ? $grade->{'entrega de Aplicativo (Entrega)'} : '-',
            isset($grade->{'entrega de Aplicativo (Recuperatorio)'}) ? $grade->{'entrega de Aplicativo (Recuperatorio)'} : '-',
            isset($grade->{'nota Final 1°Llamado Diciembre'}) ? $grade->{'nota Final 1°Llamado Diciembre'} : '-',
            isset($grade->{'nota Final 1°Llamado Febrero'}) ? $grade->{'nota Final 1°Llamado Febrero'} : '-',
            isset($grade->{'nota Final 2°Llamado Diciembre'}) ? $grade->{'nota Final 2°Llamado Diciembre'} : '-',
            isset($grade->{'nota Final 2°Llamado Febrero'}) ? $grade->{'nota Final 2°Llamado Febrero'} : '-',
            isset($grade->{'nota Final Cursada (REQUERIDO POR LA COORDINACIÓN)'}) ? $grade->{'nota Final Cursada (REQUERIDO POR LA COORDINACIÓN)'} : '-',
            isset($grade->{'nota Final Llamado Julio'}) ? $grade->{'nota Final Llamado Julio'} : '-',
            isset($grade->{'presentacion Proyecto Belgrano'}) ? $grade->{'presentacion Proyecto Belgrano'} : '-'
        ];
        fputcsv($handle, $data);
    }


    fclose($handle);
    error_log("export_selected_grades_to_csv: Archivo CSV generado en $temp_file con nombre: $filename");

    return ['temp_file' => $temp_file, 'filename' => $filename];
}


// Función para obtener la jerarquía completa del curso
function getCourseHierarchyForDrive($courseid)
{
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
function configureGoogleClient($client_secret_path, $tokenPath) {
    $client = new Google_Client();
    
    if (file_exists($client_secret_path)) {
        $client->setAuthConfig($client_secret_path);
    } else {
        throw new \Exception("Error: archivo client_secret.json no encontrado en $client_secret_path");
    }
    
    $client->addScope(Google_Service_Drive::DRIVE_FILE);
    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');
    
    if (file_exists($tokenPath)) {
        $accessToken = json_decode(file_get_contents($tokenPath), true);
        if (json_last_error() === JSON_ERROR_NONE && isset($accessToken['access_token']) && isset($accessToken['refresh_token'])) {
            $client->setAccessToken($accessToken);
        } else {
            $accessToken = null;
        }
    } else {
        $accessToken = null;
    }
    
    if (!$accessToken) {
        printf("Token inválido o no encontrado. Por favor, visita la siguiente URL y autoriza el acceso:\n%s\n", $client->createAuthUrl());
        $authCode = trim(fgets(STDIN));
        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
        $client->setAccessToken($accessToken);

        if (!file_exists(dirname($tokenPath))) {
            mkdir(dirname($tokenPath), 0700, true);
        }
        file_put_contents($tokenPath, json_encode($client->getAccessToken()));
    }
    
    if ($client->isAccessTokenExpired()) {
        refreshToken($client, $tokenPath);
    }

    return $client;
}

function refreshToken($client, $tokenPath) {
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

function verifyCourse($course) {
    if (isset($course->fullname)) {
        printf("Nombre del curso: %s\n", $course->fullname);
    } else {
        throw new \Exception("El objeto \$course no contiene el campo fullname: $course");
    }
}

function getOrCreateFolder($service, $folderName, $parentFolderId) {
    printf("Buscando o creando carpeta: %s\n", $folderName);
    $response = $service->files->listFiles(
        array(
            'q' => "name = '$folderName' and mimeType = 'application/vnd.google-apps.folder' and '$parentFolderId' in parents and trashed = false",
            'spaces' => 'drive',
            'fields' => 'files(id, name)',
        )
    );

    if (count($response->files) > 0) {
        printf("Carpeta encontrada: %s (ID: %s)\n", $folderName, $response->files[0]->id);
        return $response->files[0]->id;
    } else {
        $folderMetadata = new Google_Service_Drive_DriveFile(
            array(
                'name' => $folderName,
                'mimeType' => 'application/vnd.google-apps.folder',
                'parents' => array($parentFolderId)
            )
        );

        $folder = $service->files->create(
            $folderMetadata,
            array(
                'fields' => 'id'
            )
        );

        printf("Carpeta creada: %s (ID: %s)\n", $folderName, $folder->id);
        return $folder->id;
    }
}

function getCourseHierarchyFolders($service, $courseHierarchy, $drive_folder_id) {
    $currentFolderId = $drive_folder_id;
    $folders = explode(' > ', $courseHierarchy);

    foreach ($folders as $folderName) {
        $currentFolderId = getOrCreateFolder($service, $folderName, $currentFolderId);
    }

    return $currentFolderId;
}

function moveFilesToHistoric($service, $currentFolderId) {
    $historicFolderId = getOrCreateFolder($service, "Historico", $currentFolderId);

    $response = $service->files->listFiles(
        array(
            'q' => "'$currentFolderId' in parents and mimeType != 'application/vnd.google-apps.folder' and trashed = false",
            'spaces' => 'drive',
            'fields' => 'files(id, name, parents)',
        )
    );

    foreach ($response->files as $file) {
        $fileId = $file->id;
        $service->files->update(
            $fileId,
            new Google_Service_Drive_DriveFile(),
            array(
                'addParents' => $historicFolderId,
                'removeParents' => $currentFolderId,
                'fields' => 'id, parents'
            )
        );
        printf("Archivo movido a 'Historico': %s\n", $file->name);
    }

    return $historicFolderId;
}

function uploadFile($service, $filePath, $fileName, $parentFolderId) {
    $fileMetadata = new Google_Service_Drive_DriveFile(
        array(
            'name' => $fileName,
            'parents' => array($parentFolderId)
        )
    );

    $content = file_get_contents($filePath);
    if ($content === false) {
        throw new \Exception("No se pudo leer el contenido del archivo: $filePath");
    }

    $file = $service->files->create(
        $fileMetadata,
        array(
            'data' => $content,
            'mimeType' => 'text/csv',
            'uploadType' => 'multipart',
            'fields' => 'id'
        )
    );

    printf("Archivo subido: %s (ID: %s)\n", $fileName, $file->id);
}

function deleteExistingCourseFiles($service, $yearFolderId, $pattern) {
    printf("Buscando archivos para eliminar con patrón: %s\n", $pattern);
    $response = $service->files->listFiles(array(
        'q' => "'$yearFolderId' in parents and mimeType != 'application/vnd.google-apps.folder' and name contains '$pattern' and trashed = false",
        'spaces' => 'drive',
        'fields' => 'files(id, name, parents)',
    ));

    foreach ($response->files as $file) {
        $service->files->delete($file->id);
        printf("Archivo eliminado: %s (ID: %s)\n", $file->name, $file->id);
    }
}

function getOrCreateCurrentYearFolder($service, $drive_folder_id) {
    $currentYear = date("Y");
    $yearFolderId = null;

    $response = $service->files->listFiles(
        array(
            'q' => "name contains '$currentYear' and mimeType = 'application/vnd.google-apps.folder' and trashed = false",
            'spaces' => 'drive',
            'fields' => 'files(id, name)',
        )
    );

    if (count($response->files) > 0) {
        foreach ($response->files as $folder) {
            if (strpos($folder->name, (string) $currentYear) !== false) {
                $yearFolderId = $folder->id;
                break;
            }
        }
        printf("Carpeta del año actual encontrada con ID: %s\n", $yearFolderId);
    } else {
        $folderMetadata = new Google_Service_Drive_DriveFile(
            array(
                'name' => $currentYear,
                'mimeType' => 'application/vnd.google-apps.folder',
                'parents' => array($drive_folder_id)
            )
        );

        $folder = $service->files->create(
            $folderMetadata,
            array(
                'fields' => 'id'
            )
        );

        $yearFolderId = $folder->id;
        printf("Nueva carpeta del año actual creada con ID: %s\n", $yearFolderId);
    }

    return $yearFolderId;
}




function uploadToGoogleDrive($filePath, $fileName, $drive_service_account_credentials, $drive_folder_id, $course) {
    printf("drive_service_account_credentials %s\n", $drive_service_account_credentials);
    $client_secret_path = CLIENT_SECRET_PATH;
    $tokenPath = 'config/token.json';

    $client = configureGoogleClient($client_secret_path, $tokenPath);
    
    if (!file_exists($filePath) || !is_readable($filePath)) {
        throw new \Exception("El archivo no existe o no se puede leer: $filePath");
    }
    
    verifyCourse($course);

    $service = new Google_Service_Drive($client);
    $courseHierarchy = getCourseHierarchyForDrive($course->id);
    printf("Jerarquía del curso: %s\n", $courseHierarchy);

    $currentFolderId = getCourseHierarchyFolders($service, $courseHierarchy, $drive_folder_id);
    printf("ID de la carpeta actual: %s\n", $currentFolderId);

    moveFilesToHistoric($service, $currentFolderId);

    $yearFolderId = getOrCreateCurrentYearFolder($service, $drive_folder_id);
    printf("ID de la carpeta del año actual: %s\n", $yearFolderId);

    $pattern = "{$course->shortname}_{$course->fullname}";
    deleteExistingCourseFiles($service, $yearFolderId, $pattern);

    uploadFile($service, $filePath, $fileName, $yearFolderId);//subida a la carpeta anual de la carrera
    uploadFile($service, $filePath, $fileName, $currentFolderId); // subida a la carpeta del curso
}



//GRUPOSSS

function get_all_groups_menu()
{
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


function get_users_by_group($courseid, $groupid)
{
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

/**
 * Insertar un nuevo link en el menú de navegación del curso.
 *
 * @param navigation_node $frontpage Node representing the front page in the navigation tree.
 */
function mod_exportgrades_extend_navigation_frontpage(navigation_node $frontpage) {
    $frontpage->add(
        get_string('pluginname', 'mod_exportgrades'),
        new moodle_url('/mod/exportgrades/docs.php'),
        navigation_node::TYPE_CUSTOM,
    );
}

function mod_exportgrades_extend_navigation(global_navigation $root)
{
    $node = navigation_node::create(
        get_string('pluginname', 'mod_exportgrades'),
        new moodle_url('/mod/exportgrades/docs.php'),
    );

    $node->showinflatnavigation = true;
    $root->add_node($node);
}







