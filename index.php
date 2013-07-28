<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Strict//EN">
<html>
  <head>
    <title>Roller Derby League Logo manager</title>
  </head>
<?php
// Iterate through all files, and update/display/create info about each of them.

require_once("DerbyLogos.php");

$dir = isset($_REQUEST['dir'])?$_REQUEST['dir']:null;
$a = new Logos($dir);

$dirs = $a->getDirectories();

$a->showDirectories($dirs);

?>
</html>



