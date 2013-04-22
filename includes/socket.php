<?php
	class Socket {
		private $socket = null;
		private $host = null;
		private $port = null;
		
		public function __construct($host, $port) {
			if (is_string($host) && is_numeric($port)) {
				$this->host = $host;
				$this->port = $port;
				$this->socket = socket_create(AF_INET, SOCK_STREAM, 0);
				if (@socket_bind($this->socket, $this->host, $this->port)) {
					socket_listen($this->socket);
					socket_set_nonblock($this->socket);
				}
			}
			return false;
		}
		
		public function accept() {
			$client = @socket_accept($this->socket);
			if ($client != false) {
				socket_set_nonblock($client);
				ConnectionManagement::newConnection(new Connection($client));
				return true;
			}
			return false;
		}
	}
?>