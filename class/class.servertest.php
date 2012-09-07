<?php
// core functions
class serverTest {

  private $server = "";
  private $title = "";
  private $findstring = "";
  private $result = "";
  private $status = "";
  private $request = "";
  private $response = "";
  private $benchmark = "";
  private $version = "";
  private $hostname = "";
  
  public function __construct($title = "", $server = "") {
  // get neccessary PEAR classes
    require_once('HTTP/Request2.php');
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
    $this->request = new HTTP_Request2($this->server);     
  
    // set user agent
    $this->request->setHeader("User-Agent: Curl-Probe/". $this->version . " " . $this->title);

    $this->request->setConfig(array(
	'ssl_verify_peer'   => FALSE,
        'ssl_verify_host'   => FALSE
        ));

    // execute and close
    $this->benchmark->start();
    $this->response = $this->request->send();
    $this->result = $this->response->getBody();
    $this->benchmark->stop();

    if ($this->response->getStatus() == 200) {
      if (stristr($this->result, $this->findstring) === FALSE) {
        $this->status = false;
      } else {
        $this->status = true;
      }
    } else {
      $this->status = false;      
      $this->result = $this->response->getReasonPhrase() ."\n".$this->response->getBody();
    }
  }
}
?>