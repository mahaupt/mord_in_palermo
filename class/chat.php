<?php
/**
 * This file contains the chat class
 * @package pagepackage
 */

require_once "config.php";
require_once "database.php";
require_once "toolbelt.php";


/** GAME CHAT
 *  Handles the chat object
 */

class Chat {
  private $id;
  private $game_id;
  private $type;
  private $name;
  private $modifiable;
  private $modified_at;
  private $player_array;

  /**
  * This function creates the public chat
  * @param  int $game_id  current game id
  * @return Chat the created chat object
  */
  public static function createPublicChat($game_id)
  {
    palermoDb::get()->query("INSERT INTO chat SET
      game_id='" . $game_id . "',
      modifiable='0',
      type='0',
      name='Gruppenchat',
      modified_at='" . time() . "'");


    $chat = new Chat();
    $chat->id = palermoDb::get()->insert_id;
    $chat->game_id = $game_id;
    $chat->modifiable = false;
    $chat->type = 0;
    return $chat;

  }

/**
 * This function creates a private chat with one or more specific players
 * @param  int $game_id  current game id
 * @return Chat the created chat object
 */
  public static function createPrivateChat($game_id, $chatTitle)
  {
    //safe title
    $safe_chatTitle = htmlspecialchars($chatTitle);

    $prep = palermoDb::get()->prepare("INSERT INTO chat SET
      game_id=?,
      modifiable='1',
      type='1',
      name=?,
      modified_at='" . time() . "'");

    $prep->bind_param("is", $game_id, $safe_chatTitle);
    $prep->execute();
    $prep->close();

    $chat = new Chat();
    $chat->id = palermoDb::get()->insert_id;
    $chat->game_id = $game_id;
    $chat->modifiable = true;
    $chat->type = 1;
    $chat->name = $safe_chatTitle;
    return $chat;
  }

/**
 * Creates a chat for the wolves
 * @param  int $game_id  current game id
 * @return Chat the created chat object
 */
  public static function createWolfChat($game_id)
  {
    palermoDb::get()->query("INSERT INTO chat SET
      game_id='" . $game_id . "',
      modifiable='0',
      type='2',
      name='Komplizen',
      modified_at='" . time() . "'");

    $chat = new Chat();
    $chat->id = palermoDb::get()->insert_id;
    $chat->game_id = $game_id;
    $chat->modifiable = false;
    $chat->type = 2;
    return $chat;
  }

/**
 * This function adds one or more players to the database for a specific chat
 * @param array $player_array the players to add to the chat
 */
  public function addPlayer($player_array)
  {


    foreach ($player_array as $player_id)
    {

      //print_r ($player_array);
      //check if player exists in the database
      $prepPlayer = palermoDb::get()->prepare("SELECT * FROM player WHERE id = ?");
      $prepPlayer->bind_param('i', $player_id);
      $prepPlayer->execute();
      $result = $prepPlayer->get_result();
      $prepPlayer->close();
      if ($result->num_rows != 1)
      {
        return;
      }
      $player_obj = $result->fetch_object();
      $player = $player_obj->id;

      //check if player is part of this game
      $playerGame = $player_obj->game_id;
      if ($this->game_id != $playerGame)
      {
        return;
      }

      //If player exists and if he's part of the game, he may enter the database
      palermoDb::get()->query("INSERT INTO chat_member SET
        player_id='" .  $player . "',
        chat_id='" . $this->id . "',
        game_id='" . $this->game_id . "'");


    }
    palermoDb::get()->query("UPDATE chat
      SET modified_at='" . time() . "'
      WHERE id='" . $this->id . "'");
  }

  public static function getNewChatInfo($player_id, $lastpull)
  {

    $return = array();

    $result = palermoDb::get()->query("SELECT * FROM chat_member, chat WHERE
    chat_member.player_id='" . $player_id . "' AND chat_member.chat_id = chat.id
    AND modified_at>='" . $lastpull . "'");


      while ($obj = $result->fetch_object()){
      $myObj = new \stdClass();
      $myObj->id = $obj->id;
      $myObj->name = $obj->name;
      $myObj->type = $obj->type;
      $myObj->modified_at = $obj->modified_at;


      $result2 = palermoDb::get()->query("SELECT player_id FROM chat_member where
      chat_id = '" . $myObj->id . "'");
      $myObj->chat_member = $result2->fetch_all();

      //$result3 = palermoDb::get()->query("SELECT ");

      $return[]=$myObj;
      }

      //print_r($return);

    return $return;

  }

  public static function getPublicChat($game_id)
  {
    $myObj = array();
    $result = palermoDb::get()->query("SELECT * FROM chat
      WHERE type = 0 AND game_id = '" . $game_id . "'");

    if ($obj = $result->fetch_object("Chat"))
    {
        return $obj;
    }

    return null;
  }

  public static function getWolfChat($game_id)
  {
    $myObj = array();
    $result = palermoDb::get()->query("SELECT * FROM chat
      WHERE type = 2 AND game_id = '" . $game_id . "'");

    if ($obj = $result->fetch_object("Chat"))
    {
        return $obj;
    }

    return null;
  }

  public function emptyChat()
  {
    $prepare = palermoDb::get()->prepare("DELETE FROM chat_member
      WHERE chat_id = ? ");
    $prepare->bind_param('i', $this->id);
    $prepare->execute();
    $prepare->close();

  }

  }

?>
