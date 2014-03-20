<?php
// database functions
class database {

  private $db = "";
  private $result = "";

  public function __construct($dbuser, $dbpass, $dbhost, $dbname) {
  // get neccessary PEAR classes
    require_once('DB.php');
    $this->db = DB::connect("mysql://$dbuser:$dbpass@$dbhost/$dbname");
    if(DB::isError($this->db)) {
      throw new Exception($this->db->getMessage());
    }
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
  
  // returns array with all servers
  public function getAllProbesToCheck() {
    $sql = "SELECT * FROM `servers` WHERE `check` = 1 ORDER BY name";
    $this->result = $this->db->query($sql);
    if(DB::isError($this->result)) {
      throw new Exception($this->result->getMessage());
    } else {
    
    while($link = $this->result->fetchrow(DB_FETCHMODE_ASSOC)) {
      $links[] = $link;
    }
      return $links;
    }
  }

  // returns array with all servers
  public function getAllProbes() {
    $sql = "SELECT * FROM `servers` ORDER BY name";
    $this->result = $this->db->query($sql);
    if(DB::isError($this->result)) {
      throw new Exception($this->result->getMessage());
    } else {
    
      while($link = $this->result->fetchrow(DB_FETCHMODE_ASSOC)) {
        $links[] = $link;
      }
      
      return $links;
    }
  }
  
  public function getProbeID($name, $url, $findstring) {
    $sql = "SELECT * FROM `servers` WHERE `name` = '".mysql_real_escape_string($name)."' AND `url` = '". mysql_real_escape_string($url) ."' AND `findstring` = '". mysql_real_escape_string($findstring) ."'";
    $this->result = $this->db->query($sql);
    if(DB::isError($this->result)) {
      throw new Exception($this->result->getMessage());
    } else {
      $row = $this->result->fetchrow(DB_FETCHMODE_ASSOC);
      return $row['id'];
    }
  }

  // returns array with all responsible persons for serverid
  public function getAllResponsible($id) {
    $sql = "SELECT *, responsible.id as resp_id FROM `responsible` INNER JOIN `users` ON (user_id = users.id) WHERE `server_id` = " . intval($id). " ORDER BY users.realname";
    $this->result = $this->db->query($sql);
    if(DB::isError($this->result)) {
      throw new Exception($this->result->getMessage());
    } else {
    
      while($link = $this->result->fetchrow(DB_FETCHMODE_ASSOC)) {
        $links[] = $link;
      }
      return $links;
      
    }
  }

  // returns true if responsible exists
  public function getResponsible($probeid, $respid) {
    $sql = "SELECT * FROM `responsible` WHERE `server_id` = " . intval($probeid). " AND `user_id` = " . intval($respid);
    $this->result = $this->db->query($sql);
    if(DB::isError($this->result)) {
      throw new Exception($this->result->getMessage());
    } else {
      $row = $this->result->fetchrow(DB_FETCHMODE_ASSOC);
      return $row['id'];
    }
  }
  
  public function getUserID($name, $pass) {
    $sql = "SELECT id FROM users WHERE login = '".mysql_real_escape_string($name)."' AND password = PASSWORD('". mysql_real_escape_string($pass) ."')";
    $this->result = $this->db->query($sql);
    if(DB::isError($this->result)) {
      throw new Exception($this->result->getMessage());
    } else {
      $row = $this->result->fetchrow(DB_FETCHMODE_ASSOC);
      return $row['id'];
    }
  }

  public function getUserIDAll($login, $realname, $email) {
    $sql = "SELECT id FROM users WHERE login = '".mysql_real_escape_string($login)."' AND realname = '". mysql_real_escape_string($realname) ."' AND email = '". mysql_real_escape_string($email)."'";
    $this->result = $this->db->query($sql);
    if(DB::isError($this->result)) {
      throw new Exception($this->result->getMessage());
    } else {
      $row = $this->result->fetchrow(DB_FETCHMODE_ASSOC);
      return $row['id'];
    }
  }

  public function getUserData($id) {
    $sql = "SELECT * FROM users WHERE id = ".intval($id);
    $this->result = $this->db->query($sql);
    if(DB::isError($this->result)) {
      throw new Exception($this->result->getMessage());
    } else {
      $row = $this->result->fetchrow(DB_FETCHMODE_ASSOC);
      return $row;
    }
  }

  public function getAllUserData() {
    $sql = "SELECT * FROM users ORDER BY realname";
    $this->result = $this->db->query($sql);
    if(DB::isError($this->result)) {
      throw new Exception($this->result->getMessage());
    } else {
      while($link = $this->result->fetchrow(DB_FETCHMODE_ASSOC)) {
        $links[] = $link;
      }
      return $links;
    }
  }

  public function pauseProbe($id) {
    $sql = "UPDATE servers SET `check` = 0 WHERE id = ".intval($id);
    $this->result = $this->db->query($sql);
    if(DB::isError($this->result)) {
      throw new Exception($this->result->getMessage());
    } else {
      return true;
    }
  }

  public function setProbeStatus($id, $status) {    
    if ($status == 1) {
      $sql = "UPDATE servers SET `status` = ".intval($status)." WHERE id = ".intval($id);
      $this->result = $this->db->query($sql);
      if(DB::isError($this->result)) {
        throw new Exception($this->result->getMessage());
      } else {
        return true;
      }    
    } else {
      // only set status if it has not already been set
      $sql = "SELECT `status` FROM servers WHERE id = ".intval($id);
      $this->result = $this->db->query($sql);
      if(DB::isError($this->result)) {
        throw new Exception($this->result->getMessage());
      } else {
        $row = $this->result->fetchrow(DB_FETCHMODE_ASSOC);                                                   
      }    
      if ($row['status'] != $status) {
        $sql = "UPDATE servers SET `status` = ".intval($status)." WHERE id = ".intval($id);
        $this->result = $this->db->query($sql);
        if(DB::isError($this->result)) {
          throw new Exception($this->result->getMessage());
        } else {
          return true;
        }    
      }    
    }
  }

  public function getProbeStatus($id) {
    $sql = "SELECT `status` FROM servers WHERE id = ".intval($id);
    $this->result = $this->db->query($sql);
    if(DB::isError($this->result)) {
      throw new Exception($this->result->getMessage());
    } else {
      $row = $this->result->fetchrow(DB_FETCHMODE_ASSOC);                                                   
      return $row['status'];
    }
  }

  public function getProbeStatusLogtime($id) {
    $sql = "SELECT `logtime` FROM servers WHERE id = ".intval($id);
    $this->result = $this->db->query($sql);
    if(DB::isError($this->result)) {
      throw new Exception($this->result->getMessage());
    } else {
      $row = $this->result->fetchrow(DB_FETCHMODE_ASSOC);                                                   
      return $row['logtime'];
    }
  }

  public function unpauseProbe($id) {
    $sql = "UPDATE servers SET `check` = 1 WHERE id = ".intval($id);
    $this->result = $this->db->query($sql);
    if(DB::isError($this->result)) {
      throw new Exception($this->result->getMessage());
    } else {
      return true;
    }
  }

  public function getProbeData($id) {
    $sql = "SELECT * FROM servers WHERE id = ".intval($id);
    $this->result = $this->db->query($sql);
    if(DB::isError($this->result)) {
      throw new Exception($this->result->getMessage());
    } else {
      $row = $this->result->fetchrow(DB_FETCHMODE_ASSOC);
      return $row;
    }
  }

  public function setProbeData($id, $name, $url, $host, $findstring, $check, $checkinterval) {
    if ($check == "check") {
      $check = 1;
    } else {
      $check = 0;
    }
    $sql = "UPDATE servers SET `name` = '".$name."', `url` = '".$url."', `host` = '".$host."',`findstring` = '".$findstring."', `check` = '".$check."', `checkinterval` = ".intval($checkinterval)." WHERE id = ".intval($id);
    $this->result = $this->db->query($sql);
    if(DB::isError($this->result)) {
      throw new Exception($this->result->getMessage());
    } else {
      return true;
    }
  }

  public function deleteProbeResponsible($id) {
    $sql = "DELETE FROM `responsible` WHERE id = ".intval($id);
    $this->result = $this->db->query($sql);
    if(DB::isError($this->result)) {
      throw new Exception($this->result->getMessage());
    } else {
      return true;
    }
  }

  public function addProbeResponsible($probeid, $respid) {
    if ($this->getResponsible($probeid, $respid) == 0) {
      $sql = "INSERT INTO `responsible` (server_id, user_id) VALUES (".intval($probeid). ", " .intval($respid).")";
      $this->result = $this->db->query($sql);
      if(DB::isError($this->result)) {
        throw new Exception($this->result->getMessage());
      } else {
        return true;
      }
    }
  }

  public function addProbe($name, $url, $host, $findstring, $checkinterval, $check) {
    if ($this->getProbeID($name, $url, $findstring) == 0) {
      $sql = "INSERT INTO `servers` (`name`, `url`, `host`, `findstring`, `checkinterval`, `check`) VALUES ('".mysql_real_escape_string($name). "', '" .mysql_real_escape_string($url)."', '" .mysql_real_escape_string($host)."', '".mysql_real_escape_string($findstring)."',".intval($checkinterval).", ".intval($check).")";
      $this->result = $this->db->query($sql);
      if(DB::isError($this->result)) {
        throw new Exception($this->result->getMessage());
      } else {
        return mysql_insert_id();
      }
    }
  }

  public function deleteProbe($id) {
    $sql = "DELETE FROM `servers` WHERE id = ".intval($id);
    $this->result = $this->db->query($sql);
    if(DB::isError($this->result)) {
      throw new Exception($this->result->getMessage());
    } else {
      return true;
    }
  }

  public function addUser($login, $realname, $email, $password = 'initinit') {
    if ($this->getUserIDAll($login, $realname, $email) == 0) {
      $sql = "INSERT INTO `users` (`login`, `password`, `realname`, `email`) VALUES ('".mysql_real_escape_string($login). "', PASSWORD('" .mysql_real_escape_string($password)."'), '".mysql_real_escape_string($realname)."', '".mysql_real_escape_string($email)."')";
      $this->result = $this->db->query($sql);
      if(DB::isError($this->result)) {
        throw new Exception($this->result->getMessage());
      } else {
        return true;
      }
    }
  }

  public function deleteUser($id) {
    $sql = "DELETE FROM `users` WHERE id = ".intval($id);
    $this->result = $this->db->query($sql);
    if(DB::isError($this->result)) {
      throw new Exception($this->result->getMessage());
    } else {
      return true;
    }
  }

  public function isAdminUser($id) {
    $sql = "SELECT `admin` FROM `users` WHERE id = ".intval($id);
    $this->result = $this->db->query($sql);
    if(DB::isError($this->result)) {
      throw new Exception($this->result->getMessage());
    } else {
      $row = $this->result->fetchrow(DB_FETCHMODE_ASSOC);
      return $row['admin'];
    }
  }

  public function setUserData($id, $login, $realname, $email, $password, $admin) {
    if ($password == "") {
      $sql = "UPDATE users SET `login` = '".$login."', `realname` = '".$realname."', `email` = '".$email."', `admin` = ".$admin." WHERE id = ".intval($id);
    } else {
      $sql = "UPDATE users SET `login` = '".$login."', `realname` = '".$realname."', `email` = '".$email."', `admin` = ".$admin.", `password` = PASSWORD('".$password."') WHERE id = ".intval($id);
    }
    $this->result = $this->db->query($sql);
    if(DB::isError($this->result)) {
      throw new Exception($this->result->getMessage());
    } else {
      return true;
    }
  }

  public function addProbeEvent($type, $text, $serverId) {
    $sql = "INSERT INTO `events` (`type`, `text`, `server_id`) VALUES ('".mysql_real_escape_string($type). "', '".mysql_real_escape_string($text). "', ".intval($serverId).")";
    $this->result = $this->db->query($sql);
    if(DB::isError($this->result)) {
      throw new Exception($this->result->getMessage());
    } else {
      return mysql_insert_id();
    }
  }

  public function getProbeResponse($id) {
    $sql = "SELECT * FROM events WHERE id = ".intval($id);
    $this->result = $this->db->query($sql);
    if(DB::isError($this->result)) {
      throw new Exception($this->result->getMessage());
    } else {
      $row = $this->result->fetchrow(DB_FETCHMODE_ASSOC);
      return $row;
    }
  }

  
}
?>
