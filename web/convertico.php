<?php

define('WWW_DIR', dirname(__FILE__)); // path to the web root
define('APP_DIR', WWW_DIR . '/../app'); // path to the application root
define('LIBS_DIR', WWW_DIR . '/../lib'); // path to the libraries
include LIBS_DIR . "/Nette/loader.php";

use Nette\Image;

$file = WWW_DIR . '/../userspace/u/'.$_GET['f'];

$icon = Image::fromFile($file);
$icon->crop($_GET['x'], $_GET['y'], $_GET['w'], $_GET['h']);
if($icon->width > $icon->height)
    $icon->resize (($icon->width > 120 ? 120 : ($icon->width < 60 ? 60 : $icon->width)), null);
else
    $icon->resize (null, ($icon->width > 120 ? 120 : ($icon->width < 60 ? 60 : $icon->width)));

$icon->send(Image::PNG);
