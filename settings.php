<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/exportgrades/lib.php');
require_once($CFG->dirroot . '/mod/exportgrades/classes/setting/drive_service_account_credentials.php');

if ($hassiteconfig) {
    $settings = new admin_settingpage('mod_exportgrades_settings', get_string('pluginname', 'mod_exportgrades'));

    if ($ADMIN->fulltree) {

        // Configuración de frecuencia
        $frequencyOptions = [
            'minutes' => get_string("minutes", 'mod_exportgrades'),
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
        $time_options = array_combine(range(1, 24), range(1, 24));
        // Campo para configurar la hora (select)
        $settings->add(new admin_setting_configselect(
            'mod_exportgrades/hour',
            get_string('time_hour', 'mod_exportgrades'),
            get_string('selecttime', 'mod_exportgrades'),
            '12', // Valor por defecto
            $time_options // Opciones del select
        ));

        // Opciones para los minutos (0 a 59)
        $time_options_minute = array_combine(range(0, 59), range(0, 59));
        // Campo para configurar los minutos (select)
        $settings->add(new admin_setting_configselect(
            'mod_exportgrades/minutes',
            get_string('time_minute', 'mod_exportgrades'),
            get_string('selectminute', 'mod_exportgrades'),
            '0', // Valor por defecto
            $time_options_minute // Opciones del select de minutos
        ));

    
       // Configuraciones Semanales
        $weekDays = [
            '1' => get_string('monday', 'mod_exportgrades'),
            '2' => get_string('tuesday', 'mod_exportgrades'),
            '3' => get_string('wednesday', 'mod_exportgrades'),
            '4' => get_string('thursday', 'mod_exportgrades'),
            '5' => get_string('friday', 'mod_exportgrades'),
            '6' => get_string('saturday', 'mod_exportgrades'),
            '0' => get_string('sunday', 'mod_exportgrades')
        ];
        $settings->add(new admin_setting_configselect(
            'mod_exportgrades/weekly_day',
            get_string('dayofweek', 'mod_exportgrades'),
            get_string('selectday', 'mod_exportgrades'),
            'lunes', // Valor por defecto
            $weekDays//, PARAM_NOTAGS, 'weekly-options hidden'
        ));

          // Configuraciones Mensuales
        $settings->add(new admin_setting_configtext(
            'mod_exportgrades/monthly_day',
            get_string('dayofmonth', 'mod_exportgrades'),
            get_string('selectday', 'mod_exportgrades'),
            1, // Valor por defecto
            PARAM_INT//, 'monthly-options hidden'
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


        // Agregar la configuración de página a la administración de Moodle
        $ADMIN->add('modsettings', $settings);

    }
}


?>

