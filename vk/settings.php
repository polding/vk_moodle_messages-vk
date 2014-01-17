<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Vk configuration page
 *
 * @package    message_vk
 * @copyright  2013 Dmitriy Ivanov
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

	

if ($ADMIN->fulltree) {	
	global $CFG;
    $settings->add(new admin_setting_configtext('vklogin', get_string('vklogin', 'message_vk'), get_string('configvklogin', 'message_vk'), '', PARAM_RAW));
    $settings->add(new admin_setting_configtext('vkidapp', get_string('vkidapp', 'message_vk'), get_string('configvkidapp', 'message_vk'), '', PARAM_RAW));
    $settings->add(new admin_setting_configpasswordunmask('vkpassword', get_string('vkpassword', 'message_vk'), get_string('configvkpassword', 'message_vk'), ''));
    $settings->add(new admin_setting_configtext('vkscope', get_string('vkscope', 'message_vk'), get_string('configvkscope', 'message_vk'), '79874', PARAM_RAW));
    //$settings->add(new admin_setting_configtext(token, get_string('vktoken', 'message_vk'),'<a href="/moodle/message/output/vk/token_get.php">token_get</a>'. ' '.get_string('configvktoken', 'message_vk'), $CFG->token ,PARAM_TEXT ));
    $settings->add(new admin_setting_configtext('token', get_string('token', 'message_vk'),'<a href="../message/output/vk/token_get.php">
<img src="../message/output/vk/Access.png" ></a>'. ' '.get_string('configvktoken', 'message_vk'), '' ,PARAM_TEXT,80));
}
