<?php

require_once("DerbyLogos.php");

$x = new Logos();

$x->showDirectories($x->getDirectories("au/Queensland"));
