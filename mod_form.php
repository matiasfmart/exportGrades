<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.


// El archivo mod_form.php en un plugin de actividad de Moodle se utiliza para definir el formulario de configuración de la actividad.
// Este formulario se muestra cuando un usuario está añadiendo o editando una instancia de la actividad en un curso.
// En el contexto de tu plugin exportgrades, el archivo mod_form.php se utilizaría para definir los campos y elementos
// de formulario necesarios para configurar la actividad de exportación de calificaciones.

// En este archivo, puedes definir campos para configurar opciones específicas de la actividad, como el nombre de la actividad,
// la descripción, la visibilidad, etc. También puedes agregar campos personalizados para configurar opciones
// específicas de tu actividad, como la frecuencia de exportación o el destino de la exportación.

// Además de definir los elementos de formulario, el archivo mod_form.php también puede contener lógica para validar
// los datos del formulario antes de guardarlos, así como para procesar y guardar los datos en la base de datos una vez que el
// formulario se envía.




/**
 * The main mod_exportgrades configuration form.
 *
 * @package     mod_exportgrades
 * @copyright   2024 Your Name <you@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form.
 *
 * @package     mod_exportgrades
 * @copyright   2024 Your Name <you@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_exportgrades_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are shown.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('exportgradesname', 'mod_exportgrades'), array('size' => '64'));

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'exportgradesname', 'mod_exportgrades');

        // Adding the standard "intro" and "introformat" fields.
        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }

        // Adding the rest of mod_exportgrades settings, spreading all them into this fieldset
        // ... or adding more fieldsets ('header' elements) if needed for better logic.
        $mform->addElement('static', 'label1', 'exportgradessettings', get_string('exportgradessettings', 'mod_exportgrades'));
        $mform->addElement('header', 'exportgradesfieldset', get_string('exportgradesfieldset', 'mod_exportgrades'));

        // Add standard elements.
        $this->standard_coursemodule_elements();

        // Add standard buttons.
        $this->add_action_buttons();
    }
}
