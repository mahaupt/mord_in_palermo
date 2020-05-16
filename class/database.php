<?php
/**
 * This file contains the palermoDb class
 * @package pagepackage
 */


require_once "config.php";

/**
 * This class contains some tools for working with a database
 */
class palermoDb
{
  private static $self;
  private $mysqli;

  /**
   * This constructor establishes a database connection
   */
  private function __construct() {
    global $_CONFIG;
    $this->mysqli = new mysqli($_CONFIG['DB_HOST'], $_CONFIG['DB_USER'], $_CONFIG['DB_PASS'], $_CONFIG['DB_DB']);
    $this->mysqli->set_charset("latin1");
  }

  /**
   * This destructor closes the database connection
   */
  function __destruct() {
    $this->mysqli->close();
  }

  /**
   * This static function returns a mysqli object. If no connection has been
   * established yet, the function calls the constructor.
   * @return mysqli the mysqli object
   */
  public static function get() {
    if (self::$self == null) {
      self::$self = new palermoDb();
    }



    return self::$self->mysqli;
  }
}

 ?>
