<?php

class Logos {

	private $dir = null;
	private $defaults = array ("black_logo" => null, "white_logo" => null, "other_logo" => null, "facebook_page" => null, "league_email" => null);

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


	function showLeague() {
		// Note: Does not match hidden files (eg, .status)
		$all_files = glob("$this->dir/*");
		$status = $this->getStatus($dir);
		print "<table>";
		foreach ($all_files as $filename) {
			$filename = $this->cleanHTML($filename);
			print "<tr><td>".basename($filename)."</td>";
			if (preg_match('/(png|bmp|jpg|gif)$/', $filename)) {
				print "<td style='background-color: black'><img width=100px src='$filename'>";
				print "</td>";
				print "<td style='background-color: white'><img width=100px src='$filename'>";
				print "</td>";
			} else {
				print "<td></td><td></td>";
			}
			
			if ($status['black'] == $filename) $black = 'checked'; 
			if ($status['white'] == $filename) $white = 'checked'; 
			if ($status['other'] == $filename) $other = 'checked'; 
			print "<td>Black? <input type='checkbox' value='black' $black></td>";
			print "<td>White? <input type='checkbox' value='white' $white></td>";
			print "<td>Other? <input type='checkbox' value='other' $other></td>";
			print "</tr>";
		}
		print "</table>\n";
	}

	function displayStatus($dir) {
		$status = $this->getStatus($dir);
		$allStatus = array (""); }
		
	function getStatus($dir) {
		if ($dir == null)
			$dir=$this->dir;
		if (!file_exists("$dir/.status")) {
			$this->createStatusFile($dir);
		}
		$status = json_decode(file_get_contents("$dir/.status"), true);
		return $status;
	}

	function createStatusFile($dir) {
		// Only called if .status doesn't exist. 
		if (file_exists("$dir/.status")) {
			return false;
		}
		// Figure out everything we already know about the league.
		// I'm just going to assume the the directory seperator is '/'. This is windows unsafe.
		$parts = explode("/", $dir);
		$leaguename = basename($dir);
		$country = $parts[0];
		if (isset($parts[2])) {
			// This country has States/Counties.
			$state = $parts[1];
			$league = $parts[2];
		} else {
			// No States/Counties.
			$league = $parts[1];
		}

		$status = array ("League" => $league, "Country" => $country, "Region" => $state);

		file_put_contents("$dir/.status", json_encode(array_merge($status, $this->defaults)));
	}

}



		
