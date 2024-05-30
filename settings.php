<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('mod_exportgrades_settings', get_string('pluginname', 'mod_exportgrades'));

    if ($ADMIN->fulltree) {
        $settings->add(new admin_setting_configselect(
            'mod_exportgrades/export_frequency',
            get_string('export_frequency', 'mod_exportgrades'),
            get_string('export_frequency_desc', 'exportgrades'),
            'daily',
            array(
                'daily' => get_string('daily', 'mod_exportgrades'),
                'weekly' => get_string('weekly', 'mod_exportgrades'),
                'monthly' => get_string('monthly', 'mod_exportgrades')
            )
        ));

        $settings->add(new admin_setting_configtext(
            'mod_exportgrades/drive_folder_id',
            get_string('drive_folder_id', 'exportgrades'),
            get_string('drive_folder_id_desc', 'exportgrades'),
            ''
        ));

        $settings->add(new admin_setting_configtextarea(
            'mod_exportgrades/drive_service_account_credentials',
            get_string('drive_service_account_credentials', 'exportgrades'),
            get_string('drive_service_account_credentials_desc', 'exportgrades'),
            ''
        ));

        $ADMIN->add('modsettings', $settings);
    }
}
