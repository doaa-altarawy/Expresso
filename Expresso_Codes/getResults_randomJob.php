<?php

$submit_button = $_POST['formSubmit'];
#echo $email_address . "<br>"; 

#### connection to the Expresso database
$username="expresso";
$password="findGene";
$database="Expresso";
$server="expresso.cbb.lan";

$dbhandle = mysql_connect($server,$username,$password);
@mysql_select_db($database) or die( "Unable to select database");

$get_TF_id = "SELECT * FROM Expresso.Experiment_tbl";
$dbresults = mysql_query ($get_TF_id, $dbhandle);

echo $get_TF_id;
echo $dbresults;
echo "<br>Salam Delasa jun, khubi?? :) ";

$outpath = '/home/expresso/public_html/outputfiles';
set_include_path(get_include_path() . PATH_SEPARATOR . $outpath); 


$myfile = fopen("test.txt", "a+", 1);






$myresultsArray = array();
array_push($myresultsArray, "apple", "raspberry");
#print_r($myresultsArray);


if (mysql_num_rows($dbresults) > 0) 
{
    // output data of each row
	while ($dbrow = mysql_fetch_array($dbresults, MYSQL_ASSOC))
	{
	/*	echo $dbrow["id"]."<br>"; 
		echo $dbrow["TF_Id"]."<br>"; 
		echo $dbrow["TF_Name"]."<br>"; 
		echo $dbrow["GEO_id"]."<br>"; 
		echo $dbrow["Exp_Description"]."<br>"; 
	*/
		array_push($myresultsArray, $dbrow["id"] ."\t" . $dbrow["TF_Id"] ."\t" .  $dbrow["TF_Name"] ."\t" . $dbrow["TF_Name"] ."\t" . $dbrow["GEO_id"] ."\t" .  $dbrow["Exp_Description"]);			


	}
#	print_r($myresultsArray);	    
	$newline_separated = implode("\n", $myresultsArray);
	fwrite($myfile, $newline_separated);
}
else 
{
    echo "0 results";
}




fclose($myfile);


mysql_close();


?>
