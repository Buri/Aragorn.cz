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
NDebug::enable(NDebug::DETECT, NEnvironment::getVariable('logdir', WWW_DIR . '/../logs'));
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
$router[] = new NRoute('admin/[<presenter>/[<action>/[<id>/[<param>/]]]]', array(
                'module' => 'admin',
                'presenter' => 'dashboard',
                'action' => 'default'
));

/* Load routing table from config */
foreach(array('presenter', 'action') as $type){
    $routing_table = array();
    foreach(explode(",", NEnvironment::getVariable($type . "RoutingTable")) as $item){
        $item = explode(":", $item);
        $routing_table[$item[0]] = $item[1];
    }
    NRoute::setStyleProperty($type, NRoute::FILTER_TABLE, $routing_table);
}

$router[] = new NRoute('[<presenter>/[<action>/[<id>/[<param>/]]]]', array(
                'module' => 'frontend',
                'presenter' => 'dashboard',
                'action' => 'default'
));

$application->run();
