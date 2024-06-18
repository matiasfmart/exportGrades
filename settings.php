<?php
defined('MOODLE_INTERNAL') || die();


if ($hassiteconfig) {
    $settings = new admin_settingpage('mod_exportgrades_settings', get_string('pluginname', 'mod_exportgrades'));

    if ($ADMIN->fulltree) {

        $PAGE->requires->js_call_amd('mod_exportgrades/admin', 'init');

        $PAGE->requires->css('/mod/exportgrades/styles/styles.css');

        $settings->add(new admin_setting_configselect(
            'mod_exportgrades/export_frequency',
            get_string('frequency', 'mod_exportgrades'),
            get_string('frequency_desc', 'exportgrades'),
            'daily',
            array(
                'daily' => get_string('daily', 'mod_exportgrades'),
                'weekly' => get_string('weekly', 'mod_exportgrades'),
                'monthly' => get_string('monthly', 'mod_exportgrades')
            )
        ));

               //Configuración Diaria
                $settings->add(new admin_setting_heading('mod_exportgrades_daily_heading', get_string('dailysettings', 'mod_exportgrades'), ''));
                $settings->add(new admin_setting_configtime('mod_exportgrades/daily_hour', 'mod_exportgrades/daily_minute',
                    get_string('time', 'mod_exportgrades'), 
                    get_string('selecttime', 'mod_exportgrades'), 
                    array('h' => 8, 'm' => 0), PARAM_INT, 'daily-options hidden')); // Oculto por defecto
                //Configuración Semanal
                $settings->add(new admin_setting_heading('mod_exportgrades_weekly_heading', get_string('weeklysettings', 'mod_exportgrades'), ''));
                $settings->add(new admin_setting_configselect('mod_exportgrades/weekly_day',
                    get_string('dayofweek', 'mod_exportgrades'), 
                    get_string('selectday', 'mod_exportgrades'), 
                    '', [
                        'lunes' => get_string('monday', 'mod_exportgrades'),
                        'martes' => get_string('tuesday', 'mod_exportgrades'),
                        'miércoles' => get_string('wednesday', 'mod_exportgrades'),
                        'jueves' => get_string('thursday', 'mod_exportgrades'),
                        'viernes' => get_string('friday', 'mod_exportgrades'),
                        'sábado' => get_string('saturday', 'mod_exportgrades'),
                        'domingo' => get_string('sunday', 'mod_exportgrades')
                    ], PARAM_NOTAGS, 'weekly-options hidden')); // Oculto por defecto
                //Configuración Mensual
                $settings->add(new admin_setting_configtime('mod_exportgrades/weekly_hour', 'mod_exportgrades/weekly_minute',
                    get_string('time', 'mod_exportgrades'), 
                    get_string('selecttime', 'mod_exportgrades'), 
                    array('h' => 8, 'm' => 0), PARAM_INT, 'weekly-options hidden')); // Oculto por defecto

                $settings->add(new admin_setting_heading('mod_exportgrades_monthly_heading', get_string('monthlysettings', 'mod_exportgrades'), ''));
                $settings->add(new admin_setting_configtext('mod_exportgrades/monthly_day',
                    get_string('dayofmonth', 'mod_exportgrades'),
                    get_string('selectday', 'mod_exportgrades'), 
                    1, PARAM_INT, 'monthly-options hidden')); // Oculto por defecto

                $settings->add(new admin_setting_configtime('mod_exportgrades/monthly_hour', 'mod_exportgrades/monthly_minute',
                    get_string('time', 'mod_exportgrades'), 
                    get_string('selecttime', 'mod_exportgrades'), 
                    array('h' => 8, 'm' => 0), PARAM_INT, 'monthly-options hidden')); 

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
                    
        //Menu desplegable de grupo
        $settings->add(new admin_setting_configselect(
            'mod_exportgrades/group',
            get_string('group', 'mod_exportgrades'),
            get_string('group_desc', 'mod_exportgrades'),
            '',
            array(
                'todas' => get_string('all', 'mod_exportgrades'),
                'notas_finales' => get_string('finalgrades', 'mod_exportgrades'),
                'notas_belgrano' => get_string('belgranogrades', 'mod_exportgrades'),
                'notas_yatay' => get_string('yataygrades', 'mod_exportgrades')
            )
        ));

        // Campo de búsqueda de usuarios
        $settings->add(new admin_setting_configtext(
            'mod_exportgrades/user_field',
            get_string('users', 'mod_exportgrades'),
            get_string('users_desc', 'mod_exportgrades'),
            ''
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

        
        $ADMIN->add('modsettings', $settings);
    }
}
?>