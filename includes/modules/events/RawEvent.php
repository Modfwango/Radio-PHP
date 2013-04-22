<?php
	class @@CLASSNAME@@ {
		public $name = "RawEvent";
		
		public function preprocessEvent($name, $registrations, $connection, $data) {
			$preprocessors = $registrations[1];
			$registrations = $registrations[0];
			$ex = explode(" ", $data);
				
			foreach ($registrations as $id => $registration) {
				EventHandling::triggerEvent($name, $id, array($connection, $data, $ex));
			}
		}
		
		public function isInstantiated() {
			EventHandling::createEvent("rawEvent", $this, "preprocessEvent");
			return true;
		}
	}
?>