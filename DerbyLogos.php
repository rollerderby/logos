<?php

class Logos {

	private $dir = null;
	private $defaults = array ("facebook_page" => null, "league_email" => null);

	public $status;

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

	function showLeague($dir = null) {
		if ($dir == null)
			$dir = $this->dir;
		$this->getStatus($dir);

		$all_images = $this->getAllImages($dir);
		$teams = $this->getTeams();

		foreach ($teams as $team) {
			print "<h2>".$team['description']."</h2>\n";
			print "<table>";
			foreach ($team['logos'] as $index => $img_array) {

				$filename = $img_array['filename'];

				// Just ignore files with odd characters in them.
				if($filename != $this->cleanHTML($filename))
					continue;
				if (isset($all_images[$filename])) {
					unset($all_images[$filename]);
					print "<tr><td>".basename($filename)."</td>";
					print "<td style='background-color: black'><img width=100px src='$filename'>";
					print "</td>";
					print "<td style='background-color: white'><img width=100px src='$filename'>";
					print "</td>";
					if ($img_array['black'] == true ) $black = 'checked'; else $black = ""; 
					if ($img_array['white'] == true ) $white = 'checked'; else $white = ""; 
					if ($img_array['other'] == true ) $other = 'checked'; else $other = ""; 
					print "<td>Black? <input type='checkbox' value='black' $black></td>";
					print "<td>White? <input type='checkbox' value='white' $white></td>";
					print "<td>Other? <input type='checkbox' value='other' $other></td>";
				} else {
					// File missing. 
					print "<tr><td>".basename($filename)."</td>";
					print "<td colspan=5>Image missing</td>\n";
				}

				print "</tr>";
			}
			print "</table>\n";
		}
	}

	function getStatus($dir) {
		if ($dir == null)
			$dir=$this->dir;
		if (!file_exists("$dir/.status")) {
			$this->createStatusFile($dir);
		}
		$this->status = json_decode(file_get_contents("$dir/.status"), true);
	}

	function createStatusFile($dir) {
		$this->dir = $dir;
		// Only called if .status doesn't exist. 
		if (file_exists("$dir/.status")) {
			return false;
		}
		// Figure out everything we already know about the league.
		// I'm just going to assume the the directory seperator is '/'. This is windows unsafe.
		$parts = explode("/", $dir);

		// If first directory entry is ".", ignore it.
		if ($parts[0] == ".")
			array_shift($parts);
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

		// Really basic .status file
		$this->status = array_merge($this->defaults, array ("League" => $league, "Country" => $country, "Region" => $state));
		$this->updateStatusFile($dir);

		// Create a default team
		$this->addTeam("Default Team");

		// Associate all images with Default Team
		$images = $this->getAllImages();
		foreach ($images as $image) {
			$this->addImageToTeam("Default Team", $image);
		}
		$this->updateStatusFile($dir);
	}

	function updateStatusFile($dir = null)  {
		if ($dir == null) 
			$dir = $this->dir;
		file_put_contents("$dir/.status", json_encode($this->status));
	}

	function getAllImages($dir = null) {
		if ($dir == null)
			$dir = $this->dir;
		$all_files = glob("$dir/*");
		foreach ($all_files as $filename) {
			if (preg_match('/(png|bmp|jpg|gif)$/', $filename)) {
				$allimages[$filename] = $filename;
			}
		}
		return $allimages;
	}

	function addImageToTeam($teamname, $image) {
		if (!isset($this->status['teams'][$teamname])) 
			$this->addTeam($teamname);

		$logo_array = array ( 'filename' => $image, 'name' => $image, 'black' => false, 'white' => false, 'other' => false);
		$this->status['teams'][$teamname]['logos'][] = $logo_array;
		$this->updateStatusFile();
	}

	function addTeam($teamname) {
		if (isset($this->status['teams'][$teamname]))
			return true;
		$this->status['teams'][$teamname]['description'] = $teamname;
		$this->updateStatusFile();
	}

	function getTeams() {
		return ($this->status['teams']);
	}
}
