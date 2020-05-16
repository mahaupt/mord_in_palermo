
// OPEN / CLOSE MMENU, Handle Hamburger
$(document).ready(function( $ ) {
  $("#menu").mmenu();

  var API = $("#menu").data( "mmenu" );

  $("#open-menu-hamburger").click(function() {
    API.open();
  });


  API.bind( "open:start", function() {
    setTimeout(function() {
      $("#open-menu-hamburger").addClass("hamburger-change");
    }, 100);
  });
  API.bind( "close:start", function() {
    setTimeout(function() {
      $("#open-menu-hamburger").removeClass("hamburger-change");
    }, 100);
  });

});


var timeoutFunctionInterval;
function timeoutFunction()
{
  if (getCookie('pcode'))
  {
    window.location.href = "./game.php";
    //clearInterval(timeoutFunctionInterval);
  }
}



function inputOnChange()
{
  if ($('#input-gamecode').val().length > 0)
  {
    $('#button-gamecode').text("Spiel beitreten");
    $('#button-gamecode').attr("href", "javascript:onEnterClick()");
  }
  else
  {
    $('#button-gamecode').text("Neues Spiel");
    $('#button-gamecode').attr("href", "javascript:onNewGameClick()");
  }
}



function onEnterClick()
{
  //disable button
  $('#button-gamecode').addClass('disabled');


  webhookCall("./webhook.php",
    {request: 'joingame', code: $("#input-gamecode").val()},
    function(jsond)
    { //SUCCESS
      setCookie("pcode", jsond.pcode, 7);
      //window.location.href = "./game.php";
    },
    function (jsond)
    { //FAIL
      $('#button-gamecode').removeClass('disabled');
      if (jsond.error) {
        alert(jsond.error);
      }
    }
  );


}



function onNewGameClick()
{
  //disable button
  $('#button-gamecode').addClass('disabled');

  webhookCall("./webhook.php",
    {request: 'newgame'},
    function(jsond)
    { //SUCCESS
      setCookie("pcode", jsond.pcode, 7);
      window.location.href = "./game.php";
    },
    function (jsond)
    { //FAIL
      $('#button-gamecode').removeClass('disabled');
      if (jsond.error) {
        alert(jsond.error);
      }
    }
  );
}
