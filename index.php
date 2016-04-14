<?php
include '../database.php';
$conn = new mysqli($host, $username, $password, "deployment");

if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
}

if(isset($_POST['payload'])){
        echo "Payload received\r\n";
        $payload = json_decode(stripslashes($_POST['payload']));
        $repo = $payload->{'repository'}->{'name'};
        $commit = $payload->{'head_commit'}->{'message'};
        $time = $payload->{'head_commit'}->{'message'};
        $committer = $payload->{'head_commit'}->{'committer'}->{'username'};
        $sql = "REPLACE INTO deployments VALUES ('$repo', '$commit', '$time', '$committer', 'awaiting deployment')";
        $result = $conn->query($sql);
        if($result){
                echo "Successfully updated SQL";
        }else{
                echo "Something went wrong with SQL. Result was:\r\n";
                echo $result;
        }
                gitPull($conn, $repo, true);
}else{
        if(isset($_GET['repo'])){
                gitPull($conn, $_GET['repo']);
        }
        $result = $conn->query("SELECT * FROM deployments");
        if($result->num_rows >0){
                echo    "<table>";
                echo    "<tr>";
                echo    "<td>Repository</td><td>Last Commit</td><td>Time</td><td>User</td>";
                echo    "</tr>";
                while($row = $result->fetch_assoc()){
                echo "<tr><td>".$row['repo']."</td><td>".$row['commit']."</td><td>".$row['time']."</td><td>".$row['committer']."</td></tr>";
                }
        }
}


function gitPull($conn, $repo, $out=false){
        $managing = TRUE;
        switch($repo) {
                case "Film-Night":
                        $dir = "/var/www/films.jakestockwin.co.uk/public_html";
                        break;
                case "Deployment":
                        $dir = "/var/www/deployment.jakestockwin.co.uk/public_html";
                        break;
                default:
                        $managing = FALSE;
        }

        if($managing){
			    $sql = "UPDATE deployments SET status = 'Deployed using git pull' WHERE repo = '$repo'";
                $conn->query($sql);
                $output = shell_exec("cd $dir && git pull");
                if($out){
                        echo $output;
                }
        }else{
                //deployment is not hosted by this server.
                $sql = "UPDATE deployments SET status = 'Deployment not handled by this server' WHERE repo = '$repo'";
                $conn->query($sql);
        }
}

?>
