<?php
// twitter with pear services_twitter and oauth
class twitter_oauth {
  private $twitter;
  private $oauth;
  
  public function __construct($consumer_key, $consumer_secret, $auth_token, $token_secret) {
    require_once 'Services/Twitter.php';
    require_once 'HTTP/OAuth/Consumer.php';

    try {
      $this->twitter = new Services_Twitter();
      $this->oauth   = new HTTP_OAuth_Consumer('$consumer_key', '$consumer_secret', '$auth_token', '$token_secret');
      $this->twitter->setOAuth($this->oauth);

    } catch (Services_Twitter_Exception $e) {
      throw $e->getMessage();
    }

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

  public function send($tweet) {
    $msg = $this->twitter->statuses->update($tweet);
    print_r($msg);
  }
}
?>