<?php
// user functions
class user {

  private $name = "";
  private $email = "";
  private $userid = "";
  private $twitteruser = "";
  private $twitterpass = "";
  private $admin = false;
  private $jabberuser = "";
  
  public function __construct($name, $pass) {
    if ($_SESSION['db']->getUserID($name, $pass) <> 0) {
      $this->userid = $_SESSION['db']->getUserID($name, $pass);
      $res = $_SESSION['db']->getUserData($this->userid);
      $this->name = $res['realname'];
      $this->email = $res['email'];
      $this->twitteruser = $res['twitteruser'];
      $this->twitterpass = $res['twitterpass'];
      $this->admin = $res['admin'];
      $this->jabberuser = $res['jabberuser'];
    } else {
      throw new Exception ("Invalid login - user not found");
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

  public function isUser() {
    if ($_SESSION['db']->isAdminUser($_SESSION['user']->getUserid()) == 0) {
      return true;
    } else {
      return false;
    }
  }

  public function ownsProbe($probeId) {
    if ($_SESSION['db']->getResponsible($probeId, $_SESSION['user']->getUserid()) > 0) {
      return true;
    } else {
      return false;
    }
  }
}
?>
