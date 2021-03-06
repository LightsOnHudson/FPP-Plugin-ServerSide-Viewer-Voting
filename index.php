<?php
//error_reporting(0);
//added Dec 3 2015
//ob_implicit_flush();

$VOTE_ARROW_UP = "images/up-arrow-icon.jpg";
$VOTE_ARROW_DOWN = "images/down-arrow-icon.jpg";
$pluginName  = "FPPViewerVotingServer";
$SITE_ENABLED_STATUS = 1;
$SHOW_ENABLED_STATUS = 1;
$SEQUENCE_ENABLED_STATUS = 1;
$SEQUENCE_INACTIVE_STATUS = 2;

include_once 'functions.inc.php';
include_once 'config/userValues.inc';

$logFile = "/tmp". "/".$pluginName.".log";



$pluginConfigFile = (__DIR__)."/plugin." .$pluginName;
echo "<head> \n";
echo "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n";
echo "</head> \n";

if (file_exists($pluginConfigFile))
	$pluginSettings = parse_ini_file($pluginConfigFile);

//get the base page to return them back to
$BASE_PAGE = basename($_SERVER['PHP_SELF']);

$myPid = getmypid();

//$PORT = ReadSettingFromFile("PORT",$pluginName);
$PORT = $pluginSettings['PORT'];
$DEBUG = $pluginSettings['DEBUG'];
if($DEBUG) {
	logEntry("PluginConfig File: ".$pluginConfigFile);
	logEntry("DEBUG: ".$DEBUG);
}
logEntry(str_repeat("-=", 50));
logEntry("Incomming IP: ".$_SERVER['REMOTE_ADDR']);


$conn = dbConnect($DB_SERVER_IP, $DB_USER, $DB_PASS, $db);
if (!$conn)
{
	logEntry("Could not connect: " . mysql_error());
	
	//EXIT here because could not connect to database!
	logEntry(str_repeat("-=", 50));
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
		
			//if($DEBUG)
				logEntry( "Submitted site id: ".$SITE_ID);
		
			//show the available shows and then exit
			//currently not enableing getting shows, will do this later
				$SEQUENCES = getSequencesForSiteID($conn, $SITE_ID);
				if($DEBUG) 
					print_r($SEQUENCES);
				
				if($SEQUENCES == null) {
					echo "Sorry this site does not have any items to vote for at this time \n";
					logEntry( "Sorry this site does not have any items to vote for at this time");
					echo "<br/> <br/> \n";
					echo "Click <a href=\"".$BASE_PAGE."\">HERE</a> to try again later \n";
					exit(0);
				}
				//$SEQUENCES = getSequencesForShowID($conn, $SHOW_ID);
				
				
				
				printSequenceVoteForm($conn, $SEQUENCES);
				
				//	printFormSelectFromArray($conn, "SEQUENCE_ID", $SEQUENCES, "sequence_ID", null);
				
				logEntry(str_repeat("-=", 50));
				$conn->close();
				exit(0);
				/*
				$SHOWS = getShows($conn, $SITE_ID);
				
				echo "<form name=\"selectShow\" action=\"".$SERVER['PHP_SELF']."\" method=\"post\"> \n";
				printFormSelectFromArray($conn, "SHOW_ID", $SHOWS, "show_ID",null);
				echo "<input type=\"submit\" name=\"SUBMIT_SHOW_SELECT\" value=\"Select Show\"> \n";
				echo "</form> \n";
				
				$conn->close();
				exit(0);
				*/
			
	} elseif(isset($_POST['SUBMIT_SHOW_SELECT'])) {
		
		if(is_numeric($_POST['SHOW_ID'] ))
			$SHOW_ID = $_POST['SHOW_ID'];
			
			if($DEBUG)
				logEntry( "Submitted show id: ".$SHOW_ID);
			
			//show the available shows and then exit
			
			$SEQUENCES = getSequencesForShowID($conn, $SHOW_ID);
			
			echo "<form name=\"sequenceVote\" action=\"".$SERVER['PHP_SELF']."\" method=\"post\"> \n";
			
			printSequenceVoteForm($conn, $SEQUENCES);
			
		//	printFormSelectFromArray($conn, "SEQUENCE_ID", $SEQUENCES, "sequence_ID", null);
			
			echo "</form> \n";
			$conn->close();
			logEntry(str_repeat("-=", 50));
			exit(0);
		
	} elseif(isset($_POST['vote_down_x'])) {
		
		//the sequence ID is in the vote_down variable
		$VOTE_SEQUENCE = $_POST['sequence_ID'];
		
		if($DEBUG) {
			logEntry("We got a VOTE DOWN for sequence: ".$VOTE_SEQUENCE);
		}
		
		//write the vote to the votes table for value 0 for DOWN
		submitVote($conn, $VOTE_SEQUENCE, 0);
		//exit here since the user is inquiring about a site
		$conn->close();
		echo "Thank you for your vote <br/> \n";
		echo "<br/> <br/> \n";
		echo "Click <a href=\"".$BASE_PAGE."\">HERE</a> to vote again \n";
		logEntry(str_repeat("-=", 50));
		$conn->close();
		exit(0);
		
	} elseif(isset($_POST['vote_up_x'])){
		
		
		
		//the sequence ID is in the vote_up variable
		$VOTE_SEQUENCE = $_POST['sequence_ID'];
		
		if($DEBUG) {
			logEntry("We got a VOTE UP for sequence: ".$VOTE_SEQUENCE);
		}
	}
		//write the vote to the votes table for value 0 for DOWN
		submitVote($conn, $VOTE_SEQUENCE, 1);
		//exit here since the user is inquiring about a site
		$conn->close();
		
		echo "Thank you for your vote <br/> \n";
		echo "<br/> <br/> \n";
		echo "Click <a href=\"".$BASE_PAGE."\">HERE</a> to vote again \n";
		logEntry(str_repeat("-=", 50));
		$conn->close();
		exit(0);
	
}





///only show the below if there is no SITE ID in the POST


echo "<form name=\"selectSite\" action=\"".$SERVER['PHP_SELF']."\" method=\"post\"> \n";

$SITES = getSites($conn);
logEntry("displaying available sites");
//print select from array
printFormSelectFromArray($conn, "SITE_ID", $SITES, "site_ID",null, true);
echo "<br/> \n";
echo "<input type=\"submit\" name=\"SUBMIT_SITE_SELECT\" value=\"Select Site\"> \n";
echo "</form> \n";


$conn->close();
logEntry(str_repeat("-=", 50));

?>
