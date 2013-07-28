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

	function cleanHTML($string) {
		// Replace < and > with &gt; and &lt;
		$source = array( '/</', '/>/' );
		$dest = array( '&lt;', '&gt;' );
		return preg_replace($source, $dest, $string);
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

	function noSubdirs($dir = null) {
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
			$dir = $this->cleanHTML($dir);
			print "<a href='index.php?dir=$dir'>$dir</a><br />\n";
		}
	}


	function showFiles() {
		// Note: Does not match hidden files (eg, .status)
		$all_files = glob("$this->dir/*");
		foreach ($all_files as $filename) {
			$filename = $this->cleanHTML($filename);
			print "<table><tr><td>$filename</td><td style='background-color: black'>";
			if (preg_match('/(png|bmp|jpg|gif)$/', $filename)) {
				print "<img src='$filename'>";
			}
			print "</td></tr></table>\n";
		}
	}
}



		
