<?php

define('WWW_DIR', __DIR__ . '/../web');
define('LIBS_DIR', WWW_DIR . '/../lib'); // path to the libraries
define('CFG_DIR', WWW_DIR . '/../config'); // path to the config files
define('TEMP_DIR', WWW_DIR . '/../temp'); // path to the temp
header('Content-type: text/plain');

include LIBS_DIR . "/Nette/loader.php";
include LIBS_DIR . "/memcache.php";
include LIBS_DIR . "/database.php";
include LIBS_DIR . "/usock.php";

use Nette\Environment;
date_default_timezone_set('Europe/Prague');

echo "Running cleanup script @ " . date("d.m.Y H:i") . "\n";


/* Clear up uploaded photos older than 24h */
echo "Deleting old uploaded photos\n============================\n\n";
$dir = __DIR__ . '/../userspace/u';
foreach(Nette\Utils\Finder::findFiles('*')->in($dir) as $path => $file){
    if(filemtime($path) + 3600*24 < time()){
        echo str_pad("Deleting: " . $path, 150, " ");
        if(unlink($path)) echo "OK";
        else echo "Failed";
    }else{
        echo "Skipping new file: " . $path;
    }
    echo "\n";
}