<?php
//require_once 'ProcessGenesThread.php';

// Core functions and global variables
require_once 'CoreFunctions.php';

global $dbhandle;

/*   ---------------------------- submit a task, no processing --------------------------*/

if(isset($_GET['submit']) || isset($_POST['submit'])) {

    $email = $_GET['email'];
    global $timeTracker;
        
    $outString = "";
    $complete = 0;
    $taskId  = substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 5)), 0, 5);
   
   
    while (true){

        $taskId  = substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 5)), 0, 5);
        #	echo $s . "<br>";
        $check_key = "select task_id from Expresso.UserInfo_tbl where task_id = '" . $taskId . "';";
        #echo $check_key . "<br>";
        $dbresults = mysql_query ($check_key, $dbhandle);

        $num_rows = mysql_num_rows($dbresults);
        #echo "<br> number of rows: ". $num_rows . "<br>";

        if($num_rows == 0)
        {
            #echo "<br> This key not exists in the database <br>";
            $insertUserInfo = "INSERT INTO UserInfo_tbl (task_id, complete) VALUES ('" . $taskId . "', " . $complete . "); ";
            #echo $insertUserInfo;
            #echo "<br> Your task id is: " . $s . "<br> Your email address is: " . $email_address;
            $outString .= "<br> Your task id is: " . $taskId . " <br>";
            $dbinsert = mysql_query ($insertUserInfo, $dbhandle);
            break;
        }
    }    
    // DB work done
    mysql_close();
    
    // intialize global time tracking with this job is file name
    $timeTracker = new TimeTracker($taskId);
    $timeTracker->logTime('Task Id is added to the DB, Id= '.$taskId);
    
    if ($_FILES["file"]["error"] > 0) {
        $outString .= "Error: " . $_FILES["file"]["error"] . "<br>";
    }
   
    $fileName = $uploadsFolderPath . $taskId . ".txt"; //$_FILES["file"]["name"];
    
    $fileTmpLoc = $_FILES["file"]["tmp_name"];
    $moveResult = move_uploaded_file($fileTmpLoc, $fileName);
    
    if  ($moveResult == false) {
        $outString .= "ERROR: File not moved correctly";
        echo $outString;
        return;
    }
   
    /* Link to user's future output */
    $link =  $outpath. $taskId . ".txt";
    $outString .= "When done, <b><a href=$link> you can download results here. </a></b><br>";
    $outString .= "<br>You will be notified by email when your process is done.";
    
    // Send emaill notification
    $msg = "Thank you for using Expresso.\r\n"
            . "Your results will be available soon. \r\n"
            . "You will be notified when it is done.";   
    $headers = "From: Expresso@cs.vt.edu \r\n";
    mail($email,"Expresso Results",$msg, $headers);
    
    $timeTracker->logTime('Sent first email.');    
    
    // Return output to browser
    
    /*header('Content-Type: application/json');
    $response =  json_encode(array(
        'outString' => $outString, 
        'taskId'=> $taskId
        ));
     */
    // quick fix for php 5.1 without JSON
    $response =  $taskId . ':::' . $outString;
       
    echo $response;
     
    
}

/*---------------- Async task (no return string) to process given task ID -------------------*/
if(isset($_GET['runAsync'])) {
    
    $outString = "";
    $taskId = $_GET['taskId'];
    $email = $_GET['email'];
    global $timeTracker;
    // open the log file for the $taskId
    $timeTracker = new TimeTracker($taskId);
    
    
    $fileName = $uploadsFolderPath . $taskId . ".txt"; 
    $file = fopen($fileName, "r") or exit("Unable to open file!");
    
    $mylines = array();
    $allGenes = array();
    $allGenesString = "";

    while(!feof($file)) {
        $ExpressionValues = array();
        $All_lines= fgets($file) ."<br>" ;
        array_push($mylines, $All_lines);  
       /* End of Processing the File line by line */
    }
    fclose($file);

    /* Calling the function ProcessInputArray */
    $Genes_Dic = ProcessInputArray($mylines) ;
    $timeTracker->logTime('Finished ProcessInputArray.....');
          
    $ResultsArray =  process_gene_dic($Genes_Dic);
        
    $timeTracker->logTime('Finished process_gene_dic, Finally');

    $numResults = count($ResultsArray);
    ####### when all the processing is done, the complete field in the userinfor table should be set to 1.
    if ($numResults > 1) #it has one line of header
    {	
        $complete = 1;
        global $outpath;

        // No need for this
        set_include_path(get_include_path() . PATH_SEPARATOR . $outpath);

        $newline_separated = implode(" ", $ResultsArray);
        $file_name = $outpath  .$taskId. ".txt";
        $myfile = fopen("../".$file_name, "w", 0);
        fwrite($myfile, $newline_separated);       
    }
    else {	
        $outString .= "Sorry! Your search did not match our records in the DB!";
    }

    $update_tbl_query = "update UserInfo_tbl set complete=" . $complete . " where task_id='" . $taskId . "';";
    #echo $update_tbl_query . "<br>" ;
    global $dbhandle, $server_url;
    mysql_query ($update_tbl_query, $dbhandle);
    mysql_close();////

    // Send emaill with the link:
    $msg = "Thank you for using Expresso.\r\n"
            . "Your results are available here: \r\n"
            . $server_url . $file_name;  
    
    $msg .= $outString;
    $headers = "From: Expresso@cs.vt.edu \r\n";
    mail($email,"Expresso Results Available", $msg, $headers);

    $timeTracker->logTime('Finished sening second email.'); 
    
    $timeTracker->logTime('All Done.');

    $execTime = $timeTracker->totalExecTime();
       
    echo ':::' . $execTime;
   
}

/*    --------------------------------   Process Demo    -----------------------------------   */
if(isset($_POST['demo']) || isset($_GET['demo'])) {

   global $timeTracker;
   //ini_set('track_errors', 1);    // for debugging
   $timeTracker = new TimeTracker();
   
   $timeTracker->logTime('Demo started');
   
   echo "<br>About 100 genes and transcription factors are selected from published dataset (<a href=http://www.ncbi.nlm.nih.gov/pubmed/23136377>PMC3531837</a>) which has expression values of genes for different Arabidopsis tissues: leafs, seeds, roots, and flowers. <br><b> Reference: </b>Liu, Jun, Choonkyun Jung, Jun Xu, Huan Wang, Shulin Deng, Lucia Bernad, Catalina Arenas-Huertero, and Nam-Hai Chua. Genome-wide analysis uncovers regulation of long intergenic noncoding RNAs in Arabidopsis. The Plant Cell Online 24, no. 11 (2012): 4333-4345" ;
   
    // If user choose sample file
    if ($_GET['sampleFile'] != ''){        
        $fileName = '../Data/testFile_' .$_GET['sampleFile'] . '.txt';
    }else{ // Default
        $fileName = "../Data/demo.txt";
    }   
   $file = fopen($fileName, "r") or die("<br>Unable to open Demo file:". $fileName ."!");
   $mylines = array();
 
    while(!feof($file)){
        $ExpressionValues = array();
        $All_lines= fgets($file) ."<br>" ;
        array_push($mylines, $All_lines);  
    }
    fclose($file);

    $Genes_Dic = ProcessInputArray($mylines);
    $ResultArray =  process_gene_dic($Genes_Dic);
    $timeTracker->logTime('Demo: All done.');
    mysql_close();

    $execTime = $timeTracker->totalExecTime();
       
    echo ':::' . $execTime;
} 

/*    --------------------------------   Very simple test   -----------------------------------   */
if(isset($_GET['demoTest']) || isset($_POST['demoTest'])) {

  echo "This is a simple Ajax string response.";

} 



?>
