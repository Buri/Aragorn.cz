<?php

define('LIBS_DIR', WWW_DIR . '/../lib'); // path to the libraries
define('CFG_DIR', WWW_DIR . '/../config'); // path to the config files
define('TEMP_DIR', WWW_DIR . '/../temp'); // path to the temp

include LIBS_DIR . "/Nette/loader.php";
include LIBS_DIR . "/memcache.php";
include LIBS_DIR . "/database.php";
include LIBS_DIR . "/usock.php";

use Nette\Environment;
use Nette\Application\Routers as R;

Nette\Diagnostics\Debugger::$strictMode = TRUE;
//Environment::loadConfig(CFG_DIR . "/config.ini");
$configurator = new Nette\Configurator;
$configurator->container->params['tempDir'] = __DIR__ . '/../temp';
$container = $configurator->loadConfig(CFG_DIR . '/config.neon');
Nette\Diagnostics\Debugger::enable(Nette\Diagnostics\Debugger::DETECT, Nette\Environment::getVariable('logdir', WWW_DIR . '/../logs'));
$application = Environment::getApplication();
$container->session->setExpiration('+ 365 days');
//$application->catchExceptions = TRUE;
/*dump($container->params); 
die();*/

$router = $application->getRouter();
$router[] = new R\Route('index.php', 'Homepage:default', R\Route::ONE_WAY);
$router[] = new R\Route('ajax/[<action>/[<id>/[<param>/]]]', array(
                'module' => 'ajax',
                'presenter' => 'ajax',
                'action' => 'default'
));
$router[] = new R\Route('admin/[<presenter>/[<action>/[<id>/[<param>/]]]]', array(
                'module' => 'admin',
                'presenter' => 'dashboard',
                'action' => 'default'
));

/* Load routing table from config */
foreach(array('presenter', 'action') as $type){
    $routing_table = array();
    foreach(explode(",", Environment::getVariable($type . "RoutingTable")) as $item){
        $item = explode(":", $item);
        $routing_table[$item[0]] = $item[1];
    }
    R\Route::setStyleProperty($type, R\Route::FILTER_TABLE, $routing_table);
}

$router[] = new R\Route('[<presenter>/[<action>/[<id>/[<param>/]]]]', array(
                'module' => 'frontend',
                'presenter' => 'dashboard',
                'action' => 'default'
));

$application->run();
