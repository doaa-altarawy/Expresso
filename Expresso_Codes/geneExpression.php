<?php

/* <--------------------- Function for processing inputlines that are read from the file ----------------------------> */
function ProcessInputArray($mylines) {
  
try{
$mylines_count = count($mylines);
/* echo $mylines_count; */

/* Going over all the lines one by one*/

$Genes_Dic = array();
/* should add a check box for header or not*/

$lineStart = 1;
if (!isset($_POST['header']))
{
  $lineStart = 0;
}

/* echo "lineStart:  " . $lineStart . "<br>"; */


for($x=$lineStart ;$x<$mylines_count;$x++) 

{
 
   $cur_line = $mylines[$x];
  /*  echo $cur_line ."<br>"; */

   $cur_line_Array =   split("\t", $cur_line);

   $cur_line_Array_length = count( $cur_line_Array);

    $expression_values = array();

    for($i=1;$i<$cur_line_Array_length;$i++) 
    {
     /* echo $cur_line_Array[$i]; */
          array_push($expression_values, floatval($cur_line_Array[$i]));
    }

    $genes_line   = $cur_line_Array[0];

   /* split the genes for "," */
    $genes = explode(",", $genes_line);
   /* echo "Genes:  "; */ 
    /* print_r($genes); */
    $genes_length = count($genes); 
    /* echo "number of genes in this line:   " . $genes_length; */

    for($j=0;$j<$genes_length;$j++) 
     {
       /* echo $genes[$j]; */
       $mygene = $genes[$j]; 
       /* array_push($expression_values, $cur_line_Array[$i]); 
       array_push($Genes_Dic, $mygene => $expression_values);*/
       $cur_Gene_array= array(  $mygene  => $expression_values );
      /* print_r($cur_Gene_array); */
      /* array_merge($Genes_Dic,$cur_Gene_array); */
       $Genes_Dic= $Genes_Dic  + $cur_Gene_array; 
        /* array_push($Genes_Dic, $cur_Gene_array); */
         /* print_r($Genes_Dic);
         echo "<br> this gene finished in the dic <br>";*/
       }

}
} catch (Exception $e)
{
	echo "PHP exception 1", $e->getMesaage(), "\n";
}
return $Genes_Dic;
//end of function
}

/* <--------------------- Function for processing Genes_Dic ----------------------------> */
function process_gene_dic($Genes_Dic)
{
try{
echo "<hr>";

echo "<font size=\"4\"> <b>Results</b><br></font>";
echo "The transcription factor and target genes having correlation greater than 0.6 are colored as red. <br>";

$Genes_Dic_keys = array_keys($Genes_Dic);

/* unset($Genes_Dic_keys["\n"]); */

array_pop($Genes_Dic_keys); /* You should run it in case the last element is empty */

/* print_r($Genes_Dic_keys);*/

$or_separated_gene = implode("' or gene_id = '", $Genes_Dic_keys); /* Used for database query*/
$or_separated_gene = "'" . $or_separated_gene . "'";
/* echo $or_separated_gene; */

$or_separated_TF = implode("' or  TF_Id = '", $Genes_Dic_keys); /* Used for database query*/
$or_separated_TF = "'" . $or_separated_TF . "'";
/* echo $or_separated_TF; */


/* parsing through Genes_Dic dictionary to get all the keys and values */
/* foreach($Genes_Dic as $mygene=>$mygene_value)
 {
  echo "Key=" . $mygene . ", Value=" . $mygene_value;
  echo "<br>";
}
*/

$username="expresso";
$password="findGene";
$database="Expresso";
$server="expresso.cbb.lan";


$dbhandle = mysql_connect($server,$username,$password);
@mysql_select_db($database) or die( "Unable to select database");


$get_genes_TFs = "select gene_id, TF_Id, Experiment_tbl.TF_Name, GEO_id from Motif2Gene_tbl inner join Experiment_tbl on (experiment_geo_id=GEO_id AND Experiment_tbl.TF_Name=Motif2Gene_tbl.TF_Name) where ((TF_Id = " . $or_separated_TF . ") AND (gene_id = " . $or_separated_gene . ") AND (TF_Id != gene_id) ) group by gene_id, TF_Id;" ;
/* echo "<br> SQL command:  <br>";
echo $get_genes_TFs; */

$dbresults = mysql_query ($get_genes_TFs, $dbhandle); 
$num_rows = mysql_num_rows($dbresults);

echo "Your search resulted in  <b>$num_rows </b> transcription factors and target gene pairs <br></b>";


/* --------------------- Parsing database results and putting them into a table----------  */

echo "<br>";
echo "<table border=\"1\" align=\"center\">";
echo "<tr><th> Target gene id</th>";
echo "<th>TF_Id</th>";
echo "<th>TF_Name</th>";
echo "<th>GEO id</th>";
echo "<th>GEO link</th>";
echo "<th>Pearson correlation</th>";
/* echo "<th>Pearson correlation</th>"; */


echo "</tr>";
# $myresultsArray keeps the results that are going to be available in a text file.
$myresultsArray = array();
array_push($myresultsArray, "id" ."\t" . "Gene_id" . "\t" . "TF_Id" ."\t" .  "TF_Name" ."\t" . "GEO_id" ."\t" .  "Correlation");
$myid = 1;

//log file
$logfile = fopen(".\log.txt", "w+");
$date = new DateTime(); 
while ($dbrow = mysql_fetch_array($dbresults, MYSQL_ASSOC))
{
	
   
       /*  printf("%s  %s ( %s ) <br>", $dbrow["TF_Id"]  , '            ', $dbrow["TF_Name"] ); */
        echo "<tr>";
               echo "<td>";
               $mygene_id = $dbrow["gene_id"];
		#echo $gmyene_id;
		echo $dbrow["gene_id"]; 
	       $mylog = "processing " . $myid . "\t\t\t". $mygene_id . "\t" . $date->getTimestamp() . "\n";
		fwrite($logfile, $mylog) ;

		echo "</td>";
                
                echo "<td>";
                #echo $dbrow["TF_Id"]; 
                $myTF_id = $dbrow["TF_Id"]; 
		echo $myTF_id;
		echo "</td>";

                echo "<td>";
                #echo $dbrow["TF_Name"]; 
                $myTF_name = $dbrow["TF_Name"];
		echo $myTF_name;

		echo "</td>";

                echo "<td>";
           #     echo $dbrow["GEO_id"]; 
            	$myGEO_id = $dbrow["GEO_id"]; 
		echo $myGEO_id;
		 echo "</td>";
              
                $GEO_link = "http://www.ncbi.nlm.nih.gov/geo/query/acc.cgi?acc=" .(string)$dbrow["GEO_id"];
                echo "<td>";
               /* echo $dbrow["GEO_link"]; */
               /*  echo "<a href=https://twitter.com/angela_bradley>My Twitter</a>"; */
                echo "<a href=$GEO_link>View on GEO NCBI</a>";
                echo "</td>";


/* %%%%%%%%%%%%%%%%%%%%%% Getting the correlation %%%%%%%%%%%%%%%%%%%%  */
                echo "<td>";
                $mygene_id = "'" . $dbrow["gene_id"] . "'";
                $mygene_id = $dbrow["gene_id"];
                
                 $myTF_id = $dbrow["TF_Id"];

              
          
                $exp_values_gene =$Genes_Dic[$mygene_id] ;
               
                $exp_values_TF =$Genes_Dic[$myTF_id] ;
                
               $exp_values_gene_str = implode(',' , $exp_values_gene) ;
          
      
               $exp_values_TF_str = implode(',' , $exp_values_TF) ;
               

            /*  echo "python corr.py $exp_values_gene_str $exp_values_TF_str <br>";    */
        
             $correlation=shell_exec("python corr.py $exp_values_gene_str $exp_values_TF_str"); 
             
        /*     echo $correlation; */
        
           if (abs(floatval($correlation)) > 0.6)
            {
                 echo "<font color='red'>$correlation</font>";
            } 
            else
            {
                 echo  $correlation; 
            }
       
 
                echo "</td>";

       
          echo "</tr>";

	array_push($myresultsArray, $myid ."\t" . $dbrow["gene_id"] ."\t" . $dbrow["TF_Id"] ."\t" .  $dbrow["TF_Name"] ."\t" . $dbrow["GEO_id"] ."\t" .  $correlation);
     	$myid = $myid + 1;
             
} 

echo "</table>";

	#return $num_rows; # in future, I can return all the results in an array, together with the number of results
}
catch (Exception $e)
{
        echo "PHP exception 2", $e->getMesaage(), "\n";
}

	return $myresultsArray;
/*  --------------- Drawing table done ------------*/

}

/* End of finction process_gene_dic */

/*   ----------------------------------------------------        process submit button   ------------------------------------------------*/
if(isset($_POST['submit'])) 
{

	$complete = 0;
	$s  = substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 5)), 0, 5);
        #echo $s . "<br>";

	$username="expresso";
	$password="findGene";
	$database="Expresso";
	$server="expresso.cbb.lan";

	$dbhandle = mysql_connect($server,$username,$password);
	@mysql_select_db($database) or die( "Unable to select database");
 
	while (true == true){

		$s  = substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 5)), 0, 5);
	#	echo $s . "<br>";
		$check_key = "select task_id from Expresso.UserInfo_tbl where task_id = '" . $s . "';";
		#echo $check_key . "<br>";
		$dbresults = mysql_query ($check_key, $dbhandle);

		$num_rows = mysql_num_rows($dbresults);
		#echo "<br> number of rows: ". $num_rows . "<br>";

		if($num_rows == 0)
		{
        		#echo "<br> This key not exists in the database <br>";
        		$insertUserInfo = "INSERT INTO UserInfo_tbl (task_id, complete) VALUES ('" . $s . "', " . $complete . "); ";
        		#echo $insertUserInfo;
        		#echo "<br> Your task id is: " . $s . "<br> Your email address is: " . $email_address;
        		echo "<br> Your task id is: " . $s . " <br>";
			$dbinsert = mysql_query ($insertUserInfo, $dbhandle);
        		break;
		}

	};
	




#    echo "User has loaded his own data <br>";
	

 
if ($_FILES["file"]["error"] > 0)
  {
  echo "Error: " . $_FILES["file"]["error"] . "<br>";
  }
/*else
  {
  echo "Upload: " . $_FILES["file"]["name"] . "<br>";
  echo "Type: " . $_FILES["file"]["type"] . "<br>";
  echo "Size: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
  echo "Stored in: " . $_FILES["file"]["tmp_name"];
  }
*/

$fileName = $_FILES["file"]["name"];
$fileTmpLoc = $_FILES["file"]["tmp_name"];
$pathAndName = "../uploads/".$fileName;
$moveResult = move_uploaded_file($fileTmpLoc, $pathAndName);

/*
if ($moveResult == true) {
#    echo "File has been moved from " . $fileTmpLoc . " to" . $pathAndName;
} 

else
{
     echo "ERROR: File not moved correctly";
}
*/

if  ($moveResult == false)
{
	echo "ERROR: File not moved correctly";
}

#$file = fopen("../uploads/".$fileName, "r") or exit("Unable to open file!");
$file = fopen("../uploads/GSE38612_Big_File.txt", "r+") or exit("unable to open GSE38612_Big_File.txt");
//Output a line of the file until the end is reached

/*  }  */
/*  End of if-else statement  */


/* Link to user's future output */
 $file_name = $s . ".txt";
 echo "<a href=../outputfiles/$file_name> Download your results available here. </a><br>";





$mylines = array();
$allGenes = array();
$allGenesString = "";
/* The works that needs to be done on the file either demo file  */


while(!feof($file))
  {
  

   $ExpressionValues = array();
   $All_lines= fgets($file) ."<br>" ;

    
 array_push($mylines, $All_lines);  

 
/* End of Processing the File line by line */

  }
fclose($file);



/* Calling the function ProcessInputArray */
$Genes_Dic = ProcessInputArray($mylines) ;

$ResultsArray =  process_gene_dic($Genes_Dic);
$numResults = count($ResultsArray);

############### when all the processing is done, the complete field in the userinfor table should be set to 1.
if ($numResults > 1) #it has one line of header
{	
	$complete = 1;
	$outpath = '/home/expresso/public_html/outputfiles';
	set_include_path(get_include_path() . PATH_SEPARATOR . $outpath);

	$newline_separated = implode(" ", $ResultsArray);
	$file_name = $s . ".txt";
	$myfile = fopen($file_name, "w", 1);
        fwrite($myfile, $newline_separated);
	echo "<br><br>";
        echo "<a href=../outputfiles/$file_name>Download your results here</a><br>";


}
else
{	
	echo "Sorry! Your search did not match our records in the DB!";
}
$update_tbl_query = "update UserInfo_tbl set complete=" . $complete . " where task_id='" . $s . "';";
#echo $update_tbl_query . "<br>" ;
mysql_query ($update_tbl_query, $dbhandle);

}
############### Finished updating userinfo table for complete task
/*    ----------------------------------        Process Demo    -----------------------------------   */
if(isset($_POST['demo']))
{

   
   echo "<br>About 100 genes and transcription factors are selected from published dataset (<a href=http://www.ncbi.nlm.nih.gov/pubmed/23136377>PMC3531837</a>) which has expression values of genes for different Arabidopsis tissues: leafs, seeds, roots, and flowers. <br><b> Reference: </b>Liu, Jun, Choonkyun Jung, Jun Xu, Huan Wang, Shulin Deng, Lucia Bernad, Catalina Arenas-Huertero, and Nam-Hai Chua. Genome-wide analysis uncovers regulation of long intergenic noncoding RNAs in Arabidopsis. The Plant Cell Online 24, no. 11 (2012): 4333-4345" ;
   $file = fopen("../uploads/RNA_Seq_100_demo.txt", "r") or exit("Unable to open file!");
   $mylines = array();
 
  while(!feof($file))
  {
  
   $ExpressionValues = array();
   $All_lines= fgets($file) ."<br>" ;
  
    array_push($mylines, $All_lines);  

 
  /* End of Processing the File line by line */

  }
  fclose($file);
  
  $Genes_Dic = ProcessInputArray($mylines);
  $ResultArray =  process_gene_dic($Genes_Dic);
  


} 
mysql_close();

?>
