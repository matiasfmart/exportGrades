<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/adminlib.php');

class mod_exportgrades_drive_service_account_credentials extends admin_setting_configstoredfile {

    public function validate($data) {
        global $USER, $CFG;

        // $context = context_system::instance();

        // Chequea si client_secret.json existe en /config
        $config_folder = $CFG->dirroot . '/mod/exportgrades/config';
        $client_secret_path = $config_folder . '/client_secret.json';
        $client_secret_exists = file_exists($client_secret_path);

        // SI client_secret.json no exist, guardar el uploaded file
        if (!$client_secret_exists) {
            $stored_file = $this->get_stored_file($data);
            if (!$stored_file || $stored_file->get_filesize() == 0) {
                error_log("ERROR: client_secret.json does not exist, and no file was uploaded");
                return get_string('error_empty_drivecredentials', 'mod_exportgrades');
            }

            // Chequea si el uploaded file tiene extension .json
            $filename = $stored_file->get_filename();
            $file_extension = pathinfo($filename, PATHINFO_EXTENSION);
            if ($file_extension !== 'json') {
                error_log("ERROR: Uploaded file does not have a .json extension");
                return get_string('error_wrong_extension', 'mod_exportgrades');
            }

//            $fs = get_file_storage();
//            $file_record = array(
//                'contextid' => $context->id,
//                'component' => 'mod_exportgrades',
//                'filearea' => 'config',
//                'filepath' => '/', // Use '/' for the root directory within 'config'
//                'filename' => 'client_secret.json',
//                'userid' => $USER->id,
//                'filesize' => $stored_file->get_filesize(),
//                'mimetype' => $stored_file->get_mimetype(),
//                'timecreated' => time(),
//                'timemodified' => time(),
//                'source' => '', // Provide appropriate source information if needed
//                'author' => 'Admin User', // Replace with actual author information if needed
//                'license' => 'unknown', // Replace with actual license information if needed
//            );
//
//            try {
//                $fs->create_file_from_storedfile($file_record, $stored_file);
//                error_log("Uploaded file saved as client_secret.json in config folder");
//            } catch (stored_file_creation_exception $e) {
//                error_log("Error creating file: " . $e->getMessage());
//                return get_string('error_file_creation', 'mod_exportgrades');
//            }
        }

        return true;
    }

    private function get_stored_file($data) {
        global $USER;
        $context = context_user::instance($USER->id);
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'user', 'draft', $data, 'id', false);
        return reset($files);
    }

    public function write_setting($data) {
        $validation_result = $this->validate($data);
        if ($validation_result !== true) {
            return $validation_result;
        }
        return parent::write_setting($data);
    }
}
