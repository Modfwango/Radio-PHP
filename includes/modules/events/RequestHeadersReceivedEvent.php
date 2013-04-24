<?php
	class @@CLASSNAME@@ {
		public $name = "RequestHeadersReceivedEvent";
		private $metadata = array();
		
		public function preprocessEvent($name, $registrations, $connection, $data) {
			$preprocessors = $registrations[1];
			$registrations = $registrations[0];
			
			if (!isset($this->metadata[$connection->getID()])) {
				$this->metadata[$connection->getID()] = array();
			}
			
			if (!isset($this->metadata[$connection->getID()]['request']) || !isset($this->metadata[$connection->getID()]['headerslines'])) {
				$this->metadata[$connection->getID()]['request'] = false;
				$this->metadata[$connection->getID()]['headerslines'] = array();
			}
			
			if ($this->metadata[$connection->getID()]['request'] == false) {
				$this->metadata[$connection->getID()]['headerlines'][] = trim($data);
				
				$get = false;
				$icymeta = false;
				foreach ($this->metadata[$connection->getID()]['headerlines'] as $headerline) {
					if (preg_match("/^GET \\/ HTTP\\/1\\.(0|1)$/i", $headerline)) {
						$get = true;
					}
					elseif (preg_match("/^Icy-MetaData:\s?1$/i", $headerline)) {
						$icymeta = true;
					}
				}
				
				if ($get == true && $icymeta == true) {
					$this->metadata[$connection->getID()]['request'] = true;
					foreach ($registrations as $id => $registration) {
						EventHandling::triggerEvent($name, $id, array($connection, trim(implode("\n", $this->metadata[$connection->getID()]['headerlines']))));
					}
					unset($this->metadata[$connection->getID()]['headerlines']);
				}
			}
		}
		
		public function isInstantiated() {
			EventHandling::createEvent("requestHeadersReceivedEvent", $this, "preprocessEvent");
			return true;
		}
	}
?>