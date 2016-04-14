
<?php
include '/../database.php';
$conn = new myqli($host, $username, $password, "deployment");

if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
}

if(isset($_REQUEST['payload'])){
        $payload = json_decode(stripslashes($_REQUEST['payload']));
        $repo = $payload->{'repository'}->{'name'};
        $commit = $payload->{'head_commit'}->{'message'};
        $time = $payload->{'head_commit'}->{'message'};
        $committer = $payload->{'head_commit'}->{'committer'}->{'username'};

        $sql = "REPLACE INTO deployment VALUES ('$repo', '$commit', '$time', '$committer')";
        $conn->query($sql);
}

?>

