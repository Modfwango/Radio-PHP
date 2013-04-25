<?php
	class @@CLASSNAME@@ {
		public $name = "RequestHeadersReceivedEvent";
		private $metadata = array();
		
		public function preprocessEvent($name, $registrations, $connection, $data) {
			$preprocessors = $registrations[1];
			$registrations = $registrations[0];
			
			$chunk = explode("\n", str_ireplace("\r", null, $data));
			
			foreach ($chunk as $data) {
				$data = trim($data);
				if (!isset($this->metadata[$connection->getID()])) {
					$this->metadata[$connection->getID()] = array();
				}
			
				if (!isset($this->metadata[$connection->getID()]['headerlines'])) {
					$this->metadata[$connection->getID()]['headerlines'] = array();
				}
			
				if ($data != null && in_array(null, $data)) {
					if (!in_array(null, $data)) {
						$this->metadata[$connection->getID()]['headerlines'][] = $data;
					}
				}
				elseif (in_array(null, $data)) {
					$headers = array();
					foreach ($this->metadata[$connection->getID()]['headerlines'] as $id => $line) {
						if (trim($line) == null) {
							unset($this->metadata[$connection->getID()]['headerlines'][$id]);
						}
						else {
							if (stristr($line, ":")) {
								$val = explode(":", $line);
								if (count($val) > 2) {
									$tmp = $val[0];
									unset($val[0]);
									$val = array($tmp, implode(":", $val));
									unset($tmp);
								}
								
								$headers[strtolower(preg_replace("[^a-zA-Z0-9]", null, $val[0]))] = $val[1];
								unset($val);
							}
						}
					}
					
					foreach ($registrations as $id => $registration) {
						EventHandling::triggerEvent($name, $id, array($connection->getID(), $this->metadata[$connection->getID()]['headerlines'], $headers));
					}
					unset($this->metadata[$connection->getID()]);
				}
			}
		}
		
		public function isInstantiated() {
			EventHandling::createEvent("requestHeadersReceivedEvent", $this, "preprocessEvent");
			return true;
		}
	}
?>