<?php

defined('MOODLE_INTERNAL') || die();

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

    $sql = "SELECT u.id as id_alumno, CONCAT(u.firstname, ' ', u.lastname) as nombre_completo, c.fullname as curso,
            gi.itemname as item, gg.finalgrade as nota_final
            FROM {user} u
            JOIN {grade_grades} gg ON gg.userid = u.id
            JOIN {grade_items} gi ON gi.id = gg.itemid
            JOIN {course} c ON gi.courseid = c.id
            WHERE gi.courseid = :courseid AND gg.finalgrade IS NOT NULL";

    return $DB->get_records_sql($sql, ['courseid' => $courseid]);
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

    // Nombre del archivo CSV
    $date = new DateTime();
    $datetime = $date->format('Ymd_His');
    $filename = "{$category_path}_{$datetime}.csv";

    $temp_file = tempnam(sys_get_temp_dir(), 'export_grades_');
    $handle = fopen($temp_file, 'w');
    if (!$handle) {
        die('No se pudo abrir el archivo temporal para escritura.');
    }

    $headers = ['ID de Alumno', 'Nombre y Apellido de Alumno', 'Curso', 'Item', 'Nota Final'];
    fputcsv($handle, $headers);

    foreach ($grades as $alumno) {
        $data = [
            $alumno->id_alumno,
            $alumno->nombre_completo,
            $alumno->curso,
            $alumno->item,
            $alumno->nota_final
        ];
        fputcsv($handle, $data);
    }

    fclose($handle);

    // Añadir un mensaje de depuración
    error_log("Archivo CSV generado: $temp_file con nombre: $filename");

    return ['temp_file' => $temp_file, 'filename' => $filename];
}
?>
