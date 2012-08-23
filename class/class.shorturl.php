<?php
// tinyurl functions
class shorturl {

  private $url = "";
  public function __construct($url) {
    $this->url = $url;
  }
  
  // gets the data from a URL
  public function getShortURL() {
    $ch = curl_init();  
    $timeout = 5;  
    curl_setopt($ch,CURLOPT_URL,'http://is.gd/api.php?longurl='.$this->url);  
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);  
    curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);  
    $data = curl_exec($ch);  
    curl_close($ch);  
    return $data;  
  }
				    
  public function __destruct() {
  
  }
  
  public function __call($method, $arguments) {
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
}
?>