<?php
// mail functions
class sendMail {

  private $recipients = "";
  private $mailer = "";
  private $options = "";
  private $headers = "";
  private $result = "";
  private $mailfrom = "";
  private $replyto = "";
  
  public function __construct($recipients, $mailfrom, $replyto) {  
    require_once('Mail.php');
    $this->recipients = $recipients;
    $this->mailfrom = $mailfrom;
    $this->replyto = $replyto;
    $this->options = array(
      'host' => 'localhost'
    );

    $this->mailer = Mail::factory('smtp', $this->options);

    // set headers
    $this->headers = array(
      'From' => $mailfrom,
      'Return-Path' => $mailfrom,
      'Reply-To' => $replyto,
      'To'   => $this->recipients,
      'Subject' => '',
      'Date'  => date("r"),
      'Message-Id' => '<' . str_replace(".","_",uniqid('', true)) . '@' . gethostname() . '>',
      'Content-Type' => 'text/plain',
      'X-Priority' => '1 (Highest)',
      'X-Mailer' => 'PHP ' . phpversion()

    );
    
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
  
  // sendmail function
  public function send($subject, $text){
    // set subject
    $this->headers['Subject'] .= $subject; // ." [".date("H:i:s")."]";
    // send mail
    $this->result = $this->mailer->send($this->recipients, $this->headers, $text);  
  }
}
?>