<?php

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_login();

// Llama a la función que obtiene los usuarios según el curso y grupo seleccionados
ajax_get_users_by_course_and_group();

