<?php
	class @@CLASSNAME@@ {
		public $name = "ShoutcastConfig";
		
		public function rawEvent($name, $data) {
			$connection = $data[0];
			$ex = $data[2];
			$data = $data[1];
			Logger::info($data);
		}
		
		public function isInstantiated() {
			EventHandling::registerForEvent("rawEvent", $this, "rawEvent");
			return true;
		}
	}
?>