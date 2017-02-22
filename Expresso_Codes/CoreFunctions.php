<?php
require_once 'TimeTracker.php';

/* 
 * These are the core processing functions required in all files
 */

/* Global varibales (Constants) */
$timeTracker = null;

$uploadsFolderPath = "../uploads/";
$outpath = 'outputfiles/';
//$uploadsFolderPath = '/home/expresso/public_html/uploads/';
//$outpath = '/home/expresso/public_html/outputfiles/';

$server_url = "http://bioinformatics.cs.vt.edu/~expresso/";


/** Connect to DB , $dbhandle is a global variable**/
$username="expresso";
$password="findGene";
$database="Expresso";
$server="expresso.cbb.lan";

$dbhandle = mysql_connect($server,$username,$password);
@mysql_select_db($database) ; //or die( "Unable to select database");


/* <--------------------- Function for processing inputlines that are read from the file ----------------------------> */
function ProcessInputArray($mylines) {
  
    global $timeTracker;
    
    try{
        $mylines_count = count($mylines);
        /* echo $mylines_count; */

        /* Going over all the lines one by one*/
        
        $Genes_Dic = array();
        //$Genes_Dic = array_fill(0, $mylines_count, NULL);
        $curMaxSize = $mylines_count;
        $gen_dic_index = 0;
        /* should add a check box for header or not*/

        $lineStart = 0;
        // if user file has a header line, skip it
        if (isset($_GET['header'])){
          $lineStart = 1;
        }

        /* echo "lineStart:  " . $lineStart . "<br>"; */
        $timeTracker->logTime('Process Input array: Line count = '.$mylines_count);
        for($x=$lineStart ;$x<$mylines_count;$x++){
           
            $cur_line_Array = split("\t", $mylines[$x]);
           
            $cur_line_Array_length = count( $cur_line_Array);
            $expression_values = array_fill(1, $cur_line_Array_length-1, NULL);

            for($i=1;$i<$cur_line_Array_length;$i++){
                /* echo $cur_line_Array[$i]; */
                /////array_push($expression_values, floatval($cur_line_Array[$i]));
                $expression_values[$i] =  floatval($cur_line_Array[$i]);
            }

            $genes_line   = $cur_line_Array[0];
            /* split the genes for "," */
            $genes = explode(",", $genes_line);
            /* echo "Genes:  "; */ 
            /* print_r($genes); */
            $genes_length = count($genes); 
            /* echo "number of genes in this line:   " . $genes_length; */

//            if ($gen_dic_index+$genes_length > $curMaxSize){     
//                $curMaxSize = $gen_dic_index+$genes_length + 100; //entend array size
//                $Genes_Dic = array_pad($Genes_Dic, $curMaxSize, NULL);
//            }
            for($j=0;$j<$genes_length;$j++) {
               /* echo $genes[$j]; */
               $mygene = $genes[$j]; 
               /* array_push($expression_values, $cur_line_Array[$i]); 
               array_push($Genes_Dic, $mygene => $expression_values);*/
               //$cur_Gene_array= array(  $mygene  => $expression_values );
              /* print_r($cur_Gene_array); */
              /* array_merge($Genes_Dic,$cur_Gene_array); */
               /////////$Genes_Dic= $Genes_Dic  + $cur_Gene_array; 
               //$Genes_Dic[$gen_dic_index++] = array($mygene => $expression_values);
               $Genes_Dic[$mygene] = $expression_values;
                /* array_push($Genes_Dic, $cur_Gene_array); */
                 /* print_r($Genes_Dic);
                 echo "<br> this gene finished in the dic <br>";*/
            }            
        }
    }
    catch (Exception $e){
        $timeTracker->logTime('Error in Process Input array.');
        $timeTracker->logTime($e->getMesaage());
        echo "PHP exception:", $e->getMesaage(), "\n";
    }
    return $Genes_Dic;
    //end of function
}

/* <--------------------- Function for processing Genes_Dic ----------------------------> */
function process_gene_dic($Genes_Dic){
   
    global $timeTracker;
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

        $get_genes_TFs = "select gene_id, TF_Id, Experiment_tbl.TF_Name, GEO_id from Motif2Gene_tbl inner join Experiment_tbl on (experiment_geo_id=GEO_id AND Experiment_tbl.TF_Name=Motif2Gene_tbl.TF_Name) where ((TF_Id = " . $or_separated_TF . ") AND (gene_id = " . $or_separated_gene . ") AND (TF_Id != gene_id) ) group by gene_id, TF_Id;" ;
        /* echo "<br> SQL command:  <br>";
        echo $get_genes_TFs; */
        
        $timeTracker->logTime("Gene disc: before DB call");
        global $dbhandle;
        $dbresults = mysql_query ($get_genes_TFs, $dbhandle); 
        $timeTracker->logTime("Gene disc: after DB call, start drawing table");       
        
        
        $myresultsArray = drawTable($dbresults, $Genes_Dic);
        $timeTracker->logTime("Gene disc: done drawing table.");
               
        
    } catch (Exception $e) {
        $timeTracker->logTime("Error in Gene disc.");
        $timeTracker->logTime($e->getMesaage());
        echo "PHP exception 2", $e->getMesaage(), "\n";
    }
    
    return $myresultsArray;
}
/* End of function process_gene_dic */

/**
 * Draw a tables with results
 * @param type $dbresults
 * @return array
 */
function drawTable($dbresults, $Genes_Dic){
    global $timeTracker;
    
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
    while ($dbrow = mysql_fetch_array($dbresults, MYSQL_ASSOC))
    {
        // log count every 1000 rows
        if ($myid%1000 == 0){
            $timeTracker->logTime("Drawing raw num: " . $myid);
        }

           /*  printf("%s  %s ( %s ) <br>", $dbrow["TF_Id"]  , '            ', $dbrow["TF_Name"] ); */
                   echo "<tr>";
                   echo "<td>";
                   $mygene_id = $dbrow["gene_id"];
                    #echo $gmyene_id;
                    echo $dbrow["gene_id"]; 

                    echo "</td>";

                    echo "<td>";
                    #echo $dbrow["TF_Id"]; 
                    $myTF_id = $dbrow["TF_Id"]; 
                    echo $myTF_id;
                    echo "</td>";

                    echo "<td style='padding:5px'>";
                    #echo $dbrow["TF_Name"]; 
                    $myTF_name = $dbrow["TF_Name"];
                    echo "<input type='button' onclick='goToTransFactor(\"$myTF_name\")'"
                            . " value=$myTF_name style='width:7em;'>";

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
                    echo "<a href=$GEO_link target=\"_blank\">View on GEO NCBI</a>";
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

               if (abs(floatval($correlation)) > 0.6){
                     echo "<font color='red'>$correlation</font>";
                } 
                else{
                     echo  $correlation; 
                }


                echo "</td>";


              echo "</tr>";

            array_push($myresultsArray, $myid ."\t" . $dbrow["gene_id"] ."\t" . $dbrow["TF_Id"] ."\t" .  $dbrow["TF_Name"] ."\t" . $dbrow["GEO_id"] ."\t" .  $correlation);
            $myid = $myid + 1;

    } 

    echo "</table>";

    #return $num_rows; # in future, I can return all the results in an array, together with the number of results
    return $myresultsArray;
}
 /*  --------------- Drawing table done ------------*/

