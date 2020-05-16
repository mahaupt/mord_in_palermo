var dashb_own_vote = -1;
var dashb_own_vote_spezi = -1;

/**
 * this function handles the notmal vote
 * @param  {object} self the clicked html object
 * @return {void}
 */
function dashbClickNorm(self)
{
  var pid = self.dataset.pid;
  dashb_own_vote = pid;

  drawOwnVote();

  GAME.vote(0, pid);
}

/**
 * this function handles the special vote
 * @param  {object} self the clicked html object
 * @return {void}
 */
function dashbClickSpeci(self) {
  var pid = self.dataset.pid;
  dashb_own_vote_spezi = pid;

  drawOwnVoteSpezi();

  GAME.vote(GAME.player_self.role, pid);
}

/**
 * function to be called if player clicks on an open chat icon
 * opens a chat with a specific player
 * @param  {object} self the clicked html object
 * @return {void}
 */
function dashbOpenChat(self) {
  var pid = self.dataset.pid;

  createPrivateChatFromDashboard(pid);
}


var roleClick_active = false;
function roleClick()
{
  if (roleClick_active) return;
  roleClick_active = true;

  setTimeout(roleTimerTrigger, 5000);

  var player_role = GAME.player_self.role;
  var player_role_text = "Unbekannt";
  var player_role_icon = "<i class=\"fas fa-question\"></i>";

  switch(player_role) {
    case 0:
      player_role_text = "Bürger";
      player_role_icon = "<i class=\"fas fa-user\"></i>";
      break;
    case 1:
      player_role_text = "Spion";
      player_role_icon = "<i class=\"fas fa-eye\"></i>";
      break;
    case 2:
      player_role_text = "Mörder";
      player_role_icon = "<i class=\"fas fa-skull\"></i>";
      break;
    case 3:
      player_role_text = "Arzt";
      player_role_icon = "<i class=\"fas fa-plus\"></i>";
      break;

  }

  $('.dashb-poll-speci').removeClass('d-none');
  $('#dashb-role-button').removeClass('text-muted');
  $('#dashb-role-button').html(player_role_icon + ' ' + player_role_text);

  //SPION icons + MOERDER SIEHT SEINEN KUMPEL
  if (player_role == 1 || player_role == 2)
  {
    //$('.dashb-poll-role').show();

    for(var i=0; i < GAME.player.length; i++)
    {
      if (GAME.player[i].role != null)
      {
        $('.dashb-poll-role[data-pid=' + GAME.player[i].id + '] .fa-circle').attr('data-prefix', 'far');
        $('.dashb-poll-role[data-pid=' + GAME.player[i].id + '] .dashb-poll-role-icon').removeClass("fa-question");
        $('.dashb-poll-role[data-pid=' + GAME.player[i].id + '] .dashb-poll-role-icon').removeClass("fa-inverse");

        switch (parseInt(GAME.player[i].role))
        {
          case 0:
            $('.dashb-poll-role[data-pid=' + GAME.player[i].id + '] .dashb-poll-role-icon').addClass("fa-user");
            break;
          case 1:
            $('.dashb-poll-role[data-pid=' + GAME.player[i].id + '] .dashb-poll-role-icon').addClass("fa-eye");
            break;
          case 2:
            $('.dashb-poll-role[data-pid=' + GAME.player[i].id + '] .dashb-poll-role-icon').addClass("fa-skull");
            break;
          case 3:
            $('.dashb-poll-role[data-pid=' + GAME.player[i].id + '] .dashb-poll-role-icon').addClass("fa-plus");
            break;
        }
      }
    }


  }
}

function roleTimerTrigger() {
  $('.dashb-poll-speci').addClass('d-none');

  $('#dashb-role-button').addClass('text-muted');
  $('#dashb-role-button').html("<i class=\"far fa-eye\"></i> Rolle");

  //spion buttons
  $('.dashb-poll-role .fa-circle').attr('data-prefix', 'fas');
  $('.dashb-poll-role .dashb-poll-role-icon').addClass("fa-question");
  $('.dashb-poll-role .dashb-poll-role-icon').addClass("fa-inverse");
  $('.dashb-poll-role .dashb-poll-role-icon').removeClass("fa-skull");
  $('.dashb-poll-role .dashb-poll-role-icon').removeClass("fa-plus");
  $('.dashb-poll-role .dashb-poll-role-icon').removeClass("fa-eye");
  $('.dashb-poll-role .dashb-poll-role-icon').removeClass("fa-user");
  /*$('.dashb-poll-role').hide();
  $('.dashb-poll-role').parent().hide();
  $('.dashb-poll-role').parent().show();*/

  roleClick_active = false;
}



function dashbBuildPlayerList(plist)
{
  //if dead, display dead info
  if (GAME.player_self.alive == 0)
  {
    $('#dashb-game-dead-info').removeClass('d-none');
  } else {
    $('#dashb-game-dead-info').addClass('d-none');
  }


  $('#dasb-playerlist').html('<li class="Divider">Mitspieler</li>');

  for(var i=0; i < plist.length; i++)
  {
    var player = plist[i];
    var playername = player.name;
    if (playername.length == 0) {
      playername = "Unbekannt";
    }

    //skip self
    if (player.id == GAME.player_self.id) {
      continue;
    }

    var text = '';

    //draw alive / dark player
    if (player.alive == 1)
    {
      text += '<li>';
    }
    else {
      text += '<li style="background-color: #b0b0b0; color: #606060;">';
    }

    text += '<span>';
    text += '<span class="dashb-poll dashb-poll-role fa-layers fa-fw fa-2x" data-pid="' + player.id + '">';
    text += '<i class="fas fa-circle" style="color:grey"></i>';
    text += '<i class="fa-inverse dashb-poll-role-icon fa-question fas" data-fa-transform="shrink-6"></i>';
    text += '</span>';

    //player name
    text += '<span class="list-player-name">';
    if (player.alive == 0)
    {
      text += "&#x271D; ";
    }
    text += playername;

    text += '</span>';

    text += '<span style="float: right;">';


    //if player alive and self alive
    if (player.alive == 1 && GAME.player_self.alive == 1)
    {
      //abstimmung spezi
      if (GAME.player_self.role >= 1 && GAME.player_self.role <= 3)
      {
        text += '<span class="dashb-poll dashb-poll-speci fa-layers fa-fw fa-2x d-none" data-pid="' + player.id + '" onclick="dashbClickSpeci(this)">';

        if (GAME.player_self.role == 3)
        { // ARZT
          text += '    <i class="far fa-circle" style="color:red"></i>';
          text += '    <i class="fa-plus fas" data-fa-transform="shrink-6" style="color: red;"></i>';
        }
        else if (GAME.player_self.role == 2) {
          // MOERDER
          text += '    <i class="far fa-circle" style="color:red"></i>';
          text += '    <i class="fa-skull fas" data-fa-transform="shrink-6" style="color: red;"></i>';
        }
        else {
          // SPION
          text += '    <i class="far fa-circle" style="color:red"></i>';
          text += '    <i class="fa-eye fas" data-fa-transform="shrink-6" style="color: red;"></i>';
        }

        //counter
        text += '    <span class="fa-layers-counter d-none" style="background:Tomato"></span>';
        text += '</span>';
      }


      //abstrimmung alle
      text += '<span class="dashb-poll dashb-poll-norm fa-layers fa-fw fa-2x" data-pid="' + player.id + '" onclick="dashbClickNorm(this)">';

      text += '    <i class="far fa-circle"></i>';
      text += '    <i class="fa-crosshairs fas" data-fa-transform="shrink-6"></i>';

      //counter
      text += '    <span class="fa-layers-counter d-none" style="background:Tomato"></span>';

      text += '</span>';


      //message button
      text += '<span class="dashb-poll dashb-poll-msg fa-layers fa-fw fa-2x" data-pid="' + player.id + '" onclick="dashbOpenChat(this)">';
      text += '  <i class="fas fa-circle" style="color:grey"></i>';
      text += '  <i class="fa-inverse fa-envelope fa" data-fa-transform="shrink-6"></i>';
      text += '  <span class="fa-layers-counter d-none" style="background:Tomato"></span>';
      text += '</span>';

    } // if alive

    text += '</span>';
    text += '</span>';
    text += '</li>';

    $('#dasb-playerlist').append(text);
  }
}


function dashbDrawVotesCounter()
{
  //loop player
  for (var i = 0; i < GAME.player.length; i++)
  {
    //skip self
    if (GAME.player[i].id == GAME.player_self.id)
      continue;

    //count player votes
    var votes = 0;
    for (var j = 0; j < GAME.votes.length; j++)
    {
      //skip own votes
      if (GAME.votes[j].player_id == GAME.player_self.id)
      {
        dashb_own_vote = GAME.votes[j].vote_id;
        continue;
      }


      if (GAME.votes[j].vote_id == GAME.player[i].id)
      {
        votes++;
      }
    }

    if (votes == 0)
    {
      $('#dasb-playerlist .dashb-poll-norm[data-pid=' + GAME.player[i].id + '] .fa-layers-counter').addClass('d-none');
    } else {
      $('#dasb-playerlist .dashb-poll-norm[data-pid=' + GAME.player[i].id + '] .fa-layers-counter').removeClass('d-none');
      $('#dasb-playerlist .dashb-poll-norm[data-pid=' + GAME.player[i].id + '] .fa-layers-counter').text(votes);
    }


  }

  //draw own vote
  if (dashb_own_vote > 0)
  {
    setTimeout(drawOwnVote, 100);
  }

  renderSelfTargetInfo();
}



function renderSelfTargetInfo()
{
  var targeted_self = [];

  //self target info
  for (var i=0; i < GAME.votes.length; i++)
  {
    if (GAME.votes[i].vote_id == GAME.player_self.id)
    {

      //get player name
      for (var j=0; j < GAME.player.length; j++)
      {
        if (GAME.player[j].id == GAME.votes[i].player_id)
        {
          var pname = GAME.player[j].name;
          if (pname.length <= 0)
          {
            pname = "Unbekannt";
          }

          targeted_self.push(pname);
        }
      }
    }
  }



  if (targeted_self.length > 0) {
    var html = "<b>Warnung:</b> ";

    for (var i = 0; i < targeted_self.length; i++)
    {
        if (i != 0)
        {
          if (i == targeted_self.length-1)
          {
            html += " und ";
          } else {
            html += ", ";
          }
        }
        html += targeted_self[i];
    }

    if (targeted_self.length == 1)
    {
      html += " hat";
    }
    else
    {
      html += " haben";
    }

    html += " gegen dich abgestimmt!";

    $('#dashb-game-target-info').html(html);
    $('#dashb-game-target-info').removeClass('d-none');
  } else {
    $('#dashb-game-target-info').addClass('d-none');
  }
}



function dashbDrawRoleVotesCounter()
{

  //loop player
  for (var i = 0; i < GAME.player.length; i++)
  {
    //skip self
    if (GAME.player[i].id == GAME.player_self.id)
      continue;

    //count player votes
    var votes = 0;
    for (var j = 0; j < GAME.role_votes.length; j++)
    {
      //skip own votes
      if (GAME.role_votes[j].player_id == GAME.player_self.id)
      {
        dashb_own_vote_spezi = GAME.role_votes[j].vote_id;
        continue;
      }


      if (GAME.role_votes[j].vote_id == GAME.player[i].id)
      {
        votes++;
      }
    }

    if (votes == 0)
    {
      $('#dasb-playerlist .dashb-poll-speci[data-pid=' + GAME.player[i].id + '] .fa-layers-counter').addClass('d-none');
    } else {
      $('#dasb-playerlist .dashb-poll-speci[data-pid=' + GAME.player[i].id + '] .fa-layers-counter').removeClass('d-none');
      $('#dasb-playerlist .dashb-poll-speci[data-pid=' + GAME.player[i].id + '] .fa-layers-counter').text(votes);
    }


  }

  //draw own vote
  if (dashb_own_vote > 0)
  {
    setTimeout(drawOwnVoteSpezi, 100);
  }
}


function drawPlayerMessageCounter()
{
  for(var i = 0; i < GAME.player.length; i++)
  {
    var pid = GAME.player[i].id;

    if (pid == GAME.player_self.id)
      continue;

    var counter = getPillNumForThisPlayer(pid);

    if (counter > 0)
    {
      $('#dasb-playerlist .dashb-poll-msg[data-pid=' + pid + '] .fa-layers-counter').text(counter.toString());
      $('#dasb-playerlist .dashb-poll-msg[data-pid=' + pid + '] .fa-layers-counter').removeClass("d-none");
    } else {
      $('#dasb-playerlist .dashb-poll-msg[data-pid=' + pid + '] .fa-layers-counter').text('');
      $('#dasb-playerlist .dashb-poll-msg[data-pid=' + pid + '] .fa-layers-counter').addClass("d-none");
    }
  }
}


function dashbRenderMessagePill()
{
  var counter = totalPillNumCounter();

  if (counter > 0)
  {
    $('#dashboard #menu-icon-right .fa-layers-counter').removeClass("d-none");
    $('#dashboard #menu-icon-right .fa-layers-counter').text(counter.toString());
  } else {
    $('#dashboard #menu-icon-right .fa-layers-counter').addClass("d-none");
  }
}


function drawOwnVote()
{
  resetOwnVote();

  $('#dashboard .dashb-poll-norm[data-pid=' + dashb_own_vote + '] .fa-crosshairs').addClass('fa-inverse');
  $('#dashboard .dashb-poll-norm[data-pid=' + dashb_own_vote + '] .fa-circle').attr('data-prefix', 'fas');
}

function drawOwnVoteSpezi()
{
  resetOwnSpeziVote();

  $('#dashboard .dashb-poll-speci[data-pid=' + dashb_own_vote_spezi + '] .fa-plus').css('color','white');
  $('#dashboard .dashb-poll-speci[data-pid=' + dashb_own_vote_spezi + '] .fa-skull').css('color','white');
  $('#dashboard .dashb-poll-speci[data-pid=' + dashb_own_vote_spezi + '] .fa-eye').css('color','white');
  $('#dashboard .dashb-poll-speci[data-pid=' + dashb_own_vote_spezi + '] .fa-circle').attr('data-prefix', 'fas');
}

function resetOwnVote()
{
  $('#dashboard .dashb-poll-norm .fa-crosshairs.fa-inverse').removeClass('fa-inverse');
  $('#dashboard .dashb-poll-norm .fa-circle[data-prefix=fas]').attr('data-prefix', 'far');
}

function resetOwnSpeziVote()
{
  $('#dashboard .dashb-poll-speci .fa-plus').css('color','red');
  $('#dashboard .dashb-poll-speci .fa-skull').css('color','red');
  $('#dashboard .dashb-poll-speci .fa-eye').css('color','red');
  $('#dashboard .dashb-poll-speci .fa-circle[data-prefix=fas]').attr('data-prefix', 'far');
}
