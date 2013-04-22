<?php
	class @@CLASSNAME@@ {
		public $name = "ShoutcastStream";
		private $clients = array();
		private $lastSentTime = 0;
		
		public function connectionLoopEnd($name, $data) {
			$config = ModuleManagement::getModuleByName("ShoutcastConfig")->getConfig();
			$length = intval(((($config['bitrate'] / 8) + 1) * 1024) / (1000000 / __INTERVAL__));
			$chunk = ModuleManagement::getModuleByName("ShoutcastBuffer")->getNextChunk();
			
			foreach ($this->clients as $id => &$client) {
				if (isset($chunk[0]) && strlen($chunk[0]) > 0) {
					while (strlen($chunk[0]) < $length) {
						$chunk[0] .= chr(0);
					}
					$client->send($chunk[0].$chunk[1], false);
				}
			}
		}
		
		public function addClient($connection) {
			if (is_object($connection)) {
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