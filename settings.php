<?php
defined('MOODLE_INTERNAL') || die();
//global $PAGE;
require_once($CFG->dirroot.'/mod/exportgrades/lib.php');

if ($hassiteconfig) {
    $settings = new admin_settingpage('mod_exportgrades_settings', get_string('pluginname', 'mod_exportgrades'));

    if ($ADMIN->fulltree) {

        // Requisitos de recursos
       /*
        $PAGE->requires->jquery();
        $PAGE->requires->js('/mod/exportgrades/amd/src/admin.js');
        $PAGE->requires->css('/mod/exportgrades/styles/styles.css');
        $PAGE->requires->js_call_amd('mod_exportgrades/admin', 'init');
        $PAGE->requires->js('/mod/exportgrades/js/ajax_get_users.js');
        */
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


        // Opciones predefinidas para la hora
        $time_options = [
            '1' => '1',
            '2' => '2',
            '3' => '3',
            '4' => '4',
            '5' => '5',
            '6' => '6',
            '7' => '7',
            '8' => '8',
            '9' => '9',
            '10' => '10',
            '11' => '11',
            '12' => '12',
            '13' => '13',
            '14' => '14',
            '15' => '15',
            '16' => '16',
            '17' => '17',
            '18' => '18',
            '19' => '19'
        ];
        // Campo para configurar la hora (select)
        $settings->add(new admin_setting_configselect(
            'mod_exportgrades/hour',
            get_string('time', 'mod_exportgrades'), 
            get_string('selecttime', 'mod_exportgrades'),
            '12:00', // Valor por defecto
            $time_options // Opciones del select
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

        

        // Configuraciones Mensuales
        $settings->add(new admin_setting_configtext(
            'mod_exportgrades/monthly_day',
            get_string('dayofmonth', 'mod_exportgrades'),
            get_string('selectday', 'mod_exportgrades'),
            1, PARAM_INT, 'monthly-options hidden'
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

 
    // Agregar la configuración de página a la administración de Moodle
    $ADMIN->add('modsettings', $settings);


    }
}


?>

