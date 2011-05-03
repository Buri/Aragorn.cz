<?php

$fp = fsockopen("unix:///tmp/nodejs.socket", NULL);
header("Content-type: text/plain");
echo "CMD: ".$_GET["cmd"]."\n";
fwrite($fp, $_GET["cmd"]);
echo "Response: " . fread($fp, 409600);
fclose($fp);
