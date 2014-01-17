<?php

///////////////////////////////////////////////////////////////////////////
// //
// NOTICE OF COPYRIGHT //
// //
// Moodle - Modular Object-Oriented Dynamic Learning Environment //
// http://moodle.com //
// //
// Copyright (C) 1999 onwards Martin Dougiamas http://moodle.com //
// //
// This program is free software; you can redistribute it and/or modify //
// it under the terms of the GNU General Public License as published by //
// the Free Software Foundation; either version 2 of the License, or //
// (at your option) any later version. //
// //
// This program is distributed in the hope that it will be useful, //
// but WITHOUT ANY WARRANTY; without even the implied warranty of //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the //
// GNU General Public License for more details: //
// //
// http://www.gnu.org/copyleft/gpl.html //
// //
///////////////////////////////////////////////////////////////////////////

/**
* vk message processor
*
* @author Dmitry Ivanov
* @license http://www.gnu.org/copyleft/gpl.html GNU Public License
* @package message_vk
*/
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php'); //included from messagelib (how to fix?)
require_once($CFG->dirroot.'/message/output/lib.php');
require_once($CFG->libdir.'/filelib.php');

class message_output_vk extends message_output {
    /**
* Processes the message
* @param object $eventdata the event data submitted by the message sender plus $eventdata->savedmessageid
*/   
	
    function send_message($eventdata) {
		global $CFG;
		
		
		if (!empty($CFG->noemailever)) {
            // hidden setting for development sites, set in config.php if needed
            debugging('$CFG->noemailever active, no vk message sent.', DEBUG_MINIMAL);
            return true;
        }

        // skip any messaging suspended and deleted users
        if ($eventdata->userto->auth === 'nologin' or $eventdata->userto->suspended or $eventdata->userto->deleted) {
            return true;
        }                               
		
		//hold onto vk id preference because /admin/cron.php sends a lot of messages at once
        static $vkaddresses = array();

        if (!array_key_exists($eventdata->userto->id, $vkaddresses)) {
            $vkaddresses[$eventdata->userto->id] = get_user_preferences('message_processor_vk_vkid', null, $eventdata->userto->id);
        }
        $vkaddress = $vkaddresses[$eventdata->userto->id];
		
		//calling s() on smallmessage causes vk to display things like &lt; vk != a browser
        $vkmessage = fullname($eventdata->userfrom).': '.$eventdata->smallmessage;
		
		if (!empty($eventdata->contexturl)) {
            $vkmessage .= "\n".get_string('view').': '.$eventdata->contexturl;
        }
		
		$vkmessage .= "\n(".get_string('noreply','message').')';
		//echo $vkmessage;die;
					
 		$url_mess = new moodle_url('https://api.vk.com/method/messages.send',array('user_id' => $vkaddress,'message' => $vkmessage,'access_token' => $CFG->token));		
	
	//	echo $url_mess;die;
		$response = download_file_content($url_mess->out(false));	
		//echo $response; die;
		$decod = json_decode($response);		
	//	var_dump($decod);die;		
	//	try {
			//$conn->useEncryption(false);
	//		$conn->connect();
	//		$conn->processUntil('session_start');
	//		$conn->presence();
	//		$conn->message($jabberaddress, $jabbermessage);
	//		$conn->disconnect();
	//	} catch(XMPPHP_Exception $e) {
	//		debugging($e->getMessage());
	//		return false;
	//	}
				
        return true;
    }

    /**
* Creates necessary fields in the messaging config form.
* @param object $mform preferences form class
*/
   
   function config_form($preferences) {
     //   global $USER;
		global $CFG;		
		

		if (!$this->is_system_configured()) {
			return get_string('notconfigured','message_vk');
		} else {
		
		//static $vkaddresses = array();
		 // if (!array_key_exists($eventdata->userto->id, $vkaddresses)) {
			 // $vkaddresses[$eventdata->userto->id] = get_user_preferences('message_processor_vk_vkid', null, $eventdata->userto->id);
		 // }
		// $vkaddress = $vkaddresses[$eventdata->userto->id];
		$vkaddress = $preferences->vk_vkid;
		// $vkaddress = get_user_preferences('message_processor_vk_vkid', '', $userid);
		// echo $vkaddress;
		$url = new moodle_url('https://api.vk.com/method/friends.add', array('user_id' => $vkaddress,'access_token' => $CFG->token));		
   		$string = " ".get_string('vkid','message_vk').': <input  size="22" name="vk_vkid" value="'.s($preferences->vk_vkid).'" />';
		$string .="</p>"."<table><tr><td>".get_string('vk_friends','message_vk')."</td>"."<td>".' <a href= "'.$url.'">
		<img src="output/vk/vk.jpg" /></a>'."</td></tr></table>";   		   		  		
   		 
		return $string;
		}
	
    }   
        	
    /**
* Parses the form submitted data and saves it into preferences array.
* @param object $mform preferences form class
* @param array $preferences preferences array
*/
    
	function process_form($form, &$preferences) {
       
		 if ( isset ($form->vk_vkid) && !empty($form->vk_vkid)) {
            $preferences['message_processor_vk_vkid'] = $form->vk_vkid;
        }		
    }
		
    /**
* Loads the config data from database to put on the form (initial load)
* @param array $preferences preferences array
* @param int $userid the user id
*/
    function load_data(&$preferences, $userid) {       
        $preferences->vk_vkid = get_user_preferences( 'message_processor_vk_vkid', '', $userid);       
    }

    /**
     * Tests whether the vk settings have been configured
     * @return boolean true if vk is configured
     */
    function is_system_configured() {
    	global $CFG;
    	return (!empty($CFG->vklogin) && !empty($CFG->vkpassword));
    }
    
    
	/**
     * Tests whether the vk settings have been configured on user level
     * @param  object $user the user object, defaults to $USER.
     * @return bool has the user made all the necessary settings
     * in their profile to allow this plugin to be used.
     */
    function is_user_configured($user = null) {
        global $USER;

        if (is_null($user)) {
            $user = $USER;
        }
        return (bool)get_user_preferences('message_processor_vk_vkid', null, $user->id);
    }  
}
	


















