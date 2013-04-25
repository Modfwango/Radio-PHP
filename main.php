<?php
	/* Show all errors. */
	error_reporting(E_ALL);
	ini_set("display_errors", 1);
	
	/* Make sure the path to the project root is alphanumeric, including the / and . characters. */
	if (!preg_match("/^[a-zA-Z0-9\\/.]+$/", dirname(__FILE__))) {
		die("The full path to this file must match this regular expression:\n^[a-zA-Z0-9\\/.]+$\n");
	}
	
	/* Define the root of the project folder. */
	define("__PROJECTROOT__", dirname(__FILE__));
	
	/* Define start time to allow some fancy uptime module features and whatnot */
	define("__STARTTIME__", time());
	
	/* Define the debug constant to allow the logger to be aware of the current logging state */
	define("__DEBUG__", false);
	
	/* Define the interval at which we sleep between each main loop.  Use millionths of a second. */
	define("__INTERVAL__", 500000);
	
	require_once(__PROJECTROOT__."/includes/connection.php");
	require_once(__PROJECTROOT__."/includes/connectionManagement.php");
	require_once(__PROJECTROOT__."/includes/eventHandling.php");
	require_once(__PROJECTROOT__."/includes/logger.php");
	require_once(__PROJECTROOT__."/includes/moduleManagement.php");
	require_once(__PROJECTROOT__."/includes/socket.php");
	require_once(__PROJECTROOT__."/includes/socketManagement.php");
	require_once(__PROJECTROOT__."/includes/storageHandling.php");
	
	/* Events must be loaded first since some modules depend on them being available. */
	foreach (explode("\n", trim(file_get_contents(__PROJECTROOT__."/conf/modules.conf"))) as $module) {
		$module = trim($module);
		if (strlen($module) > 0) {
			ModuleManagement::loadModule($module);
		}
	}
	
	/* Load configured sockets. */
	foreach (explode("\n", trim(file_get_contents(__PROJECTROOT__."/conf/listen.conf"))) as $sock) {
		$sock = trim($sock);
		if (strlen($sock) > 0) {
			$sock = explode(",", $sock);
			if (count($sock) == 2) {
				$sock = new Socket($sock[0], $sock[1]);
				if ($sock != false) {
					SocketManagement::newSocket($sock);
				}
				else {
					Logger::debug("Could not bind to address.");
				}
			}
		}
	}
	
	/* Don't edit below this line unless you know what you're doing. */
	while (true) {
		foreach (SocketManagement::getSockets() as $socket) {
			$socket->accept();
		}
		
		foreach (ConnectionManagement::getConnections() as $connection) {
			$data = $connection->getData();
			if ($data != false) {
				EventHandling::receiveData($connection, $data);
			}
		}
		
		foreach (EventHandling::getEvents() as $key => $event) {
			if ($key == "connectionLoopEnd") {
				foreach ($event[2] as $id => $registration) {
					EventHandling::triggerEvent("connectionLoopEnd", $id);
				}
			}
		}
		usleep(__INTERVAL__);
	}
	
	function boolval($input) {
		if (trim($input) == "true") {
			return true;
		}
		return false;
	}
?>