<?php
require_once('../../config.php');
require_login();

$action = required_param('action', PARAM_ALPHA);
$groupid = optional_param('groupid', 0, PARAM_INT);

// Manejar la acción para obtener usuarios por grupo
if ($action === 'get_users_by_group') {
    if ($groupid > 0) {
        // Obtener usuarios según el grupo seleccionado
        $users = get_users_by_course_and_group($selected_courseid, $groupid);

        // Preparar respuesta en formato JSON
        $response = array();
        foreach ($users as $user) {
            $response['users'][$user->id] = fullname($user);
        }

        echo json_encode($response);
    } else {
        echo json_encode(array('users' => array()));  // Devolver un arreglo vacío si no se especifica un grupo
    }
    exit;
}
 