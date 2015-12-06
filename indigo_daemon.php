<?
class indigo_daemon
{
  public $log_file;
  public $pid_file;
  private $quit;
  public $daemon_name;
  public function sig_handler($signal)
  {
   switch($signal) 
      {
            case SIGTERM:
                $this->quit=1; 
                write_log_file($this->daemon_name." sigterm");
                exit;
            case SIGKILL:
                $this->quit=1; 
                write_log_file($this->daemon_name." killed");
                exit;
            case SIGINT:
                $this->quit=1; 
                write_log_file($this->daemon_name." killed");
                exit;
            case SIGQUIT:
                $this->quit=1; 
                write_log_file($this->daemon_name."  shutdown");
                exit;
            case SIGSTOP:
                $this->quit=1; 
                write_log_file($this->daemon_name." stop");
                exit;
            case SIGCHLD:
                $this->quit=1; 
                write_log_file($this->daemon_name." stop");
                exit;
        }
  }

  public function init()
  {
     declare(ticks = 1);
     gc_enable();
     $pid_h=$this->open_pid_file($this->pid_file);
     error_reporting(0);
     ini_set("max_execution_time", "0");
     ini_set("max_input_time", "0");
     set_time_limit(0);
     ob_implicit_flush ();
     pcntl_signal(SIGCHLD,"sig_handler"); 
     pcntl_signal(SIGKILL,"sig_handler"); 
     pcntl_signal(SIGTERM,"sig_handler"); 
     pcntl_signal(SIGINT,"sig_handler"); 
     pcntl_signal(SIGQUIT,"sig_handler"); 
     pcntl_signal(SIGSTOP,"sig_handler"); 

     $pid = pcntl_fork();
                     
     if ($pid === -1) 
     {
    	  die('Could not fork!');
     } 
     elseif ($pid) 
     {
	   // this is the parent process
	     exit;
     } 
     else 
     {
	     // this is the child process
	     chdir("/");
	     umask(0);
	       // reopen standard file descriptors
        // this is necessary to decouple the daemon from the TTY
	    fclose(STDIN);        
	    fclose(STDOUT);
	    fclose(STDERR);
	
	    $STDIN = fopen('/dev/null', 'r');
	    $STDOUT = fopen('/dev/null', 'wb');
	    $STDERR = fopen('/dev/null', 'wb');
	
    	// make it session leader
	    posix_setsid();
      chdir("/");
      umask(0); 
      $pid_id=posix_getpid();
      pcntl_signal(SIGCHLD, "signal_handler");
      pcntl_signal(SIGTERM, "signal_handler");
      pcntl_signal(SIGINT, "signal_handler");   
      fputs($pid_h,$pid_id);
      fclose($pid_h);
      $this->write_log_file($this->daemon_name." started");
     }
  }
  public function shutdown()
  {
    $this->write_log_file($this->daemon_name." shutdown"); 
    gc_disable(); 
  }
  public function write_log_file($text)
  {
     $fh = fopen($this->log_file, 'a') or die("can't open log file");
     fwrite($fh, date("Y:m:d H:i", time())." ".$text." \n");
     fclose($fh);
  }
  public function open_pid_file($file)
  {
     if(file_exists($file)) 
     {
        $fp = fopen($file,"r");
        $pid = fgets($fp,1024);
        fclose($fp);
        if(posix_kill($pid,0)) 
        {
            print "Server already running with PID: $pid\n";
            exit;
        }
        print "Removing PID file for defunct server process $pid\n";
        if(!unlink($file)) 
        {
            print "Cannot unlink PID file $file\n";
            exit;
        }
     }
     if($fp = fopen($file,"w")) 
     {
         return $fp;
     } 
     else 
     {
         print "Unable to open PID file $file for writing...\n";
         exit;
     }
  }
  public function infinite($useconds)
  {
   
    while($this->quit!=1) 
    {
      flush();   		
      ob_clean();    
		  $this->start_process();
      usleep($useconds);
	  }
  }
}