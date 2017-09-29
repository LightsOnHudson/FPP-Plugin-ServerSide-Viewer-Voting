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
$SEQUENCE_INACTIVE_STATUS = 2;

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
				$RESET_CMD = $_POST['CMD'];
				if(isset($_POST['SEQUENCE'])) {
					$SEQUENCE = urldecode($_POST['SEQUENCE']);
				} elseif(isset($_POST['SONG'])) {
					$SEQUENCE = urldecode($_POST['SONG']);
				}
				
				$data = json_decode($_POST, TRUE);
				logEntry("Json data: ".$data);
				
			}
		
	}
} elseif(!empty($_GET)) {
	
		foreach($_GET as $key => $value) {
			logEntry( "GET parameter '$key' has '$value'");
			if(!empty($_GET['API_TOKEN'])) {
				$CLIENT_TOKEN = $_GET['API_TOKEN'];
				$RESET_CMD = $_GET['CMD'];
				
				if(isset($_GET['SEQUENCE'])) {
					$SEQUENCE = urldecode($_GET['SEQUENCE']);
				} elseif(isset($_GET['SONG'])) {
					$SEQUENCE = urldecode($_GET['SONG']);
				} 
			}
		
	}
}

$RESET_CMD = trim(strtoupper($RESET_CMD));
//sleep(2);
logEntry("Client token: ".$CLIENT_TOKEN);

logEntry("Reset what: ".$RESET_CMD);

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
	
	if($DEBUG) {
		logEntry("Site is enabled: attempting a reset command");
	}
	//first set ALL sequences temporarily to status id disabled
	//inactive = 2
	

	switch($RESET_CMD) {
		
		case "SEQUENCE":
			
			if($DEBUG) {
				logEntry("Resetting of a sequence");
				
			}
			if(trim($SEQUENCE) == "") {
				if($DEBUG) {
					logEntry("Reset of Sequence requested, but no sequence/song sent in");
					exit(0);
				}
			}
			$sqlGetSequencesForSite = "SELECT * FROM sequences WHERE site_ID = ".$SITE_ID . " AND status_ID = ".$SEQUENCE_ENABLED_STATUS;
			$result = $conn->query($sqlGetSequencesForSite);
			
			if($result !== false) {
				
				while($row = $result->fetch_assoc()) {
					
					//search through the sequences to find the one taht was sent in
					if(strtoupper(urldecode(trim($row['fseq']))) == strtoupper(trim($SEQUENCE))) {
						
						//Sequence_ID = 
						$SEQUENCE_ID = $row['sequence_ID'];
						
						//delete all the votes for this sequence iD in the table of $VOTES
						
						resetVotesForSequenceID($conn, $SEQUENCE_ID);
						break;
					}
					
				}
				
			}
			
			
			break;
			
		case "SONG":
			
			if(trim($SEQUENCE) == "") {
				if($DEBUG) {
					logEntry("Reset of Sequence requested, but no sequence/song sent in");
					exit(0);
				}
			}
			
			$sqlGetSequencesForSite = "SELECT * FROM sequences WHERE site_ID = ".$SITE_ID . " AND status_ID = ".$SEQUENCE_ENABLED_STATUS;
			$result = $conn->query($sqlGetSequencesForSite);
			
			if($result !== false) {
				
				while($row = $result->fetch_assoc()) {
					
					//search through the sequences to find the one taht was sent in
					if(strtoupper(urldecode(trim($row['fseq']))) == strtoupper(trim($SEQUENCE))) {
						
						//Sequence_ID =
						$SEQUENCE_ID = $row['sequence_ID'];
						resetVotesForSequenceID($conn, $SEQUENCE_ID);
						break;
					}
					
				}
				
			}
			
			break;
			
		case "ALL":
			
			
			//ALL
			$sqlGetSequencesForSite = "SELECT * FROM sequences WHERE site_ID = ".$SITE_ID . " AND status_ID = ".$SEQUENCE_ENABLED_STATUS;
			$result = $conn->query($sqlGetSequencesForSite);
			
			if($result !== false) {
				
				while($row = $result->fetch_assoc()) {
					
					//delete all votes!!!
						
						//Sequence_ID =
						$SEQUENCE_ID = $row['sequence_ID'];
						resetVotesForSequenceID($conn, $SEQUENCE_ID);
						break;
					
					
				}
				
			}
			break;
			
		default:
			if($DEBUG) {
				logEntry("NO RESET CMD SENT");
			}
			break;
		
	}
	
	
	
	
	
	
	
} else {
	logEntry("Site id: ".$SITE_ID." is not enabled with API Token: ".$CLIENT_TOKEN);



}


$conn->close();

?>