<?php

define('LIBS_DIR', WWW_DIR . '/../lib'); // path to the libraries
define('CFG_DIR', WWW_DIR . '/../config'); // path to the config files
define('TEMP_DIR', WWW_DIR . '/../temp'); // path to the temp

include LIBS_DIR . "/Nette/loader.php";
/*include LIBS_DIR . "/memcache.php";
include LIBS_DIR . "/database.php";*/

use Nette\Environment;
use Nette\Application\Routers\Route;

/* Create new configurator */
$configurator = new Nette\Config\Configurator;
$configurator->setTempDirectory(__DIR__ . '/../temp');
$loader = $configurator->createRobotLoader()->addDirectory(APP_DIR)->addDirectory(LIBS_DIR)->register();
$configurator->addConfig(CFG_DIR . '/config.neon');
$container = $configurator->createContainer();
#$container->session->setExpiration('+ 365 days');

if($container->parameters["lockdown"] == 'true'){
    require_once APP_DIR . "/templates/lockdown.html";
    exit;
}

/* Setup debuging options */
Nette\Diagnostics\Debugger::enable(Nette\Diagnostics\Debugger::DEVELOPMENT, Nette\Environment::getVariable('logdir', WWW_DIR . '/../logs'));
Nette\Diagnostics\Debugger::$strictMode = TRUE;
Environment::setProductionMode(false);
if(Environment::isProduction()) Nette\Diagnostics\Debugger::$email = 'buri.buster@gmail.com';

/* Setup cookies for later use */
if(empty($_COOKIE['skin'])){
    $skin = Nette\Environment::getVariable('defaultSkin', 'dark');
    setCookie('skin', $skin, time()+3600*24*365, '/');
    $_COOKIE['skin'] = 'dark';
}
if(empty($_COOKIE['sid'])){
    setCookie('sid', 0, 0, '/');
    $_COOKIE['sid'] = 0;
}

$application = $container->application;
$application->catchExceptions = FALSE; //TRUE;

$router = $application->getRouter();
$router[] = new Route('index.php', 'frontend:dashboard:default', Route::ONE_WAY);
$router[] = new Route('index.php', 'frontend:dashboard:default', Route::ONE_WAY);
$router[] = new Route('ajax/[<action>/[<id>/[<param>/]]]', array(
                'module' => 'ajax',
                'presenter' => 'ajax',
                'action' => 'default'
));
$router[] = new Route('admin/[<presenter>/[<id>/[<action>/[<param>/]]]]', array(
                'module' => 'admin',
                'presenter' => 'dashboard',
                'action' => 'default'
));
$router[] = new Route('logout/', array(
                'module' => 'frontend',
                'presenter' => 'dashboard',
                'action' => 'logout',
));


/* Load routing table from config.neon */
$knownActions = array();
foreach(array('presenter', 'action') as $type){
    $routing_table = array();
    foreach(explode(",", $container->params[$type . "RoutingTable"]) as $item){
        $item = explode(":", $item);
        $routing_table[$item[0]] = $item[1];
        if($type == 'action') $knownActions[] = $item[0];
    }
    Route::setStyleProperty($type,
            Route::FILTER_TABLE,
            $routing_table);
}

$router[] = new Route('[<presenter>/[<action '.implode('|', $knownActions).'>/[<id>/[<param>/]]]]', array(
'module' => 'frontend',
'presenter' => 'dashboard',
'action' => 'default'
));

$router[] = new Route('<presenter>/<id>/[<param>/]', array(
'module' => 'frontend',
'presenter' => 'dashboard',
'action' => 'view'
));

$router[] = new Route('[<presenter>/[<id>/[<action>/[<param>/]]]]', array(
'module' => 'frontend',
'presenter' => 'dashboard',
'action' => 'default'
));

/* Debug panel extensions */
\Nette\Diagnostics\Debugger::addPanel(new \Nette\Application\Diagnostics\RoutingPanel($router, $container->httpRequest));
\Extras\Debug\ComponentTreePanel::register();
\Nette\Diagnostics\Debugger::addPanel(new IncludePanel);
\Panel\ServicePanel::register($container, $loader);
\Panel\Todo::register($container->params['appDir']);

$application->run();
