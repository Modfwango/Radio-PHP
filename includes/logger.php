<?php
	class Logger {
		public static function debug($msg) {
			if (__DEBUG__ == true) {
				echo " [ DEBUG ] ".trim($msg)."\n";
			}
		}
		
		public static function info($msg) {
			echo " [ INFO ]  ".trim($msg)."\n";
		}
	}
?>