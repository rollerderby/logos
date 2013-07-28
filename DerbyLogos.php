<?php

class Logos {

	private $dir = null;

	function __construct($dir = null) {
		if ($dir == null)
			$dir = ".";
		$this->updateDir($dir);
	}

	function updateDir($dir) {
		// Are people trying to be clever?
		if (preg_match("/\.\./", $dir)) 
			return false;
		$this->dir = $dir;
	}

	function canWrite() {
		// Try to create a file, return true if you can
		$filename = tempnam($this->dir, "canwrite");
		if (false !== $filename) {
			unlink($filename);
			return true;
		} else {
			return false;
		}
	}

	function getDirectories($dir = null) {
		if ($dir == null)
			$dir = $this->dir;
		if (preg_match("/\.\./", $dir)) 
			return false;	
		// Return a list of all directories under the Directory specified.
		return array_filter(glob("$dir/*"), 'is_dir');
	}

	function noSubdirectories($dir = null) {
		if ($dir == null) 
			$dir = $this->dir;
		$subdirs = $this->getDirectories($dir);
		if ($subdirs == array())
			return true;
		else
			return false;
	}



	function showDirectories($dirs) {
		foreach ($dirs as $dir) {
			print "<a href='index.php?dir=$dir'>$dir</a><br />\n";
		}
	}
}



		
