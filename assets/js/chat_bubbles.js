$(document).ready(function( $ ) {

$("#chat-window #chat-send-button").click(submitChatInputField);

$("#chat-window #menu-icon-left").click(function() {

  var chatID = $("#chat-send-button").attr('data-cid');

  for (var i = 0; i < GAME.chats.length; i++)
  {
    if (GAME.chats[i].id != chatID) {
      continue;
    }
    GAME.chats[i].last_time_checked = GAME.lastupdate + 1;
  }
  renderChatButtonWindow(GAME.chats);
  dashbRenderMessagePill();
  drawPlayerMessageCounter();
});
});

function submitChatInputField()
{

    var text_me = $("#chat-window #chat-input-field").val();
      if (text_me < 1) {return;}

    var chatID = $("#chat-send-button").attr('data-cid');

    webhookCall("./webhook.php",
      {request: 'sendMessage', newMessage: text_me, chat_ID: chatID},
      function(data) {},
      function(data) {
        alert(data.error);
      });

  $("#chat-window #chat-input-field").val([]);


}

function pickThisChatWindow(data)
{
  var chatID = data.dataset.cid;
  var chatTitle = data.dataset.title;
  //add this ChatID to html Chatwindow
  $("#chat-send-button").attr("data-cid", chatID);
  //now render the chatTitle
  var titleHtml = '<h1>'+ chatTitle +'</h1>';
  $("#chatTitlePageName").html(titleHtml);

  renderChatWindowPack(GAME.chats);
  setTimeout(scrollToBottom, 50);
}

function renderChatWindowPack(chats)
{

  var bubbleString = "";
  var chatID = $("#chat-send-button").attr('data-cid');

  //console.log(message_num);

  if (chatID == 0){
    return;}

  var messageString = [];

  for (var x = 0; x < chats.length; x++)
  {
    if (chats[x].id == chatID)
    {
      messageString = chats[x].messages;
      break;
    }

  }

  bubbleString += renderMessages(messageString);

  $("#chat-text-container").html(bubbleString);
}

function renderMessages(messageString)
{
 //console.log(messageString.length);
  var bubbleString = "";

  for (i = 0; i < messageString.length; i++)
  {
    newBubble = renderChatbubble(messageString[i]);
    bubbleString += newBubble;
  }
  return bubbleString;
}

function renderChatbubble(singleMessageData)
{
  var chatBubble = "";
  var message = singleMessageData.message;
  var sender_id = singleMessageData.sender;

  var date = new Date(singleMessageData.time * 1000);
  var hours = date.getHours();
  var minutes_pure = date.getMinutes();

  var minutes = minutes_pure;
  if (minutes_pure < 10)
  {
    var minutes = "0" + minutes_pure.toString();
  }

  var time = hours +':'+ minutes;

  if (sender_id == GAME.player_self.id)
  {
      chatBubble += '<div class="chat-container-bubble-r">';
      chatBubble +=   '<div class="chat-bubble chat-bubble-blue">';
      chatBubble +=     '<span class="chat-bubble-text">'+ message +'</span>';
      chatBubble +=     '<span class="chat-bubble-time">'+ time +'</span>';
      chatBubble +=   '</div>';
      chatBubble += '</div>';
  }
  else
  {
    var senderName = "Unbekannt";
    var senderColor = "#ffffff";

    for (var i = 0; i < GAME.player.length; i++)
    {
      if (GAME.player[i].id == sender_id) {
        senderName = GAME.player[i].name;
        senderColor = GAME.player[i].color;
        break;
      }
    }

    chatBubble += '<div class="chat-container-bubble-l">';
    chatBubble +=   '<div class="chat-bubble chat-bubble-grey">';
    chatBubble +=     '<span class="chat-bubble-sender" style="color: ' + senderColor + '">'+ senderName +'</span>';
    chatBubble +=     '<span class="chat-bubble-text">'+ message +'</span>';
    chatBubble +=     '<span class="chat-bubble-time">'+ time +'</span>';
    chatBubble +=   '</div>';
    chatBubble += '</div>';
  }
  return chatBubble;
}


function scrollToBottom()
{
  $('#chat-text-container').scrollTop(1E10);
}
