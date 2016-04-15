<?php
	include '../database.php';
	include 'functions.php';
	$conn = new mysqli($host, $username, $password, "deployment");
	
	if ($conn->connect_error) {
		var_dump(http_response_code(500));
		die("Connection failed: " . $conn->connect_error);
	}
	
	echo "<h1>Deployment Server</h1>";
	echo "<h3>Server Status:</h3>";
	
	echo "<table>
	<tr><td>CPU</td><td>RAM</td><td>Disk</td></tr>
	<tr><td>".get_server_cpu_usage()."%</td><td>".get_server_memory_usage()."%</td><td>".get_server_disk_usage()."%</td></tr>
	</table>";
	
	echo "<h3>Deployment Status</h3>";
	
	
	if(isset($_POST['payload'])){
		
		$headers = getallheaders();
		$hubSignature = $headers['X-Hub-Signature'];
		
		// Split signature into algorithm and hash
		list($algo, $hash) = explode('=', $hubSignature, 2);
		
		// Get payload
		$payload = file_get_contents('php://input');
		
		// Calculate hash based on payload and the secret
		$payloadHash = hash_hmac($algo, $payload, $secret);
		
		// Check if hashes are equivalent
		if ($hash !== $payloadHash) {
			// Kill the script or do something else here.
			die('Bad secret');
		}
		
        echo "Payload received, secret accepted\r\n";
		$payload = json_decode(stripslashes($_POST['payload']));
		
		$ref = $payload->{'ref'};
		if(strpos($ref, "refs/heads/master")===false){
			die("Push was not to master branch. Ignoring change");
		}
		
        $repo = $payload->{'repository'}->{'name'};
        $commit = $payload->{'head_commit'}->{'message'};
        $time = $payload->{'head_commit'}->{'timestamp'};
		$time = new DateTime($time);
		$time = $time->format('Y-m-d H:i:s');
        $committer = $payload->{'head_commit'}->{'committer'}->{'username'};
        $sql = "REPLACE INTO deployments VALUES ('$repo', '$commit', '$time', '$committer', 'awaiting deployment')";
        $result = $conn->query($sql);
        if($result){
			echo "Successfully updated SQL table for $repo\r\n";
			}else{
			echo "Something went wrong with SQL. Result was:\r\n";
			echo $result;
		}
        echo deploy($conn, $repo);
		}else{
        if(isset($_GET['repo'])){
			deploy($conn, $_GET['repo']);
		}
        $result = $conn->query("SELECT * FROM deployments");
        if($result->num_rows >0){
			echo    "<table>";
			echo    "<tr>";
			echo    "<td>Repository</td><td>Last Commit</td><td>Time</td><td>User</td><td>Status</td>";
			echo    "</tr>";
			while($row = $result->fetch_assoc()){
                echo "<tr><td>".$row['repo']."</td><td>".$row['commit']."</td><td>".$row['time']."</td><td>".$row['committer']."</td><td>".$row['status']."</td></tr>";
			}
		}
	}
	
	
?>
