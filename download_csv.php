<?php
require_once(__DIR__.'/../../config.php');

$file = optional_param('file', '', PARAM_TEXT);
if (!empty($file)) {
    $filepath = "/Users/matiasmartinez/Downloads/" . $file;

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