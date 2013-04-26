<?php
	class @@CLASSNAME@@ {
		public $name = "ShoutcastBuffer";
		private $currentSong = null;
		private $mediaprocess = null;
		private $mediapipes = null;
		private $seenBytes = false;
		
		public function getNextChunk() {
			$chunk = null;
			if ($this->mediapipes != null) {
				$config = ModuleManagement::getModuleByName("ShoutcastConfig")->getConfig();
				$payload = "StreamTitle='".$this->currentSong."';";
				$metalength = ceil(strlen($payload) / 16);
				$metadata = chr($metalength).$payload;
				if (strlen($payload) < ($metalength * 16)) {
					$metadata .= str_repeat(chr(0), (($metalength * 16) - strlen($payload)));
				}
				if (ord(substr($metadata, 0, 1)) != (strlen($metadata) - 1)) {
					Logger::info("Metadata did not pass the parse check.  It is not going to be sent in this chunk.  The size byte does not match ".(strlen($metadata) - 1).".  Raw data:  [{".ord(substr($metadata, 0, 1))."}]".substr($metadata, 1));
					$metadata = chr(0);
				}
				$data = null;
				$length = intval(((($config['bitrate'] / 8) + 1) * 1024) / (1000000 / __INTERVAL__));
				Logger::debug("Reading stream until ".$length." bytes.");
				for ($i = 0; $i < $length; $i++) {
					$buf = fread($this->mediapipes[1], 1);
					$data .= $buf;
				}
				
				if (strlen($data) > 0) {
					$this->seenBytes = true;
					Logger::debug("Chunk with ".strlen($data)." bytes returned.  MD5:  ".hash("md5", $data));
					$chunk = array($data, $metadata);
				}
				else {
					if ($this->seenBytes == true) {
						Logger::info("End of song.  Switching to a new song.");
						$this->currentSong = null;
						$this->mediaprocess = null;
						$this->mediapipes = null;
						$this->seenBytes = false;
						$this->transcode(ModuleManagement::getModuleByName("ShoutcastSource")->loadSong());
						$chunk = $this->getNextChunk();
					}
				}
			}
			else {
				$this->transcode(ModuleManagement::getModuleByName("ShoutcastSource")->loadSong());
				$chunk = $this->getNextChunk();
			}
			return $chunk;
		}
		
		private function transcode($song) {
			$this->currentSong = explode('.', basename($song));
			unset($this->currentSong[count($this->currentSong) - 1]);
			$this->currentSong = implode('.', $this->currentSong);
			
			Logger::info("Loading song:  ".$this->currentSong);
			$config = ModuleManagement::getModuleByName("ShoutcastConfig")->getConfig();
			$descriptorspec = array(
				0 => array("pipe", "r"),
				1 => array("pipe", "w"),
				2 => array("pipe", "a")
			);
			$cmd = "avconv -v quiet -i ".escapeshellarg($song)." -c libmp3lame -ar ".$config['samplerate']." -ab ".$config['bitrate']."k -f mp3 -";
			$this->mediaprocess = proc_open($cmd, $descriptorspec, $this->mediapipes);
			stream_set_blocking($this->mediapipes[0], 0); //
			stream_set_blocking($this->mediapipes[1], 0); //
			stream_set_blocking($this->mediapipes[2], 0); //
		}
		
		public function isInstantiated() {
			return true;
		}
	}
?>