<?php
	class @@CLASSNAME@@ {
		public $name = "Timer";
		private $timers = array();
		
		public function connectionLoopEnd() {
			foreach ($this->timers as $id => $timer) {
				if ($timer != null && $timer["runtime"] <= microtime(true)) {
					if (isset($class->name)) {
						Logger::debug("Processing timer for '".$class->name."->".$callback."()'");
					}
					else {
						Logger::debug("Processing timer for '".$callback."()'");
					}
					$class = $timer["class"];
					$callback = $timer["callback"];
					
					$class->$callback($timer["params"]);
					
					$this->timers[$id] = null;
				}
			}
			return true;
		}
		
		public function newTimer($dtime, $class, $callback, $params) {
			if (is_numeric($dtime) && $dtime > -1 && is_object($class) && method_exists($class, $callback)) {
				$i = 1;
				while (isset($this->timers[$i])) {
					$i++;
				}
				
				$this->timers[$i] = array(
					"runtime" => (microtime(true) + $dtime),
					"class" => $class,
					"callback" => $callback,
					"params" => $params
				);
				
				if (isset($class->name)) {
					Logger::debug("Timer created for '".$class->name."->".$callback."()' for ".$dtime." seconds.");
				}
				else {
					Logger::debug("Timer created for '".$callback."()' for ".$dtime." seconds.");
				}
				
				return $i;
			}
			return false;
		}
		
		public function preprocessEvent($name, $registrations, $connection, $data) {
			return true;
		}
		
		public function isInstantiated() {
			EventHandling::createEvent("connectionLoopEnd", $this, "preprocessEvent");
			EventHandling::registerForEvent("connectionLoopEnd", $this, "connectionLoopEnd");
			return true;
		}
	}
?>
