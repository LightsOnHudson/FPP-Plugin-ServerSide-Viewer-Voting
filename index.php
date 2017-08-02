<?php
//error_reporting(0);
//added Dec 3 2015
//ob_implicit_flush();


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
			
			$SHOWS = getShows($conn, $SITE_ID, null, "site_ID");
			
			echo "<form name=\"selectShow\" action=\"".$SERVER['PHP_SELF']."\" method=\"post\"> \n";
			printFormSelectFromArray($conn, "SHOW_ID", $SHOWS, null);
			echo "<input type=\"submit\" name=\"SUBMIT_SHOW_SELECT\" value=\"Select Show\"> \n";
			echo "</form> \n";
	} elseif(isset($_POST['SUBMIT_SHOW_SELECT'])) {
		
		if(is_numeric($_POST['SHOW_ID'] ))
			$SHOW_ID = $_POST['SHOW_ID'];
			
			echo "Submitted show id: ".$SHOW_ID;
			
			//show the available shows and then exit
			
			$SEQUENCES = getSequences($conn, $SHOW_ID, null,"show_ID");
			
			echo "<form name=\"selectSequence\" action=\"".$SERVER['PHP_SELF']."\" method=\"post\"> \n";
			printFormSelectFromArray($conn, "SEQUENCE_ID", $SEQUENCES, null);
			echo "<input type=\"submit\" name=\"SUBMIT_SEQUENCE_VOTE\" value=\"VOTE\"> \n";
			echo "</form> \n";
		
	}
	
	
	//exit here since the user is inquiring about a site
	$conn->close();
	exit(0);
}





///only show the below if there is no SITE ID in the POST


echo "<form name=\"selectSite\" action=\"".$SERVER['PHP_SELF']."\" method=\"post\"> \n";

$SITES = getSites($conn);

//print select from array
printFormSelectFromArray($conn, "SITE_ID", $SITES, null, "site_ID");

echo "<input type=\"submit\" name=\"SUBMIT_SITE_SELECT\" value=\"Select Site\"> \n";
echo "</form> \n";


$conn->close();


?>