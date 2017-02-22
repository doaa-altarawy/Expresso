<?php
session_start();
$submit_button = $_POST['submit'];
$myevalue = $_POST['myevalue'];
$myTF = $_POST['myTF'];
/*
echo 'salam';
echo $_SESSION['TF'];
echo $_SESSION['evalue'];
*/


#### connection to the Expresso database
$username="expresso";
$password="findGene";
$database="Expresso";
$server="expresso.cbb.lan";

$dbhandle = mysql_connect($server,$username,$password);
@mysql_select_db($database) or die( "Unable to select database");

#$get_TF_id = "SELECT * FROM Expresso.Experiment_tbl";
#$dbresults = mysql_query ($get_TF_id, $dbhandle);

$get_genes = "select gene_id, experiment_id, Motif2Gene_tbl.TF_Name from Motif_tbl inner join Motif2Gene_tbl on (experiment_id = Motif2Gene_tbl.experiment_geo_id AND motif=Motif2Gene_tbl.regular_expression AND Motif_tbl.TF_Name=Motif2Gene_tbl.TF_name)  where (`E-value`<= $myevalue AND Motif_tbl.TF_Name='$myTF') group by gene_id;"; 

/*
echo $get_genes;
*/

$dbresults = mysql_query ($get_genes, $dbhandle); 




$outpath = '/home/expresso/drupal7/outputfiles/';
set_include_path(get_include_path() . PATH_SEPARATOR . $outpath); 


$file_name = $myTF . "_target_genes.txt";
$myfile = fopen($outpath.$file_name, "w", 1);

/*$txt = "John Doe\n";
fwrite($myfile, $txt);
$txt = "Ruth Grene\n";


fwrite($myfile, $txt);
*/



$myresultsArray = array();
#array_push($myresultsArray, "apple", "raspberry");
#print_r($myresultsArray);


if (mysql_num_rows($dbresults) > 0) 
{    
    array_push($myresultsArray, "Target Gene ID"                     
                    ."\t" .  "TF Name"
                    ."\t" .  "Description"
                    ."\t" . "GEO ID");

    // output data of each row
	while ($dbrow = mysql_fetch_array($dbresults, MYSQL_ASSOC))
	{
	/*	echo $dbrow["id"]."<br>"; 
		echo $dbrow["TF_Id"]."<br>"; 
		echo $dbrow["TF_Name"]."<br>"; 
		echo $dbrow["GEO_id"]."<br>"; 
		echo $dbrow["Exp_Description"]."<br>"; 
	*/

		$mygeneid=$dbrow["gene_id"]; 
		$get_annotation = "select GeneName, Annotation from Gene_tbl where gene_id='$mygeneid' group by GeneName;"; 
         /*  echo  $get_annotation; */


        $dbAnnotation = mysql_query ($get_annotation, $dbhandle); 
        $dbrowAnnotation = mysql_fetch_array($dbAnnotation, MYSQL_ASSOC);

        array_push($myresultsArray, $dbrow["gene_id"] ."\t" .  $dbrow["TF_Name"] ."\t" .  $dbrowAnnotation["Annotation"] ."\t" . $dbrow["experiment_id"]);         
	    // array_push($myresultsArray, $dbrow["id"] ."\t" . $dbrow["gene_id"] ."\t" .  $dbrow["TF_Name"] ."\t" .  $dbrowAnnotation["Annotation"] ."\t" . $dbrow["experiment_id"]);			


	}
#	print_r($myresultsArray);	    
	$newline_separated = implode("\n", $myresultsArray);
	fwrite($myfile, $newline_separated);

	#echo "<a href=../outputfiles/$file_name>Download your results here</a><br>"; 
     
    header("Location: ../outputfiles/".$file_name); 
      	
}
else 
{
    echo "0 results";
}


fclose($myfile);


mysql_close();

exit();


?>
