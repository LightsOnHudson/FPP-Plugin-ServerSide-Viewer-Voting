<?
$page = $_SERVER['PHP_SELF']."?refresh=30";
$sec = "30";
header("Refresh: $sec; url=$page");
?>