<?php

//ad the sequence to the site in the database
function addSequenceToSite($conn, $SITE_ID, $SEQUENCE) {
	
	global $DEBUG;
	
	if($DEBUG)
		logEntry("Inside: ".__FUNCTION__,1,__FILE__,__LINE__);
		
		$timestamp = time();
		
		$sqlInsertSequence  = "INSERT INTO sequences (site_ID, fseq, timestamp) VALUES ";
		$sqlInsertSequence .= " (".$SITE_ID.",'".urlencode($SEQUENCE)."',".$timestamp.")";
		
		if($DEBUG) {
			logEntry("insert sequence sql: ".$sqlInsertSequence);
		}
		$result = $conn->query($sqlInsertSequence);
		if($result !== false) {
			return true;
		}
		
		return false;
}

function getSequenceWithHighestVotesForSite($conn, $SITE_ID) {
	
	global $DEBUG, $VOTE_UP_VALUE, $SHOW_ENABLED_STATUS, $SEQUENCE_ENABLED_STATUS;
	
	if($DEBUG)
		logEntry("Inside: ".__FUNCTION__,1,__FILE__,__LINE__);
	
	$timestamp = time();
	$VOTE_DATA = array();
	
	
	//first get the shows for a site!!!
	$showSQL = "SELECT * FROM shows WHERE site_ID = ".$SITE_ID." AND status_ID = ".$SHOW_ENABLED_STATUS;
	
	
	if($DEBUG) {
		logEntry("get shows info sql: ".$showSQL);
	}
	$result = $conn->query($showSQL);
	
	$VOTES = 0;
	
	if($result !== false) {
		while($row = $result->fetch_assoc()) {
			
			$SHOW_ID = $row['show_ID'];
			
			//now get the sequences that are enabled for the shows.
			$sequencesForShow = "SELECT * FROM sequences WHERE show_ID = ".$SHOW_ID." AND status_ID = ".$SEQUENCE_ENABLED_STATUS;
			
			
			if($DEBUG) {
				logEntry("get sequences for show info sql: ".$sequencesForShow);
			}
			$sequencesResult = $conn->query($sequencesForShow);
			
			while($sequencesRow = $sequencesResult->fetch_assoc()) {
				
				$SEQUENCE_ID = $sequencesRow['sequence_ID'];
				
				//how get the Votes for this sequence and add to the array
				
				$votesForSequence = "SELECT * FROM votes WHERE sequence_ID = ".$SEQUENCE_ID. " AND vote_value = ".$VOTE_UP_VALUE;
				
				if($DEBUG) {
					logEntry("get votes for sequences info sql: ".$votesForSequence);
				}
				$votesforsequencesResult = $conn->query($votesForSequence);
				
				while($votesforsequencesRow = $votesforsequencesResult->fetch_assoc()) {
					
					$votes = $votesforsequencesRow['vote_value'];
					//increment the vote value for this specici site and sequence
					//$VOTE_DATA[$SITE_ID][$SHOW_ID][$SEQUENCE_ID]++;
					$VOTE_DATA[$SEQUENCE_ID]['votes']++;
					
				}
			}
			
		}
		
		
	} else {
		return null;
	}
	
	return $VOTE_DATA;
	
}

//get the SEQUENCE information for a given Sequence ID
function aasort (&$array, $key) {
	$sorter=array();
	$ret=array();
	reset($array);
	foreach ($array as $ii => $va) {
		$sorter[$ii]=$va[$key];
	}
	asort($sorter);
	foreach ($sorter as $ii => $va) {
		$ret[$ii]=$array[$ii];
	}
	$array=$ret;
}
function aksort(&$array,$valrev=false,$keyrev=false) {
	if ($valrev) { arsort($array); } else { asort($array); }
	$vals = array_count_values($array);
	$i = 0;
	foreach ($vals AS $val=>$num) {
		$first = array_splice($array,0,$i);
		$tmp = array_splice($array,0,$num);
		if ($keyrev) { krsort($tmp); } else { ksort($tmp); }
		$array = array_merge($first,$tmp,$array);
		unset($tmp);
		$i = $i+$num;
		// Fixed from previous post: $i = $num;
	}
}
//get the votes for a client token
function getSiteIDFromAPIToken($conn, $CLIENT_TOKEN) {
	
	global $DEBUG, $SITE_ENABLED_STATUS;
	
	if($DEBUG)
		logEntry("Inside: ".__FUNCTION__,1,__FILE__,__LINE__);
	
	$timestamp = time();
	
	$SITE_INFO = array();
	//check that the site is ACTIVE value 1
	
	$siteQuery = "SELECT * FROM sites WHERE API_TOKEN = '".$CLIENT_TOKEN."' AND status_ID = ".$SITE_ENABLED_STATUS. " LIMIT 1";
	
	if($DEBUG) {
		logEntry("getSite info sql: ".$siteQuery);
	}
	$result = $conn->query($siteQuery);
	
	if($result !== false) {
		$row = $result->fetch_assoc();
			$SITE_ID = $row['site_ID'];
	
	
		
	
	}
	return $SITE_ID;
}

function submitVote($conn, $VOTE_SEQUENCE, $voteValue) {
	
	global $DEBUG;
	
	$timestamp = time();
	
	$sqlInsertVote = "INSERT into votes (timestamp, sequence_ID, vote_value) VALUES (".$timestamp.",".$VOTE_SEQUENCE.",".$voteValue.")";
	
	if($DEBUG)
		echo $sqlInsertVote."<br/> \n";
		
		$result = $conn->query($sqlInsertVote);
		
		if($result) {
			
			logEntry("Entered vote for sequence: ".$VOTE_SEQUENCE. " value: ".$voteValue);
		} else {
			
			logEntry("COULD NOT ENTER vote for sequence: ".$VOTE_SEQUENCE. " value: ".$voteValue);
		}
		
	
}
//print the sequence vote form
function printSequenceVoteForm($conn, $SEQUENCES) {
	
	global $DEBUG, $VOTE_ARROW_UP, $VOTE_ARROW_DOWN;
	
	
	echo "<table border=\"1\" cellspacing=\"1\" cellpadding=\"1\"> \n";
	
	foreach ($SEQUENCES as $seq) {
		
		echo "<tr> \n";
		
		echo "<td> \n";
		echo "...";
		$SEQUENCE_NAME = $from=substr(urldecode($seq['name']), 0, (strlen (urldecode($seq['name']))) - (strlen (strrchr(urldecode($seq['name']),'.'))));
		echo $SEQUENCE_NAME;
		echo "</td> \n";
		
		
		echo "<td> \n";
		echo "<input type=\"image\" name=\"vote_up\" value=\"".$seq['sequence_ID']."\" height=\"32\" width=\"32\" src=\"".$VOTE_ARROW_UP."\" alt=\"Vote Up\"> \n";
		echo "</td> \n";
		echo "<td> \n";
		echo "<input type=\"image\"  name=\"vote_down\" value=\"".$seq['sequence_ID']."\" height=\"32\" width=\"32\" src=\"".$VOTE_ARROW_DOWN."\" alt=\"Vote Down\"> \n";
		echo "</td> \n";
		
		echo "</tr> \n";
	//	echo "<option value=\"".$data_arr[$index_ID_name]."\">".$data_arr['name']." - ".$data_arr['description']."</option> \n";
		//print_r($data_arr);
		
		//echo "<input type=\"submit\" name=\"SUBMIT_SEQUENCE_VOTE\" value=\"VOTE\"> \n";
	}
	
	
	echo "</table> \n";
	
	
}
//print a select option of the array passed with the selected item as optional??
function printFormSelectFromArray($conn, $select_name, $data_array, $index_ID_name, $selected_item=null, $useStatusID) {
	
	echo "<select name=\"".$select_name."\"> \n";
	
	foreach ($data_array as $data_arr) {
		if($useStatusID) {
			if($data_arr['status_ID'] == 1) 
				echo "<option value=\"".$data_arr[$index_ID_name]."\">".$data_arr['name']." - ".$data_arr['description']."</option> \n";
			} else {
				echo "<option value=\"".$data_arr[$index_ID_name]."\">".$data_arr['name']." - ".$data_arr['description']."</option> \n";
			}
		//print_r($data_arr);
	}
	echo "</select> \n";
	
	
}

function getSequenceInfoForSequenceID($conn, $sequence_ID) {
	//This returns that
	
	global $DEBUG;
	
	$SEQUENCES = array();
	//$SITES = null;
	
	$sequenceQuery = "SELECT * FROM sequences WHERE sequence_ID = ".$sequence_ID . " LIMIT 1";
	$result = $conn->query($sequenceQuery);
	
	if(!empty($result))
		while($row = $result->fetch_assoc()) {
			$SEQUENCES[] = $row;
		}
	
	
	if($DEBUG) {
		echo "SEQUENCES DEBUG <br/> \n <pre>";
		print_r($SEQUENCES);
		echo "</pre> \n";
	}
	
	return $SEQUENCES;
}

function getSequencesForShowID($conn, $show_ID) {
	//This returns that
	
	global $DEBUG;
	
	$SEQUENCES = array();
	//$SITES = null;
	$SEQ_INDEX = 0;
	$siteQuery = "SELECT * FROM sequences WHERE show_ID = ".$show_ID;
	$result = $conn->query($siteQuery);
	
	if(!empty($result))
		while($row = $result->fetch_assoc()) {
			$SEQUENCES[$SEQ_INDEX] = $row;
			//if the name has not yet been defined, then use the FSEQ name..
			if($row['name'] === "") {
				$SEQUENCES[$SEQ_INDEX]['name'] = $row['fseq'];
				
			}
			$SEQ_INDEX++;
		}
	
	if($DEBUG) {
		echo "SEQUENCES DEBUG <br/> \n <pre>";
		print_r($SEQUENCES);
		echo "</pre> \n";
	}
	
	return $SEQUENCES;
}

function getSequencesForSiteID($conn, $site_ID) {
	//This returns that
	
	global $DEBUG;
	
	$SEQUENCES = array();
	//$SITES = null;
	
	$SEQ_INDEX = 0;
	$siteQuery = "SELECT * FROM sequences WHERE site_ID = ".$site_ID;
	$result = $conn->query($siteQuery);
	
	if(mysql_num_rows($result) <= 0 ) {
		return null;
	}
	if(!empty($result)) 
		while($row = $result->fetch_assoc()) {
			$SEQUENCES[$SEQ_INDEX] = $row;
			//if the name has not yet been defined, then use the FSEQ name..
			if($row['name'] === "") {
				$SEQUENCES[$SEQ_INDEX]['name'] = $row['fseq'];
				
			} 
			$SEQ_INDEX++;
		}
	
	
		if($DEBUG) {
			echo "SEQUENCES DEBUG <br/> \n <pre>";
			print_r($SEQUENCES);
			echo "</pre> \n";
		}
		
		return $SEQUENCES;
	
}


function getShows($conn, $site_ID) {
	//This returns that
	
	global $DEBUG;
	
	$SHOWS = array();
	//$SITES = null;
	
	$siteQuery = "SELECT * FROM shows WHERE site_ID = ".$site_ID;
	$result = $conn->query($siteQuery);
	
	if(!empty($result))
		while($row = $result->fetch_assoc()) {
			$SHOWS[] = $row;
		}
	
	
	if($DEBUG) {
		echo "SHOWS DEBUG <br/> \n <pre>";
		print_r($SHOWS);
		echo "</pre> \n";
	}
	
	return $SHOWS;
}
//get a list of enabled sites
//TODO: get by GEO location?? or zipcode range?
function getSites($conn) {
	//This returns that
	
	global $DEBUG;
	
	$SITES = array();
	//$SITES = null;
	
	$siteQuery = "SELECT * FROM sites";
	$result = $conn->query($siteQuery);
	
	if(!empty($result)) 
		while($row = $result->fetch_assoc()) {
			$SITES[] = $row;
		}
		
		
	if($DEBUG) {
		echo "SITES DEBUG <br/> \n <pre>";
		print_r($SITES);
		echo "</pre> \n";
	}
	
	return $SITES;
}


//create DB connection /return the connection
function dbConnect($servername, $username, $password, $dbname) {
	
	global $DEBUG, $TSMS_from, $MSG_MAINTENANCE;
	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	// Check connection
	if ($conn->connect_error) {
		
		logEntry("DATABASE CONNECTION ERROR");
		//send a message to the user that something is wrong, or maintenance is running
		sendTSMSMessage($MSG_MAINTENANCE,$TSMS_from);
		
		die("Connection failed: " . $conn->connect_error);
		exit(0);
		
		
		
	}
	
	return $conn;
}

//create unique GUID:
function getGUID(){
	if (function_exists('com_create_guid')){
		return com_create_guid();
	}else{
		mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
		$charid = strtoupper(md5(uniqid(rand(), true)));
		$hyphen = chr(45);// "-"
		$uuid = chr(123)// "{"
		.substr($charid, 0, 8).$hyphen
		.substr($charid, 8, 4).$hyphen
		.substr($charid,12, 4).$hyphen
		.substr($charid,16, 4).$hyphen
		.substr($charid,20,12)
		.chr(125);// "}"
		return $uuid;
	}
}

function tryGetHost($ip)
{
	$string = '';
	exec("dig +short -x $ip 2>&1", $output, $retval);
	if ($retval != 0)
	{
		// there was an error performing the command
	}
	else
	{
		$x=0;
		while ($x < (sizeof($output)))
		{
			$string.= $output[$x];
			$x++;
		}
	}
	
	if (empty($string))
		$string = $ip;
		else //remove the trailing dot
			$string = substr($string, 0, -1);
			
			return $string;
}

//celcius to farenhieht
function celciusToFarenheight($celcius) {
	return round(((9/5)*$celcius)+32);
	
}

//function to get all the falcon system elements
function PrintFalconSystemsSelect() {
	
	return;
}


//get a specific falcon id object from an ip address status page
function getFalconObjectValue($IP_ADDRESS, $objectName, $objectType) {
	
	global $DEBUG;
	$elements = getAllFalconObjects($IP_ADDRESS);
	
	
	$doc = new DOMDocument();
	$doc->loadHTML($elements);
	$xpath = new DOMXPath($doc);
	
	$result = $xpath->evaluate("//".$objectType."[@id='$objectName']");
	foreach ($result as $node) {
		
		return $node->nodeValue;
		
	}
}

//get the processor temp
//get all items and then get the processor temp
function getProcessorTemp($IP_ADDRESS) {
	
	$elements = getAllFalconObjects($IP_ADDRESS);
	
	
	$doc = new DOMDocument();
	$doc->loadHTML($elements);
	$xpath = new DOMXPath($doc);
	
	$result = $xpath->evaluate("//td[@id='fldChipTemp']");
	foreach ($result as $node) {
		
		return $node->nodeValue;
		
	}
			
	
}


//get all the falcon telements
function getAllFalconObjects($IP_ADDRESS) {
	
	global $DEBUG;
	logEntry("Inside getting all falcon objects for ip address: ".$IP_ADDRESS);
	
	//for the falcon board
	//index.htm
	
	$URL = "http://".$IP_ADDRESS."/index.htm";
	//$elements= file_get_html($URL);
	$elements = file_get_contents($URL);
	return $elements;
	//foreach($elements->find('element') as $ele) {
		
	//	print_r($ele);
		
	//}
	
	//return or output
}

function sendTCP($IP, $PORT, $cmd) {
	
	
/* Create a TCP/IP socket. */
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === false) {
    logEntry("socket_create() failed: reason: " . socket_strerror(socket_last_error()));
} else {
   logEntry("TCPIP Socket Created");
}


$result = socket_connect($socket, $IP, $PORT);
if ($result === false) {
    logEntry("socket_connect() failed. Reason: ($result) " . socket_strerror(socket_last_error($socket)));
} else {
    logEntry("TCPIP CONNECTED");
}


socket_write($socket, $cmd, strlen($cmd));


logEntry("Reading response");
while ($out = socket_read($socket, 2048)) {
    logEntry($out);
}

logEntry("Closing socket...");
socket_close($socket);
logEntry("OK");

}
function hex_dump($data, $newline="\n")
{
  static $from = '';
  static $to = '';

  static $width = 16; # number of bytes per line

  static $pad = '.'; # padding for non-visible characters

  if ($from==='')
  {
    for ($i=0; $i<=0xFF; $i++)
    {
      $from .= chr($i);
      $to .= ($i >= 0x20 && $i <= 0x7E) ? chr($i) : $pad;
    }
  }

  $hex = str_split(bin2hex($data), $width*2);
  $chars = str_split(strtr($data, $from, $to), $width);

$HEX_OUT ="";
  $offset = 0;
  foreach ($hex as $i => $line)
  {
    $HEX_OUT.= sprintf('%6X',$offset).' : '.implode(' ', str_split($line,2)) . ' [' . $chars[$i] . ']';
    $offset += $width;
  }
return $HEX_OUT;
}

function decode_code($code)
{
    return preg_replace_callback('@\\\(x)?([0-9a-f]{2,3})@',
        function ($m) {
            if ($m[1]) {
                $hex = substr($m[2], 0, 2);
                $unhex = chr(hexdec($hex));
		echo "UNHEX: ".$unhex;
                if (strlen($m[2]) > 2) {
                    $unhex .= substr($m[2], 2);
                }
                return $unhex;
            } else {
                return chr(octdec($m[2]));
            }
        }, $code);
}


function logEntry($data) {

	global $logFile,$myPid,$callBackPid;
	
	if($callBackPid != "") {
		$data = $_SERVER['PHP_SELF']." : [".$callBackPid.":".$myPid."] ".$data;
	} else { 
	
		$data = $_SERVER['PHP_SELF']." : [".$myPid."] ".$data;
	}
	$logWrite= fopen($logFile, "a") or die("Unable to open file!");
	fwrite($logWrite, date('Y-m-d h:i:s A',time()).": ".$data."\n");
	fclose($logWrite);
}


function escapeshellarg_special($file) {
	return "'" . str_replace("'", "'\"'\"'", $file) . "'";
}


function processCallback($argv) {

	global $DEBUG,$pluginName;
	
	
	if($DEBUG)
		print_r($argv);
	//argv0 = program
	
	//argv2 should equal our registration // need to process all the rgistrations we may have, array??
	//argv3 should be --data
	//argv4 should be json data
	
	$registrationType = $argv[2];
	$data =  $argv[4];
	
	logEntry("PROCESSING CALLBACK");
	$clearMessage=FALSE;
	
	switch ($registrationType)
	{
		case "media":
			if($argv[3] == "--data")
			{
				$data=trim($data);
				logEntry("DATA: ".$data);
				$obj = json_decode($data);
	
				$type = $obj->{'type'};
	
				switch ($type) {
						
					case "sequence":
	
						//$sequenceName = ;
						processSequenceName($obj->{'Sequence'});
							
						break;
					case "media":
							
						logEntry("We do not understand type media at this time");
							
						exit(0);
	
						break;
	
					default:
						logEntry("We do not understand: type: ".$obj->{'type'}. " at this time");
						exit(0);
						break;
	
				}
	
	
			}
	
			break;
			exit(0);
				
		default:
			exit(0);
	
	}
	


}
?>
