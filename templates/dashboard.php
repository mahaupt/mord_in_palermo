<div class="header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-2 text-left">
        <a href="#settings" id="menu-icon-left"><i class="fa fa-cog fa-2x"></i></a>
      </div>

      <div class="col-8 text-center">
        <h1>Dashboard</h1>
      </div>

      <div class="col-2 text-right">
        <a href="#messages" id="menu-icon-right">
          <span class="fa-layers fa-fw fa-2x" style="margin-top: 10px;">
            <i class="fa fa-envelope"></i>
            <span class="fa-layers-counter d-none" style="font-size: 40px; background:Tomato">26</span>
          </span>
        </a>
      </div>
    </div>
  </div>
</div>


<ul id="dashb-personalinfo">
  <li class="Divider">
    Profil
    <i style="float: right; cursor: pointer;" class="fa-stack fa-fw" onclick="startTutorial()">
      <i class="fas fa-circle fa-stack-2x"></i>
      <i class="fas fa-question fa-stack-1x fa-inverse"></i>
    </i>

  </li>
  <li><span id="dashb-personalinfo-name" class="text-center">Unbekannt</span></li>
  <li><span id="dashb-personalinfo-timer" class="text-center jsTimer d-none">:00</span></li>
  <li><a id="dashb-role-button" href="#" class="text-center text-muted" onclick="roleClick()"><i class="far fa-eye"></i> Rolle</a></li>
</ul>

<button id="dashb-game-start-button" class="btn btn-primary col-12 d-none" onclick="GAME.startGame()">Spiel starten</button>
<div id="dashb-game-start-info" style="margin-top: 10px;" class="card d-none">
  <div class="card-body">
    Das Spiel ist pausiert.<br>Sobald alle bereit sind, kann der Host das Spiel starten.
  </div>
</div>
<div id="dashb-game-dead-info" style="margin-top: 10px;" class="card d-none">
  <div class="card-body">
    Du wurdest ermordet.<br>Du kannst nichts mehr machen, außer auf ein neues Spiel zu warten.
  </div>
</div>
<div id="dashb-game-vote-info" style="margin-top: 10px;" class="text-danger d-none">
  Du kannst für diese Runde noch abstimmen!
</div>

<div id="dashb-game-target-info" class="alert alert-warning d-none" style="margin-top: 10px;" role="alert">
  <b>Warnung:</b> X, Y, Z haben gegen dich abgestimmt!
</div>


<ul id="dasb-playerlist">
  <li class="Divider">Mitspieler</li>


</ul>
