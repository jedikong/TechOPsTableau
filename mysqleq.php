<?php

$servername = "10.71.49.125:3306";
$username = "root";
$password = "root";
$dbname = "jira";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

mysqli_query($conn,'TRUNCATE TABLE eq');

/*
$sql = "INSERT INTO eq(project) VALUES('test')";

if ($conn->query($sql) === TRUE) {
    echo "New record created successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
*/
?>

<?php

$username = 'kkong';
$password = 'Tr3bl@d1';
              
$url = "https://bugs.corp.qc/rest/api/2/search?jql=labels%20%3D%20EQ&maxResults=10000&fields=key%2Cresolutiondate%2Cproject%2Clabels%2Cassignee%2Cstatus%2Ccreator%2Cupdated%2Cissuetype%2Ccreated%2Csummary&expand=changelog";
 
$ch = curl_init();
 
curl_setopt($_h, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );  
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
curl_setopt($ch, CURLOPT_VERBOSE, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
//curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");

$result = curl_exec($ch);
$issue_list = (curl_exec($ch)); 
$ch_error = curl_error($ch);
  
if ($ch_error) {echo "cURL Error: $ch_error";};

$decode = json_decode($issue_list); 
//echo $result;
curl_close($ch);

?>

<script type="text/javascript">
var jira = <?php echo $issue_list; ?>
</script>

<?php


$count = 1;

foreach ($decode->issues as $u) {
	foreach ($u->fields->labels as $label) {
		$jira_key = $u->key; // jira_key
		$project = $u->fields->project->key;//project
		$issuetype = $u->fields->issuetype->name;//issuetype
		$updated = $u->fields->updated;//updated
		$assignee = $u->fields->assignee->displayName;//assignee
		$creator = $u->fields->creator->displayName;//creator
		$create_date = $u->fields->created;//create_date
		$label = $label;//labels
		

		$labelarraylength = count($u->fields->labels);
		$labelarray= array();
		
		echo $labelarraylength;
		//echo $u->fields->labels[1];
		
		for($w = 0; $w<$labelarraylength; ++$w){
		$labelarray[] = $u->fields->labels[$w];
		};
		$summary = $u->fields->summary;//summary
		$summarysplit = split(":", $summary);
		$tasktype = split("-", $summarysplit[0]);
		$tasktype = $tasktype[0];
		$resolutiondate = $u->fields->resolutiondate;//resolutiondate
		$status = $u->fields->status->name;//status
		$fromarray = array();
		$toarray = array();
		$arraylength = count($u->changelog->histories);
		//echo $arraylength;
		//echo $u;
		for($i = 0;$i<$arraylength;++$i){
		$fromarray[]=$u->changelog->histories[$i]->items[0]->fromString;
		$toarray[]=$u->changelog->histories[$i]->items[0]->toString;
		};
		//echo "from";
		//print_r($fromarray);
		//echo "<br>to";
		//print_r($toarray);

		$fka =array_search('FKA-HiPo', $labelarray);
		echo $fka;
		print_r($labelarray);
		if(empty($fka)){

		} else {

		$fka = "FKA";			
		};

		$checkclosed = array_search('Closed',$toarray);
		$checkinprogress =array_search('In Progress',$toarray);
		

		//echo $checkclosed;
		//echo $checkinprogress;

		if (empty($checkinprogress)){
			$progressdateposition = $checkclosed;
		} else {
			$progressdateposition = $checkinprogress;
		};

		//echo $progressdateposition;
		$progressdate = $u->changelog->histories[$progressdateposition]->created;
		//echo $progressdate;
		echo"<br>";

	
		//echo $jira_key;
		$sql = "INSERT INTO eq(jira_key,project,issuetype,updated,assignee,creator,create_date,labels,summary,resolutiondate,inprogressdate,status,fka,taskytype) VALUES('$jira_key','$project','$issuetype','$updated','$assignee','$creator','$create_date','$label','$summary','$resolutiondate','$progressdate','$status','$fka','$tasktype')";

		if ($conn->query($sql) === TRUE) {	
			echo "New record created successfully";
		} else {
			echo "Error: " . $sql . "<br>" . $conn->error;
		};


	};
};

//$sql = "INSERT INTO eq(jira_key,project,issuetype,updated,assignee,creator,create_date,labels,summary,resolutiondate,inprogressdate) VALUES('$jira_key','$project','$issuetype','$updated','$assignee','$creator','$create_date','$label','$summary','$resolutiondate','$progressdate')";

//if ($conn->query($sql) === TRUE) {
 //   echo "New record created successfully";
//} else {
//    echo "Error: " . $sql . "<br>" . $conn->error;
//};


$conn->close();

?>
