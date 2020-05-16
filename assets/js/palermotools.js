function webhookCall(url, senddata, success, failure)
{

  //ajax request
  var ajaxh = $.post(url, senddata)
  .done(function (data)
  {

    if (data.status == 'success') {
      success(data);
    } else {
      failure(data);
    }


  })
  .fail(function (data)
  {
    alert("Verbindungsfehler! Bitte versuche es später erneut!");
    console.log(data);
    failure();
  });
}


function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+ d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

function delCookie(cname) {
  if (getCookie(cname)) {
    setCookie(cname, "", 0);
  }
}



var tools_timer;
function initTimer()
{
  if (tools_timer != null)
  {
    clearInterval(tools_timer);
  }

  tools_timer = setInterval(timerTimeout, 1000);
}

function timerTimeout()
{
  $('.jsTimer').each(function () {
    var val = $(this).text();
    var time = textToTime(val);


    //count up or down
    if ($(this).hasClass('jsTimerUp'))
    {
      time++;
    } else {
      time--;
    }

    //overflow
    if (time < 0)
    {
      time = 0;
    }

    var text = timeToText(time);


    $(this).text(text);

  });
}


function textToTime(text)
{
  var split = text.split(":");


  var seconds = 0;
  var minutes = 0;
  var hours = 0;

  // parse text
  if (split.length > 0)
  {
    seconds = split[split.length-1];
    if (seconds == "") seconds = 0;
    seconds = parseInt(seconds);

    if (split.length > 1)
    {
      minutes = split[split.length-2];
      if (minutes == "") minutes = 0;
      minutes = parseInt(minutes);
    }

    if (split.length > 2)
    {
      hours = split[split.length-3];
      if (hours == "") hours = 0;
      hours = parseInt(hours);
    }
  }

  return seconds + minutes*60 + hours*60*60;
}


function timeToText(time)
{
  var hours = Math.floor(time/60/60);
  time -= hours*60*60;
  var minutes = Math.floor(time/60);
  time -= minutes*60;
  var seconds = time;


  //convert back to text
  var text = "";

  if (hours > 0)
  {
    var hourtext = hours.toString();
    if (hourtext.length <= 1) {
      hourtext = "0" + hourtext;
    }

    text += hourtext + ":";
  }
  if (minutes > 0 || hours > 0)
  {
    var minutetext = minutes.toString();
    if (minutetext.length <= 1) {
      minutetext = "0" + minutetext;
    }

    text += minutetext;
  }

  var secondstext = seconds.toString();
  if (secondstext.length <= 1) {
    secondstext = "0" + secondstext;
  }

  text += ":" + secondstext;

  return text;

}



function getRandColor(brightness)
{

  var R = clip(Math.round(Math.random()*205 + 50), 0, 255);
  var G = clip(Math.round(Math.random()*205 + 50), 0, 255);
  var B = clip(Math.round(Math.random()*205 + 50), 0, 255);

  var b1 = Math.sqrt(R*R + G*G + B*B);
  var h = brightness / b1;
  R *= h;
  G *= h;
  B *= h;

  R = Math.round(R);
  G = Math.round(G);
  B = Math.round(B);

  R = clip(R, 0, 255);
  G = clip(G, 0, 255);
  B = clip(B, 0, 255);

  //console.log(R);
  //console.log(G);
  //console.log(B);

  return "#" + R.toString(16) + G.toString(16) + B.toString(16);
}


function clip(num, min, max)
{
  if (num > max)
  {
    num = max;
  }
  if (num < min)
  {
    num = min;
  }
  return num;
}


function sleep_ms(millisecs) {
    var initiation = new Date().getTime();
    while ((new Date().getTime() - initiation) < millisecs);
}
