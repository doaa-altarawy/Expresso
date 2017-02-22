<?php
session_start();
$submit_button = $_POST['submit'];
$evalue = $_POST['evalue'];

$genes = $_POST['geneList'];

$pattern = '/[\/;, \r?\n \t\n]/';
$geneList = preg_split($pattern, $genes);
$geneListCommaSep = implode("' or gene_id = '", $geneList);

#### connection to the Expresso database
$username="expresso";
$password="findGene";
$database="Expresso";
$server="expresso.cs.vt.edu";

$dbhandle = mysql_connect($server,$username,$password);
@mysql_select_db($database) or die( "Unable to select database");


// $get_genes = "select gene_id, experiment_id, Motif2Gene_tbl.TF_Name from Motif_tbl inner join Motif2Gene_tbl on (experiment_id = Motif2Gene_tbl.experiment_geo_id AND motif=Motif2Gene_tbl.regular_expression AND Motif_tbl.TF_Name=Motif2Gene_tbl.TF_name)  where (`E-value`<= $myevalue AND Motif_tbl.TF_Name='$myTF') group by gene_id;"; 

$get_genes = "select * from Motif_tbl inner join Motif2Gene_tbl on (experiment_id = Motif2Gene_tbl.experiment_geo_id AND motif = Motif2Gene_tbl.regular_expression) inner join Experiment_tbl on (experiment_geo_id = Experiment_tbl.GEO_id  and Motif2Gene_tbl.TF_name = Experiment_tbl.TF_name) 
where (`E-value`<= $evalue AND (gene_id = '$geneListCommaSep'));";



$dbresults = mysql_query ($get_genes, $dbhandle); 



$outpath = '/home/expresso/drupal7/outputfiles/';
set_include_path(get_include_path() . PATH_SEPARATOR . $outpath); 


$file_name = $genes . "_genes.txt";
$myfile = fopen($outpath.$file_name, "w", 1);


$myresultsArray = array();
#array_push($myresultsArray, "apple", "raspberry");
// print_r($myresultsArray);


if (mysql_num_rows($dbresults) > 0) 
{
   
    array_push($myresultsArray, "Target Gene Id" 
                    ."\t" . "TF Id" 
                    ."\t" .  "TF Name"
                    ."\t" .  "Motif by MEME"
                    ."\t" . "GEO id");

    // output data of each row
	while ($dbrow = mysql_fetch_array($dbresults, MYSQL_ASSOC))
	{
		// $mygeneid=$dbrow["gene_id"]; 
		// $get_annotation = "select GeneName, Annotation from Gene_tbl where gene_id='$mygeneid' group by GeneName;"; 
  //       $dbAnnotation = mysql_query ($get_annotation, $dbhandle); 
	 //   $dbrowAnnotation = mysql_fetch_array($dbAnnotation, MYSQL_ASSOC);

	   array_push($myresultsArray, $dbrow["gene_id"] 
                    ."\t" . $dbrow["TF_Id"] 
                    ."\t" .  $dbrow["TF_Name"] 
                    ."\t" .  $dbrow["regular_expression"]
                    ."\t" . $dbrow["GEO_id"]);	                      
             
	}
#	print_r($myresultsArray);	    
	$newline_separated = implode("\n", $myresultsArray);
	fwrite($myfile, $newline_separated);
     
    header("Location: ../outputfiles/".$file_name); 
}
else 
{
    echo "0 results";
}


fclose($myfile);
mysql_close();
exit();





//----------------------------------------------



?>