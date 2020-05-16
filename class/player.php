<?php
/**
 * This file contains the player class
 * @package pagepackage
 */

require_once "config.php";
require_once "database.php";

/**
 * Player class, handles most of player interaction
 */
class Player
{


  private $id;
  private $name;
  private $code;
  private $role;
  private $alive;
  private $game_id;
  private $is_admin = false;


  /**
   * creates a new, empty player handle
   * @return Player returnes the new player
   */
  public static function createNewPlayer() {
    $player = new Player();
    $player->code = self::getNewPlayerCode();
    return $player;
  }


  /**
   * Generates a new unused player code
   * @return string player code
   */
  private static function getNewPlayerCode() {
    global $_CONFIG;

    $player_code = "";

    do {
      $player_code = Toolbelt::genCode($_CONFIG['PLAYERCODE_LENGTH']);
      $result = palermoDb::get()->query("SELECT id FROM player WHERE code='$player_code'");
    } while($result->num_rows > 0);

    return $player_code;
  }


  /**
   * Gets a player object from a player code
   * @param  string $pcode the player code
   * @return mixed        either the player object or null
   */
  public static function getFromPcode($pcode)
  {
    $prepare = palermoDb::get()->prepare("SELECT * FROM player WHERE code=?");
    $prepare->bind_param('s', $pcode);
    $prepare->execute();
    $result = $prepare->get_result();
    $prepare->close();

    if ($result->num_rows != 1) {
      return;
    }

    $obj = $result->fetch_object("Player");

    return $obj;
  }

  /**
   * Get a player from a player id
   * @param  int $id player id
   * @return Player     the player object or null if not found
   */
  public static function getFromId($id)
  {
    $prepare = palermoDb::get()->prepare("SELECT * FROM player WHERE id=?");
    $prepare->bind_param('i', $id);
    $prepare->execute();
    $result = $prepare->get_result();
    $prepare->close();

    if ($result->num_rows != 1) {
      return;
    }

    $obj = $result->fetch_object("Player");

    return $obj;
  }


  /**
   * Gets all new player info
   * @param  int $game_id  the game id
   * @param  int $lastpull the timestamp of the last pull
   * @return array           new player info
   */
  public static function getNewPlayerInfo($game_id, $lastpull)
  {
    $myObj = array();
    $result = palermoDb::get()->query("SELECT id, name, alive, is_admin FROM player
      WHERE game_id='" . $game_id . "' AND modified_at>='" . $lastpull . "'");

    while($obj = $result->fetch_object())
    {
        $myObj[] = $obj;
    }

    return $myObj;
  }


  /**
   * Plants the player code inside the users cookies
   * @return void
   */
  public function plantPlayerCode() {
    setcookie('pcode', $this->code, 60*60*24*7);
  }


  /**
   * kills player by id
   * @param  int $id the player id
   * @return void
   */
  public static function killPlayerById($id)
  {
    palermoDb::get()->query("UPDATE player
      SET alive=0, modified_at='" . time() . "'
      WHERE id='" . $id . "'");

    palermoDb::get()->query("DELETE FROM vote WHERE player_id='" . $id . "'");
  }


  /**
   * sets the player name or throws an error on failure
   * @param string $name the new player name
   */
  public function setName($name)
  {
    $re = '/^(?=.{2,20}$)(?![\s])(?!.*[\s]{2})[a-zäöüA-ZÄÖÜ0-9\s]+$/m';

    if (strlen($this->name) > 0)
    {
      throw new Exception("Du kannst deinen Namen nicht mehr ändern!");
    }

    $array = array();
    if (!preg_match($re, $name, $array))
    {
      throw new Exception("Der Name hat nicht die erforderliche Struktur");
    }

    //just safety
    $name = htmlspecialchars($name);
    $time = time();

    $prep = palermoDb::get()->prepare("UPDATE player SET name=?, modified_at=? WHERE id=?");
    $prep->bind_param("sii", $name, $time, $this->id);
    $prep->execute();
    $prep->close();
  }


  /**
   * This function returns the player code
   * @return string Player code
   */
  public function getCode() {
    return $this->code;
  }

  /**
   * This function returns the player id
   * @return void
   */
  public function getId() {
    return $this->id;
  }

  /**
   * This function sets the player id
   * @param int $id Player ID
   */
  public function setId($id) {
    $this->id = $id;
  }


  /**
   * Sets the game id of the player
   * @param int $id the game id
   */
  public function setGameId($id) {
    $this->game_id = $id;
  }


  /**
   * Returns the game id
   * @return int game id
   */
  public function getGameId() {
    return $this->game_id;
  }


  /**
   * Gets the player role
   * @return int the player role
   */
  public function getRole() {
    return $this->role;
  }

  /**
   * Gets the player name
   * @return string the player name
   */
  public function getName() {
    return $this->name;
  }

  /**
   * sets if this the player is and admin
   * @param bool $admin true if admin
   */
  public function setAdmin($admin)
  {
    $this->is_admin = $admin;
  }


  /**
   * gets the admin status of the player
   * @return boolean true if admin
   */
  public function is_admin()
  {
    return $this->is_admin;
  }


  /**
   * returns true if player is alive
   * @return boolean true if player is alive
   */
  public function isAlive()
  {
    return $this->alive;
  }

}

 ?>
