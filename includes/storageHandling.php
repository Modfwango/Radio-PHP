<?php
	class StorageHandling {
		public static function createDirectory($module, $name) {
			$mname = $module->name;
			$file = __PROJECTROOT__."/moddata/".$mname."/".$name;
			
			if (substr(realpath($file), 0, strlen(__PROJECTROOT__)) == __PROJECTROOT__ && self::initDirectories($mname)) {
				if (is_writable(dirname($file))) {
					return mkdir($file);
				}
			}
			return false;
		}
		
		public static function loadFile($module, $name) {
			$mname = $module->name;
			$file = __PROJECTROOT__."/moddata/".$mname."/".$name;
			
			if (substr(realpath($file), 0, strlen(__PROJECTROOT__)) == __PROJECTROOT__ && self::initDirectories($mname, $name)) {
				if (is_readable($file)) {
					return file_get_contents($file);
				}
			}
			return false;
		}
		
		public static function saveFile($module, $name, $contents) {
			$mname = $module->name;
			$file = __PROJECTROOT__."/moddata/".$mname."/".$name;
			
			if (substr(realpath($file), 0, strlen(__PROJECTROOT__)) == __PROJECTROOT__ && self::initDirectories($mname, $name)) {
				if (is_writable($file)) {
					return file_put_contents($file, $contents);
				}
			}
			return false;
		}
		
		private static function initDirectories($mname, $name = null) {
			$moddata = __PROJECTROOT__."/moddata";
			$moddir = $moddata."/".$mname;
			$file = $moddir."/".$name;
			
			if (!file_exists($moddata)) {
				$ret = mkdir($moddata);
				if ($ret == false) {
					return false;
				}
			}
			
			if (!file_exists($moddir)) {
				$ret = mkdir($moddir);
				if ($ret == false) {
					return false;
				}
			}
			
			if ($name != null && !file_exists($file)) {
				$ret = touch($file);
				if ($ret == false) {
					return false;
				}
			}
			
			return true;
		}
	}
?>