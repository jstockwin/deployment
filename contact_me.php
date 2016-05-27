<?php
$request_headers        = apache_request_headers();
$http_origin            = $request_headers['Origin'];
$allowed_http_origins   = array(
                            "http://jakestockwin.co.uk",
                            "https://s4s.jakestockwin.co.uk",
                            "https://students4students.org.uk",
                            "https://www.students4students.org.uk"
                          );
if (in_array($http_origin, $allowed_http_origins)){
    header("Access-Control-Allow-Origin: " . $http_origin);
}else{
	die("Request did not originate from a valid domain");
}
// Check for empty fields
if(empty($_POST['name'])  		||
   empty($_POST['to'])			||
   empty($_POST['email']) 		||
   empty($_POST['phone']) 		||
   empty($_POST['message'])	||
   !filter_var($_POST['email'],FILTER_VALIDATE_EMAIL))
   {
	echo "No arguments Provided!";
	return false;
   }

$name = $_POST['name'];
$email_address = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
$to = filter_var($_POST['to'], FILTER_VALIDATE_EMAIL);
if ($email_address === FALSE || $to === FALSE) {
    echo 'Invalid email';
    exit(1);
}
$phone = $_POST['phone'];
$message = $_POST['message'];


// Create the email and send the message
$email_subject = "Website Contact Form:  $name";
$email_body = "You have received a new message from your website contact form.\n\n"."Here are the details:\n\nName: $name\n\nEmail: $email_address\n\nPhone: $phone\n\nMessage:\n$message";
$headers = "From: jstockwin@gmail.com\n"; // This is the email address the generated message will be from. We recommend using something like noreply@yourdomain.com.
$headers .= "Reply-To: $email_address";
mail($to,$email_subject,$email_body,$headers);
return true;
?>
