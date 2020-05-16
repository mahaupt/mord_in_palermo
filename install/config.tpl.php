<?php

$_CONFIG = array();
$_CONFIG['VERSION'] = "0.9.1";

/* GENERAL SETTINGS */
$_CONFIG['URL'] = "{{install_url}}";
$_CONFIG['GAMECODE_LENGTH'] = 6;
$_CONFIG['PLAYERCODE_LENGTH'] = 18;

/* GAME DEFAULTS */
$_CONFIG['SPIONE'] = 10;
$_CONFIG['AERZTE'] = 20;
$_CONFIG['MOERDER'] = 30;
$_CONFIG['ROUNDTIME'] = 300;

/* DATABASE */
$_CONFIG['DB_DB'] = "{{install_dbname}}";
$_CONFIG['DB_HOST'] = "{{install_dbhost}}";
$_CONFIG['DB_USER'] = "{{install_dbuser}}";
$_CONFIG['DB_PASS'] = "{{install_dbpass}}";

?>
