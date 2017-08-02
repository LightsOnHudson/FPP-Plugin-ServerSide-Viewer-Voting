<?php
//error_reporting(0);
//added Dec 3 2015
//ob_implicit_flush();

$VOTE_ARROW_UP = "images/up-arrow-icon.jpg";
$VOTE_ARROW_DOWN = "images/down-arrow-icon.jpg";
$pluginName  = "FPPViewerVotingServer";

include_once 'functions.inc.php';
include_once 'config/userValues.inc';

$logFile = "/tmp". "/".$pluginName.".log";



$pluginConfigFile = (__DIR__)."/plugin." .$pluginName;
logEntry("PluginConfig File: ".$pluginConfigFile);

if (file_exists($pluginConfigFile))
	$pluginSettings = parse_ini_file($pluginConfigFile);


$myPid = getmypid();

//$PORT = ReadSettingFromFile("PORT",$pluginName);
$PORT = $pluginSettings['PORT'];
$DEBUG = $pluginSettings['DEBUG'];
logEntry("DEBUG: ".$DEBUG);




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

if($DEBUG) {
	print_r($_POST);
}

if(!empty($_POST)) {
	
	if(isset($_POST['SUBMIT_SITE_SELECT'])) {
		if(is_numeric($_POST['SITE_ID'] ))
			$SITE_ID = $_POST['SITE_ID'];
		
			echo "Submitted site id: ".$SITE_ID;
		
			//show the available shows and then exit
			
			$SHOWS = getShows($conn, $SITE_ID);
			
			echo "<form name=\"selectShow\" action=\"".$SERVER['PHP_SELF']."\" method=\"post\"> \n";
			printFormSelectFromArray($conn, "SHOW_ID", $SHOWS, "show_ID",null);
			echo "<input type=\"submit\" name=\"SUBMIT_SHOW_SELECT\" value=\"Select Show\"> \n";
			echo "</form> \n";
	} elseif(isset($_POST['SUBMIT_SHOW_SELECT'])) {
		
		if(is_numeric($_POST['SHOW_ID'] ))
			$SHOW_ID = $_POST['SHOW_ID'];
			
			echo "Submitted show id: ".$SHOW_ID;
			
			//show the available shows and then exit
			
			$SEQUENCES = getSequences($conn, $SHOW_ID);
			
			echo "<form name=\"sequenceVote\" action=\"".$SERVER['PHP_SELF']."\" method=\"post\"> \n";
			
			printSequenceVoteForm($conn, $SEQUENCES);
			
		//	printFormSelectFromArray($conn, "SEQUENCE_ID", $SEQUENCES, "sequence_ID", null);
			
			echo "</form> \n";
		
	} elseif(isset($_POST['vote_down'])) {
		
		//the sequence ID is in the vote_down variable
		$VOTE_SEQUENCE = $_POST['vote_down'];
		
		if($DEBUG) {
			logEntry("We got a VOTE DOWN for sequence: ".$VOTE_SEQUENCE);
		}
		
		//write the vote to the votes table for value 0 for DOWN
		submitVote($conn, $VOTE_SEQUENCE, 0);
		
	} elseif(isset($_POST['vote_up'])){
		
		
		
		//the sequence ID is in the vote_up variable
		$VOTE_SEQUENCE = $_POST['vote_up'];
		
		if($DEBUG) {
			logEntry("We got a VOTE UP for sequence: ".$VOTE_SEQUENCE);
		}
	}
		//write the vote to the votes table for value 0 for DOWN
		submitVote($conn, $VOTE_SEQUENCE, 1);
	
	//exit here since the user is inquiring about a site
	$conn->close();
	exit(0);
}





///only show the below if there is no SITE ID in the POST


echo "<form name=\"selectSite\" action=\"".$SERVER['PHP_SELF']."\" method=\"post\"> \n";

$SITES = getSites($conn);

//print select from array
printFormSelectFromArray($conn, "SITE_ID", $SITES, "site_ID");

echo "<input type=\"submit\" name=\"SUBMIT_SITE_SELECT\" value=\"Select Site\"> \n";
echo "</form> \n";


$conn->close();


?>