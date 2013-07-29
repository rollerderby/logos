<?php

class Logos {

	private $dir = null;
	private $defaults = array ("facebook_page" => null, "league_email" => null);

	public $status;

	function __construct($dir = null) {
		if ($dir == null)
			$dir = ".";
		$dir = $this->checkFilename($dir);
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
		$source = array( '/</', '/>/', "/'/", '/"/' );
		$dest = array( '&lt;', '&gt;', '&#39', '&quot' );
		return preg_replace($source, $dest, $string);
	}

	function checkFilename($filename) {
		$filename = preg_replace("/\/+/", "/", $filename);
		$filename = preg_replace("/\.\./", "", $filename);
		$filename = preg_replace("/^\//", "", $filename);
		return $filename;
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
		// This inherits to all forms on the page. Handy.
		print "<input type='hidden' name='dir' value='$dir'>\n";

		foreach ($teams as $teamname => $team) {
			print "<form method='post'>\n";
			print "<h2>".$team['description']."</h2>";
			print "<input type='hidden' name='teamname' value='$teamname'>\n";
			print "<input type='text' name='".$teamname."' value='".$team['description']."'><input type='submit' value='Rename' name='action' /><input type='submit' name='action' value='Delete'><input type='submit' name='action' value='Create'></form>\n";
			print "<table>";
			foreach ($team['logos'] as $index => $img_array) {

				$filename = $img_array['filename'];

				// Just ignore files with odd characters in them.
				if($filename != $this->cleanHTML($filename))
					continue;
				if (isset($all_images[$filename])) {
					unset($all_images[$filename]);
					print "<tr><form method='post'><input type='hidden' name='filename' value='".$filename."'><td>".basename($filename)."</td>";
					print "<td style='background-color: black'><img width=100px src='$filename'>";
					print "</td>";
					print "<td style='background-color: white'><img width=100px src='$filename'>";
					print "</td>";
					if ($img_array['black'] == true ) $black = 'checked'; else $black = ""; 
					if ($img_array['white'] == true ) $white = 'checked'; else $white = ""; 
					if ($img_array['other'] == true ) $other = 'checked'; else $other = ""; 
					print "<td>Black? <input type='checkbox' name='black' value='true' $black></td>";
					print "<td>White? <input type='checkbox' name='white' value='true' $white></td>";
					print "<td>Other? <input type='checkbox' name='other' value='true' $other></td>";
				} else {
					// File missing. 
					print "<tr><td>".basename($filename)."</td>";
					print "<td colspan=5>Image missing</td>\n";
				}
				print "<td>Assign to Team: <select name='teamname'>";
				foreach ($teams as $name => $teamsel) { 
					print "<option value='$name'>".$teamsel['description']."</option>\n"; }
				print "</select></td>";

				print "<td><input type='submit' name='action' value='Update' /></td>";
				print "<td><input type='submit' name='action' value='Remove' /></td>";
				print "</form></tr>\n";	
			}
			print "</table>\n";
		}
		if ($all_images != null) { // There's files left over.
			print "<h2>New Files</h2>\n";	
			print "<form method='post'><input type='text' name='newteamname' value='New Team Name'><input type='submit' value='New Team' /></form>\n";
			print "<table>";
			foreach ($all_images as $filename) {
				print "<tr><form method='post'><input type='hidden' name='filename' value='".$filename."'><td>".basename($filename)."</td>";
				print "<td style='background-color: black'><img width=100px src='$filename'>";
				print "</td>";
				print "<td style='background-color: white'><img width=100px src='$filename'>";
				print "</td>";
				print "<td>Black? <input type='checkbox' name='black' value='true'></td>";
				print "<td>White? <input type='checkbox' name='white' value='true'></td>";
				print "<td>Other? <input type='checkbox' name='other' value='true'></td>";
				print "<td>Assign to Team: <select name='teamname'>";
				foreach ($teams as $name => $teamsel) { print "<option value='$name'>".$teamsel['description']."</option>\n"; }
				print "</select></td>";
				print "<td><input type='submit' name='action' value='Add' /></td></form></tr>";
			}
			
		}
		print "</form>\n";

	}

	private function explodeTeam($dir) {
		// Figure out everything we already know about the league.
		// I'm just going to assume the the directory seperator is '/'. This is windows unsafe.
		$parts = explode("/", $dir);

		// If first directory entry is ".", ignore it.
		if ($parts[0] == ".")
			array_shift($parts);
		$ret['leaguename'] = basename($dir);
		$ret['country'] = $parts[0];
		if (isset($parts[2])) {
			// This country has States/Counties.
			$ret['state'] = $parts[1];
			$ret['league'] = $parts[2];
		} else {
			// No States/Counties.
			$ret['league'] = $parts[1];
			$ret['state'] = "";
		}
		return $ret;
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
		$leaguearray = $this->explodeTeam($dir);

		// Really basic .status file
		$this->status = array_merge($this->defaults, array ("League" => $leaguearray['league'], "Country" => $leaguearray['country'], "Region" => $leaguearray['state']));
		$this->updateStatusFile($dir);

		// Create a default team
		$this->addTeam("default", "Default Team");

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
			return false;

		$logo_array = array ( 'filename' => $image, 'name' => $image, 'black' => false, 'white' => false, 'other' => false);
		$this->status['teams'][$teamname]['logos'][] = $logo_array;
		$this->updateStatusFile();
	}

	function addTeam($teamname, $description) {
		if (isset($this->status['teams'][$teamname]))
			return false;
		print "Here\n";
		$this->status['teams'][$teamname]['description'] = $this->cleanHTML($description);
		$this->updateStatusFile();
	}

	function doRenameTeam($teamname, $newname) {
		if (!isset($this->status['teams'][$teamname]))
			return false;
		$this->status['teams'][$teamname]['description'] = $this->cleanHTML($newname);
		$this->updateStatusFile();
	}

	function getTeams() {
		return ($this->status['teams']);
	}

	function updateData($req) {
		// Has anything been given to us?
		if (!isset($req['action']))
			return true;
		// We have stuff to do.
		switch($req['action']) {
			case 'Add':
				$this->addFoundLogo($req);
				break;
			case 'Remove':
				$this->removeLogo($req);
				break;
			case 'Update':
				$this->updateLogo($req);
				break;
			case 'Rename': 
				$this->renameTeam($req);
				break;
			case 'Create':
				$this->createTeam($req);
				break;
			case 'Delete':
		}
	}

	function addFoundLogo($req) {
		// Someone's clicked on 'Add' to add a new file.
		// Make sure that everything's sane.
		foreach (array ('dir', 'filename', 'teamname', 'black', 'white', 'other') as $var) {
			$$var = isset($req[$var])?$this->checkFilename($req[$var]):null;
		}
		if ($dir == null || $filename == null || $teamname == null)
			return false;
		
		$this->getStatus($dir);
		
		// Add a logo to the required team.
		$this->addImageToTeam($teamname, $filename);

		$this->updateLogoUsage($filename, "black", $black);
		$this->updateLogoUsage($filename, "white", $white);
		$this->updateLogoUsage($filename, "other", $other);
	}

	function updateLogoUsage($filename, $colour, $state) {
		if ($state == "" || $state == null)
			$state = false;
		else
			$state = true;
		
		// Find the file as part of the team.
		foreach ($this->status['teams'] as $teamkey => $team) {
			foreach ($team['logos'] as $logokey => $logo) {
				if ($logo['filename'] == $filename)
					$this->status['teams'][$teamkey]['logos'][$logokey][$colour] = $state;
			}
		}
		$this->updateStatusFile();
	}

	function removeLogo($req) {

		foreach (array ('dir', 'filename', 'teamname', 'black', 'white', 'other') as $var) {
			$$var = isset($req[$var])?$this->checkFilename($req[$var]):null;
		}
		if ($dir == null || $filename == null || $teamname == null)
			return false;

		$this->doRemoveLogo($dir, $filename);
	}

	function doRemoveLogo($dir, $filename) {

		$this->getStatus($dir);

		// We've been asked to remove a logo.
		foreach ($this->status['teams'] as $teamkey => $team) {
			foreach ($team['logos'] as $logokey => $logo) {
				if ($logo['filename'] == $filename) {
					unset($this->status['teams'][$teamkey]['logos'][$logokey]);
					// Only do it once. 
					$this->updateStatusFile();
					return true;
				}
			}
		}
	}

	function updateLogo($req) {
		foreach (array ('dir', 'filename', 'teamname', 'black', 'white', 'other') as $var) {
			$$var = isset($req[$var])?$this->checkFilename($req[$var]):null;
		}
		if ($dir == null || $filename == null || $teamname == null)
			return false;

		// Delete it.
		$this->doRemoveLogo($dir, $filename);
		
		// Add it to a team. 
		$this->addImageToTeam($teamname, $filename);

		// Set the colours
		$this->updateLogoUsage($filename, "black", $black);
		$this->updateLogoUsage($filename, "white", $white);
		$this->updateLogoUsage($filename, "other", $other);

		$this->updateStatusFile();

	}

	function renameTeam($req) {
		// Not QUITE as easy as the previous ones.
		$dir = isset($req['dir'])?$this->checkFilename($req['dir']):null;
		$teamname = isset($req['teamname'])?$req['teamname']:null;
		$newname = isset($req[$teamname])?$req[$teamname]:null;

		if ($dir == null || $teamname == null || $newname == null) 
			return false;

		$this->getStatus($dir);

		$this->doRenameTeam($teamname, $newname); 

		$this->updateStatusFile();
	}

}
