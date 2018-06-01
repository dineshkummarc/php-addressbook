<?

include"master_inc.php";

$sql="SELECT * FROM users WHERE username='$username_from_cookie'";

$result=mysqli_query($db,$sql);

// mysqli_num_row is counting table rows

$count=mysqli_num_rows($result);

//echo "count: $count<br>";

// If result matches $myusername and $mypassword, table row must be 1 row

if($count==0){

{

echo"Sorry but we don't have that email in our system.  <a href='email_password.php'>Please try again.</a>  Thank you!"

}

?>
