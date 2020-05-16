
<?php
/**
 * This file contains the toolbelt class
 * @package pagepackage
 */

require_once "player.php";

/**
 * This class contains some often used tools
 */
class Toolbelt
{


    /**
     * generates a random string with a given length
     * @param  integer $length length of the random string
     * @param  string $q (optional) string of available characters
     * @return string  the random string
     */

    public static function genCode($length = 5, $q = "0987654321abcdefghijklmnopqrstuvwxyz") {
      $r = "";

      if ($length <= 0 || !is_numeric($length))
        throw new \Exception("Invalid Parameter", 1);

      // build random string
      for($i=0; $i < $length; $i++) {
        $x = random_int(0, strlen($q)-1);
        $r .= $q[$x];
      }

      return $r;
    }


    /**
     * gets the player from the pcode or dies if error
     * it also outputs the error as json
     * @param  stdClass $outpObj the prefilled output object
     * @param  string $pcode   player code
     * @return Player          the player object
     */
    public static function getPlayerFromPcodeOrDie($outpObj, $pcode)
    {
      //player or game invalid
      if (!($player = Player::getFromPcode($_COOKIE['pcode']))) {
        $outpObj->status = 'error';
        $outpObj->redirect_url = 'index.php';
        $outpObj->error = 'Das Spiel ist nicht mehr vorhanden';
        echo json_encode($outpObj);
        die();
      }

      return $player;
    }


    /**
     * gets the game from a game id or dies if error
     * it also outputs the error as json
     * @param  stdClass $outpObj the prefilled output object
     * @param  int $game_id the game id
     * @return Game          the game object
     */
    public static function getGameFromIdOrDie($outpObj, $game_id)
    {
      //get game object
      if (!($game = Game::getGameFromId($game_id)))
      {
        $outpObj->status = 'error';
        $outpObj->redirect_url = 'index.php';
        $outpObj->error = 'Spiel nicht gefunden!';
        echo json_encode($outpObj);
        die();
      }

      return $game;
    }
}


?>
