<!doctype html>
<html lang="de">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="<?= Template::getUrl("/assets/css/bootstrap.min.css") ?>">
    <link rel="stylesheet" href="<?= Template::getUrl("/assets/css/fa-svg-with-js.css") ?>">
    <link rel="stylesheet" href="<?= Template::getUrl("/assets/css/jquery.mmenu.all.css") ?>">
    <link rel="stylesheet" href="<?= Template::getUrl("/assets/css/site.css") ?>?v=1.34">

    <script src="<?= Template::getUrl("/assets/js/fontawesome-all.min.js") ?>"></script>
    <script src="<?= Template::getUrl("/assets/js/jquery-3.3.1.min.js") ?>"></script>
    <script src="<?= Template::getUrl("/assets/js/jquery.mmenu.all.js") ?>"></script>
    <script src="<?= Template::getUrl("/assets/js/palermotools.js") ?>"></script>
    <script src="<?= Template::getUrl("/assets/js/site.js") ?>?v=1.34"></script>

    <script>
       $(document).ready(function( $ ) {
          $("#menu").mmenu();
       });
    </script>


    <title><?= $_PAGE['title'] ?></title>
  </head>
  <body>
