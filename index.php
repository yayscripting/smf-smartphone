<?php
/**********************************************************************************
* index.php                                                                       *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 1.1.19                                          *
* Software by:                Simple Machines (http://www.simplemachines.org)     *
* Copyright 2006-2009 by:     Simple Machines LLC (http://www.simplemachines.org) *
*           2001-2006 by:     Lewis Media (http://www.lewismedia.com)             *
* Support, News, Updates at:  http://www.simplemachines.org                       *
***********************************************************************************
* This program is free software; you may redistribute it and/or modify it under   *
* the terms of the provided license as published by Simple Machines LLC.          *
*                                                                                 *
* This program is distributed in the hope that it is and will be useful, but      *
* WITHOUT ANY WARRANTIES; without even any implied warranty of MERCHANTABILITY    *
* or FITNESS FOR A PARTICULAR PURPOSE.                                            *
*                                                                                 *
* See the "license.txt" file for details of the Simple Machines license.          *
* The latest version can always be found at http://www.simplemachines.org.        *
**********************************************************************************/


/*	This, as you have probably guessed, is the crux on which SMF functions.
	Everything should start here, so all the setup and security is done
	properly.  The most interesting part of this file is the action array in
	the smf_main() function.  It is formatted as so:

		'action-in-url' => array('Source-File.php', 'FunctionToCall'),

	Then, you can access the FunctionToCall() function from Source-File.php
	with the URL index.php?action=action-in-url.  Relatively simple, no?
*/

$forum_version = 'SMF 1.1.19';

// Get everything started up...
define('SMF', 1);
@set_magic_quotes_runtime(0);
error_reporting(E_ALL);
$time_start = microtime();

// Make sure some things simply do not exist.
foreach (array('db_character_set') as $variable)
	if (isset($GLOBALS[$variable]))
		unset($GLOBALS[$variable], $GLOBALS[$variable]);

// Load the settings...
require_once(dirname(__FILE__) . '/Settings.php');

// And important includes.
require_once($sourcedir . '/QueryString.php');
require_once($sourcedir . '/Subs.php');
require_once($sourcedir . '/Errors.php');
require_once($sourcedir . '/Load.php');
require_once($sourcedir . '/Security.php');

// Using an old version of PHP?
if (@version_compare(PHP_VERSION, '4.2.3') != 1)
	require_once($sourcedir . '/Subs-Compat.php');

// If $maintenance is set specifically to 2, then we're upgrading or something.
if (!empty($maintenance) && $maintenance == 2)
	db_fatal_error();

// Connect to the MySQL database.
if (empty($db_persist))
	$db_connection = @mysql_connect($db_server, $db_user, $db_passwd);
else
	$db_connection = @mysql_pconnect($db_server, $db_user, $db_passwd);

// Show an error if the connection couldn't be made.
if (!$db_connection || !@mysql_select_db($db_name, $db_connection))
	db_fatal_error();

// Load the settings from the settings table, and perform operations like optimizing.
reloadSettings();
// Clean the request variables, add slashes, etc.
cleanRequest();
$context = array();

// Seed the random generator?
if (empty($modSettings['rand_seed']) || mt_rand(1, 250) == 69)
	smf_seed_generator();

// Determine if this browser is on a smartphone
$user_agent = @$_SERVER['HTTP_USER_AGENT']; // get the user agent value - this should be cleaned to ensure no nefarious input gets executed
$accept     = @$_SERVER['HTTP_ACCEPT']; // get the content accept value - this should be cleaned to ensure no nefarious input gets executed
$smartphone = false
    || (preg_match('/ipad/i',$user_agent))
    || (preg_match('/ipod/i',$user_agent)||preg_match('/iphone/i',$user_agent))
    || (preg_match('/android/i',$user_agent))
    || (preg_match('/opera mini/i',$user_agent))
    || (preg_match('/blackberry/i',$user_agent))
    || (preg_match('/(pre\/|palm os|palm|hiptop|avantgo|plucker|xiino|blazer|elaine)/i',$user_agent))
    || (preg_match('/(iris|3g_t|windows ce|opera mobi|windows ce; smartphone;|windows ce; iemobile)/i',$user_agent))
    || (preg_match('/(mini 9.5|vx1000|lge |m800|e860|u940|ux840|compal|wireless| mobi|ahong|lg380|lgku|lgu900|lg210|lg47|lg920|lg840|lg370|sam-r|mg50|s55|g83|t66|vx400|mk99|d615|d763|el370|sl900|mp500|samu3|samu4|vx10|xda_|samu5|samu6|samu7|samu9|a615|b832|m881|s920|n210|s700|c-810|_h797|mob-x|sk16d|848b|mowser|s580|r800|471x|v120|rim8|c500foma:|160x|x160|480x|x640|t503|w839|i250|sprint|w398samr810|m5252|c7100|mt126|x225|s5330|s820|htil-g1|fly v71|s302|-x113|novarra|k610i|-three|8325rc|8352rc|sanyo|vx54|c888|nx250|n120|mtk |c5588|s710|t880|c5005|i;458x|p404i|s210|c5100|teleca|s940|c500|s590|foma|samsu|vx8|vx9|a1000|_mms|myx|a700|gu1100|bc831|e300|ems100|me701|me702m-three|sd588|s800|8325rc|ac831|mw200|brew |d88|htc\/|htc_touch|355x|m50|km100|d736|p-9521|telco|sl74|ktouch|m4u\/|me702|8325rc|kddi|phone|lg |sonyericsson|samsung|240x|x320|vx10|nokia|sony cmd|motorola|up.browser|up.link|mmp|symbian|smartphone|midp|wap|vodafone|o2|pocket|kindle|mobile|psp|treo)/i',$user_agent))
    || ((strpos($accept,'text/vnd.wap.wml')>0)||(strpos($accept,'application/vnd.wap.xhtml+xml')>0))
    || (isset($_SERVER['HTTP_X_WAP_PROFILE'])||isset($_SERVER['HTTP_PROFILE']))
    || (in_array(strtolower(substr($user_agent,0,4)),array('1207'=>'1207','3gso'=>'3gso','4thp'=>'4thp','501i'=>'501i','502i'=>'502i','503i'=>'503i','504i'=>'504i','505i'=>'505i','506i'=>'506i','6310'=>'6310','6590'=>'6590','770s'=>'770s','802s'=>'802s','a wa'=>'a wa','acer'=>'acer','acs-'=>'acs-','airn'=>'airn','alav'=>'alav','asus'=>'asus','attw'=>'attw','au-m'=>'au-m','aur '=>'aur ','aus '=>'aus ','abac'=>'abac','acoo'=>'acoo','aiko'=>'aiko','alco'=>'alco','alca'=>'alca','amoi'=>'amoi','anex'=>'anex','anny'=>'anny','anyw'=>'anyw','aptu'=>'aptu','arch'=>'arch','argo'=>'argo','bell'=>'bell','bird'=>'bird','bw-n'=>'bw-n','bw-u'=>'bw-u','beck'=>'beck','benq'=>'benq','bilb'=>'bilb','blac'=>'blac','c55/'=>'c55/','cdm-'=>'cdm-','chtm'=>'chtm','capi'=>'capi','cond'=>'cond','craw'=>'craw','dall'=>'dall','dbte'=>'dbte','dc-s'=>'dc-s','dica'=>'dica','ds-d'=>'ds-d','ds12'=>'ds12','dait'=>'dait','devi'=>'devi','dmob'=>'dmob','doco'=>'doco','dopo'=>'dopo','el49'=>'el49','erk0'=>'erk0','esl8'=>'esl8','ez40'=>'ez40','ez60'=>'ez60','ez70'=>'ez70','ezos'=>'ezos','ezze'=>'ezze','elai'=>'elai','emul'=>'emul','eric'=>'eric','ezwa'=>'ezwa','fake'=>'fake','fly-'=>'fly-','fly_'=>'fly_','g-mo'=>'g-mo','g1 u'=>'g1 u','g560'=>'g560','gf-5'=>'gf-5','grun'=>'grun','gene'=>'gene','go.w'=>'go.w','good'=>'good','grad'=>'grad','hcit'=>'hcit','hd-m'=>'hd-m','hd-p'=>'hd-p','hd-t'=>'hd-t','hei-'=>'hei-','hp i'=>'hp i','hpip'=>'hpip','hs-c'=>'hs-c','htc '=>'htc ','htc-'=>'htc-','htca'=>'htca','htcg'=>'htcg','htcp'=>'htcp','htcs'=>'htcs','htct'=>'htct','htc_'=>'htc_','haie'=>'haie','hita'=>'hita','huaw'=>'huaw','hutc'=>'hutc','i-20'=>'i-20','i-go'=>'i-go','i-ma'=>'i-ma','i230'=>'i230','iac'=>'iac','iac-'=>'iac-','iac/'=>'iac/','ig01'=>'ig01','im1k'=>'im1k','inno'=>'inno','iris'=>'iris','jata'=>'jata','java'=>'java','kddi'=>'kddi','kgt'=>'kgt','kgt/'=>'kgt/','kpt '=>'kpt ','kwc-'=>'kwc-','klon'=>'klon','lexi'=>'lexi','lg g'=>'lg g','lg-a'=>'lg-a','lg-b'=>'lg-b','lg-c'=>'lg-c','lg-d'=>'lg-d','lg-f'=>'lg-f','lg-g'=>'lg-g','lg-k'=>'lg-k','lg-l'=>'lg-l','lg-m'=>'lg-m','lg-o'=>'lg-o','lg-p'=>'lg-p','lg-s'=>'lg-s','lg-t'=>'lg-t','lg-u'=>'lg-u','lg-w'=>'lg-w','lg/k'=>'lg/k','lg/l'=>'lg/l','lg/u'=>'lg/u','lg50'=>'lg50','lg54'=>'lg54','lge-'=>'lge-','lge/'=>'lge/','lynx'=>'lynx','leno'=>'leno','m1-w'=>'m1-w','m3ga'=>'m3ga','m50/'=>'m50/','maui'=>'maui','mc01'=>'mc01','mc21'=>'mc21','mcca'=>'mcca','medi'=>'medi','meri'=>'meri','mio8'=>'mio8','mioa'=>'mioa','mo01'=>'mo01','mo02'=>'mo02','mode'=>'mode','modo'=>'modo','mot '=>'mot ','mot-'=>'mot-','mt50'=>'mt50','mtp1'=>'mtp1','mtv '=>'mtv ','mate'=>'mate','maxo'=>'maxo','merc'=>'merc','mits'=>'mits','mobi'=>'mobi','motv'=>'motv','mozz'=>'mozz','n100'=>'n100','n101'=>'n101','n102'=>'n102','n202'=>'n202','n203'=>'n203','n300'=>'n300','n302'=>'n302','n500'=>'n500','n502'=>'n502','n505'=>'n505','n700'=>'n700','n701'=>'n701','n710'=>'n710','nec-'=>'nec-','nem-'=>'nem-','newg'=>'newg','neon'=>'neon','netf'=>'netf','noki'=>'noki','nzph'=>'nzph','o2 x'=>'o2 x','o2-x'=>'o2-x','opwv'=>'opwv','owg1'=>'owg1','opti'=>'opti','oran'=>'oran','p800'=>'p800','pand'=>'pand','pg-1'=>'pg-1','pg-2'=>'pg-2','pg-3'=>'pg-3','pg-6'=>'pg-6','pg-8'=>'pg-8','pg-c'=>'pg-c','pg13'=>'pg13','phil'=>'phil','pn-2'=>'pn-2','pt-g'=>'pt-g','palm'=>'palm','pana'=>'pana','pire'=>'pire','pock'=>'pock','pose'=>'pose','psio'=>'psio','qa-a'=>'qa-a','qc-2'=>'qc-2','qc-3'=>'qc-3','qc-5'=>'qc-5','qc-7'=>'qc-7','qc07'=>'qc07','qc12'=>'qc12','qc21'=>'qc21','qc32'=>'qc32','qc60'=>'qc60','qci-'=>'qci-','qwap'=>'qwap','qtek'=>'qtek','r380'=>'r380','r600'=>'r600','raks'=>'raks','rim9'=>'rim9','rove'=>'rove','s55/'=>'s55/','sage'=>'sage','sams'=>'sams','sc01'=>'sc01','sch-'=>'sch-','scp-'=>'scp-','sdk/'=>'sdk/','se47'=>'se47','sec-'=>'sec-','sec0'=>'sec0','sec1'=>'sec1','semc'=>'semc','sgh-'=>'sgh-','shar'=>'shar','sie-'=>'sie-','sk-0'=>'sk-0','sl45'=>'sl45','slid'=>'slid','smb3'=>'smb3','smt5'=>'smt5','sp01'=>'sp01','sph-'=>'sph-','spv '=>'spv ','spv-'=>'spv-','sy01'=>'sy01','samm'=>'samm','sany'=>'sany','sava'=>'sava','scoo'=>'scoo','send'=>'send','siem'=>'siem','smar'=>'smar','smit'=>'smit','soft'=>'soft','sony'=>'sony','t-mo'=>'t-mo','t218'=>'t218','t250'=>'t250','t600'=>'t600','t610'=>'t610','t618'=>'t618','tcl-'=>'tcl-','tdg-'=>'tdg-','telm'=>'telm','tim-'=>'tim-','ts70'=>'ts70','tsm-'=>'tsm-','tsm3'=>'tsm3','tsm5'=>'tsm5','tx-9'=>'tx-9','tagt'=>'tagt','talk'=>'talk','teli'=>'teli','topl'=>'topl','hiba'=>'hiba','up.b'=>'up.b','upg1'=>'upg1','utst'=>'utst','v400'=>'v400','v750'=>'v750','veri'=>'veri','vk-v'=>'vk-v','vk40'=>'vk40','vk50'=>'vk50','vk52'=>'vk52','vk53'=>'vk53','vm40'=>'vm40','vx98'=>'vx98','virg'=>'virg','vite'=>'vite','voda'=>'voda','vulc'=>'vulc','w3c '=>'w3c ','w3c-'=>'w3c-','wapj'=>'wapj','wapp'=>'wapp','wapu'=>'wapu','wapm'=>'wapm','wig '=>'wig ','wapi'=>'wapi','wapr'=>'wapr','wapv'=>'wapv','wapy'=>'wapy','wapa'=>'wapa','waps'=>'waps','wapt'=>'wapt','winc'=>'winc','winw'=>'winw','wonu'=>'wonu','x700'=>'x700','xda2'=>'xda2','xdag'=>'xdag','yas-'=>'yas-','your'=>'your','zte-'=>'zte-','zeto'=>'zeto','acs-'=>'acs-','alav'=>'alav','alca'=>'alca','amoi'=>'amoi','aste'=>'aste','audi'=>'audi','avan'=>'avan','benq'=>'benq','bird'=>'bird','blac'=>'blac','blaz'=>'blaz','brew'=>'brew','brvw'=>'brvw','bumb'=>'bumb','ccwa'=>'ccwa','cell'=>'cell','cldc'=>'cldc','cmd-'=>'cmd-','dang'=>'dang','doco'=>'doco','eml2'=>'eml2','eric'=>'eric','fetc'=>'fetc','hipt'=>'hipt','http'=>'http','ibro'=>'ibro','idea'=>'idea','ikom'=>'ikom','inno'=>'inno','ipaq'=>'ipaq','jbro'=>'jbro','jemu'=>'jemu','java'=>'java','jigs'=>'jigs','kddi'=>'kddi','keji'=>'keji','kyoc'=>'kyoc','kyok'=>'kyok','leno'=>'leno','lg-c'=>'lg-c','lg-d'=>'lg-d','lg-g'=>'lg-g','lge-'=>'lge-','libw'=>'libw','m-cr'=>'m-cr','maui'=>'maui','maxo'=>'maxo','midp'=>'midp','mits'=>'mits','mmef'=>'mmef','mobi'=>'mobi','mot-'=>'mot-','moto'=>'moto','mwbp'=>'mwbp','mywa'=>'mywa','nec-'=>'nec-','newt'=>'newt','nok6'=>'nok6','noki'=>'noki','o2im'=>'o2im','opwv'=>'opwv','palm'=>'palm','pana'=>'pana','pant'=>'pant','pdxg'=>'pdxg','phil'=>'phil','play'=>'play','pluc'=>'pluc','port'=>'port','prox'=>'prox','qtek'=>'qtek','qwap'=>'qwap','rozo'=>'rozo','sage'=>'sage','sama'=>'sama','sams'=>'sams','sany'=>'sany','sch-'=>'sch-','sec-'=>'sec-','send'=>'send','seri'=>'seri','sgh-'=>'sgh-','shar'=>'shar','sie-'=>'sie-','siem'=>'siem','smal'=>'smal','smar'=>'smar','sony'=>'sony','sph-'=>'sph-','symb'=>'symb','t-mo'=>'t-mo','teli'=>'teli','tim-'=>'tim-','tosh'=>'tosh','treo'=>'treo','tsm-'=>'tsm-','upg1'=>'upg1','upsi'=>'upsi','vk-v'=>'vk-v','voda'=>'voda','vx52'=>'vx52','vx53'=>'vx53','vx60'=>'vx60','vx61'=>'vx61','vx70'=>'vx70','vx80'=>'vx80','vx81'=>'vx81','vx83'=>'vx83','vx85'=>'vx85','wap-'=>'wap-','wapa'=>'wapa','wapi'=>'wapi','wapp'=>'wapp','wapr'=>'wapr','webc'=>'webc','whit'=>'whit','winw'=>'winw','wmlb'=>'wmlb','xda-'=>'xda-',)))
;

if($smartphone){

	//$_REQUEST['smartphone'] = 1;

}

// Determine if this is using WAP, WAP2, or imode.  Technically, we should check that wap comes before application/xhtml or text/html, but this doesn't work in practice as much as it should.
if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/vnd.wap.xhtml+xml') !== false)
	$_REQUEST['wap2'] = 1;
elseif (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'text/vnd.wap.wml') !== false)
{
	if (strpos($_SERVER['HTTP_USER_AGENT'], 'DoCoMo/') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'portalmmm/') !== false)
		$_REQUEST['imode'] = 1;
	else
		$_REQUEST['wap'] = 1;
}

if (!defined('WIRELESS'))
	define('WIRELESS', isset($_REQUEST['wap']) || isset($_REQUEST['wap2']) || isset($_REQUEST['imode']) || isset($_REQUEST['smartphone']));

// Some settings and headers are different for wireless protocols.
if (WIRELESS)
{
	define('WIRELESS_PROTOCOL', isset($_REQUEST['wap']) ? 'wap' : (isset($_REQUEST['wap2']) ? 'wap2' : (isset($_REQUEST['imode']) ? 'imode' : (isset($_REQUEST['smartphone']) ? 'smartphone' : ''))));

	// Some cellphones can't handle output compression...
	$modSettings['enableCompressedOutput'] = '0';
	
	// !!! Do we want these hard coded?
	$modSettings['defaultMaxMessages'] = 10;
	$modSettings['defaultMaxTopics'] = 15;

	// Wireless protocol header.
	if (WIRELESS_PROTOCOL == 'wap')
		header('Content-Type: text/vnd.wap.wml');
}

// Check if compressed output is enabled, supported, and not already being done.
if (!empty($modSettings['enableCompressedOutput']) && !headers_sent() && ob_get_length() == 0)
{
	// If zlib is being used, turn off output compression.
	if (@ini_get('zlib.output_compression') == '1' || @ini_get('output_handler') == 'ob_gzhandler' || @version_compare(PHP_VERSION, '4.2.0') == -1)
		$modSettings['enableCompressedOutput'] = '0';
	else
		ob_start('ob_gzhandler');
}
// This makes it so headers can be sent!
if (empty($modSettings['enableCompressedOutput']))
	ob_start();

// Emit some headers for some modicum of protection against nasties.
if (!headers_sent())
{
	// Future versions will make some of this configurable. This is primarily a 'safe' configuration for most cases for now.
	header('X-Frame-Options: SAMEORIGIN');
	header('X-XSS-Protection: 1; mode=block');
	header('X-Content-Type-Options: nosniff');
}

// Register an error handler.
set_error_handler('error_handler');

// Start the session. (assuming it hasn't already been.)
loadSession();

// What function shall we execute? (done like this for memory's sake.)
call_user_func(smf_main());

// Call obExit specially; we're coming from the main area ;).
obExit(null, null, true);

// The main controlling function.
function smf_main()
{
	global $modSettings, $settings, $user_info, $board, $topic, $maintenance, $sourcedir;

	// Special case: session keep-alive.
	if (isset($_GET['action']) && $_GET['action'] == 'keepalive')
		die;

	// Load the user's cookie (or set as guest) and load their settings.
	loadUserSettings();

	// Load the current board's information.
	loadBoard();

	// Load the current theme.  (note that ?theme=1 will also work, may be used for guest theming.)
	loadTheme();

	// Check if the user should be disallowed access.
	is_not_banned();

	// Load the current user's permissions.
	loadPermissions();

	// Do some logging, unless this is an attachment, avatar, theme option or XML feed.
	if (empty($_REQUEST['action']) || !in_array($_REQUEST['action'], array('dlattach', 'jsoption', '.xml')))
	{
		// Log this user as online.
		writeLog();

		// Track forum statistics and hits...?
		if (!empty($modSettings['hitStats']))
			trackStats(array('hits' => '+'));
	}

	// Is the forum in maintenance mode? (doesn't apply to administrators.)
	if (!empty($maintenance) && !allowedTo('admin_forum'))
	{
		// You can only login.... otherwise, you're getting the "maintenance mode" display.
		if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'login2' || $_REQUEST['action'] == 'logout'))
		{
			require_once($sourcedir . '/LogInOut.php');
			return $_REQUEST['action'] == 'login2' ? 'Login2' : 'Logout';
		}
		// Don't even try it, sonny.
		else
		{
			require_once($sourcedir . '/Subs-Auth.php');
			return 'InMaintenance';
		}
	}
	// If guest access is off, a guest can only do one of the very few following actions.
	elseif (empty($modSettings['allow_guestAccess']) && $user_info['is_guest'] && (!isset($_REQUEST['action']) || !in_array($_REQUEST['action'], array('coppa', 'login', 'login2', 'register', 'register2', 'reminder', 'activate', 'smstats', 'help', 'verificationcode'))))
	{
		require_once($sourcedir . '/Subs-Auth.php');
		return 'KickGuest';
	}
	elseif (empty($_REQUEST['action']))
	{
		// Action and board are both empty... BoardIndex!
		if (empty($board) && empty($topic))
		{
			require_once($sourcedir . '/BoardIndex.php');
			return 'BoardIndex';
		}
		// Topic is empty, and action is empty.... MessageIndex!
		elseif (empty($topic))
		{
			require_once($sourcedir . '/MessageIndex.php');
			return 'MessageIndex';
		}
		// Board is not empty... topic is not empty... action is empty.. Display!
		else
		{
			require_once($sourcedir . '/Display.php');
			return 'Display';
		}
	}

	// Here's the monstrous $_REQUEST['action'] array - $_REQUEST['action'] => array($file, $function).
	$actionArray = array(
		'activate' => array('Register.php', 'Activate'),
		'admin' => array('Admin.php', 'Admin'),
		'announce' => array('Post.php', 'AnnounceTopic'),
		'ban' => array('ManageBans.php', 'Ban'),
		'boardrecount' => array('Admin.php', 'AdminBoardRecount'),
		'buddy' => array('Subs-Members.php', 'BuddyListToggle'),
		'calendar' => array('Calendar.php', 'CalendarMain'),
		'cleanperms' => array('Admin.php', 'CleanupPermissions'),
		'collapse' => array('Subs-Boards.php', 'CollapseCategory'),
		'convertentities' => array('Admin.php', 'ConvertEntities'),
		'convertutf8' => array('Admin.php', 'ConvertUtf8'),
		'coppa' => array('Register.php', 'CoppaForm'),
		'deletemsg' => array('RemoveTopic.php', 'DeleteMessage'),
		'detailedversion' => array('Admin.php', 'VersionDetail'),
		'display' => array('Display.php', 'Display'),
		'dlattach' => array('Display.php', 'Download'),
		'dumpdb' => array('DumpDatabase.php', 'DumpDatabase2'),
		'editpoll' => array('Poll.php', 'EditPoll'),
		'editpoll2' => array('Poll.php', 'EditPoll2'),
		'featuresettings' => array('ModSettings.php', 'ModifyFeatureSettings'),
		'featuresettings2' => array('ModSettings.php', 'ModifyFeatureSettings2'),
		'findmember' => array('Subs-Auth.php', 'JSMembers'),
		'help' => array('Help.php', 'ShowHelp'),
		'helpadmin' => array('Help.php', 'ShowAdminHelp'),
		'im' => array('PersonalMessage.php', 'MessageMain'),
		'jsoption' => array('Themes.php', 'SetJavaScript'),
		'jsmodify' => array('Post.php', 'JavaScriptModify'),
		'lock' => array('LockTopic.php', 'LockTopic'),
		'lockVoting' => array('Poll.php', 'LockVoting'),
		'login' => array('LogInOut.php', 'Login'),
		'login2' => array('LogInOut.php', 'Login2'),
		'logout' => array('LogInOut.php', 'Logout'),
		'maintain' => array('Admin.php', 'Maintenance'),
		'manageattachments' => array('ManageAttachments.php', 'ManageAttachments'),
		'manageboards' => array('ManageBoards.php', 'ManageBoards'),
		'managecalendar' => array('ManageCalendar.php', 'ManageCalendar'),
		'managesearch' => array('ManageSearch.php', 'ManageSearch'),
		'markasread' => array('Subs-Boards.php', 'MarkRead'),
		'membergroups' => array('ManageMembergroups.php', 'ModifyMembergroups'),
		'mergetopics' => array('SplitTopics.php', 'MergeTopics'),
		'mlist' => array('Memberlist.php', 'Memberlist'),
		'modifycat' => array('ManageBoards.php', 'ModifyCat'),
		'modifykarma' => array('Karma.php', 'ModifyKarma'),
		'modlog' => array('Modlog.php', 'ViewModlog'),
		'movetopic' => array('MoveTopic.php', 'MoveTopic'),
		'movetopic2' => array('MoveTopic.php', 'MoveTopic2'),
		'news' => array('ManageNews.php', 'ManageNews'),
		'notify' => array('Notify.php', 'Notify'),
		'notifyboard' => array('Notify.php', 'BoardNotify'),
		'optimizetables' => array('Admin.php', 'OptimizeTables'),
		'packageget' => array('PackageGet.php', 'PackageGet'),
		'packages' => array('Packages.php', 'Packages'),
		'permissions' => array('ManagePermissions.php', 'ModifyPermissions'),
		'pgdownload' => array('PackageGet.php', 'PackageGet'),
		'pm' => array('PersonalMessage.php', 'MessageMain'),
		'post' => array('Post.php', 'Post'),
		'post2' => array('Post.php', 'Post2'),
		'postsettings' => array('ManagePosts.php', 'ManagePostSettings'),
		'printpage' => array('Printpage.php', 'PrintTopic'),
		'profile' => array('Profile.php', 'ModifyProfile'),
		'profile2' => array('Profile.php', 'ModifyProfile2'),
		'quotefast' => array('Post.php', 'QuoteFast'),
		'quickmod' => array('Subs-Boards.php', 'QuickModeration'),
		'quickmod2' => array('Subs-Boards.php', 'QuickModeration2'),
		'recent' => array('Recent.php', 'RecentPosts'),
		'regcenter' => array('ManageRegistration.php', 'RegCenter'),
		'register' => array('Register.php', 'Register'),
		'register2' => array('Register.php', 'Register2'),
		'reminder' => array('Reminder.php', 'RemindMe'),
		'removetopic2' => array('RemoveTopic.php', 'RemoveTopic2'),
		'removeoldtopics2' => array('RemoveTopic.php', 'RemoveOldTopics2'),
		'removepoll' => array('Poll.php', 'RemovePoll'),
		'repairboards' => array('RepairBoards.php', 'RepairBoards'),
		'reporttm' => array('SendTopic.php', 'ReportToModerator'),
		'reports' => array('Reports.php', 'ReportsMain'),
		'requestmembers' => array('Subs-Auth.php', 'RequestMembers'),
		'search' => array('Search.php', 'PlushSearch1'),
		'search2' => array('Search.php', 'PlushSearch2'),
		'sendtopic' => array('SendTopic.php', 'SendTopic'),
		'serversettings' => array('ManageServer.php', 'ModifySettings'),
		'serversettings2' => array('ManageServer.php', 'ModifySettings2'),
		'smileys' => array('ManageSmileys.php', 'ManageSmileys'),
		'smstats' => array('Stats.php', 'SMStats'),
		'spellcheck' => array('Subs-Post.php', 'SpellCheck'),
		'splittopics' => array('SplitTopics.php', 'SplitTopics'),
		'stats' => array('Stats.php', 'DisplayStats'),
		'sticky' => array('LockTopic.php', 'Sticky'),
		'theme' => array('Themes.php', 'ThemesMain'),
		'trackip' => array('Profile.php', 'trackIP'),
		'about:mozilla' => array('Karma.php', 'BookOfUnknown'),
		'about:unknown' => array('Karma.php', 'BookOfUnknown'),
		'unread' => array('Recent.php', 'UnreadTopics'),
		'unreadreplies' => array('Recent.php', 'UnreadTopics'),
		'viewErrorLog' => array('ManageErrors.php', 'ViewErrorLog'),
		'viewmembers' => array('ManageMembers.php', 'ViewMembers'),
		'viewprofile' => array('Profile.php', 'ModifyProfile'),
		'verificationcode' => array('Register.php', 'VerificationCode'),
		'vote' => array('Poll.php', 'Vote'),
		'viewquery' => array('ViewQuery.php', 'ViewQuery'),
		'who' => array('Who.php', 'Who'),
		'sms' => array('SMS-1april.php', 'Notify'),
		'.xml' => array('News.php', 'ShowXmlFeed'),
	);

	// Get the function and file to include - if it's not there, do the board index.
	if (!isset($_REQUEST['action']) || !isset($actionArray[$_REQUEST['action']]))
	{
		// Catch the action with the theme?
		if (!empty($settings['catch_action']))
		{
			require_once($sourcedir . '/Themes.php');
			return 'WrapAction';
		}

		// Fall through to the board index then...
		require_once($sourcedir . '/BoardIndex.php');
		return 'BoardIndex';
	}

	// Otherwise, it was set - so let's go to that action.
	require_once($sourcedir . '/' . $actionArray[$_REQUEST['action']][0]);
	return $actionArray[$_REQUEST['action']][1];
}

?>