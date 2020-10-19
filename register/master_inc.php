<?
//no space before <?
  include"login_config.php";

   // connect to the server
   mysqli_connect( $db_host, $db_username, $db_password )
      or die( "Error! Could not connect to database: " . mysqli_error() );
   
   // select the database
   mysqli_select_db( $db )
      or die( "Error! Could not select the database: " . mysqli_error() );
	  
?>