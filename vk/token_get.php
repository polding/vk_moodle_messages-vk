<?php 


/**
 * Vk configuration page
 *
 * @package    token_get
 * @copyright  2013 Dmitriy Ivanov
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('SHOW_ERRORS', TRUE);

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php'); //included from messagelib (how to fix?)	
	
	include ("html_class.php");
	$http= new HttpTools();
	
	
	$login = $CFG->vklogin;			
	$password = $CFG->vkpassword;	
	$client_id = $CFG->vkidapp;
	$scope = $CFG->vkscope;		
	
	$url  = 'https://oauth.vk.com/authorize?';
	$url .= 'client_id=' . $client_id;
	$url .= '&scope=' . $scope;
	$url .= '&redirect_uri=http://oauth.vk.com/blank.html';
	$url .= '&display=wap';
	$url .= '&response_type=token';
    $url .= '&v=5.2';
    $url .= '&revoke=1';
	
	
	$html = $http->sendGetRequest($url, '', false);

		
	$_origin = $http->parseParam($html, '<input type="hidden" name="_origin" value="','">');
	$ip_h = $http->parseParam($html, '<input type="hidden" name="ip_h" value="','" />');
	$to = $http->parseParam($html, '<input type="hidden" name="to" value="','">');
	
	
	$url2  = 'https://login.vk.com/?act=login&soft=1&utf8=1';
	$url2 .= '&ip_h=' . $ip_h;
	$url2 .= '&to=' . $to;
	$url2 .= '&_origin=' . $_origin;
	$url2 .= '&email=' . $login;
	$url2 .= '&pass=' . $password;
	

	
	list($headers, $reply) = $http->sendPostRequest($url2, '', true);	
		
	
	$cookies1 = $http->getPageCookies($headers);
	$headers = $http->formatHeadersArray($headers);
	$location = @$headers['Location'];
	
	print "Send GET request 3: $location\n";
	
	list($headers, $html) = $http->sendGetRequest($location, '', true);		
		
	$confirmUrl = $http->parseParam($html, '<form method="post" action="','">');		
	$cookies = $http->getPageCookies($headers);
		
	$http->setCookie('remixsid='.$cookies['remixsid'].';remixlang=3'.';s='.$cookies1['s'].';l='.$cookies1['l'].';p='.$cookies1['p'].';h='.$cookies1['h']);
	
	print "Send POST request 4: $confirmUrl\n";
	
	list($headers, $reply)  = $http->sendPostRequest($confirmUrl, '', true);				
	$headers = $http->formatHeadersArray($headers);		
	$location = trim(@$headers['Location']);
        
       echo "Query string: $location ";
		
	$token = $http->parseParam($location, 'https://oauth.vk.com/blank.html#access_token=', '&');	
	set_config('token', $token);
	header("location: /moodle/admin/settings.php?section=messagesettingvk");
        
	
