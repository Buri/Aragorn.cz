<?php

$mtime = explode(" ", microtime());
$_STARTTIME = $mtime[1] + $mtime[0];

define('WWW_DIR', dirname(__FILE__)); // path to the web root
define('APP_DIR', WWW_DIR . '/../app'); // path to the application root
require APP_DIR . '/bootstrap.php'; // load bootstrap file 
