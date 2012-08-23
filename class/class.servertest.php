<?php
// core functions
class serverTest {

  private $server = "";
  private $title = "";
  private $findstring = "";
  private $result = "";
  private $status = "";
  private $curl = "";
  private $benchmark = "";
  private $version = "";
  private $hostname = "";
  
  public function __construct($title = "", $server = "") {
  // get neccessary PEAR classes
    require_once('Net/Curl.php');
    require_once('Benchmark/Timer.php');
    $this->title = $title;
    $this->server = $server;
    $this->benchmark = new Benchmark_Timer();
  }

  public function __destruct() {
  }
  
  function __call($method, $arguments) {
    $prefix = strtolower(substr($method, 0, 3));
    $property = strtolower(substr($method, 3));

    if (empty($prefix) || empty($property)) {
      return;
    }

    if ($prefix == "get" && isset($this->$property)) {
      return $this->$property;
    }
    
    if ($prefix == "set") {
      $this->$property = $arguments[0];
    }
  }
  
  // serverTest function: checks if servertest response is $findstring, 
  // otherwise mails with error messages will be sent
  public function test() {
    $this->curl = new Net_Curl($this->server);     
    
    // create object
    //$curl = new Net_Curl($server.'/servertest/'); 
  
    // set user agent
    $this->curl->userAgent = 'Curl-Probe/'. $this->version . " " . $this->title; //. ' ('.$hostname.')';
    
    // set timeout of 30 seconds
    $this->curl->timeout = 30;

    // execute and close
    $this->benchmark->start();
    $this->result = $this->curl->execute();
    $this->benchmark->stop();
    
    if (false === PEAR::isError($this->result)) {    
      if (stristr($this->result, $this->findstring) === FALSE) {
        $this->status = false;
      } else {
        $this->status = true;
      }
    } else {
      $this->status = false;
      $this->result = $this->result->getMessage();
    }
    $this->curl->close(); 
  }
}
?>