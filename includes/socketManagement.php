<?php
	class SocketManagement {
		private static $sockets = array();
		
		public static function newSocket($socket) {
			if (is_object($socket) && get_class($socket) == "Socket") {
				self::$sockets[] = $socket;
				Logger::debug("New socket added to the socket manager.");
				return true;
			}
			return false;
		}
		
		/*public static function getSocketByNetworkName($name) { // This will need to be changed to something else.
			foreach (self::$sockets as $socket) {
				if (strtolower(trim($name)) == strtolower(trim($socket->getNetworkName()))) {
					return $socket;
				}
			}
			return false;
		}*/
		
		public static function getSockets() {
			return self::$sockets;
		}
	}
?>