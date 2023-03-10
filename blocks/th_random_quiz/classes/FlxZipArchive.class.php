<?php

class th_random_quiz_FlxZipArchive extends ZipArchive {
	public function addDir($location, $name) {
		$this->addEmptyDir($name);
		$this->addDirDo($location, $name);
	}
	
	private function addDirDo($location, $name) {
		$name .= '/';
		$location .= '/';
		$dir = opendir($location);
		while ($file = readdir($dir)) {
			if ($file == '.' || $file == '..') {
				continue;
			}

			// if ($file != '.docx'){
			$do = (filetype($location . $file) == 'dir') ? 'addDir' : 'addFile';
			$this->$do($location . $file, $name . $file);
			// }
		}
	}
}
?>