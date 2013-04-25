<?php
	class EventHandling {
		private static $events = array();
		
		public static function createEvent($name, $module, $callback) {
			if (method_exists($module, $callback) && !isset(self::$events[$name])) {
				Logger::debug("Event '".$name."' with callback '".$callback."' created.");
				self::$events[$name] = array($module, $callback, array(), array());
				return true;
			}
			return false;
		}
		
		public static function destroyEvent($name) {
			if (isset(self::$events[$name])) {
				Logger::debug("Event '".$name."' destroyed.");
				unset(self::$events[$name]);
				return true;
			}
			return false;
		}
		
		public static function getEvents() {
			return self::$events;
		}
		
		public static function receiveData($connection, $data) {
			foreach (self::$events as $key => $event) {
				if (count($event[2]) > 0) {
					Logger::debug("Event '".$key."' is being preprocessed.");
					$event[0]->$event[1]($key, array($event[2], $event[3]), $connection, $data);
					Logger::debug("Event '".$key."' has been preprocessed.");
				}
			}
			return true;
		}
		
		public static function registerForEvent($name, $module, $callback, $data = null) {
			if (isset(self::$events[$name]) && method_exists($module, $callback)) {
				Logger::debug("Module '".$module->name."' registered for event '".$name."'");
				self::$events[$name][2][] = array($module, $callback, $data);
				return true;
			}
			return false;
		}
		
		public static function registerAsEventPreprocessor($name, $module, $callback, $data = null) {
			if (isset(self::$events[$name]) && method_exists($module, $callback)) {
				Logger::debug("Module '".$module->name."' registered as an event preprocessor for '".$name."'");
				self::$events[$name][3][] = array($module, $callback, $data);
				return true;
			}
			return false;
		}
		
		public static function triggerEvent($name, $id, $data = null) {
			if (isset(self::$events[$name])) {
				$registration = self::$events[$name][2][$id];
				if (method_exists($registration[0], $registration[1])) {
					//if ($name != "connectionLoopEnd") {
						Logger::debug("Event '".$name."' has been triggered for '".$registration[0]->name."'");
					//}
					$registration[0]->$registration[1]($name, $data);
				}
				return true;
			}
			return false;
		}
		
		public static function unregisterForEvent($name, $module) {
			if (isset(self::$events[$name])) {
				foreach (self::$events[$name][2] as $key => $registration) {
					if ($registration[0]->name == $module->name) {
						Logger::debug("Module '".$module->name."' unregistered for event '".$name."'");
						unset(self::$events[$name][2][$key]);
						return true;
					}
				}
			}
			return false;
		}
		
		public static function unregisterModule($module) {
			foreach (self::$events as $key => $event) {
				self::unregisterForEvent($key, $module);
			}
			return true;
		}
	}
?>