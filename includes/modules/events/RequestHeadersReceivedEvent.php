<?php
	class @@CLASSNAME@@ {
		public $name = "RequestHeadersReceivedEvent";
		
		public function preprocessEvent($name, $registrations, $connection, $data) {
			$preprocessors = $registrations[1];
			$registrations = $registrations[0];
			
			if (!isset($connection->metadata['request']) || !isset($connection->metadata['headerslines'])) {
				$connection->metadata['request'] = false;
				$connection->metadata['headerslines'] = array();
			}
			
			if ($connection->metadata['request'] == false) {
				$connection->metadata['headerlines'][] = trim($data);
				
				$get = false;
				$icymeta = false;
				foreach ($connection->metadata['headerlines'] as $headerline) {
					if (preg_match("/^GET \\/ HTTP\\/1\\.(0|1)$/i", $headerline)) {
						$get = true;
					}
					elseif (preg_match("/^Icy-MetaData:\s?1$/i", $headerline)) {
						$icymeta = true;
					}
				}
				
				if ($get == true && $icymeta == true) {
					$connection->metadata['request'] = true;
					foreach ($registrations as $id => $registration) {
						EventHandling::triggerEvent($name, $id, array($connection, trim(implode("\n", $connection->metadata['headerlines']))));
					}
					unset($connection->metadata['headerlines']);
				}
			}
		}
		
		public function isInstantiated() {
			EventHandling::createEvent("requestHeadersReceivedEvent", $this, "preprocessEvent");
			return true;
		}
	}
?>