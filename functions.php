<?php

function get_server_memory_usage(){

    $free = shell_exec('free');
    $free = (string)trim($free);
    $free_arr = explode("\n", $free);
    $mem = explode(" ", $free_arr[1]);
    $cache = explode(" ", $free_arr[2]);
    $mem = array_filter($mem);
    $cache = array_filter($cache);
    $mem = array_merge($mem);
    $cache = array_merge($cache);
    $memory_usage = round($mem[2]/($mem[1]+$cache[3])*100);
    return $memory_usage;
}


function get_server_cpu_usage(){

    $load = sys_getloadavg();
    return $load[0];

}

function get_server_disk_usage(){
        $free = disk_free_space("/");
        $total = disk_total_space("/");
        return round((1-$free/$total)*100);
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
				case "book-sales":
						$dir = "/home/jake/book-sales";
						break;
                default:
                        $managing = FALSE;
        }

        if($managing){
			$sql = "UPDATE deployments SET status = 'Deployed using git pull' WHERE repo = '$repo'";
			$conn->query($sql);
			$output = shell_exec("cd $dir && git reset --hard HEAD && git pull");
			echo "Executing a git pull in directory $dir\r\n";
			echo $output;
			if(strpos($output, "Fast-forward")!==false){
				$sql = "UPDATE deployments SET status = 'Deployed Successfully' WHERE repo = '$repo'";
				$conn->query($sql);
				echo "Success: Fast forward performed";
			}else if(strpos($output, "Already up-to-date")!==false){
				$sql = "UPDATE deployments SET status = 'Deployed Successfully' WHERE repo = '$repo'";
				$conn->query($sql);
				echo "The repository was already up to date\r\n";
			}else{
				$sql = "UPDATE deployments SET status = 'Deployment Failed. Check github webpush logs for details' WHERE repo = '$repo'";
				$conn->query($sql);
				die("Error: Git pull failed");
				var_dump(http_response_code(500));
			}
					
        }else{
            echo "$repo is not hosted by this server";
            $sql = "UPDATE deployments SET status = 'Deployment not handled by this server' WHERE repo = '$repo'";
            $conn->query($sql);
        }
}


?>