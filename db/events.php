<?php
defined('MOODLE_INTERNAL') || die();

$observers = array(
    array(
        'eventname'   => '\core\event\config_log_created',
        'callback'    => 'mod_exportgrades\Observer::config_updated',
    ),
);