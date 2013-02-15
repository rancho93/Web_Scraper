<?php
try{
	$json = $_REQUEST['data'];
	$data = urldecode($json);
	$data = json_decode($json);
	$length = count($data->{'QAPairs'});
	}
catch (Exception $e){
	echo "fail"; exit();
}

$db_server = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "project";
$db_tablename = "questions";
$db_Qcolumn = "questions";
$db_Acolumn = "answers";

try{
mysql_connect($db_server, $db_user, $db_pass);
mysql_select_db($db_name);

for ($i = 0; $i < $length; $i++) {
	mysql_query("INSERT INTO ".$db_tablename." (".$db_Qcolumn.", ".$db_Acolumn.") VALUES('".($data->{'QAPairs'}[$i]->{'question'})."', '".($data->{'QAPairs'}[$i]->{'answer'})."' ) ");  
}
}
catch (Exception $e){
	echo "fail"; exit();
}

echo "Inserted ".$length." questions into database successfully. ";
?>