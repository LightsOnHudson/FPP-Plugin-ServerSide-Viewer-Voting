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


$con = mysql_connect($DB_SERVER_IP,$DB_USER,$DB_PASS);
if (!$con)
{
	logEntry("Could not connect: " . mysql_error());
}



mysql_select_db($db, $con);






?>