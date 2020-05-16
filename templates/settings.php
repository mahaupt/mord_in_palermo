<div class="header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-2 text-left">
      </div>

      <div class="col-8 text-center">
        <h1>Einstellungen</h1>
      </div>

      <div class="col-2 text-right">
        <a href="#dashboard" id="menu-icon-right"><i class="fa fa-arrow-right fa-2x"></i></a>
      </div>
    </div>
  </div>
</div>

<ul>
  <li class="Divider">
    Einladecode
  </li>
  <li><span class="text-center"><b id="settings-gcode">loading...</b></span></li>
  <li><a href="#" class="text-center text-danger" onclick="GAME.leaveGame()"><i class="fas fa-sign-out-alt"></i> Spiel Verlassen</a></li>
</ul>



<ul id="settSlider" class="d-none" style="position: relative;">
  <div class="block-overlay d-none">
    <div class="block-overlay-inner">
      <b>Einstellungen können während eines laufenden Spiels nicht verändert werden</b>
    </div>
  </div>

  <li class="Divider">
    Einstellungen
  </li>

  <li>
    <!--<input type="checkbox" checked="checked" class="Toggle" />-->
		<span>Spione</span>
    <span class="slide-container" id="settings-slider-spione">
      <span class="slider-text">10% (2)</span>
      <input class="slider" type="range" value="10" min="0" max="100" step="10">
    </span>
  </li>
  <li>
    <!--<input type="checkbox" checked="checked" class="Toggle" />-->
		<span>Ärzte</span>
    <span class="slide-container" id="settings-slider-aerzte">
      <span class="slider-text">20% (3)</span>
      <input class="slider" type="range" value="20" min="0" max="100" step="10">
    </span>
  </li>
  <li>
		<span>Mörder</span>
    <span class="slide-container" id="settings-slider-moerder">
      <span class="slider-text">30% (4)</span>
      <input class="slider" type="range" value="30" min="0" max="100" step="10">
    </span>
  </li>

  <li>
    <!--<input type="checkbox" checked="checked" class="Toggle" />-->
		<span>Rundentimer</span>
    <span class="slide-container" id="settings-slider-timer">
      <span class="slider-text">5 Minuten</span>
      <input class="slider" type="range" value="5" min="0" max="20" step="1">
    </span>
  </li>
</ul>


<ul id="settPlayerList" class="d-none" style="position: relative;">
  <div class="block-overlay d-none">
    <div class="block-overlay-inner">
      <b>Spieler können nur vom Host (Martin) verwaltet werden</b>
    </div>
  </div>

  <li class="Divider">
    Spielerverwaltung
  </li>

</ul>
