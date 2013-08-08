<?php

require_once("DerbyLogos.php");

$x = new Logos("");
print "I have ".$x->dir."\n";

$dir = "./foo/blah";
$z = $x->getAllDirectories();
print_r($z);

