<?php
$file = '/mnt/data/grades_course_51_20240611_014007.csv';
$content = file_get_contents($file);
echo nl2br($content); // Imprimir contenido con saltos de línea
?>
