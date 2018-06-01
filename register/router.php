<? 

include"master_inc.php";

$username_from_cookie = $_COOKIE[$cookiename]; //retrieve contents of cookie 

//Query to get permissions

$sql = "SELECT * FROM users WHERE `username` = '$username_from_cookie'"; 

$numresults = mysqli_query($sql);
$numrows = mysqli_num_rows($numresults); 

// get results
$result = mysqli_query($query) or die("Couldn't execute query");

// now you can display the results returned
while ($row= mysqli_fetch_array($result)) {

$permissions = $row["permissions"];

//Set Rules for where various permissions go after login

if($permissions==5)
{

header("location:$level_5_url");

}
if($permissions==4)
{

header("location:$level_4_url");

}
if($permissions==3)
{

header("location:$level_3_url");

}
if($permissions==2)
{

header("location:$level_2_url");

}

if($permissions==1)
{

header("location:$level_1_url");

}

}

//}


?>