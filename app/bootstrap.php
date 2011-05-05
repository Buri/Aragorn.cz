<?php

define('LIBS_DIR', WWW_DIR . '/../lib'); // path to the libraries
define('CFG_DIR', WWW_DIR . '/../config'); // path to the config files
define('TEMP_DIR', WWW_DIR . '/../temp'); // path to the temp

include LIBS_DIR . "/Nette/loader.php";
include LIBS_DIR . "/memcache.php";
include LIBS_DIR . "/database.php";
include LIBS_DIR . "/usock.php";

NDebug::$strictMode = TRUE;
NEnvironment::loadConfig(CFG_DIR . "/config.ini");
NDebug::enable(Debug::DETECT, NEnvironment::getVariable('logdir', WWW_DIR . '/../logs'));
$application = NEnvironment::getApplication();
//$application->errorPresenter = 'Error';
//$application->catchExceptions = TRUE;

$router = $application->getRouter();
$router[] = new NRoute('index.php', 'Homepage:default', NRoute::ONE_WAY);
$router[] = new NRoute('ajax/[<action>/[<id>/[<param>/]]]', array(
                'module' => 'ajax',
                'presenter' => 'ajax',
                'action' => 'default'
));
$router[] = new NRoute('[<presenter>/[<action>/[<id>/[<param>/]]]]', array(
                'module' => 'frontend',
                'presenter' => 'dashboard',
                'action' => 'default'
));
$router[] = new NRoute('admin/[<presenter>/[<action>/[<id>/[<param>/]]]]', array(
                'module' => 'admin',
                'presenter' => 'dashboard',
                'action' => 'default'
));


$user =  NEnvironment::getUser();
$application->run();
