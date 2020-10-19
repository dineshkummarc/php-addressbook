<?

include"master_inc.php";

		  $email  = $_REQUEST['email'];
		  $pw_url = $_REQUEST['password'];
		  
// Connect to server and select databse.
$sql="SELECT * FROM addr_users WHERE email='$email' AND md5_pass ='$pw_url'";

$result=mysqli_query($db,$sql);

// mysqli_num_row is counting table rows

$count=mysqli_num_rows($result);

// If result matches $myusername and $mypassword, table row must be 1 row

if($count==0){


header("location:login_failed.php");


}
		  	  
//Change to temporary password:

$temp_pw_raw=date("YmdHis");   

$encrypted_temp_pw = md5($temp_pw_raw);


$query = "UPDATE `users` SET `password`='$encrypted_temp_pw',`password_hint`='' WHERE `email`='$email'"; 

// save the info to the database
$results = mysqli_query( $query );

// print out the results
if( $results )

{ echo( "<font size='2' face='Verdana, Arial, Helvetica, sans-serif'>An encrypted temporary password has been assigned.  Choose a new password in the form below:<br></font>  " );
}
else
{
die( "<font size='2' face='Verdana, Arial, Helvetica, sans-serif'>Trouble saving information to the database:</font><br><br> " . mysqli_error() );
}


?>
<div align="center">
  <p><font size="4" face="Verdana, Arial, Helvetica, sans-serif"><strong>Password Reset </strong></font></p>
  <form action="reset_password_save.php" method="post" name="form" id="form">
  <TABLE cellSpacing="0" cellPadding="10" width="30%" border="0">
    <TBODY>
      <TR>
        <TD width="171"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Username</font></TD>
        <TD width="204"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
          <? 
		  
		  $query = "SELECT * FROM users WHERE email='$email' AND password='$encrypted_temp_pw'";

$numresults=mysqli_query($query);
$numrows=mysqli_num_rows($numresults); 

// get results
$result = mysqli_query($query) or die("Couldn't execute query");

// now you can display the results returned
while ($row= mysqli_fetch_array($result)) {

$username= $row["username"];
$email= $row["email"];
$password= $row["password"];
		  
		  echo $username;
		  
		  }
		  
		   ?>
          <input type = "hidden" value="<? echo $email; ?>" name="email" width="50" />
          <input type = "hidden" value="<? echo $username; ?>" name="username" width="50" />
</font></TD>
      </TR>
    </TBODY>
  </TABLE>
  <TABLE cellSpacing="0" cellPadding="10" width="30%" border="0">
    <TBODY>
      <TR>
        <TD width="171"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">New Password</font></TD>
        <TD width="204"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
          <INPUT type = "password" value="" name="password" width="50">
        </font></TD>
      </TR>
    </TBODY>
  </TABLE>
  <TABLE cellSpacing="0" cellPadding="10" width="30%" border="0">
    <TBODY>
      <TR>
        <TD width="171"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Confirm New Password </font></TD>
        <TD width="204"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
          <INPUT type = "password" value="" name="password_confirm" width="50">
        </font></TD>
      </TR>
	  <TR>
        <TD><font size="2" face="Verdana, Arial, Helvetica, sans-serif">New Password Hint </font></TD>
        <TD width="204"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
          <INPUT type = "text" value="" name="password_hint" width="80">
        </font></TD>
      </TR>
    </TBODY>
  </TABLE>
  <p><font size="1" face="Arial, Helvetica, sans-serif">
    <input type="submit" value="Save and Continue" name="submit2" />
  </font></p>
  </form>
  <p>
    <?

?>
  </p>
  <p>&nbsp;</p>
</div>
