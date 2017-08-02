<?php
//error_reporting(0);
//added Dec 3 2015
//ob_implicit_flush();

include 'php_serial.class.php';
include_once('projectorCommands.inc');

$skipJSsettings = 1;
include_once '/opt/fpp/www/config.php';
include_once '/opt/fpp/www/common.php';

$pluginName  = "FPPViewerVotingServer";

include_once 'functions.inc.php';


include_once 'config/userValues.inc';

$logFile = "/tmp". "/".$pluginName.".log";

$SITE_ENABLED_STATUS = 1;

$pluginConfigFile = (__DIR__)."/plugin." .$pluginName;
logEntry("PluginConfig File: ".$pluginConfigFile);

if (file_exists($pluginConfigFile))
	$pluginSettings = parse_ini_file($pluginConfigFile);


$myPid = getmypid();

//$PORT = ReadSettingFromFile("PORT",$pluginName);
$PORT = $pluginSettings['PORT'];
$DEBUG = $pluginSettings['DEBUG'];
logEntry("DEBUG: ".$DEBUG);


//$ENABLED = ReadSettingFromFile("ENABLED",$pluginName);
$ENABLED = urldecode($pluginSettings['ENABLED']);


if($DEBUG) {
	logEntry("PORT: ".$PORT);
	logEntry("ENabled: ".$ENABLED);
	
}

if(!$ENABLED) {
	logEntry("Not enabled exiting");
	exit(0);	
}


if(!empty($_POST)) {
	if($DEBUG) {
		foreach($_POST as $key => $value) {
			logEntry( "POST parameter '$key' has '$value'");
			if(!empty($_POST['API_TOKEN'])) {
				$CLIENT_TOKEN = $_POST['API_TOKEN'];
				
			}
		}
	}
} elseif(!empty($_GET)) {
	if($DEBUG) {
		foreach($_GET as $key => $value) {
			logEntry( "GET parameter '$key' has '$value'");
			if(!empty($_GET['API_TOKEN'])) {
				$CLIENT_TOKEN = $_GET['API_TOKEN'];
				
			}
		}
	}
}
//sleep(2);
logEntry("Client token: ".$CLIENT_TOKEN);

$conn = dbConnect($DB_SERVER_IP, $DB_USER, $DB_PASS, $db);
if (!$conn)
{
	logEntry("Could not connect: " . mysql_error());
	
	//EXIT here because could not connect to database!
	exit(0);
} else {
	if($DEBUG) {
		logEntry("Connected to database: ".$db);
	}
	
}

//// put whatever response in the array below to respond back using HTML. Then the client will decode it!

//check the votes for the API Token / site 
$SITE_ID = getSiteIDFromAPIToken($conn, $CLIENT_TOKEN);

if(count($SITE_ID) <=0 || count($SITE_ID) == null) {
	logEntry("No active Site id found for token: ".$CLIENT_TOKEN);
	
	$SITE_ENABLED = false;
}

$json = array();
$itemObject = new stdClass();
$itemObject->CLIENT_TOKEN = $CLIENT_TOKEN;
$itemObject->SITE_ENABLED = $SITE_ENABLED;
$itemObject->SEQUENCE = $SEQUENCE;
$itemObject->VOTES = $VOTES;
$itemObject->LAST_VOTE_TIMESTAMP= $LAST_VOTE_TIMESTAMP;



array_push($json, $itemObject);
$json = json_encode($json, JSON_PRETTY_PRINT);
echo $json;


?>