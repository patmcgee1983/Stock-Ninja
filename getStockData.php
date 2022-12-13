<?php
$result = new stdClass();

ini_set('memory_limit','512M');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function getConnection()
{
	// WAMP Creds
  
  $host="127.0.0.1";
  $port=3306;
  $socket="";
  $user="root";
  $password="";
  $dbname="stockninja";
	
	
  $con = new mysqli($host, $user, $password, $dbname, $port, $socket)
  	or die ('Could not connect to the database server' . mysqli_connect_error());

  return $con;
}

$con = getConnection();
$sql = "SELECT stockninja.data.Symbol,stockninja.data.Data \"Data\", stockninja.rsi.Data \"Rsi\" from stockninja.data INNER JOIN stockninja.rsi on stockninja.data.Symbol = stockninja.rsi.symbol";
$query = mysqli_query($con, $sql);

$result->data = array();
$result->status = "success";

while ($row=mysqli_fetch_assoc($query))
{
	$tempArray = array();
	
	array_push($tempArray, $row["Symbol"]);
	
array_push($tempArray, str_replace("'","\"",$row["Data"]) . "}");
	array_push($tempArray, $row["Rsi"]);
	
	array_push($result->data,$tempArray); 
}

echo json_encode($result);
?>