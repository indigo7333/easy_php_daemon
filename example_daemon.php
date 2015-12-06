#!/usr/bin/php -q
<?php
require_once("classes/indigo_daemon.class.php");

class yourclass extends indigo_daemon
{
  public function start_process()
  {
      //your daemon work
      return 1;
  }  
  public function include_libraries()
  {
      //include some libraries
  }
}




  
$daemon=new yourclass();
$daemon->log_file='/path/to/log_file';  //Path to Log file
$daemon->pid_file='/path/to/pid_file';    //Path to PidFile
$daemon->daemon_name='Your Daemon Name';  //Your Daemon Name
$daemon->init();
$daemon->write_log_file("test"); //write something to log file
$daemon->include_libraries();
$daemon->infinite(1000000); //execute the process every second
$daemon->shutdown();


