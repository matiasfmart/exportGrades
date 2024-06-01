<?php
namespace mod_exportgrades\event;

defined('MOODLE_INTERNAL') || die();

class course_module_viewed extends \core\event\course_module_viewed {
    protected function init() {
        $this->data['crud'] = 'r'; // read
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'exportgrades';
    }

    public static function get_name() {
        return get_string('eventcoursemoduleviewed', 'mod_exportgrades');
    }

    public function get_description() {
        return "The user with id '{$this->userid}' viewed the course module with id '{$this->contextinstanceid}'.";
    }

    public function get_url() {
        return new \moodle_url('/mod/exportgrades/view.php', array('id' => $this->contextinstanceid));
    }
}
