<?php

try{
    $pdo = new PDO("sqlite:".$datapath."/news.s3db");
    //$db = new NotORM($pdo);
    $sql = "SELECT * FROM news;";
    $this->template->news = $pdo->query($sql);
    $pdo = null;
}
catch(Exception $e){
    echo $e;
}