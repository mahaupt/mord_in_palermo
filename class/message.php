<?php
/**
 * This file contains the message class
 * @package pagepackage
 */

require_once "config.php";
require_once "database.php";
require_once "toolbelt.php";
require_once "chat.php";

/** GAME MESSAGE
 *  Handles the message object
 *
 */
class Message{
  private $id;
  private $game_id;
  private $chat_id;
  private $player_id;
  private $time;
  private $message;
  private $sender; //me or other

public static function enterNewChatMessage($newMessage, $chat_id, $player_id, $game_id)
{

  $result = palermoDb::get()->prepare("SELECT * FROM chat_member WHERE
  chat_member.player_id=? AND chat_member.chat_id =?");

  $result->bind_param("ii", $player_id, $chat_id);
  $result->execute();
  $result_obj = $result->get_result();
  $result->close();

  if ($result_obj->num_rows == 0){
  return;
  }

  //safe chat message
  $safe_message = htmlspecialchars($newMessage);

  $prep = palermoDb::get()->prepare("INSERT INTO message SET
    player_id=?,
    chat_id=?,
    game_id=?,
    time='" . time() . "',
    message=?");

    $prep->bind_param("iiis", $player_id, $chat_id, $game_id, $safe_message);
    $prep->execute();
    $prep->close();

    $message = new Message();
    $message->id = palermoDb::get()->insert_id;
    $message->chat_id = $chat_id;
    $message->player_id = $player_id;
    $message->message = $safe_message;

    return $message;

    //palermoDb::get()->query("UPDATE chat
      //SET chat.modified_at='" . time() . "'
      //WHERE chat.id='" . $chat_id . "'");

}

public static function getNewMessageInfo($player_id, $lastpull)
{
  $return = array();

  $result = palermoDb::get()->query("SELECT message.id, chat_member.chat_id,
    message.player_id, message.message, message.time
    FROM message, chat_member, chat WHERE
    message.chat_id = chat.id AND chat_member.player_id='" . $player_id . "'
    AND chat_member.chat_id = chat.id AND message.time>='" . $lastpull . "'");

  while ($obj = $result->fetch_object())
  {
      $myObj = new \stdClass();
      $myObj->id = $obj->id;
      $myObj->chat_id = $obj->chat_id;
      $myObj->sender = $obj->player_id;
      $myObj->message = $obj->message;
      $myObj->time = $obj->time;
  //$myObj->created_at = $obj->created_at;

  $return[]=$myObj;
  }
  return $return;
}


}
 ?>
