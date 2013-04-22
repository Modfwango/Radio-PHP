<?php
	class ModuleManagement {
		private static $modules = array();
		
		public static function isLoaded($name) {
			foreach (self::$modules as $module) {
				if (strtolower($module->name) == strtolower($name)) {
					return true;
				}
			}
			return false;
		}
		
		public static function getModuleByName($name) {
			foreach (self::$modules as $module) {
				if (strtolower($module->name) == strtolower($name)) {
					return $module;
				}
			}
			return false;
		}
		
		public static function loadModule($name, $suppressNotice = false) {
			if ($suppressNotice == false) {
				Logger::debug("Loading module \"".$name."...\"");
			}
			
			if (!self::isLoaded(basename($name)) && is_readable(__PROJECTROOT__."/includes/modules/".$name.".php")) {
				$classname = basename($name).time().mt_rand();
				$eval = str_ireplace("@@CLASSNAME@@", $classname, substr(trim(file_get_contents(__PROJECTROOT__."/includes/modules/".$name.".php")), 5, -2));
				eval($eval);
				if (class_exists($classname)) {
					$module = new $classname();
					if (is_object($module) && method_exists($module, "isInstantiated") && $module->isInstantiated()) {
						self::$modules[] = $module;
						Logger::info("Loaded module \"".$name."\"");
						return true;
					}
					else {
						Logger::info("Unable to load module \"".$name."\"");
						Logger::debug("Class \"".$classname."\" does not contain method \"isInstantiated()\" or it returned false.  Failing quietly.");
					}
				}
				else {
					Logger::info("Unable to load module \"".$name.".\"");
					Logger::debug("Class \"".$classname."\" was not created by eval().  Failing quietly.");
				}
			}
			return false;
		}
		
		public static function reloadModule($name) {
			Logger::debug("Reloading module \"".$name."...\"");
			if (self::isLoaded(basename($name))) {
				if (self::unloadModule($name, true)) {
					if(self::loadModule($name, true)) {
						Logger::info("Reloaded module \"".$name."\"");
						return true;
					}
				}
			}
			return false;
		}
		
		public static function unloadModule($name, $suppressNotice = false) {
			if ($suppressNotice == false) {
				Logger::debug("Unloading module \"".$name."...\"");
			}
			
			if (self::isLoaded(basename($name))) {
				foreach (self::$modules as $key => $module) {
					if (strtolower($module->name) == strtolower(basename($name))) {
						EventHandling::unregisterModule($module);
						unset(self::$modules[$key]);
						Logger::info("Unloaded module \"".$name."\"");
						return true;
					}
				}
			}
			return false;
		}
	}
?>
