<?php
	class Connection {
		private $socket = null;
		private $id = null;
		
		public function __construct($socket) {
			if (is_resource($socket)) {
				$this->socket = $socket;
				return true;
			}
			return false;
		}
		
		public function disconnect() {
			if (is_resource($this->socket)) {
				@socket_shutdown($this->socket);
				@socket_close($this->socket);
				$this->socket = null;
				return true;
			}
			return false;
		}
		
		public function getIP() {
			if (is_resource($this->socket)) {
				return gethostbyname($this->socket);
			}
			return false;
		}
		
		public function getHost() {
			if (is_resource($this->socket)) {
				return gethostbyaddr($this->socket);
			}
			return false;
		}
		
		public function getData() {
			if (is_resource($this->socket)) {
				if (($buf = @socket_read($this->socket, 8192)) === false && socket_last_error($this->socket) != 11) {
					$this->disconnect();
				}
				else {
					$data = trim($buf);
					if ($data != false && strlen($data) > 0) {
						Logger::debug("Data received from client:  '".$data."'");
						return $data;
					}
				}
			}
			return false;
		}
		
		public function getID() {
			return $this->id;
		}
		
		public function setID($id) {
			if ($this->id == null && is_numeric($id)) {
				$this->id = intval($id);
				return true;
			}
			return false;
		}
		
		public function send($data) {
			if (is_resource($this->socket)) {
				Logger::debug("Sending data to client:  '".$data."'");
				if ($newline == true) {
					$status = @socket_write($this->socket, $data."\n"); // Send data
				}
				else {
					$status = @socket_write($this->socket, $data); // Send data
				}
			
				if ($status === false) {
					$this->disconnect();
				}
				else {
					return true;
				}
			}
			return false;
		}
	}
?>
