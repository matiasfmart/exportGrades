<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_exportgrades_mod_form extends moodleform_mod {
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('exportgradesname', 'mod_exportgrades'), array('size' => '64'));

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'exportgradesname', 'mod_exportgrades');

        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }
        
        $mform->addElement('static', 'label1', 'exportgradessettings', get_string('exportgradessettings', 'mod_exportgrades'));
        $mform->addElement('header', 'exportgradesfieldset', get_string('exportgradesfieldset', 'mod_exportgrades'));

        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }
}
