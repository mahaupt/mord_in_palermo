<?php

/**
 * public message for every player
 */
class PublicMessage
{

  /**
   * create a public message from scratch
   * @param  int $game_id the game id
   * @param  string $type    the message type
   * @param  Object $obj     the object to be converted to json to send
   * @return void
   */
  public static function createMessage($game_id, $type, $obj)
  {
    $prepare = palermoDb::get()->prepare("INSERT INTO public_message
      SET game_id=?, type=?, message=?, created_at=?");
    $time = time();
    $message = json_encode($obj);
    $prepare->bind_param("issi", $game_id, $type, $message, $time);
    $prepare->execute();
    $prepare->close();
  }


  /**
   * get all new public messages from database
   * @param  int $game_id  the game id
   * @param  int $lastpull the lastpull timestamp
   * @return array           the new public messages
   */
  public static function getNewPublicMessages($game_id, $lastpull)
  {
    $result = palermoDb::get()->query("SELECT * FROM public_message
      WHERE game_id='" . $game_id . "' AND created_at>='" . $lastpull . "'");

    $return = array();
    while($obj = $result->fetch_object())
    {
      $return[] = $obj;
    }

    return $return;
  }



}



 ?>
