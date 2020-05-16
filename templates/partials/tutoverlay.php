<div class="tut-overlay">
  <div class="tut-wrapper">
    <div id="tut-box-1" class="tut-box col-11 col-sm-8 col-md-6">
      <h5>Tutorial (1/7)</h5>
      <p>Willkommen bei Mord in Palermo! Wir wollen dir kurz das Benutzerinterface
        zeigen, damit du sofort los legen kannst!</p>

      <p><a href="<?= Template::getUrl("/?p=2") ?>" target="_blank">Eine ausführliche Spielbeschreibung findest du hier</a></p>

      <hr>
      <button class="btn btn-secondary" onclick="endTutorial()">Beenden</button>
      <button class="btn btn-primary" style="float: right;" onclick="nextTutorial()">Weiter</button>
    </div>


    <div id="tut-box-2" class="tut-box col-11 col-sm-8 col-md-6">
      <h5>Tutorial (2/7)</h5>
      <p>In Mord in Palermo gibt es vier Rollen:</p>
        <img class="img-responsive" style="max-width: 100%; width: 300px;" src="<?= Template::getUrl("/assets/img/tut_roles.png") ?>">

      <hr>
      <button class="btn btn-secondary" onclick="endTutorial()">Beenden</button>
      <button class="btn btn-primary" style="float: right;" onclick="nextTutorial()">Weiter</button>
    </div>


    <div id="tut-box-2" class="tut-box col-11 col-sm-8 col-md-6">
      <h5>Tutorial (3/7)</h5>

      <p>Du befindest dich auf dem <b>Dashboard</b>. Hier siehst du alle deine Mitspieler.
        Als Bürger kannst du hier jede Runde abstimmen,
        wer am Ende der Runde erhängt wird.</p>

        <img class="img-responsive" style="width: 100%;" src="<?= Template::getUrl("/assets/img/tut_2.png") ?>">

      <hr>
      <button class="btn btn-secondary" onclick="endTutorial()">Beenden</button>
      <button class="btn btn-primary" style="float: right;" onclick="nextTutorial()">Weiter</button>
    </div>


    <div id="tut-box-3" class="tut-box col-11 col-sm-8 col-md-6">
      <h5>Tutorial (4/7)</h5>
      <p>Sobald das Spiel gestartet wurde, bekommst du zufällig eine Rolle zugewiesen.
        Mit einem Klick auf diesen Button erfährst du deine Rolle</p>

        <img class="img-responsive" style="width: 100%;" src="<?= Template::getUrl("/assets/img/tut_3.png") ?>">

      <hr>
      <button class="btn btn-secondary" onclick="endTutorial()">Beenden</button>
      <button class="btn btn-primary" style="float: right;" onclick="nextTutorial()">Weiter</button>
    </div>


    <div id="tut-box-4" class="tut-box col-11 col-sm-8 col-md-6">
      <h5>Tutorial (5/7)</h5>
      <p>Mit diesem Button erscheinen auch Abstimmungsmöglichkeiten.
        Als Arzt, Spion oder Mörder kannst du zusätzlich mit den roten Buttons Mitspieler auswählen.</p>

        <img class="img-responsive" style="width: 100%;" src="<?= Template::getUrl("/assets/img/tut_4.png") ?>">

      <hr>
      <button class="btn btn-secondary" onclick="endTutorial()">Beenden</button>
      <button class="btn btn-primary" style="float: right;" onclick="nextTutorial()">Weiter</button>
    </div>


    <div id="tut-box-5" class="tut-box col-11 col-sm-8 col-md-6">
      <h5>Tutorial (6/7)</h5>
      <p>Wenn du Spion bist, kannst du hier auch die Rollen anderer Spieler sehen,
        die du zuvor ausgespäht hast.</p>

        <img class="img-responsive" style="width: 100%;" src="<?= Template::getUrl("/assets/img/tut_5.png") ?>">

      <hr>
      <button class="btn btn-secondary" onclick="endTutorial()">Beenden</button>
      <button class="btn btn-primary" style="float: right;" onclick="nextTutorial()">Weiter</button>
    </div>

    <div id="tut-box-6" class="tut-box col-11 col-sm-8 col-md-6">
      <h5>Tutorial (7/7)</h5>
      <p>Zuletzt gibt es gewisse Einstellungen, die der Ersteller des Spiels verändern kann.
        Werft gemeinsam einen Blick darauf, dann kann das Spiel los gehen!</p>


      <p><a href="<?= Template::getUrl("/?p=2") ?>" target="_blank">Eine ausführliche Spielbeschreibung findest du hier</a></p>

      <hr>
      <button class="btn btn-primary" style="float: right;" onclick="endTutorial()">Abschließen</button>
    </div>
  </div>
</div>

<script>
var tutorialIndex = 1;

function endTutorial()
{
  $('#tut-box-' + tutorialIndex.toString()).fadeOut();
  $('.tut-overlay').fadeOut();
  setCookie("palermoTut", "1", 3600*24*365);
}

function startTutorial()
{
  tutorialIndex = 1;
  $('.tut-overlay').fadeIn();
  $('#tut-box-' + tutorialIndex.toString()).fadeIn();
}

function nextTutorial()
{
  $('#tut-box-' + tutorialIndex.toString()).fadeOut(400, function() {
    tutorialIndex++;
    if (tutorialIndex > 7)
      tutorialIndex = 7;

    $('#tut-box-' + tutorialIndex.toString()).fadeIn();
  });

}
</script>
