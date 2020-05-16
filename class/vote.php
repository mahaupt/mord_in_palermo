<?php

require_once "player.php";

/**
 * This class handles all the votes
 */
class Vote
{
  /**
   * Get new vote information
   * @param  int $game_id  the game id
   * @param  int $lastpull the time of last pull
   * @param  int $vote_type the role type of the vote
   * @return array           the vote info array
   */
  public static function getNewVoteInfo($game_id, $lastpull, $vote_type = 0)
  {
    //only moerders and buergers get vote info
    //if ($vote_type != 0 && $vote_type != 2) return;

    $myObj = array();
    $result = palermoDb::get()->query("SELECT vote.id, vote.player_id, vote.vote_id
      FROM vote, player
      WHERE player.game_id='" . $game_id . "' AND vote.player_id=player.id AND
      vote.modified_at>='" . $lastpull . "' AND vote.type='" . $vote_type . "'");

    while($obj = $result->fetch_object()) {
      $myObj[] = $obj;
    }

    return $myObj;
  }



  /**
   * gets new spion data from database
   * @param  int $player_id player id
   * @param  int $lastpull  last pull time stamp
   * @return array
   */
  public static function getNewSpionData($player_id, $lastpull)
  {
      $result = palermoDb::get()->query("SELECT data_id as id, role
        FROM spion_data
        WHERE player_id='" . $player_id . "' AND created_at >= '" . $lastpull . "'");

      $return = array();
      while($obj = $result->fetch_object())
      {
        $return[] = $obj;
      }

      return $return;
  }



  /**
   * This function inserts a vote into the sql database
   * @param  Player $player    The current player who votes
   * @param  int $type    The type of vote: 0: Bürger // 1: Mörder // 2: Arzt
   * @param  int $vote_id The player id that is voted against
   * @return bool true if successful, false if not
   */
  public static function doVote($player, $type, $vote_id)
  {
    //check if self is alive
    if ($player->isAlive() == 0)
    {
      return false;
    }

    //check if player is valid
    $vote_player = Player::getFromId($vote_id);
    if (!$vote_player) {
      return false;
    }
    if ($vote_player->getGameId() != $player->getGameId()) {
      return false;
    }

    $prep = palermoDb::get()->prepare("SELECT id FROM vote WHERE player_id=? AND type=?");
    $pid = $player->getId();
    $prep->bind_param("ii", $pid, $type);
    $prep->execute();
    $result = $prep->get_result();
    $prep->close();

    $time = time();

    if ($result->num_rows >= 1)
    {
      //edit vote
      $fetch = $result->fetch_object();

      $prep = palermoDb::get()->prepare("UPDATE vote SET vote_id=?, modified_at=? WHERE id=?");
      $prep->bind_param('iii', $vote_id, $time, $fetch->id);
      $prep->execute();
    }
    else
    {
      //insert new vote
      $prep = palermoDb::get()->prepare("INSERT INTO vote
        SET player_id=?, game_id=?, type=?, vote_id=?, modified_at=?");
      $pid = $player->getId();
      $gid = $player->getGameId();
      $prep->bind_param('iiiii', $pid, $gid, $type, $vote_id, $time);
      $prep->execute();
    }
    return true;
  }


  /**
   * checks if all players voted
   * @param  int $game_id the game id
   * @return bool          true if all voted, false otherwise
   */
  public static function checkAllPlayerVoted($game_id)
  {
    $pcount = 0;
    $all_player = array();

    //save player
    $res1 = palermoDb::get()->query("SELECT COUNT(id) AS pcount, role FROM player
      WHERE game_id='" . $game_id . "' AND alive=1 GROUP BY role");

    while($obj = $res1->fetch_object())
    {
      $all_player[$obj->role] = $obj->pcount;
      $pcount += $obj->pcount;
    }


    //save votes
    $res2 = palermoDb::get()->query("SELECT COUNT(id) AS vcount, type FROM vote
      WHERE game_id='" . $game_id . "' GROUP BY type");

    $vote_count = [0,0,0,0];
    while ($obj = $res2->fetch_object())
    {
      $vote_count[$obj->type] = $obj->vcount;
    }

    //go through votes
    foreach($vote_count as $type=>$count)
    {
      if ($type == 0)
      { // ANY PLAYER
        if ($count < $pcount)
        {
          return false;
        }
      } else
      { // ROLE
        if ($count < $all_player[$type])
        {
          return false;
        }
      }
    }

    return true;
  }

}


 ?>
