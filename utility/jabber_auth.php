#!/usr/bin/php5
<?php
include __DIR__ . '/../lib/NotORM.php';
include __DIR__ . '/../lib/Jabber/stdio.php';
include __DIR__ . '/../lib/Jabber/JabberAuth.php';

$notorm = new NotORM(new PDO('mysql:host=127.0.0.1;port=3306;dbname=aragorn_cz;', 'root', 'a'));
$jabber = new JabberAuth($notorm, new stdio());

do{
    $jabber->go();
}while(true);