<?php
	class @@CLASSNAME@@ {
		public $name = "ShoutcastSource";
		private $lastPlayed = null;
		private $lastFullPlaylist = array();
		private $playlist = array();
		
		private function loadPlaylist() {
			foreach ($this->playlist as $key => $item) {
				if (!file_exists($item)) {
					unset($this->playlist[$key]);
				}
			}
			
			$config = ModuleManagement::getModuleByName("ShoutcastConfig")->getConfig();
			if (count($this->playlist) > 0) {
				$fullPlaylist = array_unique(array_merge($this->lastFullPlaylist, $this->directoryToArray($config['directory'], true)), SORT_STRING);
				foreach ($fullPlaylist as $key => $item) {
					if (!file_exists($item)) {
						unset($fullPlaylist[$key]);
					}
				}
				$newSongs = array_diff($fullPlaylist, array_intersect($this->lastFullPlaylist, $fullPlaylist));
				$this->lastFullPlaylist = $fullPlaylist;
				$this->playlist = array_merge($this->playlist, $newSongs);
			}
			else {
				$fullPlaylist = $this->directoryToArray($config['directory'], true);
				$this->lastFullPlaylist = $fullPlaylist;
				$this->playlist = $fullPlaylist;
			}
		}
		
		public function loadSong() {
			$this->loadPlaylist();
			$rand = array_rand($this->playlist, 1);
			$song = $this->playlist[$rand];
			unset($this->playlist[$rand]);
			return $song;
		}
		
		private function directoryToArray($directory, $recursive) {
			$array_items = array();
			if ($handle = opendir($directory)) { //
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != "..") {
						if (is_dir($directory."/".$file)) {
							if ($recursive) {
								$array_items = array_merge($array_items, $this->directoryToArray($directory."/".$file, $recursive));
							}
						}
						else {
							$file = $directory."/".$file;
							$array_items[] = preg_replace("/\/\//si", "/", $file);
						}
					}
				}
				closedir($handle);
			}
			return $array_items;
		}
		
		public function isInstantiated() {
			return true;
		}
	}
?>