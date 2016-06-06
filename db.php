<?php 

function connect_db()
{
	$host = "";
	$user = "";
	$passw = "";
	$db = "";
	return mysqli_connect($host, $user, $passw, $db); 
}

function disconnect_db($conn)
{
	$error = mysqli_errno($conn);
	mysqli_close($conn);
	return $error;
}

?>