<?php
include '../database.php';
$conn = new mysqli($host, $username, $password, "deployment");

if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
}



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
		$payload = json_decode($payload);
        $repo = $payload->{'repository'}->{'name'};
        $commit = $payload->{'head_commit'}->{'message'};
        $time = $payload->{'head_commit'}->{'timestamp'};
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


function deploy($conn, $repo){
        $managing = TRUE;
        switch($repo) {
                case "Film-Night":
                        $dir = "/var/www/films.jakestockwin.co.uk/public_html";
                        break;
                case "deployment":
                        $dir = "/var/www/deployment.jakestockwin.co.uk/public_html";
                        break;
                default:
                        $managing = FALSE;
        }

        if($managing){
			$sql = "UPDATE deployments SET status = 'Deployed using git pull' WHERE repo = '$repo'";
			$conn->query($sql);
			$output = shell_exec("cd $dir && git pull");
			echo "Executing a git pull in directory $dir\r\n";
			echo $output;
        }else{
            echo "$repo is not hosted by this server";
            $sql = "UPDATE deployments SET status = 'Deployment not handled by this server' WHERE repo = '$repo'";
            $conn->query($sql);
        }
}

?>
