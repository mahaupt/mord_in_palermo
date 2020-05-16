var GAME;

function Game() {
  this.id;
  this.player = [];
  this.player_self;
  this.is_started;
  this.code;
  this.spione;
  this.aerzte;
  this.moerder;

  this.chats = [];

  this.votes = [];
  this.role_votes = [];

  this.lastupdate;
  this.interval_handler;

  this.nameRegex = /^(?=.{2,20}$)(?![\s])(?!.*[\s]{2})[a-zäöüA-ZÄÖÜ0-9\s]+$/m;

  /**
   * starts up the js site of the game and sets up all important variables
   * @return {void}
   */
  this.startup = function()
  {
    this.lastupdate = 0;

    this.player_self = {};

    //check if pcode exists
    var pcode = getCookie("pcode");
    if (pcode.length <= 0) {
      window.location.href = "./index.php";
    }

    this.interval_handler = setInterval(function() { GAME.loop(); }, 1000);


    //display tutorial
    var tutCookie = getCookie("palermoTut");
    if (tutCookie != 1)
    {
      startTutorial();
    }
  }


  /**
   * the main game loop, sends pull requests to the server to get information
   * @return void
   */
  this.loop = function()
  {
    $.post("./webhook.php", {'request':'pull', 'lastpull':this.lastupdate})
      .done(function (data)
      {
        console.log(data);

        //exit the game
        if (data.redirect_url) {
          delCookie('pcode');
          window.location.href = data.redirect_url;
          return;
        }

        //data income
        if (data.status == 'success')
        {
          GAME.lastupdate = data.server_time;

          //get own player id
          GAME.player_self.id = data.data.self_id;
          GAME.player_self.role = data.data.self_role;

          //get game data
          if ('id' in data.data.game)
          {
            var gamejuststarted = (GAME.is_started == 0 && data.data.game.is_started == 1);

            GAME.code = data.data.game.code;
            GAME.spione = data.data.game.spione;
            GAME.aerzte = data.data.game.aerzte;
            GAME.moerder = data.data.game.moerder;
            GAME.is_started = data.data.game.is_started;
            GAME.roundtime = data.data.game.roundtime;
            GAME.round_started_at = data.data.game.round_started_at;


            //game just started actions
            if (gamejuststarted)
            {
              deleteOldWulfChat();
              GAME.resetAllPlayer();
            }


            //parse player lists
            GAME.parseGamePlayerList(data.data.game.player);

            //update game UI
            GAME.updateSettings();
          }


          //get player data
          if (data.data.player.length > 0)
          {
            GAME.updatePlayerInfo(data.data.player);
            renderChatButtonWindow(GAME.chats);
          }

          //update player UI
          if ('id' in data.data.game || data.data.player.length > 0)
          {
            GAME.updatePlayerLists();
            drawPlayerMessageCounter();
          }

          //get public message data
          if (data.data.public_msg.length > 0)
          {
            GAME.parsePublicMessages(data.data.public_msg);
          }

          //get vote data
          if (data.data.vote.length > 0 || 'id' in data.data.game || data.data.player.length > 0)
          {
            GAME.parseVoteData(data.data.vote);
            GAME.displayVoteNotification();
          }

          //parse role vote data
          if (data.data.role_vote)
            if (data.data.role_vote.length > 0 || 'id' in data.data.game || data.data.player.length > 0)
            {
              GAME.parseRoleVoteData(data.data.role_vote);
              GAME.displayVoteNotification();
            }

          //get spione secret role info
          if (data.data.spion_data.length > 0)
          {
            GAME.getSpionInfo(data.data.spion_data);
          }

          //get chat data
          if (data.data.chat.length > 0)
          {
            //check if chat appears two times
            for (var x = 0; x < data.data.chat.length; x++)
            {
              var chatDouble = false;

              for (var j = 0; j < GAME.chats.length; j++)
              {
                //if chat already exists, replace relevant information
                if (data.data.chat[x].id == GAME.chats[j].id)
                {
                  chatDouble = true;
                  GAME.chats[j].chat_member = data.data.chat[x].chat_member;
                  GAME.chats[j].modified_at = data.data.chat[x].modified_at;
                  GAME.chats[j].name = data.data.chat[x].name;
                  GAME.chats[j].type = data.data.chat[x].type;

                  break;
                }
              }

              // If chat doesn't exist yet, create new chat object
              if (!chatDouble)
              {
                GAME.chats.push(data.data.chat[x]);
                var li = GAME.chats.length-1;
                GAME.chats[li].messages = [];
                GAME.chats[li].last_message_time = GAME.chats[li].modified_at;
                GAME.chats[li].last_time_checked = GAME.chats[li].modified_at;
              }

            }//for

            //directly join new chat when created in Dashboard
            if (joinNextCreatedChat)
            {
              var li = GAME.chats.length -1;
              joinNextCreatedChat = false;
              var data2 = {dataset: {cid: GAME.chats[li].id, title: GAME.chats[li].name}};
              pickThisChatWindow(data2);
              var api = $("#menu").data( "mmenu" );
              api.openPanel( $("#chat-window") );
            }

            //finally render the chat buttons
            if (GAME.chats.length > 0)
            {
              renderChatButtonWindow(GAME.chats);
            }
          }

          //get message data
          if (data.data.message.length > 0)
          {
            for ( var i = 0; i < GAME.chats.length; i++)
            {
              //if message doesn't exist, create empty array to avoid errors
              if (GAME.chats[i].messages == null)
              {
                GAME.chats[i].messages = [];
              }

              //select the corresponding chat for the new message
              for ( var x = 0; x < data.data.message.length; x++)
              {
                if (GAME.chats[i].id != data.data.message[x].chat_id)
                {
                  continue;
                }

                //check for double messages
                var msgDouble = false;
                var startIndex = GAME.chats[i].messages.length -5;
                if (startIndex < 0) startIndex = 0;
                for (var j = startIndex; j < GAME.chats[i].messages.length; j++)
                {
                  if (data.data.message[x].id == GAME.chats[i].messages[j].id)
                  {
                    msgDouble = true;
                    break;
                  }
                }

                //only if message doesn't already exist, push it into the object
                //and create a new last message time stamp
                if (!msgDouble)
                {
                  GAME.chats[i].messages.push(data.data.message[x]);
                  GAME.chats[i].last_message_time = data.data.message[x].time;
                }
              }//for x
            }//for i

            //render the chatbuttons for a new pillnumber and run renderChatWindowPack()
            renderChatButtonWindow(GAME.chats)
            renderChatWindowPack(GAME.chats);
            dashbRenderMessagePill();
            drawPlayerMessageCounter();
            scrollToBottom();
          } // if
        }
      });
  }


  /**
   * takes the player list from the game
   * checks if player left the game and removes this player from this.player
   * also sees if a new player joined the game and adds it to this.player
   * @param  {array} gplist the player list from data.game.player
   * @return {void}
   */
  this.parseGamePlayerList = function(gplist)
  {
    //marks every existing player as outdated
    for (var j=0; j < this.player.length; j++) {
      this.player[j].updated = false;
    }

    //goes through every new player
    for(var i=0; i < gplist.length; i++)
    {
      //searches for existing player
      var playerfound = false
      for (var j=0; j < this.player.length; j++) {
        if (this.player[j].id == gplist[i].id) {
          this.player[j].updated = true;
          playerfound = true;
        }
      }

      //player is new - add it
      if (!playerfound) {
        var ni = this.player.length;
        //console.log("Add Player" + ni);
        this.player[ni] = Object.create(gplist[i]);
        this.player[ni].updated = true;
        this.player[ni].color = getRandColor(400);
        //console.log(this.player[ni]);
      }
    }

    //deletes outdated player
    for (var j=this.player.length-1; j>=0; j--) {
      if (!this.player[j].updated) {
        //console.log("Delete Player" + j);
        //console.log(this.player[j]);
        this.player.splice(j, 1);
      }
    }
  }


  /**
   * updates name, id, alive and is_admin from a player
   * takes only player who have been recently modified
   * @param  {array} plist the data.player array
   * @return {void}
   */
  this.updatePlayerInfo = function(plist)
  {

    //goes through every updated player
    for(var i=0; i < plist.length; i++)
    {

      for(var j=0; j < this.player.length; j++)
      {
        //player found
        if (plist[i].id == this.player[j].id)
        {
          //console.log("Update Player" + j);


          //update information
          this.player[j].name = plist[i].name;
          this.player[j].is_admin = plist[i].is_admin;
          this.player[j].alive = plist[i].alive;

          //console.log(this.player[j]);

          //player is self
          if (this.player_self.id == this.player[j].id)
          {
            var ownRole = this.player_self.role;
            this.player_self = Object.create(this.player[j]);
            this.player_self.role = ownRole;

            //check for player name
            if (this.player_self.name.length <= 0)
            {
              this.askForName();
            }
          }

        }
      }
    }
  }


  /**
   * updates the settings page
   * @return {void}
   */
  this.updateSettings = function()
  {
    var code = this.code;

    $('#settings-gcode').html('<span style="margin-right: 5px;">'
      + code.substr(0,3)
      + '</span>'
      + code.substr(3, 3));

    //game slider settings display -> game_settings

    $('#settings-slider-spione input').val(this.spione);
    $('#settings-slider-aerzte input').val(this.aerzte);
    $('#settings-slider-moerder input').val(this.moerder);
    $('#settings-slider-timer input').val(Math.round(parseInt(this.roundtime)/60));

    //calculate text
    calculateNewSliderValues();
  }

  /**
   * updates player lists at the settings and dashboard page
   * also sets own player name
   * @return {void}
   */
  this.updatePlayerLists = function()
  {
    //set user name to user interface
    var pname = this.player_self.name;
    if (pname.length == 0) {
      pname = "Unbekannt";
    }

    $('#dashb-personalinfo-name').text(pname);

    //prepend cross if dead
    if (this.player_self.alive == 0)
    {
      $('#dashb-personalinfo-name').prepend("&#x271D; ");
    }

    //display counter if necessary
    if (GAME.roundtime > 0 && GAME.is_started == 1)
    {
      var remaining = GAME.roundtime - (GAME.lastupdate - GAME.round_started_at);
      $('#dashb-personalinfo-timer').removeClass("d-none");
      $('#dashb-personalinfo-timer').text(timeToText(remaining));
    } else {
      $('#dashb-personalinfo-timer').addClass("d-none");
    }

    //display game start button if plyer admin
    if (this.player_self.is_admin == 1 && this.is_started == 0)
    {
      $('#dashb-game-start-button').removeClass('d-none');
      $('#dashb-game-start-button').removeClass('disabled');
      $('#dashb-game-start-info').removeClass('d-none');
    } else if (this.is_started == 0) {
      $('#dashb-game-start-info').removeClass('d-none');
    } else {
      $('#dashb-game-start-info').addClass('d-none');
      $('#dashb-game-start-button').addClass('d-none');
    }


    //update player lists
    dashbBuildPlayerList(this.player);
    settBuildPlayerList(this.player);

    //reinit panels
    var api = $("#menu").data( "mmenu" );
    api.initPanels();
  }


  this.parseVoteData = function(votes)
  {
    for(var i=0; i < votes.length; i++)
    {
      var votefound = false;

      for(var j=0; j < this.votes.length; j++)
      {
          if (votes[i].id == this.votes[j].id)
          {
            votefound = true;
            this.votes[j] = votes[i];
          }
      }

      //new vote - add to votes
      if (!votefound)
      {
          this.votes.push(votes[i]);
      }

    }

    //draw UI
    dashbDrawVotesCounter();
  }


  this.parseRoleVoteData = function(votes)
  {
    for(var i=0; i < votes.length; i++)
    {
      var votefound = false;

      for(var j=0; j < this.role_votes.length; j++)
      {
          if (votes[i].id == this.role_votes[j].id)
          {
            votefound = true;
            this.role_votes[j] = votes[i];
          }
      }

      //new vote - add to votes
      if (!votefound)
      {
          this.role_votes.push(votes[i]);
      }

    }

    //draw UI
    dashbDrawRoleVotesCounter();
  }


  this.displayVoteNotification = function()
  {
    var displayIt = false;

    if (GAME.is_started == 1)
    {
      if (dashb_own_vote < 0)
      {
        displayIt = true;
      }

      if (dashb_own_vote_spezi < 0 && GAME.player_self.role > 0)
      {
        displayIt = true;
      }

      if (GAME.player_self.alive == 0)
      {
        displayIt = false;
      }
    }


    if (displayIt)
    {
      $('#dashb-game-vote-info').removeClass("d-none");
    }
    else
    {
      $('#dashb-game-vote-info').addClass("d-none");
    }
  }



  this.getSpionInfo = function(info)
  {
    for(var i = 0; i < info.length; i++)
    {
      var pid = info[i].id;
      var role = info[i].role;

      //put this into player array
      for (var j = 0; j < this.player.length; j++)
      {
        if (this.player[j].id == pid)
        {
          this.player[j].role = role;
        }
      }
    }
  }


  /**
   * this function sends a leavegame command to the server
   * to leave the game
   * @return {void}
   */
  this.leaveGame = function()
  {
    this.stopPull();

    webhookCall("webhook.php", {request: 'leavegame'},
      function(data)
      { // SUCCESS
        delCookie('pcode');
        window.location.href = data.redirect_url;
        return;
      },
      function(data)
      { // FAIL
        GAME.leaveGame();
      });
  }

  /**
   * this function stops the pull request loop
   * @return {void}
   */
  this.stopPull = function()
  {
    clearInterval(this.interval_handler);
  }

  /**
   * this funciton sends a vote request to the server
   * @param  {int} type the type of vote
   * @param  {int} pid  the player id to vote against
   * @return {void}
   */
  this.vote = function(type, pid)
  {
    //console.log("vote: " + type + " " + pid);
    webhookCall("webhook.php",
      {request: 'vote', 'type':type, 'vote_id':pid},
      function(data)
      {
        //SUCCESS
      },
      function(data)
      {
        //FAIL
        if (data.error)
        {
          alert(data.error);
        }
      });
  }


  /**
   * kicks a player from the game
   * only works if playeer is admin
   * @param  {int} id player id
   * @return {void}
   */
  this.kickPlayer = function(id)
  {
    webhookCall("webhook.php",
      {request: 'kick', 'player_id': id},
      function(data)
      {
        //SUCCESS
      },
      function(data)
      {
        //FAIL
        if (data.error)
        {
          alert(data.error);
        }
      });
  }


  /**
   * saves settings to the game
   * only works if player is admin
   * @param  {int} spione  spione ratio
   * @param  {int} aerzte  aerzte ratio
   * @param  {moerder} moerder moerder ratio
   * @return {void}
   */
  this.saveSettings = function(spione, aerzte, moerder, timer)
  {
    webhookCall("webhook.php",
      {request: 'savesettings', 'spione': spione, 'aerzte': aerzte, 'moerder': moerder, 'timer': timer},
      function(data)
      {
        //SUCCESS
      },
      function(data)
      {
        //FAIL
        if (data.error)
        {
          alert(data.error);
        }
      });
  }


  /**
   * starts the game
   * @return {void}
   */
  this.startGame = function()
  {
    $('#dashb-game-start-button').addClass('disabled');

    webhookCall("webhook.php",
      {request: 'startgame'},
      function(data)
      {
        //SUCCESS
      },
      function(data)
      {
        $('#dashb-game-start-button').removeClass('disabled');

        //FAIL
        if (data.error)
        {
          alert(data.error);
        }
      });
  }


  this.sendName = function(name)
  {
    webhookCall("webhook.php",
      {
        'request': 'sendname',
        'name': name
      },
      function(data)
      {
        //SUCCESS
      },
      function(data)
      {

        //FAIL
        if (data.error)
        {
          alert(data.error);
        }
      });
  }


  this.askForName = function()
  {
    var presetName = getCookie("pname");
    if (!GAME.nameRegex.test(presetName))
    {
      presetName = "";
    }

    msgbox_visible = true;

    var html = "<p>Bitte gib hier deinen Spielernamen ein:</p>";
    html += '<input class="form-control" type="text" id="gameInputPlayerName">';

    $('#messageBox .modal-title').text("Spielername");
    $('#messageBox .modal-body').html(html);
    $('#messageBox .modal-footer .btn-primary').html("Speichern");
    $('#messageBox .modal-footer .btn-secondary').addClass("d-none");
    $('#messageBox .close').addClass("d-none");
    $('#messageBox').modal({
      'backdrop':'static',
      'keyboard': false
    });

    $('#gameInputPlayerName').val(presetName);

    msgbox_callback = function()
    {
      var pname = $('#gameInputPlayerName').val();

      if (!GAME.nameRegex.test(pname))
      {
        $('#gameInputPlayerName').val("");
        alert("Der Spielername ist ungültig! Es sind nur alphanumerische Zeichen und Leerzeichen erlaubt! Zusätzlich dürfen nur zwei bis 20 Zeichen verwendet werden.");
        return;
      }


      setCookie("pname", pname, 3600*24*30*12);
      $('#messageBox').modal("hide");
      GAME.sendName(pname);
      msgbox_visible = false;
    }
  }


  this.parsePublicMessages = function(msg)
  {
    //only get last msg
    var id = msg.length-1;
    var message = msg[id];


    //cookie id check
    var msg_id = parseInt(message.id);
    var cookie_id = parseInt(getCookie('pub_msg_id'));
    if (msg_id <=  cookie_id) return;
    setCookie("pub_msg_id", msg_id, 3600*24*30*12);



    if (message.type == "endround")
    {
      this.votes = [];
      this.role_votes = [];

      resetOwnVote();
      resetOwnSpeziVote();

      dashb_own_vote = -1;
      dashb_own_vote_spezi = -1;

      dashbDrawVotesCounter();
      dashbDrawRoleVotesCounter();

      this.displayVoteNotification();

      var html = this.generateRoundEndMessage(JSON.parse(message.message));
      this.displayPublicMessage("Ende der Runde", html);
    }
    else {
      var msg = JSON.parse(message.message);
      this.displayPublicMessage(msg.title, msg.body);
    }
  }


  this.generateRoundEndMessage = function(data)
  {
    var html = "";


    // BUERGER

    html += "Der Tag neigt sich dem Ende zu...<br>";
    html += 'Am Ende des Tages haben die Bürger folgende Person erhängt:<br>';

    var pname_buerger = "Unbekannt";

    if (data.killedby_buerger > 0)
    {
      //search for player

      for (var i = 0; i < this.player.length; i++)
      {
        if (this.player[i].id == data.killedby_buerger)
        {
          if (this.player[i].name.length > 0)
          {
            pname_buerger = this.player[i].name;
          }
          break;
        }
      }

      html += "<b>" + pname_buerger + "</b>";


    } else {
      html += "keine";
    }


    // MOERDER
    html += "<br><br><br>";
    html += "Der nächste Tag bricht an!";

    if (data.killedby_moerder != 0) {
      html += " Die Bürger finden folgende Person tot vor:<br>";
    }

    if (data.killedby_moerder > 0)
    {
      //search for player
      var pname = "Unbekannt";
      for (var i = 0; i < this.player.length; i++)
      {
        if (this.player[i].id == data.killedby_moerder)
        {
          if (this.player[i].name.length > 0)
          {
            pname = this.player[i].name;
          }
          break;
        }
      }

      html += "<b>" + pname + "</b>";


    } else if (data.killedby_moerder == -2)
    {
      html += "keine";
      html += "<p class='text-info'>Anscheinend haben die Ärzte eine Person gerettet!</p>";
    }
    else if (data.killedby_moerder == -3)
    {
      html += "keine";
      html += "<p class='text-info'>Anscheinend wollten die Mörder ebenfalls <b>" + pname_buerger + "</b> töten.!</p>";
    }


    // GAMEOVER
    if (data.gameover == true)
    {
      html += "<br><br><br>";
      html += "<b class='text-success'>Damit ist das Spiel vorbei!</b><br>";
      html += "Gewonnen haben die: ";

      if (data.gameover_var == 1)
      {
        html += "<b>Bürger</b>!";
      } else {
        html += "<b>Mörder</b>!";
      }
    }


    return html;
  }



  this.displayPublicMessage = function(title, message)
  {
    if (msgbox_visible) return;

    $('#messageBox .modal-title').text(title);
    $('#messageBox .modal-body').html(message);
    $('#messageBox .modal-footer .btn-primary').html("Schließen");
    $('#messageBox .modal-footer .btn-secondary').addClass("d-none");
    $('#messageBox .close').removeClass("d-none");
    $('#messageBox').modal();

    msgbox_callback = function()
    {
      $('#messageBox').modal("hide");
    }
  }


  this.resetAllPlayer = function()
  {
    for(var i = 0; i < GAME.player.length; i++)
    {
      GAME.player[i].role = null;
    }
  }
}



$(document).ready(function()
{

  //start game js
  GAME = new Game();
  GAME.startup();

  initTimer();
});
