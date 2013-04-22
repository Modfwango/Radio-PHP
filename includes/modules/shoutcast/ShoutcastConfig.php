<?php
	class @@CLASSNAME@@ {
		public $name = "ShoutcastConfig";
		
		public function getConfig() {
			return parse_ini_string(StorageHandling::loadFile($this, "shoutcast.conf"), true);
		}
		
		public function isInstantiated() {
			return true;
		}
	}
?>