<?php
$error = false;
if (file_exists("../class/config.php")) {
  $error = "Die Konfigurationsdatei existiert bereits! Aus Sicherheitsgründen muss diese Datei vorher gelöscht werden!";
}

$path = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
if (strpos($path, "index.php") > 0) {
  $path = substr($path, 0, -9);
}
if ($path[strlen($path)-1] == '/') {
  $path = substr($path, 0, -1);
}
$path = substr($path, 0, -8);

?>

<!doctype html>
<html lang="de">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/fa-svg-with-js.css">

    <script src="../assets/js/fontawesome-all.min.js"></script>
    <script src="../assets/js/jquery-3.3.1.min.js"></script>


    <title>Installation</title>
  </head>
  <body>

    <div class="container">
      <div class="row">
        <div class="col-12">
          <br><br>
          <h2>Palermo Installation</h2>

          <?php if (!$error) { ?>
          <div id="inputfields">
            <div class="form-group">
              <label for="input_url">Seiten URL</label>
              <input type="text" name="url" class="form-control" id="input_url" aria-describedby="input_url_help" value="<?= htmlspecialchars($path); ?>">
              <small id="input_url_help" class="form-text text-muted">Die URL wodurch Palermo durch das Internet erreichbar ist.</small>
            </div>

            <div class="form-group">
              <label for="input_dbhost">SQL Host</label>
              <input type="text" name="dbhost" class="form-control" id="input_dbhost" aria-describedby="input_dbhost_help" value="localhost">
              <small id="input_dbhost_help" class="form-text text-muted">Die IP des Datenbankservers</small>
            </div>

            <div class="form-group">
              <label for="input_dbuser">SQL Benutzername</label>
              <input type="text" name="dbuser" class="form-control" id="input_dbuser" aria-describedby="input_dbuser_help" value="palermo">
              <small id="input_dbuser_help" class="form-text text-muted">Der Benutzername für die Datenbankverbindung</small>
            </div>

            <div class="form-group">
              <label for="input_dbpass">SQL Passwort</label>
              <input type="password" name="dbpass" class="form-control" id="input_dbpass" aria-describedby="input_dbpass_help" value="">
              <small id="input_dbpass_help" class="form-text text-muted">Der Benutzername für die Datenbankverbindung</small>
            </div>

            <div class="form-group">
              <label for="input_dbdb">Name der Datenbank</label>
              <input type="text" name="dbdb" class="form-control" id="input_dbdb" aria-describedby="input_dbdb_help" value="palermo">
              <small id="input_dbdb_help" class="form-text text-muted">Der Name der Datenbank</small>
            </div>


            <button id="start-button" type="submit" class="btn btn-primary" onclick="testSettings()">Installieren</button>
          </div>

          <div id="progressbar" style="display:none;">
            <br>
            <div class="progress">
              <div id="pbar1" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
            </div>
            <br>
            <div class="card">
              <div class="card-body" id="install_log">
              </div>
            </div>
          </div>
        <?php } else { ?>
          <?= $error ?>
        <?php } ?>

        </div>
      </div>
    </div>

    <script>
      function testSettings() {

        $('#start-button').addClass('disabled');
        $('#inputfields').hide();
        $('#progressbar').show();
        $('#install_log').html('<p>Die Installation wird gestartet...</p>');

        display_log('Die Einstellungen werden überprüft... ');
        $.post("./install.php",
          {
            request: 'test',
            url: $('#input_url').val(),
            dbhost: $('#input_dbhost').val(),
            dbuser: $('#input_dbuser').val(),
            dbpass: $('#input_dbpass').val(),
            dbdb: $('#input_dbdb').val()
          }).done(function (data) {
            console.log(data);
            if (data.status == 'success') {
              display_log('<i class="fas fa-check text-success"></i><br><br>');
              setProgress(15);
              writeDatabase();
            } else {
              display_log('<i class="fas fa-times text-danger"></i><br><br>');
              if (data.status == 'error') {
                display_log('<p class="text-danger">' + data.error + '</p>');
              } else {
                display_log('<p class="text-danger">Ein unbekannter Fehler ist aufgetreten!</p>');
              }
              add_back_button();
            }
          });
        return false;
      }

      function writeDatabase() {

        display_log('Die Datenbank wird beschrieben... ');
        $.post("./install.php",
          {
            request: 'db',
            url: $('#input_url').val(),
            dbhost: $('#input_dbhost').val(),
            dbuser: $('#input_dbuser').val(),
            dbpass: $('#input_dbpass').val(),
            dbdb: $('#input_dbdb').val()
          }).done(function (data) {
            console.log(data);
            if (data.status == 'success') {
              display_log('<i class="fas fa-check text-success"></i><br><br>');
              setProgress(60);
              writeConfig();
            } else {
              display_log('<i class="fas fa-times text-danger"></i><br><br>');
              if (data.status == 'error') {
                display_log('<p class="text-danger">' + data.error + '</p>');
              } else {
                display_log('<p class="text-danger">Ein unbekannter Fehler ist aufgetreten!</p>');
              }
              add_back_button();
            }
          });
      }

      function writeConfig() {

        display_log('Die Konfigurationsdatei wird beschrieben... ');
        $.post("./install.php",
          {
            request: 'config',
            url: $('#input_url').val(),
            dbhost: $('#input_dbhost').val(),
            dbuser: $('#input_dbuser').val(),
            dbpass: $('#input_dbpass').val(),
            dbdb: $('#input_dbdb').val()
          }).done(function (data) {
            console.log(data);
            if (data.status == 'success') {
              display_log('<i class="fas fa-check text-success"></i><br><br>');
              setProgress(100);
              add_success_button();
            } else {
              display_log('<i class="fas fa-times text-danger"></i><br><br>');
              if (data.status == 'error') {
                display_log('<p class="text-danger">' + data.error + '</p>');
              } else {
                display_log('<p class="text-danger">Ein unbekannter Fehler ist aufgetreten!</p>');
              }
              add_back_button();
            }
          });
      }

      function setProgress(val) {
        $('#pbar1').text(val + '%');
        $('#pbar1').attr('aria-valuenow', val);
        $('#pbar1').css('width', val+'%');
      }

      function display_log(text) {
        $('#install_log').append(text);
      }

      function add_back_button() {
        $('#install_log').append('<button class="btn btn-primary" onclick="back_button_click()">Zurück</button>');
      }

      function add_success_button() {
        $('#install_log').append('<a class="btn btn-primary" href="../">Weiter zur Seite</a>');
      }

      function back_button_click() {
        $('#start-button').removeClass('disabled');
        $('#inputfields').show();
        $('#progressbar').hide();
      }
    </script>

  </body>
</html>
