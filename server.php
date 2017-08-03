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
$SHOW_ENABLED_STATUS = 1;
$SEQUENCE_ENABLED_STATUS = 1;

$pluginConfigFile = (__DIR__)."/plugin." .$pluginName;
logEntry("PluginConfig File: ".$pluginConfigFile);

if (file_exists($pluginConfigFile))
	$pluginSettings = parse_ini_file($pluginConfigFile);


$myPid = getmypid();

//$PORT = ReadSettingFromFile("PORT",$pluginName);
$PORT = $pluginSettings['PORT'];
$DEBUG = $pluginSettings['DEBUG'];
logEntry("DEBUG: ".$DEBUG);

$SEQUENCE = null;
$VOTES = 0;
$LAST_VOTE_TIMESTAMP=0;
$VOTE_UP_VALUE = 1;


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

if($SITE_ID != "" && $SITE_ID != 0 && $SITE_ID != null) {
	$SITE_ENABLED_STATUS = true;
} else {
	$SITE_ENABLED_STATUS = false;
}

if($SITE_ENABLED_STATUS) {
	//get the votes fot the highest sequence
	$SEQUENCE_VOTES = getSequenceWithHighestVotesForSite($conn, $SITE_ID);
	
	if($SEQUENCE_VOTES!= null) {
		if($DEBUG) {
			logEntry("We got sequence votes for site id: ".$SITE_ID);
			print_r($SEQUENCE_VOTES);
			
			
	
			
			aasort($SEQUENCE_VOTES,"votes");
			
			print_r($SEQUENCE_VOTES);
			
			//unfortunately the last one has the value.. UGH 
			//TODO: get a better sort to but it on the top!
			$TOTAL_SEQUENCE_VOTES = count($SEQUENCE_VOTES);
			
			$SEQUENCE_IDS = array_keys($SEQUENCE_VOTES);
			
			print_r($SEQUENCE_IDS);
			
			$SEQUENCE_WITH_HIGHEST_VOTES_FOR_SITE_ID = $SEQUENCE_VOTES[array_pop($SEQUENCE_IDS)];
			
			echo "Sequence with highest votes: ".$SEQUENCE_WITH_HIGHEST_VOTES_FOR_SITE_ID;
			
		}
		
		
	} else {
		logEntry("NO Sequence votes data for site id: ".$SITE_ID);
		$VOTES = 0;
	}
}

$json = array();
$itemObject = new stdClass();
$itemObject->CLIENT_TOKEN = $CLIENT_TOKEN;
$itemObject->SITE_ENABLED = $SITE_ENABLED_STATUS;
$itemObject->SEQUENCE = $SEQUENCE;
$itemObject->VOTES = $VOTES;
$itemObject->LAST_VOTE_TIMESTAMP= $LAST_VOTE_TIMESTAMP;



array_push($json, $itemObject);
$json = json_encode($json, JSON_PRETTY_PRINT);

//echo it out so it can be retrieved by the client!
echo $json;

$conn->close();

?>