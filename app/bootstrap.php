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

$configurator = new Nette\Config\Configurator;
$configurator->setTempDirectory(__DIR__ . '/../temp');
$configurator->createRobotLoader()->addDirectory(APP_DIR)->addDirectory(LIBS_DIR)->register();
$configurator->addConfig(CFG_DIR . '/config.neon');
$configurator->addParameters(array("libsDir"=>LIBS_DIR));
$container = $configurator->createContainer();
Nette\Diagnostics\Debugger::enable(Nette\Diagnostics\Debugger::DEVELOPMENT, Nette\Environment::getVariable('logdir', WWW_DIR . '/../logs'));
Nette\Diagnostics\Debugger::$strictMode = TRUE;
Environment::setProductionMode(false);
$application = Environment::getApplication();
$container->session->setExpiration('+ 365 days');
//$application->catchExceptions = TRUE;
if(empty($_COOKIE['skin'])){
    $skin = Nette\Environment::getVariable('defaultSkin', 'dark');
    setCookie('skin', $skin, time()+3600*24*365);
    $_COOKIE['skin'] = $skin;
}

$router = $application->getRouter();
$router[] = new R\Route('index.php', 'Homepage:default', R\Route::ONE_WAY);
/*$router[] = new R\Route('login/', array(
        'module'=>'frontend',
        'presenter' => 'dashboard',
), R\Route::SECURED);*/
$router[] = new R\Route('ajax/[<action>/[<id>/[<param>/]]]', array(
                'module' => 'ajax',
                'presenter' => 'ajax',
                'action' => 'default'
));
$router[] = new R\Route('admin/[<presenter>/[<id>/[<action>/[<param>/]]]]', array(
                'module' => 'admin',
                'presenter' => 'dashboard',
                'action' => 'default'
));

/* Load routing table from config */
foreach(array('presenter', 'action') as $type){
    $routing_table = array();
    foreach(explode(",", $container->params[$type . "RoutingTable"]) as $item){
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

/* Debug panel extensions */
Extras\Debug\ComponentTreePanel::register();
\Nette\Diagnostics\Debugger::addPanel(new IncludePanel);

$application->run();
