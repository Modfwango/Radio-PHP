<?php
	class @@CLASSNAME@@ {
		public $name = "ShoutcastStream";
		private $clients = array();
		
		public function connectionLoopEnd($name, $data) {
			$config = ModuleManagement::getModuleByName("ShoutcastConfig")->getConfig();
			$length = intval(((($config['bitrate'] / 8) + 4) * 1024) / (1000000 / __INTERVAL__));
			$chunk = ModuleManagement::getModuleByName("ShoutcastBuffer")->getNextChunk();
			
			foreach ($this->clients as $id => &$client) {
				if (isset($chunk[0]) && strlen($chunk[0]) > 0) {
					while (strlen($chunk[0]) < $length) {
						$chunk[0] .= chr(0);
					}
					if ($client[1] == true) {
						ConnectionManagement::getConnectionByID($client[0])->send($chunk[0].$chunk[1], false);
					}
					else {
						ConnectionManagement::getConnectionByID($client[0])->send($chunk[0], false);
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
