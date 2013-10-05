<?php
	class @@CLASSNAME@@ {
		public $name = "ShoutcastStream";
		private $clients = array();
		private $offset = 0;
		private $temp = null;
		
		public function connectionLoopEnd($name, $data) {
			$config = ModuleManagement::getModuleByName("ShoutcastConfig")->getConfig();
			$length = intval(((($config['bitrate'] / 8) + 1) * 1024) / (1000000 / __INTERVAL__));
			$chunk = ModuleManagement::getModuleByName("ShoutcastBuffer")->getNextChunk();
			if ($offset > 0) {
				$chunk[0] = $this->temp.$chunk[0];
				$this->temp = substr($chunk[0], $length);
				$chunk[0] = substr($chunk[0], 0, $length);
			}
			
			foreach ($this->clients as $id => &$client) {
				if (isset($chunk[0]) && strlen($chunk[0]) > 0) {
					if (strlen($chunk[0]) < $length) {
						$this->offset = $length - $chunk[0];
						$this->temp = $chunk[0];
					}
					else {
						if ($client[1] == true) {
							ConnectionManagement::getConnectionByID($client[0])->send($chunk[0].$chunk[1], false);
						}
						else {
							ConnectionManagement::getConnectionByID($client[0])->send($chunk[0], false);
						}
					}
				}
			}
		}
		
		public function addClient($connection) {
			if (is_array($connection) && is_numeric($connection[0]) && is_bool($connection[1])) {
				if ($connection[1] == true) {
					Logger::info("Client will receive metadata.");
				}
				else {
					Logger::info("Client will not receive metadata.");
				}
				$this->clients[] = $connection;
				return true;
			}
			return false;
		}
		
		public function isInstantiated() {
			EventHandling::registerForEvent("connectionLoopEnd", $this, "connectionLoopEnd");
			return true;
		}
	}
?>