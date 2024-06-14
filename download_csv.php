<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/mod/exportgrades/lib.php');

global $CFG;

$file = required_param('file', PARAM_RAW);
$filename = required_param('filename', PARAM_RAW);

// Decodificar la ruta del archivo
$temp_file = urldecode($file);

error_log("Verificando el archivo: $temp_file");

if (!file_exists($temp_file)) {
    die('El archivo no existe.');
}

// Configurar headers para descargar el archivo
header('Content-Type: text/csv');
header("Content-Disposition: attachment; filename=\"{$filename}\"");
header('Content-Length: ' . filesize($temp_file));
readfile($temp_file);
unlink($temp_file);
exit();
?>
