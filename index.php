<?php
include '../database.php';
$conn = new mysqli($host, $username, $password, "deployment");

if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
}




if(isset($_POST['payload'])){
        echo "Payload recieved\r\n";
        $payload = json_decode(stripslashes($_POST['payload']));
        $repo = $payload->{'repository'}->{'name'};
        $commit = $payload->{'head_commit'}->{'message'};
        $time = $payload->{'head_commit'}->{'message'};
        $committer = $payload->{'head_commit'}->{'committer'}->{'username'};
        $sql = "REPLACE INTO deployments VALUES ('$repo', '$commit', '$time', '$committer')";
        $result = $conn->query($sql);
        if($result){
                echo "Success";
        }else{
                echo "Something went wrong with SQL. Result was:\r\n";
                echo $result;
        }
}else{
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
?>
