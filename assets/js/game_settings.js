/**
 * renders the settings page from the player list
 * @param  {array} plist array of players
 * @return {void}
 */

function settBuildPlayerList(plist)
{
  var text = "";

  //player is admin - display player list
  if (GAME.player_self.is_admin == 1)
  {
    $('#settPlayerList').removeClass('d-none');
    $('#settSlider').removeClass('d-none');
  }

  text += '<li class="Divider">';
  text += '  Spielerverwaltung';
  text += '</li>';

  for (var i = 0; i < plist.length; i++)
  {
    var player = plist[i];
    var player_name = "Unbekannt";
    if (player.name.length > 0) {
      player_name = player.name;
    }

    text += '<li>';
    text += '  <span>';

    //kick player button
    text += '    <span class="dashb-poll dashb-poll-role fa-layers fa-fw fa-2x" data-pid="' + player.id + '" onclick="GAME.kickPlayer(' + player.id + ')">';
    text += '      <i class="fas fa-circle" style="color:Tomato"></i>';

    text += '      <i class="fa-inverse fa-times fas" data-fa-transform="shrink-6"></i>';
    text += '    </span>';

    text += '    <span class="list-player-name">';
    text += '      ' + player_name;

    if (player.id == GAME.player_self.id) {
      text += ' (ich)';
    }

    text += '    </span>';
    text += '  </span>';
    text += '</li>';
  }

  $('#settPlayerList').html(text);
}


/**
 * calculates new text values for the sliders in the settings page
 * @return {void}
 */
function calculateNewSliderValues()
{
  if (GAME.player_self.is_admin == 0) {
    $('#settSlider').addClass("d-none");
  }

  var slider_spione = $('#settings-slider-spione input').val();
  var slider_aerzte = $('#settings-slider-aerzte input').val();
  var slider_moerder = $('#settings-slider-moerder input').val();
  var slider_timer = $('#settings-slider-timer input').val();

  var playerNumber = GAME.player.length;
  var spione = Math.round(slider_spione * GAME.player.length / 100);
  var aerzte = Math.round(slider_aerzte * GAME.player.length / 100);
  var moerder = Math.round(slider_moerder * GAME.player.length / 100);


  //needs at least one moerder
  if (moerder <= 0)
    moerder = 1;

  if (playerNumber > 0)
  {
    //check if fole count fits player count
    while (moerder+aerzte+spione > playerNumber)
    {

      if (spione > 0)
        spione--;

      //check again role counts
      if (moerder+aerzte+spione <= playerNumber)
        break;

      if (aerzte > 0)
        aerzte--;

      //check again role counts
      if (moerder+aerzte+spione <= playerNumber)
        break;

      if (moerder > 1)
        moerder--;
    }
  }


  $('#settings-slider-spione .slider-text').text(slider_spione + "% (" + spione + ")");
  $('#settings-slider-aerzte .slider-text').text(slider_aerzte + "% (" + aerzte + ")");
  $('#settings-slider-moerder .slider-text').text(slider_moerder + "% (" + moerder + ")");

  if (slider_timer == 0)
  {
    $('#settings-slider-timer .slider-text').text("Unbegrenzt");
  } else {
    $('#settings-slider-timer .slider-text').text(slider_timer + " Minuten");
  }

}


var settings_slider_timer;
/**
 * the timeout for the slider
 * each time the admin changes the slider settings, the timer gets activated
 * if a specific time passed without the admin making changes, the new
 * settings get uploaded to the server
 * thus avoiding spamming the server with new settings every millisecond
 * @return {void}
 */
function settSliderTimeout()
{
  var slider_spione = $('#settings-slider-spione input').val();
  var slider_aerzte = $('#settings-slider-aerzte input').val();
  var slider_moerder = $('#settings-slider-moerder input').val();
  var slider_timer = parseInt($('#settings-slider-timer input').val())*60;

  GAME.saveSettings(slider_spione, slider_aerzte, slider_moerder, slider_timer);
}


$(document).ready(function()
{
  $('.slider').change(function() {
    calculateNewSliderValues();
    clearTimeout(settings_slider_timer);
    settings_slider_timer = setTimeout(settSliderTimeout, 2000);
  });

  $('.slider').on('input', function () {
    $(this).trigger('change');
  });


});
