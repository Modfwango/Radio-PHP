<?php
	class ConnectionManagement {
		private static $connections = array();
		
		public static function newConnection($connection) {
			if (is_object($connection) && get_class($connection) == "Connection") {
				self::$connections[] = $connection;
				Logger::debug("New connection added to the connection manager.");
				return true;
			}
			return false;
		}
		
		/*public static function getConnectionByNetworkName($name) { // This will need to be changed to something else.
			foreach (self::$connections as $connection) {
				if (strtolower(trim($name)) == strtolower(trim($connection->getNetworkName()))) {
					return $connection;
				}
			}
			return false;
		}*/
		
		public static function getConnections() {
			return self::$connections;
		}
	}
?>