<?php
	class Connection {
		private $socket = null;
		public $metadata = array();
		
		public function __construct($socket) {
			if (is_resource($socket)) {
				$this->socket = $socket;
				$this->constructed = true;
				return true;
			}
			return false;
		}
		
		public function kill() {
			Logger::info("Malfunction while sending/receiving data.  Terminating connection.  Error:  ".socket_last_error($this->socket));
			socket_shutdown($this->socket);
			socket_close($this->socket);
			return true;
		}
		
		public function getIP() {
			socket_getpeername($this->socket, $address);
			return gethostbyname($address);
		}
		
		public function getHost() {
			socket_getpeername($this->socket, $address);
			return gethostbyaddr($address);
		}
		
		public function getData() {
			$status = @socket_read($this->socket, 8192);
			
			if (is_resource($this->socket) && $status === false) {
				$this->kill();
			}
			else {
				$data = trim($status);
				if ($data != false && strlen($data) > 0) {
					Logger::debug("Data received from client:  '".$data."'");
					return $data;
				}
			}
			return false;
		}
		
		public function send($data, $newline = true) {
			Logger::debug("Sending data to client:  '".$data."'");
			if ($newline == true) {
				$status = @socket_write($this->socket, $data."\n"); // Send data
			}
			else {
				$status = @socket_write($this->socket, $data); // Send data
			}
			
			if (is_resource($this->socket) && $status === false) {
				$this->kill();
				return false;
			}
			return true;
		}
	}
?>