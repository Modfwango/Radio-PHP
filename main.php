<?php
  // Make sure that we're running on a compatible version of PHP
  if (version_compare(phpversion(), '5.3', '<')) {
    echo "You must have PHP version 5.3 or higher to use Modfwango.\n";
    exit(1);
  }

  // Set the default timezone to UTC; modules can temporarily set
  // their own timezone configuration if they wish
  define("__TIMEZONE__", "UTC");
  // Set the project root constant to the launcher's directory
  define("__PROJECTROOT__", dirname(__FILE__));

  // Make sure the launcher is named main.php for consistency
  if (basename(__FILE__) != "main.php") {
    rename(__FILE__, __PROJECTROOT__."/main.php");
  }

  // Setup an array for missing directories or files
  $missing = array();
  // Define mandatory directories
  $directories = array(
    __PROJECTROOT__."/conf",
    __PROJECTROOT__."/conf/connections",
    __PROJECTROOT__."/conf/ssl",
    __PROJECTROOT__."/data",
    __PROJECTROOT__."/modules"
  );
  // Define mandatory files
  $files = array();
  // Check each directory and don't fail if it doesn't exist
  foreach ($directories as $directory) {
    if (!file_exists($directory)) {
      mkdir($directory);
    }
  }
  // Check each file and fail if it doesn't exist
  foreach ($files as $file) {
    if (!file_exists($file)) {
      $missing[] = $file;
      touch($file);
    }
  }

  // Tell the user if there was a missing file that was mandatory
  $ending = "\n * ";
  if (count($missing) > 0) {
    echo "Some mandatory configuration files were missing, and thus replaced. ".
      "They are listed below:".$ending.implode($ending, $missing)."\n";
    exit(0);
  }

  // End if prelaunch is requested
  if (isset($argv[1]) && strtolower($argv[1]) == "prelaunch") {
    exit(0);
  }

  // Require Modfwango core to ignite the project
  require_once(__PROJECTROOT__."/.modfwango/main.php");
?>
