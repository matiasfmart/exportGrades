<?php
defined('MOODLE_INTERNAL') || die();
global $PAGE;
require_once($CFG->dirroot.'/mod/exportgrades/lib.php');

if ($hassiteconfig) {
    $settings = new admin_settingpage('mod_exportgrades_settings', get_string('pluginname', 'mod_exportgrades'));

    if ($ADMIN->fulltree) {


    // Añadir el código JavaScript antes de la configuración select de usuarios y grupos
          echo '<script>';
          echo 'var allUsersOptionText = "' . addslashes(get_string('all')) . '";';
          echo '</script>';


        // Requisitos de recursos
        $PAGE->requires->jquery();
        $PAGE->requires->js('/mod/exportgrades/amd/src/admin.js');
        $PAGE->requires->css('/mod/exportgrades/styles/styles.css');
        $PAGE->requires->js_call_amd('mod_exportgrades/admin', 'init');
        $PAGE->requires->js('/mod/exportgrades/js/ajax_get_users.js');

        // Configuración de frecuencia
        $frequencyOptions = [
            'daily' => get_string('daily', 'mod_exportgrades'),
            'weekly' => get_string('weekly', 'mod_exportgrades'),
            'monthly' => get_string('monthly', 'mod_exportgrades')
        ];
        $settings->add(new admin_setting_configselect(
            'mod_exportgrades/export_frequency',
            get_string('frequency', 'mod_exportgrades'),
            get_string('frequency_desc', 'mod_exportgrades'),
            'daily',
            $frequencyOptions
        ));

        // Configuraciones Diarias
        $settings->add(new admin_setting_configtime(
            'mod_exportgrades/daily_hour',
            'mod_exportgrades/daily_minute',
            get_string('time', 'mod_exportgrades'),
            get_string('selecttime', 'mod_exportgrades'),
            ['h' => 8, 'm' => 0], PARAM_INT, 'daily-options hidden'
        ));

        // Configuraciones Semanales
        $weekDays = [
            'lunes' => get_string('monday', 'mod_exportgrades'),
            'martes' => get_string('tuesday', 'mod_exportgrades'),
            'miércoles' => get_string('wednesday', 'mod_exportgrades'),
            'jueves' => get_string('thursday', 'mod_exportgrades'),
            'viernes' => get_string('friday', 'mod_exportgrades'),
            'sábado' => get_string('saturday', 'mod_exportgrades'),
            'domingo' => get_string('sunday', 'mod_exportgrades')
        ];
        $settings->add(new admin_setting_configselect(
            'mod_exportgrades/weekly_day',
            get_string('dayofweek', 'mod_exportgrades'),
            get_string('selectday', 'mod_exportgrades'),
            '', $weekDays, PARAM_NOTAGS, 'weekly-options hidden'
        ));

        $settings->add(new admin_setting_configtime(
            'mod_exportgrades/weekly_hour',
            'mod_exportgrades/weekly_minute',
            get_string('time', 'mod_exportgrades'),
            get_string('selecttime', 'mod_exportgrades'),
            ['h' => 8, 'm' => 0], PARAM_INT, 'weekly-options hidden'
        ));

        // Configuraciones Mensuales
        $settings->add(new admin_setting_configtext(
            'mod_exportgrades/monthly_day',
            get_string('dayofmonth', 'mod_exportgrades'),
            get_string('selectday', 'mod_exportgrades'),
            1, PARAM_INT, 'monthly-options hidden'
        )); 

        $settings->add(new admin_setting_configtime(
            'mod_exportgrades/monthly_hour', // Nombre de la configuración para la hora
            'mod_exportgrades/monthly_minute', // Nombre de la configuración para los minutos
            get_string('time', 'mod_exportgrades'), // Label de la configuración
            get_string('selecttime', 'mod_exportgrades'), // Descripción o texto de ayuda
            ['h' => 8, 'm' => 0], // Valores por defecto: 8 horas y 0 minutos
            PARAM_INT, // Tipo de parámetro
            'monthly-options hidden' // Clases CSS para controlar la visibilidad
        ));
        

        //Menu desplegable con las carreras y materias (muestra las categorias)
        $categories = \core_course_category::make_categories_list();
        $formatted_categories = [];
        foreach ($categories as $id => $name) {
        // Añade un guion por nivel de profundidad para entender la jerarquía
            $depth = substr_count($name, '/');
            $formatted_name = str_repeat('-', $depth) . ' ' . trim($name);
            $formatted_categories[$id] = $formatted_name;
        }
                    
        $settings->add(new admin_setting_configselect(
            'mod_exportgrades/category',
            get_string('category', 'mod_exportgrades'),
            get_string('selectcategory', 'mod_exportgrades'),  
            '',
            $formatted_categories
        ));
                    
        



        // Campo para el directorio de exportación
        $settings->add(new admin_setting_configtext(
            'mod_exportgrades/exportdirectory',
            get_string('exportdirectory', 'mod_exportgrades'),
            get_string('exportdirectory_desc', 'mod_exportgrades'),
            ''
        ));


        // Campo para ID de carpeta de Google Drive
        $settings->add(new admin_setting_configtext(
            'mod_exportgrades/drive_folder_id',
            get_string('drivefolderid', 'mod_exportgrades'),
            get_string('drivefolderid_desc', 'mod_exportgrades'),
            ''
        ));

        // Campo para cargar las credenciales del servicio de Google Drive
        $settings->add(new admin_setting_configstoredfile(
            'mod_exportgrades/drive_service_account_credentials',
            get_string('drivecredentials', 'mod_exportgrades'),
            get_string('drivecredentials_desc', 'mod_exportgrades'),
            'drivecredentials'  // Área de archivo en la que se almacenará el archivo
        ));

     // Obtener la jerarquía de cursos
        $course_hierarchy = get_course_hierarchy();

        // Crear el array de opciones para el desplegable
        $options = array();

        foreach ($course_hierarchy as $category) {
            foreach ($category['courses'] as $course_id => $course_name) {
                $options[$course_id] = $category['name'] . ' - ' . $course_name;
            }
        }

        $settings->add(new admin_setting_configselect(
            'mod_exportgrades/courseid',
            get_string('selectcourse', 'mod_exportgrades'),
            '',
            0,
            $options
        ));

        
        $ADMIN->add('modsettings', $settings);
    }




// Obtener las opciones del menú desplegable de grupos
$group_options = get_all_groups_menu();

// Agregar la configuración select para el grupo
$settings->add(new admin_setting_configselect(
    'mod_exportgrades/group',
    get_string('group', 'mod_exportgrades'),
    get_string('group_desc', 'mod_exportgrades'),
   '',
    $group_options
));

$settings->add($group_select);//agregado despues 16:08


//DESPLEGABLE DE USUARIOS SEGUN CURSO Y GRUPO SELECCIONADO


// Agregar la configuración select para el curso y grupo
$settings->add(new admin_setting_configselect(
    'mod_exportgrades/courseid',
    get_string('selectcourse', 'mod_exportgrades'),
    '',
    0,
    $options
));

$settings->add(new admin_setting_configselect(
    'mod_exportgrades/group',
    get_string('group', 'mod_exportgrades'),
    get_string('group_desc', 'mod_exportgrades'),
    '',
    $group_options
));

 // Contenedor para la lista de usuarios
 echo '<div id="users-dropdown-container">' . html_writer::select(array('' => get_string('all')), 'mod_exportgrades_users', '', array('' => get_string('all'))) . '</div>';

 // Agregar script JavaScript al final de la página
 $PAGE->requires->js_call_amd('mod_exportgrades/admin', 'init');

 // Agregar la configuración de página a la administración de Moodle
 $ADMIN->add('modsettings', $settings);



// Configurar la lista desplegable de usuarios
$settings->add(new admin_setting_configselect(
    'mod_exportgrades/users',
    get_string('users', 'mod_exportgrades'),
    get_string('users_desc', 'mod_exportgrades'),
    '',
    array('' => get_string('all'))  // Inicialmente solo una opción "Todos"
));

// Agregar script JavaScript al final de la página
$PAGE->requires->js_call_amd('mod_exportgrades/admin', 'init');

// Agregar la configuración de página a la administración de Moodle
$ADMIN->add('modsettings', $settings);



}

?>

