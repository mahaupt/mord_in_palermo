$(document).ready(function() {

$("#messages #menu-icon-right").click(function() {

  /**
   * Code the Inside of the messageBox with html for selecting a player to chat with
   * Each player namefield is being created in a for-loop which attaches the player-id
   * to the corresponding button.
   * @type {String}
   */
  var spielerliste = '<div class="input-group mb-3">';
  spielerliste += '<input id="chatNameInputForChat" type="text" class="form-control" placeholder="Wie soll der Chat heißen?" aria-label="Username" aria-describedby="basic-addon1">';
  spielerliste += '</div>';
  spielerliste += '<ul class="list-group">';
  for (var i = 0; i < GAME.player.length; i++)
  {
    //console.log(i);
    var pname = 'Unbekannt';
    if (GAME.player[i].name.length > 0)
    {
      pname = GAME.player[i].name;
    }
    if (GAME.player[i].id == GAME.player_self.id)
    {
      continue;
    }
    spielerliste += '<li data-pid="' + GAME.player[i].id + '" class="list-group-item" onclick="clickOnNameForChat(this)">' + pname + '</li>';
  }
  spielerliste += '</ul>';

  /**
   * Manipulate messageBox for player selection usage
   */
  $("#messageBox").modal();
  $("#messageBox .modal-title").html("Chatte mit einem Mitspieler");
  $("#messageBox .modal-footer .btn.btn-primary").html("Chat erstellen");
  $("#messageBox .modal-footer .btn.btn-secondary").html("Zurück");
  $("#messageBox .modal-body").html(spielerliste);

/**
 * When opening the messageBox in the messenger this msgbox_callback function
 * will be activated. By pressing the 'Chat erstellen'-button this function will
 * create an array containing all player id's attached to the activated buttons
 * and send it to the server.
 * @return {array} player_array
 */
msgbox_callback = function()
{
  var player_array = [];
  var chatTitleInput = $("#messageBox #chatNameInputForChat").val();
    if (chatTitleInput < 1) {
      chatTitleInput = 'Privater Chat';
      }

  $("#messageBox .modal-body .list-group .active").each(function()
   {
     var pid = $(this).attr('data-pid');
     player_array.push(pid);
     console.log(pid);
    });

    //console.log(player_array);
  /**
   * Request to server for creating a chat with chosen players and chosen chatTitle
   */
  webhookCall("./webhook.php",
    {request: 'createPrivateChat', player_ids: player_array, chatTitle: chatTitleInput},
    function(data) {},
    function(data) {
      alert(data.error);
    });

  $("#messageBox").modal('hide'); //Close msgBox after creating the chat

} //Closes the msgbox_callback function

}); //Closes the PlusOnClick function

}); //Closes the document.ready function

/**
 * This function toggles the playername button.
 * @param  {[type]} data [description]
 * @return {[type]}      [description]
 */
function clickOnNameForChat(data)
{
  var pid = data.dataset.pid;
  //console.log(pid);
  $("#messageBox .modal-body .list-group .list-group-item[data-pid=" + pid + "]").toggleClass("active");
}

function renderChatButtonWindow(chatData)
{
  //console.log(chatData);
  var newChatButtons = "";
  var chat_num = chatData.length;
  //var modified_at = chatData.modified_at;

  //console.log(chatData.messages.time);
  var chatDataSort = chatData.sort(function (a, b) {
    if (a.last_message_time > b.last_message_time) {
      return -1;
    }
    if (a.last_message_time < b.last_message_time) {
      return 1;
    }
    // a muss gleich b sein
    return 0;
  });

  //console.log(GAME.player[0].name);
  //console.log(chatData[0].chat_member);
  for (var i = 0; i < chat_num; i++)
  {
    var chatMember_string = createChatMemberList(chatDataSort[i].chat_member);
    var chatID = chatDataSort[i].id;
    var chatTitle = chatDataSort[i].name;
    var badgePill_num = pillNumCounter(chatData[i]);

    newChatButtons += renderChatButton(chatTitle, chatID, chatMember_string, badgePill_num);

  }

  $("#chat-group-container").html(newChatButtons);

}

function renderChatButton(chatTitle, chatID, chatMember_string, badgePill_num)
{

  if (badgePill_num > 0){
    var pillBadge = '<h5 class="badge badge-primary badge-pill">' + badgePill_num + '</h5> </div>';
  }
  else {
    var pillBadge = '</div>';
  }

  var chatButton = '<a href=#chat-window onclick="pickThisChatWindow(this)" data-title ="'+ chatTitle +'" data-cid="'+ chatID +'" class="list-group-item list-group-item-action flex-column align-items-start">';
  chatButton += '<div class="d-flex w-100 justify-content-between">';
  chatButton += '<h5 class="mb-1">' + chatTitle + '</h5>';
  chatButton += pillBadge;
  chatButton += '<p class="mb-1">' + chatMember_string + '</p>';
  chatButton += '</a>';

  return chatButton;
}

function createChatMemberList(chat_member)
{
  chatMember_string = [];

  for (var i = 0; i < GAME.player.length; i++)
  {
    for (var x = 0; x < chat_member.length; x++)
    {
      if (GAME.player[i].id != chat_member[x] || GAME.player[i].id == GAME.player_self.id){
      continue;
      }
      chatMember_string.push(' '+ GAME.player[i].name +'');
    }
  }
  return chatMember_string;
}

function totalPillNumCounter()
{
  var totalPillNum = 0;

  for (var i = 0; i < GAME.chats.length; i++)
  {
    for (var x = 0; x < GAME.chats[i].messages.length; x++)
    {
      if (GAME.chats[i].last_time_checked > GAME.chats[i].messages[x].time){
        continue;
      }
      totalPillNum = totalPillNum + 1;
    }
  }//for

  return totalPillNum;
}

function pillNumCounter(chatData)
{
  var pillNum = 0;

  for (var i = 0; i < chatData.messages.length; i++)
  {
    if (chatData.last_time_checked > chatData.messages[i].time){
      continue;
    }
    pillNum = pillNum + 1;
  }

  return pillNum;
}

function getPillNumForThisPlayer(player_id)
{
  var pillNum = 0;
  // check each chat with 2 members if player_id is in the chat
  for (var i = 0; i < GAME.chats.length; i++)
  {
    if (GAME.chats[i].chat_member.length > 2){
      continue;
    }
    // check if desired player is in the private chat
    for (var y = 0; y < GAME.chats[i].chat_member.length; y++)
    {
      if (GAME.chats[i].chat_member[y] != player_id){
        continue;
      }
      //when chat is found, count how many messages are new
      for (var x = 0; x < GAME.chats[i].messages.length; x++)
      {
        if (GAME.chats[i].last_time_checked > GAME.chats[i].messages[x].time){
          continue;
        }
        pillNum = pillNum + 1;
      }//for x
      //console.log(pillNum)
    } //for y
  }//for i
  return pillNum;
}

var joinNextCreatedChat = false;

function createPrivateChatFromDashboard(player_id)
{
  var exists = false;
  var chat_id;
  var chat_title;
  var run_again_counter = 0;

  for (var i = 0; i < GAME.chats.length; i++)
  {
    if (GAME.chats[i].chat_member.length > 2){
      continue;
      }

    for (var x = 0; x < GAME.chats[i].chat_member.length; x++)
    {
      if (player_id == GAME.chats[i].chat_member[x]) {
        exists = true;
        chat_id = GAME.chats[i].id;
        chat_title = GAME.chats[i].name;

        break;
      }
    }
    if (exists){
      break;
    }
  }//for

  if (exists )
  {
    var data = {dataset: {cid: chat_id, title: chat_title}};
    pickThisChatWindow(data);
    var api = $("#menu").data( "mmenu" );
    api.openPanel( $("#chat-window") );
  }
  else
  {
    webhookCall("./webhook.php",
      {request: 'createPrivateChat', player_ids: [player_id], chatTitle: 'Privater Chat'},
      function(data) {},
      function(data) {
        alert(data.error);
      });

      joinNextCreatedChat = true;

      var api = $("#menu").data( "mmenu" );
      api.openPanel( $("#messages") );


  }
}

function deleteOldWulfChat()
{
  for(var i=0; i < GAME.chats.length; i++)
  {
    if (GAME.chats[i].type == 2)
    {
      GAME.chats.splice(i, 1);
      break;
    }
  }
}
