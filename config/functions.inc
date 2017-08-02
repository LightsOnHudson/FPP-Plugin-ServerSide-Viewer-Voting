<?php

//get the events for a tournament
function getEventsForTournamentId($tournamentId) {
	GLOBAL $DEBUG;

	$getEventsQuery = "SELECT * FROM tournamentDetails";

	if($DEBUG)
		echo $getEventsQuery;


	$result = mysql_query($getEventsQuery);

	if($result) {
		if($DEBUG)
		echo "Success! <br/>";

	} else {
		if($DEBUG)
		echo "error getting events! <br/> \n";
		return null;
	}


	if(mysql_num_rows($result) > 0 ) {

	$eventCount=0;
	$events=array();
	
		while ($eventsRow = mysql_fetch_assoc($result)) {

		$eventIDs = $eventsRow['events'];
		
		}
		
		$eventNumbers = explode(",",$eventIDs);
		print_r($eventNumbers);
	}
	
	return null;
	
}

//get division based on average

function getDivisionFromAverage($average) {


	GLOBAL $DEBUG;

	$getDivisionQuery = "SELECT * FROM divisions";

	if($DEBUG)
		echo $getDivisionQuery;


	$result = mysql_query($getDivisionQuery);

	if($result) {
		if($DEBUG)
		echo "Success! <br/>";

	} else {
		if($DEBUG)
		echo "error getting division! <br/> \n";
	}


	if(mysql_num_rows($result) > 0 ) {


		while ($divisionRow = mysql_fetch_assoc($result)) {


			$divisionId = $divisionRow['divisionId'];
			$divisionName = $divisionRow['divisionName'];
			$divisionMinimum = $divisionRow['divisionMinimum'];
			$divisionMaximum = $divisionRow['divisionMaximum'];

			if (((int)$average >= (int)$divisionMinimum) && ((int)$average <= (int)$divisionMaximum)) {
				return $divisionName;

			}

		}

	}
	return null;
}

//add a tournament

function createTournament($houseId,$tournamentName,$tournamentDate, $tournamentDuration) {

	GLOBAL $DEBUG;

	$addTournament = "INSERT into tournaments (houseId, tournamentName, tournamentDate, duration, created) values (".$houseId.",'".$tournamentName."','".$tournamentDate."','".$tournamentDuration."',".time().")";

	if($DEBUG)
		echo $addTournament."<br/> \n";

	$result = mysql_query($addTournament);

	if($result) {

		echo "success! <br/> \n";
	} else {

		echo "error inserting tournament <br/>";
	}
	//print_r($result);

	addToLog($houseId, $addTournament);


	updateAccountModified($accountId);



}

//add an average for an account

function addAverageForAccountId($accountId, $average) {

	GLOBAL $DEBUG;

	$addAverageQuery = "INSERT into averages (accountId, average, created) values (".$accountId.",".$average.",".time().")";

	if($DEBUG)
	echo $addAverageQuery."<br/> \n";

	$result = mysql_query($addAverageQuery);

	if($result) {

		echo "success! <br/> \n";
	} else {

		echo "error inserting average <br/>";
	}
	//print_r($result);

	addToLog($accountId, $addAverageQuery);


	updateAccountModified($accountId);



}


//update the account modified date
function updateAccountModified($accountId) {


	GLOBAL $DEBUG;

	$updateQuery = "UPDATE accountDetails SET modified=".time()." WHERE accountId=".$accountId;

	$updateResult = mysql_query($updateQuery);

	addToLog($accountId,$updateQuery);

}

//get the account id from the first and last name

function getAccountIdFromHouseName($houseName) {


	//$DEBUG=true;
	$getAccountIdQuery = "SELECT * FROM houses WHERE houseName='".$houseName."' LIMIT 1";
	$getAccountIdResult = mysql_query($getAccountIdQuery);

	if($DEBUG)
		echo $getAccountIdQuery."<br/> \n";

	if(mysql_num_rows($getAccountIdResult) > 0) {
	
		$accountIdRow = mysql_fetch_assoc($getAccountIdResult);

		$houseId = $accountIdRow['houseId'];

		return $houseId;
	} else {

		return null;
	}

}

//get the account id from the first and last name

function getAccountIdFromName($firstName,$lastName) {


	//$DEBUG=true;
	$getAccountIdQuery = "SELECT * FROM accounts WHERE firstName='".$firstName."' AND lastName='".$lastName."' LIMIT 1";
	$getAccountIdResult = mysql_query($getAccountIdQuery);

	if($DEBUG)
		echo $getAccountIdQuery."<br/> \n";

	if(mysql_num_rows($getAccountIdResult) > 0) {
	
		$accountIdRow = mysql_fetch_assoc($getAccountIdResult);

		$accountId = $accountIdRow['accountId'];

		return $accountId;
	} else {

		return null;
	}

}

//add the housenames as a function to fill int

function getHouseNamesForForm() {

	$names=array();
	$n=0;	
	$getNamesQuery = "SELECT * FROM houses";
	$getNamesResult = mysql_query($getNamesQuery);

	if($DEBUG)
		echo $getNamesQuery."<br/> \n";

	if(mysql_num_rows($getNamesResult) > 0) {

		while ($getNamesRow = mysql_fetch_assoc($getNamesResult)) {

			$names[$n] = $getNamesRow['houseName'];
			$n++;
		}

		return $names;

	} else {
		return null;

	}

}

//add the names as a function to fill int

function getNamesForForm() {

	$names=array();
	$n=0;	
	$getNamesQuery = "SELECT * FROM accounts";
	$getNamesResult = mysql_query($getNamesQuery);

	if($DEBUG)
		echo $getNamesQuery."<br/> \n";

	if(mysql_num_rows($getNamesResult) > 0) {

		while ($getNamesRow = mysql_fetch_assoc($getNamesResult)) {

			$names[$n] = $getNamesRow['firstName']. " ".$getNamesRow['lastName'];
			$n++;
		}

		return $names;

	} else {
		return null;

	}

}


//get latest averages of account

function getAverages($accountId) {

        global $DEBUG;

        $getAverageQuery= "SELECT * FROM averages WHERE accountId=".$accountId. " ORDER BY created DESC";
        $getAverageResult = mysql_query($getAverageQuery);

        if($DEBUG)
                echo $getAverageQuery."<br/> \n";


	if(mysql_num_rows($getAverageResult) > 0 ) {

		$averages = mysql_fetch_assoc($getAverageResult);


	        return $averages['average'];
	} else {

		return null;
	}

}


//get the total number of accounts configured on the system

function getAccountQty() {

	global $DEBUG;
	
	$getAccountQtyQuery = "SELECT accountId FROM accounts";
	$getAccountQtyResult = mysql_query($getAccountQtyQuery);
	
	if($DEBUG)
		echo $getAccountQtyQuery."<br/> \n";
		
	$accountQty = mysql_num_rows($getAccountQtyResult);
	

	return $accountQty;

}

//add an account transaction for accounting purposes
function addAccountTransaction($accountId, $downloadQty=0, $successfulQty=0, $UnSuccessfulQty=0) {

global $DEBUG;
	
	if($DEBUG)
		echo "Entering into Account Transaction: AccountId: ".$accountId." DownloadQty: ".$downloadQty. " SuccessfulQty: ".$successfulQty." UnSuccessfulQty: ".$UnSuccessfulQty."<br/> \n";
		
	$insertQuery = "INSERT INTO account_transaction ";
	$insertQuery .= " (accountId, downloadQty, successfulQty, unSuccessfulQty, timestamp) values (";
	$insertQuery .= "'".$accountId."'";
	$insertQuery .= ",'".$downloadQty."'";
	$insertQuery .= ",'".$successfulQty."'";
	$insertQuery .= ",'".$UnSuccessfulQty."'";
	$insertQuery .= ",'".time()."'";
	$insertQuery .= ")";
	
	if($DEBUG)
		echo $insertQuery."<br/> \n";
		
	$insertResult = mysql_query($insertQuery);


}
//add an entry to the log
function addToLog($accountId, $data) {


	global $DEBUG;
	
	if($DEBUG)
		echo "Entering into log: AccountId: ".$accountId." Data: ".$data."<br/> \n";
		
	$insertLogQuery = "INSERT INTO log ";
	$insertLogQuery .= " (accountId, data) values (";
	$insertLogQuery .= "'".$accountId."'";
	$insertLogQuery .= ",'".quotemeta($data)."'";
	$insertLogQuery .= ")";
	
	if($DEBUG)
		echo $insertLogQuery."<br/> \n";
		
	$insertLogResult = mysql_query($insertLogQuery);

	if($DEBUG)
		echo "insert result: ".$insertLogResult."<br/> \n";	
}


function position_in_string($haystack, $needle, $offset = 0)
{
    if(trim($haystack) != "" && trim($needle) != "" && $offset <= strlen($haystack))
    {
        $last_pos = $offset;
        $found = false;
        while(($curr_pos = strpos($haystack, $needle, $last_pos)) !== false)
        {
            $found = true;
            $last_pos = $curr_pos + 1;
        }
        if($found)
        {
                 return $last_pos - 1;
        }
        else
        {
            return false;
        }
    }
    else
    {
        return false;
    }
}

function strrpos_string($haystack, $needle, $offset = 0)
{
    if(trim($haystack) != "" && trim($needle) != "" && $offset <= strlen($haystack))
    {
        $last_pos = $offset;
        $found = false;
        while(($curr_pos = strpos($haystack, $needle, $last_pos)) !== false)
        {
            $found = true;
            $last_pos = $curr_pos + 1;
        }
        if($found)
        {
                return true;
//                 return $last_pos - 1;
        }
        else
        {
            return false;
        }
    }
    else
    {
        return false;
    }
}




function send_email_response($to, $response) {

	global $DEBUG;

require("smtp/smtp.php");
require("sasl/sasl.php");
//$from = $to;
$from = 'benhigleyvoip@gmail.com';
//$to = 'ben.higley@amberfin.com';

$smtp=new smtp_class;
$smtp->host_name="smtp.gmail.com";
$smtp->host_port='465';
$smtp->user='benhigleyvoip@gmail.com';
$smtp->password='Bpug0815';
$smtp->ssl=1;
$smtp->debug=1;       //0 here in production
$smtp->html_debug=1; //same

$smtp->SendMessage($from,array($to),array(
"From: $from",
"To: $to",
"Subject: $response",
"Date: ".strftime("%a, %d %b %Y %H:%M:%S %Z")
,"message body"));


}


?>
