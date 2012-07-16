<?php

$out = "/tmp/" . uniqid() . ".temp";

foreach($argv as $index => $file){
    if($index == 0) continue;
    shell_exec("java -jar yuic.jar -o \"$out\" \"$file\"");
    echo file_get_contents($out);
    if($index != count($argv) -1)
    echo ';';
}
unlink($out);
