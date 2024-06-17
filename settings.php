<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('mod_exportgrades_settings', get_string('pluginname', 'mod_exportgrades'));

    if ($ADMIN->fulltree) {
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

        $settings->add(new admin_setting_configtext(
            'mod_exportgrades/drive_folder_id',
            get_string('drivefolderid', 'exportgrades'),
            get_string('drivefolderid_desc', 'exportgrades'),
            ''
        ));

        $settings->add(new admin_setting_configtext(
            'mod_exportgrades/drive_service_account_credentials',
            get_string('drivecredentials', 'exportgrades'),
            get_string('drivecredentials_desc', 'exportgrades'),
            ''
        ));

        $settings->add(new admin_setting_configtext(
            'mod_exportgrades/exportdirectory',
            get_string('exportdirectory', 'mod_exportgrades'),
            get_string('exportdirectory_desc', 'mod_exportgrades'),
            ''
        ));

        // Añadir opción de selección de idioma
        $settings->add(new admin_setting_configselect(
            'mod_exportgrades/language',
            get_string('language', 'mod_exportgrades'),
            get_string('configlanguage_desc', 'mod_exportgrades'), // Corrige aquí para que coincida con tu archivo lang
            $current_language, 
            $langoptions
            
        ));

         // Añadir opciones de grupo
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

        // Añadir campo personalizado de autocompletado para usuarios
        $settings->add(new admin_setting_configtext(
            'mod_exportgrades/user_field',
            get_string('users', 'mod_exportgrades'),
            get_string('users_desc', 'mod_exportgrades'),
            '', // Optional default value
            array('disabled' => true) // Disable the text field
          ));

        $ADMIN->add('modsettings', $settings);
    }
}

function get_user_field_html() {
    global $PAGE;

    // Incluir JavaScript solo en la página de configuraciones relevantes
    $PAGE->requires->js_call_amd('mod_exportgrades/user_selector', 'init');

    // HTML para el campo de búsqueda de usuarios
    $html = '<input type="text" id="user_selector" />';
    $html .= '<div id="user_selector_results"></div>';

    return $html;
}