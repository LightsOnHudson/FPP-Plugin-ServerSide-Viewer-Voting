#!/usr/bin/php
<?php
error_reporting(0);
//added Dec 3 2015
ob_implicit_flush();

include 'php_serial.class.php';
include_once('projectorCommands.inc');

$skipJSsettings = 1;
include_once '/opt/fpp/www/config.php';
include_once '/opt/fpp/www/common.php';

$pluginName  = "FPPViewerVotingServer";

include_once 'functions.inc.php';
include_once 'commonFunctions.inc.php';

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
		}
	}
} elseif(!empty($_GET)) {
	if($DEBUG) {
		foreach($_GET as $key => $value) {
			logEntry( "POST parameter '$key' has '$value'");
		}
	}
}


?>