<?php
require_once "class/config.php";
require_once "class/database.php";
require_once "class/game.php";
require_once "class/player.php";
require_once "class/chat.php";
require_once "class/message.php";
require_once "class/vote.php";
require_once "class/public_message.php";

ini_set('display_errors', "0");
header('Content-Type: application/json');


if (array_key_exists('request', $_POST))
{

  // NEW GAME
  if ($_POST['request'] == 'newgame')
  {
    //create objects
    $game = Game::createNewGame();
    $player = Player::createNewPlayer();
    $game->addPlayer($player);
    $player->plantPlayerCode();

    //send command back
    $myObj = new \stdClass();
    $myObj->request = 'newgame';
    $myObj->status = 'success';
    $myObj->pcode = $player->getCode();
    echo json_encode($myObj);
  }

  // JOIN GAME
  else if ($_POST['request'] == 'joingame')
  {
    $myObj = new \stdClass();
    $myObj->request = 'joingame';

    //check if game is joinable
    try {
      $game = Game::getGameFromCode($_POST['code']);
    }
    catch(Exception $e) {
      $myObj->status = 'error';
      $myObj->error = $e->getMessage();
      echo json_encode($myObj);
      die();
    }

    //create objects
    $player = Player::createNewPlayer();
    $game->addPlayer($player);
    $player->plantPlayerCode();

    //send redirect command back
    $myObj->status = 'success';
    $myObj->pcode = $player->getCode();
    echo json_encode($myObj);
  }


  //leave game
  else if ($_POST['request'] == 'leavegame')
  {
    $myObj = new \stdClass();
    $myObj->request = 'leavegame';

    //get player or die
    $player = Toolbelt::getPlayerFromPcodeOrDie($myObj, $_COOKIE['pcode']);

    //get game
    $game = Game::getGameFromId($player->getGameId());

    $game->removePlayer($player);

    //send command back
    $myObj->status = 'success';
    $myObj->redirect_url = 'index.php';
    echo json_encode($myObj);
  }


  // PULL REQUEST
  else if ($_POST['request'] == 'pull')
  {
    $myObj = new \stdClass();
    $myObj->request = 'pull';

    //get player or die
    $player = Toolbelt::getPlayerFromPcodeOrDie($myObj, $_COOKIE['pcode']);

    //TODO: test if sqli proof
    $lastpull = intval($_POST['lastpull']);
    $myObj->data = new \stdClass();


    //own player info
    $myObj->data->self_id = $player->getId();
    $myObj->data->self_role = $player->getRole();


    //game info
    $myObj->data->game = Game::getNewGameInfo($player->getGameId(), $lastpull);


    //get player info
    $myObj->data->player = Player::getNewPlayerInfo($player->getGameId(), $lastpull);


    //get vote info
    $myObj->data->vote = Vote::getNewVoteInfo($player->getGameId(), $lastpull);


    //role vote info
    if ($player->getRole() != 0) {
      $myObj->data->role_vote = Vote::getNewVoteInfo($player->getGameId(), $lastpull, $player->getRole());
    }


    //spion secret info
    $myObj->data->spion_data = Vote::getNewSpionData($player->getId(), $lastpull);


    //send public messages
    $myObj->data->public_msg = PublicMessage::getNewPublicMessages($player->getGameId(), $lastpull);


    //get chat info
    $myObj->data->chat = Chat::getNewChatInfo($player->getId(),$lastpull);

    //get message info
    $myObj->data->message = Message::getNewMessageInfo($player->getID(),$lastpull);


    //last but not least: end round if timer depleated
    $game = Game::getGameFromId($player->getGameId());
    $game->roundTimeoutChecker();


    $myObj->status = 'success';
    $myObj->server_time = time();
    echo json_encode($myObj);
  }


  // VOTE
  else if ($_POST['request'] == 'vote')
  {
    $myObj = new \stdClass();
    $myObj->request = 'vote';

    //get player or die
    $player = Toolbelt::getPlayerFromPcodeOrDie($myObj, $_COOKIE['pcode']);

    //if not (type = 0 or type = role)
    if (!($_POST['type'] == $player->getRole() || $_POST['type'] == 0))
    {
      $myObj->status = 'error';
      $myObj->error = 'Ungültiger Votetyp';
      echo json_encode($myObj);
      die();
    }

    //execute vote
    if (!Vote::doVote($player, $_POST['type'], $_POST['vote_id']))
    {
      $myObj->status = 'error';
      $myObj->error = 'Vote konnte nicht aufgeführt werden.
                        Entweder wurde der Spieler nicht
                        gefunden oder du bist tot.';
      echo json_encode($myObj);
      die();
    }

    //get game and check if the current round can end
    $game = Game::getGameFromId($player->getGameId());
    $game->voteCompleteChecker();

    $myObj->status = 'success';
    echo json_encode($myObj);
  }


  // KICK
  else if ($_POST['request'] == 'kick')
  {
    $myObj = new \stdClass();
    $myObj->request = 'kick';

    //get player or die
    $player = Toolbelt::getPlayerFromPcodeOrDie($myObj, $_COOKIE['pcode']);

    //player is not admin - not able to kick
    if ($player->is_admin() == 0) {
      $myObj->status = 'error';
      $myObj->error = 'Du bist kein admin!';
      echo json_encode($myObj);
      die();
    }

    //get game object or dies
    $game = Toolbelt::getGameFromIdOrDie($myObj, $player->getGameId());


    //get player to kick
    if (!($kickplayer = Player::getFromId($_POST['player_id'])))
    {
      $myObj->status = 'error';
      $myObj->error = 'Spieler nicht gefunden!';
      echo json_encode($myObj);
      die();
    }

    //player to kick is not in same game
    if ($kickplayer->getGameId() != $player->getGameId()) {
      $myObj->status = 'error';
      $myObj->error = 'Spieler sind nicht im selben Spiel!';
      echo json_encode($myObj);
      die();
    }

    //kick player
    $game->removePlayer($kickplayer);


    //check if the current round can end
    $game->voteCompleteChecker();

    $myObj->status = 'success';
    echo json_encode($myObj);
  }


  // SAVE SETTINGS
  else if ($_POST['request'] == 'savesettings')
  {
    $myObj = new \stdClass();
    $myObj->request = 'savesettings';

    //get player or die
    $player = Toolbelt::getPlayerFromPcodeOrDie($myObj, $_COOKIE['pcode']);

    //player is not admin - not able to kick
    if ($player->is_admin() == 0) {
      $myObj->status = 'error';
      $myObj->error = 'Du bist kein admin!';
      echo json_encode($myObj);
      die();
    }

    //get game object or dies
    $game = Toolbelt::getGameFromIdOrDie($myObj, $player->getGameId());

    //save game settings
    if (!$game->saveSettings(intval($_POST['aerzte']), intval($_POST['spione']), intval($_POST['moerder']), intval($_POST['timer'])))
    {
      $myObj->status = 'error';
      $myObj->error = 'Fehler: Einstellungen ungültig';
      echo json_encode($myObj);
      die();
    }

    $myObj->status = 'success';
    echo json_encode($myObj);
  }


  // START GAME
  else if ($_POST['request'] == 'startgame')
  {
    $myObj = new \stdClass();
    $myObj->request = 'startgame';

    //get player or die
    $player = Toolbelt::getPlayerFromPcodeOrDie($myObj, $_COOKIE['pcode']);

    //player is not admin - not able to kick
    if ($player->is_admin() == 0) {
      $myObj->status = 'error';
      $myObj->error = 'Du bist kein admin!';
      echo json_encode($myObj);
      die();
    }

    //get game object or dies
    $game = Toolbelt::getGameFromIdOrDie($myObj, $player->getGameId());


    //start the game
    try {
      $game->start();
    }
    catch(Exception $e)
    {
      $myObj->status = 'error';
      $myObj->error = $e->getMessage();
      echo json_encode($myObj);
      die();
    }


    $myObj->status = 'success';
    echo json_encode($myObj);
  }


  // SEND NAME
  else if ($_POST['request'] == 'sendname')
  {
    $myObj = new \stdClass();
    $myObj->request = 'sendname';

    //get player or die
    $player = Toolbelt::getPlayerFromPcodeOrDie($myObj, $_COOKIE['pcode']);

    try {
      $player->setName($_POST['name']);
    } catch (\Exception $e) {
      $myObj->status = 'error';
      $myObj->error = $e->getMessage();
      echo json_encode($myObj);
      die();
    }


    $myObj->status = 'success';
    echo json_encode($myObj);
  }


  // PRIVATE CHAT
  else if ($_POST['request'] == 'createPrivateChat')
  {
    $myObj = new \stdClass();
    $myObj->request = 'createPrivateChat';

    //get player or die
    $player = Toolbelt::getPlayerFromPcodeOrDie($myObj, $_COOKIE['pcode']);

    $game_id = $player->getGameId();
    $player_id = $player->getId();
    $player_array = $_POST['player_ids'];
    $player_array[] = $player_id;
    $chatTitle = $_POST['chatTitle'];

    //create object 'chat'
    $chat = Chat::createPrivateChat($game_id, $chatTitle);
    $chat->addPlayer($player_array);
    //print_r ($player_array);
    $myObj->status = 'success';
    echo json_encode($myObj);

  }

  // SEND CHAT MESSAGE
  else if ($_POST['request'] == 'sendMessage')
  {
    $myObj = new \stdClass();
    $myObj->request = 'sendMessage';

    $player = Toolbelt::getPlayerFromPcodeOrDie($myObj, $_COOKIE['pcode']);

    $game_id = $player->getGameId();
    $player_id = $player->getId();
    $newMessage = $_POST['newMessage'];
    $chat_ID = $_POST['chat_ID'];
    //create object 'message'
    $message = Message::enterNewChatMessage($newMessage, $chat_ID, $player_id, $game_id);

    $myObj->status = 'success';
    echo json_encode($myObj);
  }

  // UNKNOWN
  else
  {
    $myObj = new \stdClass();
    $myObj->request = 'unknown';
    $myObj->status = 'error';
    $myObj->error = 'Unbekannter Request';
    echo json_encode($myObj);
    die();
  }



}
?>
