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
		echo "Submitted site id: ".$_POST['SITE_ID'];
	}
}


echo "<form name=\"selectSite\" action=\"".$SERVER['PHP_SELF']."\" method=\"post\"> \n";

$SITES = getSites($conn);

//print select from array
printFormSelectFromArray($conn, "SITE_ID", $SITES, null);

echo "<input type=\"submit\" name=\"SUBMIT_SITE_SELECT\" value=\"Select Site\"> \n";
echo "</form> \n";


$conn->close();


?>