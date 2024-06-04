<?php
require_once(__DIR__.'/../../config.php');

$file = optional_param('file', '', PARAM_TEXT);
$export_directory = get_config('mod_exportgrades', 'exportdirectory');

if (!empty($file)) {

    if (substr($export_directory, -1) !== DIRECTORY_SEPARATOR) {
        $export_directory .= DIRECTORY_SEPARATOR;
    }

    $filepath = $export_directory . $file;

    if (file_exists($filepath)) {
        // Forzar la descarga del archivo CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="' . basename($filepath) . '"');
        readfile($filepath);
        exit();
    } else {
        echo 'Error: El archivo no existe.';
    }
} else {
    echo 'Error: Nombre de archivo no proporcionado.';
}