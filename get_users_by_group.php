<?php
require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

global $DB;

$groupid = required_param('groupid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);

$user_options = get_users_by_group($courseid, $groupid);

echo json_encode($user_options);
?>
