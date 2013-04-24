<?php
	class ConnectionManagement {
		private static $connections = array();
		
		public static function newConnection($connection) {
			if (is_object($connection) && get_class($connection) == "Connection" && $connection->configured() == true) {
				self::$connections[] = $connection;
				
				end(self::$connections);
				$i = key(self::$connections);
				
				self::$connections[$i]->setID($i);
				Logger::info("Connection #".$i." added to the connection manager.");
				return true;
			}
			return false;
		}
		
		public static function getConnectionByID($id) {
			if (isset(self::$connections[$id])) {
				return self::$connections[$id];
			}
			return false;
		}
		
		public static function getConnections() {
			return self::$connections;
		}
	}
?>