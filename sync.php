<?php
//error_reporting(0);
//added Dec 3 2015
//ob_implicit_flush();




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
	
		foreach($_POST as $key => $value) {
			logEntry( "POST parameter '$key' has '$value'");
			if(!empty($_POST['API_TOKEN'])) {
				$CLIENT_TOKEN = $_POST['API_TOKEN'];
				$SYNC_CMD = $_POST['SYNC_CMD'];
			}
		
	}
} elseif(!empty($_GET)) {
	
		foreach($_GET as $key => $value) {
			logEntry( "GET parameter '$key' has '$value'");
			if(!empty($_GET['API_TOKEN'])) {
				$CLIENT_TOKEN = $_GET['API_TOKEN'];
				$SYNC_CMD = $_GET['SYNC_CMD'];
				$data = json_decode($_GET, TRUE);
				logEntry("Json data: ".$data);
				
				//print_r($data);
				if($DEBUG) {
					logEntry("JSON data back from server: ".$SERVER_IP);
					
					foreach($data[0] as $key => $value) {
						logEntry("KEY: ".$key. " = ".$value);
					}
					
				}
			}
		
	}
}
//sleep(2);
logEntry("Client token: ".$CLIENT_TOKEN);

logEntry("Sync what: ".$SYNC_CMD);

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

} else {
	logEntry("Site id: ".$SITE_ID." is not enabled with API Token: ".$CLIENT_TOKEN);
}
$conn->close();

?>