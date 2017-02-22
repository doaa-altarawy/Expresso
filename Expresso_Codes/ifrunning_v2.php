<?php

$email_address = $_POST['email'];
#echo $email_address . "<br>"; 

#### connection to the Expresso database
$username="expresso";
$password="findGene";
$database="Expresso";
$server="expresso.cbb.lan";

$dbhandle = mysql_connect($server,$username,$password);
@mysql_select_db($database) or die( "Unable to select database");

#check existense of a random  string, if it does not exist in the database, insert the email address and assigned rask id into the database
while (true){

$s  = substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 5)), 0, 5);
#echo $s . "<br>";
$check_key = "select task_id from Expresso.UserInfo_tbl where task_id = '$s'"; 	
$dbresults = mysql_query ($check_key, $dbhandle);

$num_rows = mysql_num_rows($dbresults);
#echo "<br> number of rows: ". $num_rows;

if($num_rows == 0)
{ 
	#echo "<br> This key not exists in the database <br>";
	$insertUserInfo = "INSERT INTO UserInfo_tbl (task_id, email, complete) VALUES ('$s', '$email_address', 0); ";
	#echo $insertUserInfo;
	echo "<br> Your task id is: " . $s . "<br> Your email address is: " . $email_address;
	$dbinsert = mysql_query ($insertUserInfo, $dbhandle);
 	break;
}
}

#$tmp_array = scandir("/home/expresso/public_html/outputfiles");
#$tmp_array_size = count($tmp_array);
#echo "<br>" . $tmp_array_size;
#print_r($tmp_array);
#for ($i = 2; $i < $tmp_array_size ; $i++)
#{
#	echo "<br>";
#	echo $tmp_array[$i];
#	echo "<br>";
#}

while (true){
	$tmp_array = scandir("/home/expresso/public_html/outputfiles");
    	$tmp_array_size = count($tmp_array);
  
	#$all_files = array_values($tmp_array);
	#print_r($all_files);
   	#print_r($tmp_array);

	#Get all the tasks with "complete = 0" in the database
   	$get_incomplete = "SELECT task_id,email FROM Expresso.UserInfo_tbl where complete=0"; #Delasa: I added "and email = '$myemail'" for test
	#echo "<br> $get_incomplete<br>";
	$db_get_incomplete = mysql_query ($get_incomplete, $dbhandle);
	#$db_get_incomplete2 = mysql_query ($get_incomplete, $dbhandle);	
	$num_incomplete = mysql_num_rows($db_get_incomplete);
	
	if ($num_incomplete == 0)
	{
		echo "<br> There is no incomplete task right now <br>";
		break;
	}
	else
	{
	echo "<br> There are $num_incomplete incomplete tasks currently for user";
	#break;
    	while ($dbrow = mysql_fetch_array($db_get_incomplete, MYSQL_ASSOC))
	{
		#check if tmp_array has any of the incomplete tasks in it.If it has any of those keys, it means that those tasks are comlpete and the 
		$mytaskid=$dbrow["task_id"];
		$myemail = $dbrow["email"];
		#echo "my task id is: $mytaskid, my email is: $myemail <br>";	
	#	if (array_key_exists($mytaskid, $tmp_array)) 
#		if (array_key_exists('1234.txt', $tmp_array))
		$key = array_search($mytaskid, $tmp_array);
		if ($key != '') 
		{
			echo "<br> $key , $mytaskid <br>";
			echo "$mytaskid has been finished. it exists in the /outputfiles directory <br>";
                        $update_query =  "UPDATE UserInfo_tbl SET complete=complete+1 WHERE (task_id = '$mytaskid' and email = '$myemail');";
                        $db_update_query = mysql_query ($update_query, $dbhandle);
			echo "$mytaskid has been updated, check out in the database";   
                        mail($myemail, 'Expresso calculations finished for ' . $mytaskid, 'Expresso calculations were successfully finished for ' . $mytaskid . " \n You can access to your results at http://bioinformatics.cs.vt.edu/expresso/outputfiles/" . $mytaskid, null,'Delasa Aghamirzaie');
			echo "<br> ***************************** <br>"; 
			
		}
#	sleep(2);


	}

	}

}
mysql_close();
?>
