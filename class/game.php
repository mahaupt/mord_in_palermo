<?php
/**
 * This file contains the game class
 * @package pagepackage
 */

require_once "config.php";
require_once "database.php";
require_once "toolbelt.php";
require_once "chat.php";
require_once "public_message.php";




/**
 *  This class handles the game, adds and removes player
 *  starts and stopps the game and also starts and stopps rounds
 *
 */

class Game
{
  private $id;
  private $is_started;
  private $spione = 10;
  private $aerzte = 20;
  private $moerder = 30;
  private $roundtime = 300;
  private $code;
  private $created_at;
  private $modified_at;
  private $round_started_at;
  private $round_end_block = 0;


  /**
   * Creates a new game
   * @return Game the created Game
   */
  public static function createNewGame()
  {
    global $_CONFIG;
    
    $game = new Game();
    $game->id = 0;
    $game->code = self::getNewGameCode();
    $game->created_at = time();
    $game->modified_at = $game->created_at;

    $game->spione = $_CONFIG['SPIONE'];
    $game->aerzte = $_CONFIG['AERZTE'];
    $game->moerder = $_CONFIG['MOERDER'];
    $game->roundtime = $_CONFIG['ROUNDTIME'];


    $result = palermoDb::get()->query(
      "INSERT INTO game SET
        code='" . $game->code . "',
        spione='" . $game->spione . "',
        aerzte='" . $game->aerzte . "',
        moerder='" . $game->moerder . "',
        roundtime='" . $game->roundtime . "',
        created_at='" . $game->created_at . "',
        modified_at='" . $game->modified_at . "'"
      );

    $game->id = palermoDb::get()->insert_id;

    //Insert Public chat and Wolf Chat into the database
    Chat::createPublicChat($game->id);
    Chat::createWolfChat($game->id);

    return $game;
  }


  /**
   * Tries to join a game by a code. Returns the game object when successful
   * Throws an exception on failure
   * @param  string $gcode the game code
   * @return Game        the game object
   */
  public static function getGameFromCode($gcode)
  {
    $prep = palermoDb::get()->prepare("SELECT * FROM game WHERE code=?");
    $prep->bind_param('s', $gcode);
    $prep->execute();
    $result = $prep->get_result();
    $prep->close();

    if ($result->num_rows == 0) {
      throw new Exception("Spiel wurde nicht gefunden!");
      return;
    }

    //get object
    $gobj = $result->fetch_object("Game");
    if ($gobj->is_started == 1) {
      throw new Exception("Spiel läuft bereits! Du kannst keinem laufendem Spiel beitreten!");
      return;
    }

    return $gobj;
  }


  /**
   * Get the game from the game id
   * @param  int $id the game id
   * @return Game     the game object or null if not found
   */
  public static function getGameFromId($id)
  {
      $prep = palermoDb::get()->prepare("SELECT * FROM game WHERE id=?");
      $prep->bind_param('i', $id);
      $prep->execute();
      $result = $prep->get_result();
      $prep->close();

      if ($result->num_rows <= 0) {
        return;
      }

      $gobj = $result->fetch_object("Game");

      return $gobj;
  }


  /**
   * Generates an unused game code. It generates a random game code and checks
   * if this code has been used before. If not, the code will be returned.
   * If the code has been used before, it will generate a new code.
   * @return string unused game code
   */
  private static function getNewGameCode()
  {
    global $_CONFIG;

    $game_code = "";

    do {
      $game_code = Toolbelt::genCode($_CONFIG['GAMECODE_LENGTH'], "0987654321");
      $result = palermoDb::get()->query("SELECT id FROM game WHERE code='$game_code'");
    } while($result->num_rows > 0);

    return $game_code;
  }

  /**
   * Adds a player to the game
   * @param Player $player The player to be added to the game
   */
  public function addPlayer(Player $player)
  {
    //first player - admin
    $is_admin = 0;
    if ($this->countPlayers() == 0) {
      $is_admin = 1;
      $player->setAdmin(true);
    }

    //add to game
    palermoDb::get()->query("INSERT INTO player SET
      code='" . $player->getCode() . "',
      game_id='" . $this->id . "',
      is_admin='" . $is_admin . "',
      modified_at='" . time() . "'");
    $player->setId(palermoDb::get()->insert_id);
    $player->setGameId($this->id);

    //add to public chat
    $publicChat = Chat::getPublicChat($this->id);
    $publicChat->addPlayer([$player->getId()]);

    /*palermoDb::get()->query("INSERT INTO chat_member (chat_id, game_id, player_id)
      SELECT id, '" . $this->id . "', '" . $player->getId() . "'
      FROM chat WHERE game_id='" . $this->id . "'");

    //update public chat
    /*palermoDb::get()->query("UPDATE chat
      SET modified_at='" . time() . "'
      WHERE game_id='" . $this->id . "' AND type='0'");*/

    //update game modified time
    palermoDb::get()->query("UPDATE game
      SET modified_at='" . time() . "'
      WHERE id='" . $this->id . "'");
  }


  /**
   * Removes a player from a game
   * @param  Player $player The player to be removed
   * @return void
   */
  public function removePlayer(Player $player)
  {

    //get all chat id's where player is in
    $prev_chats = palermoDb::get()->query("SELECT DISTINCT chat.id FROM chat, chat_member
      WHERE chat_member.player_id='" . $player->getId() . "' AND chat_member.chat_id=chat.id");

    //remove from all chats
    palermoDb::get()->query("DELETE FROM chat_member WHERE
      player_id='" . $player->getId() . "' ");

    //set all chat modified_at where player was in
    while ($chat_obj = $prev_chats->fetch_object())
    {
      palermoDb::get()->query("UPDATE chat
        SET modified_at='" . time() . "' WHERE id='" . $chat_obj->id . "'");
    }


    //remove votes
    palermoDb::get()->query("DELETE FROM vote WHERE
      player_id='" . $player->getId() . "'");

    //remove spion data
    palermoDb::get()->query("DELETE FROM spion_data WHERE
      player_id='" . $player->getId() . "'");

    //remove from game
    palermoDb::get()->query("SET foreign_key_checks = 0;");
    palermoDb::get()->query("DELETE FROM player WHERE
      id='" . $player->getId() . "'");
    palermoDb::get()->query("SET foreign_key_checks = 1;");

    //check if game empty
    if ($this->countPlayers() == 0)
    {
      //game empty
      $this->deleteGame();
      return;
    }
    else
    {
      //admin left - chose differend admin
      if ($player->is_admin())
      {
        palermoDb::get()->query("UPDATE player SET
          is_admin='1', modified_at='" . time() . "'
          WHERE game_id='" . $this->id . "' LIMIT 1");
      }

      //game not empty
      //update game modified time
      palermoDb::get()->query("UPDATE game
        SET modified_at='" . time() . "'
        WHERE id='" . $this->id . "'");

      //check if game is over or round ends
      $this->voteCompleteChecker();
    }
  }


  /**
   * Delete the whole game
   * @return void
   */
  public function deleteGame()
  {
    palermoDb::get()->query("SET foreign_key_checks = 0;");
    palermoDb::get()->query("DELETE FROM chat_member WHERE game_id='" . $this->id . "'");
    palermoDb::get()->query("DELETE FROM message WHERE game_id='" . $this->id . "'");
    palermoDb::get()->query("DELETE FROM chat WHERE game_id='" . $this->id . "'");
    palermoDb::get()->query("DELETE FROM vote WHERE game_id='" . $this->id . "'");
    palermoDb::get()->query("DELETE FROM spion_data WHERE game_id='" . $this->id . "'");
    palermoDb::get()->query("DELETE FROM player WHERE game_id='" . $this->id . "'");
    palermoDb::get()->query("DELETE FROM public_message WHERE game_id='" . $this->id . "'");
    palermoDb::get()->query("DELETE FROM game WHERE id='" . $this->id . "'");
    palermoDb::get()->query("SET foreign_key_checks = 1;");
  }


  /**
   * This method starts or stops the game
   * @param bool $status 1 for start, 0 for stopping the game
   */
  private function setGameStatus($status)
  {
    if (!($status === 1 || $status === 0))
    {
      throw Exception("Invalid parameter value");
    }

    $this->round_started_at = time();
    $this->modified_at = time();
    $this->is_started = $status;

    palermoDb::get()->query("UPDATE game
      SET is_started='" . $status . "', round_started_at='" . $this->round_started_at . "', modified_at='" . $this->modified_at . "'
      WHERE id='" . $this->id . "'");
  }


  /**
   * starts the game or throws an exception on failure
   * @return void
   */
  public function start()
  {
    $nplayers = $this->countPlayers();
    $spione_count = round($this->spione * $nplayers / 100);
    $aerzte_count = round($this->aerzte * $nplayers / 100);
    $moerder_count = round($this->moerder * $nplayers / 100);

    //too few players
    if ($nplayers < 4)
    {
      throw new Exception("Zu wenige Spieler vorhanden! Es werden mindestens 4 Spieler benötigt!");
    }

    //needs at least one moerder
    if ($moerder_count <= 0)
      $moerder_count = 1;

    //check if fole count fits player count
    while ($moerder_count+$aerzte_count+$spione_count > $nplayers)
    {

      if ($spione_count > 0)
        $spione_count--;

      //check again role counts
      if ($moerder_count+$aerzte_count+$spione_count <= $nplayers)
        break;

      if ($aerzte_count > 0)
        $aerzte_count--;

      //check again role counts
      if ($moerder_count+$aerzte_count+$spione_count <= $nplayers)
        break;

      if ($moerder_count > 1)
        $moerder_count--;
    }

    $this->resetAllPlayer();
    $mp = $this->pickRandomRole(2, $moerder_count);
    $this->pickRandomRole(1, $spione_count);
    $this->pickRandomRole(3, $aerzte_count);

    //TODO: delete all chats eventually?

    $this->setGameStatus(1);

    $wolfChat = Chat::getWolfChat($this->id);
    $wolfChat->emptyChat();
    $wolfChat->addPlayer($mp);

  }


  /**
   * End the round, kills the player
   * @return void
   */
  private function endRound()
  {
    //try to block round end
    $test = palermoDb::get()->query("UPDATE game SET round_end_block=1
      WHERE id='" . $this->id . "' AND round_end_block=0");

    if (palermoDb::get()->affected_rows != 1)
    {
      // failure - other instance ends the round for us
      return;
    }

    $killedby_buerger = -1;
    $killedby_moerder = -1;


    //bürger vote
    $result_buerger = palermoDb::get()->query("SELECT vote_id, COUNT(*) FROM vote
      WHERE game_id='" . $this->id . "' AND type=0
      GROUP BY vote_id
      ORDER BY COUNT(*) DESC");

    if ($obj = $result_buerger->fetch_object())
    {
      $killedby_buerger = $obj->vote_id;
      Player::killPlayerById($killedby_buerger);
    }


    //get moerder votes
    $result_moerder = palermoDb::get()->query("SELECT vote_id, COUNT(*) FROM vote
      WHERE game_id='" . $this->id . "' AND type=2
      GROUP BY vote_id
      ORDER BY COUNT(*) DESC");

    if ($obj = $result_moerder->fetch_object())
    {
      $killedby_moerder = $obj->vote_id;
    }

    //moerder victim already killed by player
    if ($killedby_moerder == $killedby_buerger)
    {
      $killedby_moerder = -3;
    }



    //get arzt votes - prevent moerder kill
    if ($killedby_moerder > 0)
    {
      $arzt_votes = palermoDb::get()->query("SELECT player_id, vote_id
        FROM vote
        WHERE game_id='" . $this->id . "' AND type=3");

      while($obj = $arzt_votes->fetch_object())
      {
        if ($obj->vote_id == $killedby_moerder)
        {
          $killedby_moerder = -2;
          break;
        }
      }
    }


    //kill moerder victim
    if ($killedby_moerder > 0)
    {
      Player::killPlayerById($killedby_moerder);
    }




    //get spion votes
    $spion_votes = palermoDb::get()->query("SELECT player_id, vote_id
      FROM vote
      WHERE game_id='" . $this->id . "' AND type=1");

    while($svote = $spion_votes->fetch_object())
    {
      $spied_person = Player::getFromId($svote->vote_id);
      palermoDb::get()->query("INSERT INTO spion_data
        SET
        game_id='" . $this->id . "',
        player_id='" . $svote->player_id . "',
        data_id='" . $spied_person->getId() . "',
        role='" . $spied_person->getRole() . "',
        created_at='" . time() . "'");
    }


    //delete all votes
    palermoDb::get()->query("DELETE FROM vote WHERE game_id='" . $this->id . "'");


    //prepare sendback message
    $myObj = new stdClass();
    $myObj->killedby_buerger = $killedby_buerger;
    $myObj->killedby_moerder = $killedby_moerder;
    $myObj->gameover = false;


    //check if game is over
    $gameover_var = $this->gameOverChecker();
    if ($gameover_var != 0)
    {
        //GAME IS OVER
        $myObj->gameover = true;
        $myObj->gameover_var = $gameover_var;

        //insert game over round end message
        PublicMessage::createMessage($this->id, "endround", $myObj);

        //free table and set is_started to zero
        $this->modified_at = time();
        palermoDb::get()->query("UPDATE game
          SET round_end_block=0, round_started_at='0', is_started='0',
          modified_at='" . $this->modified_at . "'
          WHERE id='" . $this->id . "'");

        return;
    }


    //insert round end message
    PublicMessage::createMessage($this->id, "endround", $myObj);


    //free table
    $this->round_started_at = time();
    $this->modified_at = $this->round_started_at;
    palermoDb::get()->query("UPDATE game
      SET round_end_block=0, round_started_at='" . $this->round_started_at . "',
      modified_at='" . $this->modified_at . "'
      WHERE id='" . $this->id . "'");

  }


  /**
   * this function checks if the round timer has ended
   * @return void
   */
  public function roundTimeoutChecker()
  {
    //skip if game is not started
    if ($this->is_started == 0)
    {
      return;
    }

    //timer ended
    if ($this->roundtime > 0)
    {
      if (time() >= $this->roundtime + $this->round_started_at)
      {
        $this->endRound();
      }
    }
  }


  /**
   * this function checks if all player have voted
   * @return void
   */
  public function voteCompleteChecker()
  {
    //skip if game is not started
    if ($this->is_started == 0)
    {
      return;
    }

    if (Vote::checkAllPlayerVoted($this->id))
    {
      $this->endRound();
    }
  }


  /**
   *  checks if a game is over and compiles all necessary steps
   * @return int returns 0 if game is not over, returns 1 if buerger won, returns 2 if moerder won
   */
  private function gameOverChecker()
  {
    $result = palermoDb::get()->query("SELECT COUNT(*) AS pcount, role FROM player
      WHERE game_id='" . $this->id . "' AND alive=1 GROUP BY role");

    $pcount = 0;
    $roles_counter = [0,0,0,0];

    while($obj = $result->fetch_object())
    {
      $roles_counter[$obj->role] = $obj->pcount;
      $pcount += $obj->pcount;
    }


    //no moerder exist - game over
    if ($roles_counter[2] == 0)
    {
      return 1; //buerger won
    }

    //if moerder == buerger
    // Ein Arzt kann es noch retten, wenn es mindestens zwei Nichtmörder gibt
    //  sonst -> moerder won
    $allexcmoer = $pcount - $roles_counter[2];
    if ($allexcmoer == $roles_counter[2])
    {
      if ($allexcmoer > 1 && $roles_counter[3] >= 1)
      {
        return 0;
      } else {
        return 2;
      }
    }

    //moerder > alle anderen -> moerder won
    if ($allexcmoer < $roles_counter[2])
    {
      return 2;
    }

    //niemand won
    return 0;
  }


  /**
   * resets all player roles and makes all players alive
   */
  private function resetAllPlayer()
  {
    palermoDb::get()->query("UPDATE player SET role=0, alive=1, modified_at='" . time() . "' WHERE game_id='" . $this->id . "'");

    //also reset spion data when resetting roles
    palermoDb::get()->query("DELETE FROM spion_data WHERE game_id='" . $this->id . "'");
  }


  /**
   * sets the player roles by random
   * @param  int $role   the role to be set
   * @param  int $number the number of players for that role
   * @return array of player ids who got picked
   */
  private function pickRandomRole($role, $number)
  {
    $return = array();

    for ($i=0; $i < $number; $i++)
    {
      $res = palermoDb::get()->query("SELECT id FROM player
                WHERE game_id='" . $this->id . "' AND role=0
                ORDER BY RAND() LIMIT 1");

      if ($obj = $res->fetch_object())
      {
        palermoDb::get()->query("UPDATE player
            SET role='" . $role . "'
            WHERE id='" . $obj->id . "'");

          $return[] = $obj->id;

        // , modified_at='" . time() . "' - no need
        // and possibility to peek who got a role
      }
    }


    //update spion data for moerders
    if ($role == 2)
    {
      $moerder = array();
      $res = palermoDb::get()->query("SELECT id FROM player WHERE game_id='" . $this->id . "' AND role=2");

      while($obj = $res->fetch_object())
      {
        $moerder[] = $obj->id;
      }

      //every moeder to every moerder
      foreach($moerder as $m1)
      {
        foreach($moerder as $m2)
        {
          if ($m1 == $m2) continue;

          palermoDb::get()->query("INSERT INTO spion_data
            SET game_id='" . $this->id . "', player_id='" . $m1 . "',
            data_id='" . $m2 . "', role=2, created_at='" . time() . "'");
        }
      }
    }

    return $return;
  }


  /**
   * Get game info and a player list
   * @param  int $game_id  The ID of the game
   * @param  int $lastpull timestamp of last pull
   * @return stdClass           Object of new game infos and player list
   */
  public static function getNewGameInfo($game_id, $lastpull)
  {
    $myObj = new \stdClass();

    //get game variables
    $result = palermoDb::get()->query("SELECT * FROM game WHERE
      id='" . $game_id . "' AND modified_at>='" . $lastpull . "'");

    if ($result->num_rows > 0)
    {
      $obj = $result->fetch_object();
      $myObj = $obj;

      //get list of players also
      $result2 = palermoDb::get()->query("SELECT id, name FROM player WHERE
        game_id='" . $game_id . "'");
      $myObj->player = array();

      while ($pobj = $result2->fetch_object()) {
        $myObj->player[] = $pobj;
      }
    }

    return $myObj;
  }


  /**
   * Saves the game settings
   * @param  int $aerzte
   * @param  int $spione
   * @param  int $moerder
   * @param  int $timer
   * @return bool          true if successful or false if failed
   */
  public function saveSettings($aerzte, $spione, $moerder, $timer)
  {
    //only take settings if game is not started
    if ($this->is_started)
    {
      return false;
    }

    if (!(is_integer($aerzte) && is_integer($spione) && is_integer($moerder) && is_integer($timer))) {
      return false;
    }

    if ($aerzte < 0 || $aerzte > 100) {
      return false;
    }
    if ($spione < 0 || $spione > 100) {
      return false;
    }
    if ($moerder < 0 || $moerder > 100) {
      return false;
    }
    if ($timer < 0 || $timer > 3600) {
      return false;
    }

    $this->aerzte = $aerzte;
    $this->spione = $spione;
    $this->moerder = $moerder;
    $this->roundtime = $timer;
    $this->modified_at = time();

    //save stuff
    $prep = palermoDb::get()->prepare("UPDATE game
      SET aerzte=?, spione=?, moerder=?, roundtime=?, modified_at=?
      WHERE id=?");

    $prep->bind_param("iiiiii",
      $this->aerzte,
      $this->spione,
      $this->moerder,
      $this->roundtime,
      $this->modified_at,
      $this->id);

    $prep->execute();
    return true;
  }


  /**
   * Returns the number of players currently in the game
   * @return int number of players
   */
  public function countPlayers()
  {
    $result = palermoDb::get()->query("SELECT COUNT(*) AS count FROM player WHERE
      game_id='" . $this->id . "'");

    $obj = $result->fetch_object();
    return $obj->count;
  }
}

 ?>
