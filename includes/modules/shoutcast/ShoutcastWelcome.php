<?php
	class @@CLASSNAME@@ {
		public $name = "ShoutcastWelcome";
		
		public function requestHeadersReceived($name, $data) {
			$connection = ConnectionManagement::getConnectionByID($data[0]);
			$request = $data[1];
			$headers = $data[2];
			
			Logger::info("Welcoming new client at ".$connection->getHost()." (".$connection->getIP().")");
			$config = ModuleManagement::getModuleByName("ShoutcastConfig")->getConfig();
			
			$welcome = array();
			$welcome[] = "HTTP/1.0 200 OK";
			$welcome[] = "Content-Type: audio/mpeg";
			$welcome[] = "Server: Radio-PHP (Based off Modfwango-Server) https://github.com/clayfreeman/Modfwango-Server";
			$welcome[] = "Cache-Control: no-cache";
			$welcome[] = "icy-br: ".$config['bitrate'];
			$welcome[] = "icy-description: ".$config['description'];
			$welcome[] = "icy-genre: ".$config['genre'];
			$welcome[] = "icy-name: ".$config['name'];
			$welcome[] = "icy-pub: -1";
			$welcome[] = "icy-url: ".$config['url'];
			$meta = false;
			if (isset($headers['icymetadata']) && trim($headers['icymetadata']) == '1') {
				$meta = true;
				$welcome[] = "icy-metaint: ".intval(((($config['bitrate'] / 8) + 4) * 1024) / (1000000 / __INTERVAL__));
			}
			$welcome[] = null;
			
			foreach ($welcome as $line) {
				$connection->send($line);
			}
			
			if (ModuleManagement::getModuleByName("ShoutcastStream")->addClient(array($connection->getID(), $meta))) {
				Logger::info("Client added to stream listeners.");
			}
			else {
				Logger::info("Unable to add client to stream listeners.");
			}
		}
		
		public function isInstantiated() {
			EventHandling::registerForEvent("requestHeadersReceivedEvent", $this, "requestHeadersReceived");
			return true;
		}
	}
?>
