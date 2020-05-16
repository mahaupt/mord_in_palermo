<?php
ini_set('display_errors', "0");
header('Content-Type: application/json');

/**
 * The installation class
 */
class Install {
  private $url;
  private $dbhost;
  private $dbuser;
  private $dbpass;
  private $dbdb;

  private $mysqli;


  /**
   * Gets the variables from post parameters
   */
  function __construct() {
    $this->url = $_POST['url'];
    $this->dbhost = $_POST['dbhost'];
    $this->dbuser = $_POST['dbuser'];
    $this->dbpass = $_POST['dbpass'];
    $this->dbdb = $_POST['dbdb'];
  }


  /**
   * Checks if the config file already exists and throws an exception if true
   * @return bool true if no config file exists and the installation can be commenced
   */
  public function securityCheck()
  {
    if (file_exists("../class/config.php")) {
      throw new Exception("Die Konfigurationsdatei existiert bereits! Aus Sicherheitsgründen muss diese Datei vorher gelöscht werden!");
    }
    return true;
  }

  /**
   * This function tests the installation settings
   * @return bool Returns true if successful. Throws exception otherwise.
   */
  public function testSettings()
  {
    if (!$this->securityCheck()) {
      return;
    }

    $this->mysqli = new mysqli($this->dbhost, $this->dbuser, $this->dbpass, $this->dbdb);

    if ($this->mysqli->connect_error) {
      throw new Exception("SQL Verbindung konnte nicht hergestellt werden: " . $this->mysqli->connect_error);
    }

    if (!$this->mysqli->query("CREATE TABLE IF NOT EXISTS install_test (id INT);")) {
      throw new Exception("Konnte keine SQL Tabelle erstellen: " . $this->mysqli->error);
    }

    if (!$this->mysqli->query("DROP TABLE install_test;")) {
      throw new Exception("Konnte die Testtabelle nicht löschen: " . $this->mysqli->error);
    }


    if (!file_put_contents("../class/config.php", "test")) {
      throw new Exception("Keine Schreibrechte für /class/config.php");
    }
    unlink("../class/config.php");

    return true;
  }


  /**
   * Puts the settings into a config file
   * @return bool Returns true if the process is completed.
   */
  public function writeConfig()
  {
    if (!$this->securityCheck()) {
      return;
    }

    $config = file_get_contents("config.tpl.php");

    $config = str_replace("{{install_url}}", addslashes($this->url), $config);
    $config = str_replace("{{install_dbname}}", addslashes($this->dbdb), $config);
    $config = str_replace("{{install_dbhost}}", addslashes($this->dbhost), $config);
    $config = str_replace("{{install_dbuser}}", addslashes($this->dbuser), $config);
    $config = str_replace("{{install_dbpass}}", addslashes($this->dbpass), $config);

    file_put_contents("../class/config.php", $config);

    return true;
  }


  /**
   * Writes the sql tables into the database
   * @return bool Returns true if successful, throws exception on error
   */
  public function writeDatabase()
  {
    $this->testSettings();
    $sql = file_get_contents("db.sql");

    if (!$this->mysqli->multi_query($sql)) {
      throw new Exception("Fehler beim Schreiben der Datenbank: " . $this->mysqli->error);
    }

    return true;
  }

}


if (array_key_exists("request", $_POST))
{
  $install = new Install();
  $myObj = new \stdClass();
  $myObj->request = $_POST['request'];
  $myObj->status = 'failed';

  try {
    if ($_POST['request'] == 'test')
    {
      if ($install->testSettings()) {
        $myObj->status = 'success';
      }
    }

    else if ($_POST['request'] == 'db')
    {
      if ($install->writeDatabase()) {
        $myObj->status = 'success';
      }
    }

    else if ($_POST['request'] == 'config')
    {
      if ($install->writeConfig()) {
        $myObj->status = 'success';
      }
    }
  }
  catch(Exception $e)
  {
    $myObj->status = 'error';
    $myObj->error = $e->getMessage();
  }

  echo json_encode($myObj);
}



 ?>
