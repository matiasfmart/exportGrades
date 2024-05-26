<?php

require_once($CFG->libdir.'/formslib.php');

class export_grades_form extends moodleform {
    public function definition() {
        $mform = $this->_form;

        // Elemento de formulario para seleccionar periodicidad
        $mform->addElement('select', 'frequency', get_string('frequency', 'mod_exportacion'), array(
            'daily' => get_string('daily', 'mod_exportacion'),
            'weekly' => get_string('weekly', 'mod_exportacion'),
            'monthly' => get_string('monthly', 'mod_exportacion')
        ));

        // Botón de envío
        $this->add_action_buttons(true, get_string('export', 'mod_exportacion'));
    }
}
?>