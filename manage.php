<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Strict//EN">
<html>
  <head>
    <title>Roller Derby League Logo manager</title>
  </head>
<?php
exit; // Remove this to actually use it.

require_once("DerbyLogos.php");

$dir = isset($_REQUEST['dir'])?$_REQUEST['dir']:null;

$a = new Logos($dir);

// Handle any updates
$a->updateData($_REQUEST);

if ($a->noSubdirs()) {
	// This is the end of the tree. Display files
	$a->showLeague();
} else {
	// Subdirectories. List them.
	$dirs = $a->getAllUnderFolder();
	$a->showDirectories($dirs['directories']);
}

?>
</html>



