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
$loginQuery = "SELECT * FROM users";
$result = $conn->query($loginQuery);


	while($row = $result->fetch_assoc()) {
		
		echo "user id: ".$row['user_ID'];
		echo "<br/> \n";
		echo "first_name: ".urldecode($row['first_name']);
		echo "<br/> \n";
		echo "last_name: ".urldecode($row['last_name']);
		echo "<br/> \n";
		echo "email: ".urldecode($row['email']);
		echo "<br/> \n";
		echo "phone number: ".urldecode($row['contact_number']);
		echo "<br/> \n";
		echo date('d M Y H:i:s',$row['timestamp']);
		echo "<br/> \n";
		
	}

$conn->close();


?>