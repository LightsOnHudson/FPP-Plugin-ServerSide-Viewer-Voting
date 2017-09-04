<?php
error_reporting(0);
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

//if($DEBUG) {
	logEntry(str_repeat("-=", 50));
	logEntry("Incomming IP: ".$_SERVER['REMOTE_ADDR']);
	
//}

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
				
			}
		
	}
} elseif(!empty($_GET)) {
	
	if(!empty($_GET['API_TOKEN'])) {
		$CLIENT_TOKEN = $_GET['API_TOKEN'];
		
	}
	if($DEBUG) {
		foreach($_GET as $key => $value) {
			logEntry( "GET parameter '$key' has '$value'");
			
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
		if($DEBUG) 
			logEntry("We got sequence votes for site id: ".$SITE_ID);
			if($DEBUG) {
				//do not output to the screen only logentry for debug
				
				//print_r($SEQUENCE_VOTES);
			}
			
	
			
			aasort($SEQUENCE_VOTES,"votes");
			
			if($DEBUG) {
				//print_r($SEQUENCE_VOTES);
			}
			//unfortunately the last one has the value.. UGH 
			//TODO: get a better sort to but it on the top!
			$TOTAL_SEQUENCE_VOTES = count($SEQUENCE_VOTES);
			
			$SEQUENCE_IDS = array_keys($SEQUENCE_VOTES);
			
			if($DEBUG) {
				//print_r($SEQUENCE_IDS);
			}
			$SEQUENCE_WITH_HIGHEST_VOTES_FOR_SITE_ID = $SEQUENCE_IDS[count($SEQUENCE_IDS)-1];
			
			if($DEBUG) 
				logEntry("Sequence with highest votes: ".$SEQUENCE_WITH_HIGHEST_VOTES_FOR_SITE_ID);
			
			$VOTES = $SEQUENCE_VOTES[$SEQUENCE_WITH_HIGHEST_VOTES_FOR_SITE_ID]['votes'];
			
			logEntry("Received: ".$VOTES. " for sequence: ".$SEQUENCE_WITH_HIGHEST_VOTES_FOR_SITE_ID);
		
		
		
	} else {
		logEntry("NO Sequence votes data for site id: ".$SITE_ID);
		$VOTES = 0;
	}
} else {
	logEntry("Site  is not enabled");
	logEntry(str_repeat("-=", 50));
	exit(0);
}

if($SEQUENCE_WITH_HIGHEST_VOTES_FOR_SITE_ID != 0) {
	$SEQUENCE_INFO = getSequenceInfoForSequenceID($conn, $SEQUENCE_WITH_HIGHEST_VOTES_FOR_SITE_ID);
	
	if($DEBUG) {
		//print_r($SEQUENCE_INFO);
	}
	//strip the .FSEQ from it if it exists
	$FSEQ = urldecode($SEQUENCE_INFO[0]['fseq']);
	if($DEBUG)
		logEntry("FSEQ before decoding and trimming: ".$FSEQ);
	//$FSEQ = urldecode($FSEQ);
	$FSEQ=substr($FSEQ, 0, (strlen ($FSEQ) - strlen (strrchr($FSEQ,'.'))));
	
	//if there are spaces in the name have to replace with _ because the playlists require _ for spaces!!!
	$FSEQ = preg_replace('/\s+/', '_', $FSEQ);
	
	if($DEBUG)
		logEntry("Sending fseq: ".$FSEQ." back to system that asked");
	
	//	$LAST_VOTE_TIMESTAMP = $SEQUENCE_VOTES[$SEQUENCE_WITH_HIGHEST_VOTES_FOR_SITE_ID]['timestamp'];

		//get the last time this sequence was voted on
		$SQLGetLastTimestamp = "SELECT * FROM votes WHERE sequence_ID = ".$SEQUENCE_WITH_HIGHEST_VOTES_FOR_SITE_ID. " ORDER BY timestamp DESC";
		
		if($DEBUG) {
			logEntry("get sql last timestamp for sequence: ".$SQLGetLastTimestamp);
		}
		$result = $conn->query($SQLGetLastTimestamp);
		if($result !== false) {
			$rowSql = mysql_fetch_assoc($result);
			//the first one is the highest one
			$LAST_VOTE_TIMESTAMP = $rowSql['timestamp'];
		}
		

}


$json = array();
$itemObject = new stdClass();
$itemObject->CLIENT_TOKEN = $CLIENT_TOKEN;
$itemObject->SITE_ENABLED = $SITE_ENABLED_STATUS;
$itemObject->FSEQ = $FSEQ;
$itemObject->VOTES = $VOTES;
$itemObject->LAST_VOTE_TIMESTAMP= $LAST_VOTE_TIMESTAMP;



array_push($json, $itemObject);
$json = json_encode($json, JSON_PRETTY_PRINT);

//echo it out so it can be retrieved by the client!
echo $json;

$conn->close();

logEntry(str_repeat("-=", 50));

?>