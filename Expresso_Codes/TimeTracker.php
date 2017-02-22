<?php

/* 
 * A simple log class for duration taken from the last log call
 * Used to measure performance like time taken for a function
 */

class TimeTracker{
    private $initTime       = 	0;
    private $time_start     =   0;
    private $time_end       =   0;
    private $duration       =   0;
    private $logFile;
    
    const logFilePath = '/home/expresso/public_html/log/';
    //private $logFilePath = ''../log/';
    
    public function __construct($logFileName){
        // use log file name, open for append
        if ($logFileName){ 
            $this->logFile = fopen(self::logFilePath . $logFileName.'_log.txt', "a") or die("Unable to open log file!"); //. $php_errormsg);
        }
        // use time stamp
        else{
            $this->logFile = fopen(self::logFilePath . time().'.txt', "a") or die("Unable to open log file!"); // . $php_errormsg);
        }
        $this->initTime = microtime(true);
        $this->time_start= microtime(true);
    }
       
    public function logTime($s){
        $this->time_end = microtime(true);        
        $this->duration = sprintf("%.3f", ($this->time_end - $this->time_start));
        $this->time_start = $this->time_end;
        fwrite($this->logFile, $s.', Duration:'.$this->duration.PHP_EOL);
        return $this->duration;
    }
       
    public function totalExecTime(){
    	$this->time_end = microtime(true);        
        $this->duration = sprintf("%.2f", ($this->time_end - $this->initTime));
        $this->time_start = $this->time_end;
        fwrite($this->logFile, 'Total Exec Time:'.$this->duration.PHP_EOL);
    	return $this->duration;
    }

    public function __destruct(){
        fclose($this->logFile);
    }
    
}
