<?php

define('WWW_DIR', dirname(__FILE__)); // path to the web root
define('APP_DIR', WWW_DIR . '/../app'); // path to the application root
define('LIBS_DIR', WWW_DIR . '/../lib'); // path to the libraries
include LIBS_DIR . "/Nette/loader.php";

use Nette\Image;

if(empty($_GET['file']))
    $file = WWW_DIR . '/assets/images/favicon.png';
else
    $file = $_GET['file'];

$icon = Image::fromFile($file);
$num = empty($_GET['num']) ? '' : $_GET['num'];
$icon->ttfText(12, 0, 3, 18, Image::rgb(255,0,0), '../assets/arial.ttf', $num);
$icon->send(Image::PNG);