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

/**
 * Plugin strings are defined here.
 *
 * @package     mod_exportgrades
 * @category    string
 * @copyright   2024 Your Name <you@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'export-grades-cloud';

$string['exportgrades'] = 'Export Grades';
$string['frequency'] = 'Export Frequency';
$string['frequency_desc'] = 'Select how often you want to export grades to Google Drive.';
$string['daily'] = 'Daily';
$string['weekly'] = 'Weekly';
$string['monthly'] = 'Monthly';
$string['fileexported'] = 'File has been successfully exported and uploaded to Google Drive.';
$string['exportgradestask'] = 'Export Grades Task';
$string['drivefolderid'] = 'Google Drive Folder ID';
$string['drivefolderid_desc'] = 'The ID of the Google Drive folder where the exported Excel files will be uploaded.';
$string['drivecredentials'] = 'Google Drive Service Account Credentials';
$string['drivecredentials_desc'] = 'The JSON credentials for the Google Drive service account. Paste the entire JSON content here.';