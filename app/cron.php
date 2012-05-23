<?php

define('WWW_DIR', __DIR__ . '/../web');
define('LIBS_DIR', WWW_DIR . '/../lib'); // path to the libraries
define('CFG_DIR', WWW_DIR . '/../config'); // path to the config files
define('TEMP_DIR', WWW_DIR . '/../temp'); // path to the temp
define('APP_DIR', WWW_DIR . '/../app'); // path to the temp
header('Content-type: text/plain');
date_default_timezone_set('Europe/Prague');

include LIBS_DIR . "/Nette/loader.php";
include LIBS_DIR . "/memcache.php";
include LIBS_DIR . "/database.php";
include LIBS_DIR . "/usock.php";

use Nette\Environment;
$configurator = new Nette\Config\Configurator;
$configurator->setTempDirectory(__DIR__ . '/../temp');
$configurator->createRobotLoader()->addDirectory(APP_DIR)->addDirectory(LIBS_DIR)->register();
$configurator->addConfig(CFG_DIR . '/config.neon');
$configurator->addParameters(array("libsDir"=>LIBS_DIR));
$container = $configurator->createContainer();

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

/* SQL Clearup */
/* Move old forum posts to disk archive */
echo "\n\nArchiving old forums\n====================\n\n";
$posts = DB::forum_posts()->where('time < ?', time() - 15552000); // 3600*24*180 Archive post older then half a year
$postCount = 0;
$sqlite = new NotORM(new PDO('sqlite:' . WWW_DIR . '/../db/system/archive.s3db'));
foreach($posts as $post){
    $postCount++;
    echo str_pad("Archiving post " . $post['id'], 150, " ");
    try{
        $sqlite->forum_posts()->insert($post);
        $data = DB::forum_posts_data('id', $post['id'])->fetch();
        $sqlite->forum_posts_data()->insert(array(
            'id' => $post['id'], 
            'post' => gzencode($data['post'], 9))           // Store data compressed
                );
        $post->delete();
        $data->delete();
        echo "OK";
    }
    catch(Exception $e){
        echo "Failed";
    }
    echo "\n";
}
echo "Posts archived: " . $postCount . "\n";
