<?php

namespace mod_exportgrades\task;

use core\task\scheduled_task;

class grade_export_task extends scheduled_task {

    public function get_name() {
        return get_string('grade_export_task', 'mod_exportgrades');
    }

    public function execute() {
        global $DB, $CFG;

        error_log("grade_export_task: Inicio de la ejecución de la tarea.");

        require_once($CFG->dirroot . '/mod/exportgrades/lib.php');

        // Obtener el contexto y los cursos
        $context = $this->determine_context();
        //error_log("grade_export_task: Contexto determinado: " . json_encode($context));

        // Obtener la configuración del cliente de Google Drive
        $drive_folder_id = get_config('mod_exportgrades', 'drive_folder_id');
        $drive_service_account_credentials = get_config('mod_exportgrades', 'drive_service_account_credentials');


        $courses = $this->get_courses($context);
        error_log("grade_export_task: Cursos obtenidos: " . json_encode($courses));

        $export_frequency = get_config('mod_exportgrades', 'export_frequency');
        $last_export_time = get_config('mod_exportgrades', 'last_export_time');
        $current_time = time();

        error_log("grade_export_task: Frecuencia de exportación: $export_frequency, Último tiempo de exportación: $last_export_time, Tiempo actual: $current_time");

        // Comprobamos si debemos exportar
        if ($this->should_export($export_frequency, $last_export_time, $current_time)) {
            foreach ($courses as $course) {
                error_log("grade_export_task: Exportando calificaciones para el curso ID: " . $course->id);

                // Generar el archivo CSV y obtener información del archivo
                $file_info = export_selected_grades_to_csv($course->id);
                if ($file_info) {
                    $filepath = $file_info['temp_file'];
                    $filename = $file_info['filename'];

                    error_log("grade_export_task: Archivo CSV generado: $filepath, Nombre del archivo: $filename");

                    // Directorio destino para mover los archivos CSV
                    $destination_directory = 'C:\\Users\\marce\\Downloads\\';

                    // Mover el archivo CSV al directorio destino
                    $destination_path = $destination_directory . $filename; 

                    if (rename($filepath, $destination_path)) {
                        // Éxito al mover el archivo, aquí puedes registrar o realizar otras acciones necesarias
                        error_log("grade_export_task: Archivo CSV movido correctamente a $destination_path");
                    } else {
                        // Manejar errores si no se pudo mover el archivo
                        error_log("grade_export_task: Error al mover el archivo CSV a $destination_path");
                    }

                    // Subir el archivo a Google Drive
                      try {
                        uploadToGoogleDrive($destination_directory, $filename, $drive_service_account_credentials, $drive_folder_id, $course);
                        error_log("Archivo CSV subido a Google Drive: $filename");
                      } catch (Exception $e) {
                        error_log("Error al subir el archivo CSV a Google Drive: " . $e->getMessage());
                        }

                    // Registrar el último tiempo de exportación
                    set_config('last_export_time', $current_time, 'mod_exportgrades');
                } else {
                    error_log("grade_export_task: Error al generar el archivo CSV para el curso ID: " . $course->id);
                }
            }
        } else {
            error_log("grade_export_task: No es necesario exportar en este momento.");
        }
    }

    public function determine_context() {
        global $PAGE;

        if ($PAGE->context->contextlevel == CONTEXT_COURSE) {
            return (object)['type' => 'course', 'id' => $PAGE->context->instanceid];
        } else {
            return (object)['type' => 'site', 'id' => null];
        }
    }

    public function get_courses($context) {
        global $DB;

        if ($context->type == 'course') {
            return [$DB->get_record('course', ['id' => $context->id])];
        } elseif ($context->type == 'site') {
            return $DB->get_records('course', []);
        }

        return [];
    }

    private function should_export($export_frequency, $last_export_time, $current_time) {
        // Lógica para determinar si se debe exportar
        return ($current_time - $last_export_time) >= $export_frequency;
    }
}

?>
