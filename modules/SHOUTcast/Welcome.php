<?php
  class __CLASSNAME__ {
    private $config = array();
    public  $depend = array();
    public  $name   = "Welcome";

    public function getOption($name) {
      // Fetch the desired configuration option (if exists)
      return (isset($this->config[$name]) ? $this->config[$name] : false);
    }

    public function loadConfig() {
      $config = @json_decode(StorageHandling::loadFile($this, "config.json"),
        true);
      if (is_array($config)) {
        // Array of required fields in $config
        $required    = array("bitrate", "description", "genre", "name", "music",
          "samplerate", "url");

        // Pre-formatted strings for use in multiple error messages
        $not_defined = "The configuration option '%s' was not defined in the ".
          "configuration file at %s.";
        $invalid     = "The configuration option '%s' contains an invalid ".
          "value.  This field requires a%s value%s.";

        // Verify that required fields are present
        foreach ($required as $key)
          if (!isset($config[$key])) {
            Logger::info(sprintf($not_defined, $key, escapeshellarg(
              StorageHandling::getPath($this, "config.json"))));
            return false;
          }

        // Autocomplete preload field if not given
        if (!isset($config["preload"]))
          $config["preload"] = 0;

        try {
          foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
              $config["music"])) as $file => $obj)
            if (is_file($file) && is_readable($file))
              $songs[] = $file;
        } catch (Exception $e) {}
        // Verify that there is music to play
        if (!is_dir($config["music"]) || !is_readable($config["music"]) ||
            !isset($songs) || count($songs) < 1) {
          Logger::info("Could not find any music to play.  Check that the ".
            "configuration option 'music' in the file at ".escapeshellarg(
            StorageHandling::getPath($this, "config.json"))." has the proper ".
            "path, there are files in the directory, and both the directory ".
            "and files are readable.");
          return false;
        }

        // Verify constraints of the bitrate variable
        if (!is_numeric($config["bitrate"]) ||
            floatval($config["bitrate"]) != intval($config["bitrate"]) ||
            $config["bitrate"] < 64 || $config["bitrate"] > 320) {
          Logger::info(sprintf($invalid, "bitrate", "n integer", " from 64 ".
            "to 320"));
          return false;
        }
        // Sanitize the field by grabbing its integer value
        $config["bitrate"] = intval($config["bitrate"]);

        // Verify constraints of the preload variable
        if (!is_numeric($config["preload"]) ||
            floatval($config["preload"]) != intval($config["preload"]) ||
            $config["preload"] < 0) {
          Logger::info(sprintf($invalid, "preload", "n integer", " above 0"));
          return false;
        }
        // Sanitize the field by grabbing its integer value
        $config["preload"] = intval($config["preload"]);

        // Verify constraints of the repeatfreq variable
        if (!is_numeric($config["repeatfreq"]) ||
            floatval($config["repeatfreq"]) != intval($config["repeatfreq"]) ||
            $config["repeatfreq"] < 0) {
          Logger::info(sprintf($invalid, "repeatfreq", "n integer", " above ".
            "0"));
          return false;
        }
        // Sanitize the field by grabbing its integer value
        $config["repeatfreq"] = intval($config["repeatfreq"]);

        // Verify constraints of the samplerate variable
        if (!is_numeric($config["samplerate"]) ||
            floatval($config["samplerate"]) != intval($config["samplerate"]) ||
            $config["samplerate"] < 44100) {
          Logger::info(sprintf($invalid, "samplerate", "n integer", " above ".
            "44100"));
          return false;
        }
        // Sanitize the field by grabbing its integer value
        $config["samplerate"] = intval($config["samplerate"]);

        // Sanitize the description, genre, name, and url fields
        $config["description"] = preg_replace("/[^\x20-\x7E]/", null,
          $config["description"]);
        $config["genre"] = preg_replace("/[^\x20-\x7E]/", null,
          $config["genre"]);
        $config["name"] = preg_replace("/[^\x20-\x7E]/", null, $config["name"]);
        $config["url"] = preg_replace("/[^\x20-\x7E]/", null, $config["url"]);

        // Verify the constraints of the description variable
        if (strlen($config["description"]) < 1) {
          Logger::info(sprintf($invalid, "description", " non-null", " that ".
            "contains human-readable ASCII characters"));
          return false;
        }

        // Verify the constraints of the genre variable
        if (strlen($config["genre"]) < 1) {
          Logger::info(sprintf($invalid, "genre", " non-null", " that ".
            "contains human-readable ASCII characters"));
          return false;
        }

        // Verify the constraints of the name variable
        if (strlen($config["name"]) < 1) {
          Logger::info(sprintf($invalid, "name", " non-null", " that contains ".
            "human-readable ASCII characters"));
          return false;
        }

        // Verify the constraints of the url variable
        if (strlen($config["url"]) < 1) {
          Logger::info(sprintf($invalid, "url", " non-null", " that contains ".
            "human-readable ASCII characters"));
          return false;
        }

        // Set a polling rate (in seconds) to broadcast data
        $config["rate"]     = 0.01;

        // Calculate the burstint field
        $config["burstint"] = intval((($config["bitrate"] * 1000) / 8) *
          $config["rate"]);

        // Accept the config as given
        $this->config = $config;
        return true;
      }
      else {
        // Flush a default configuration file
        StorageHandling::saveFile($this, "config.json", json_encode(array(
          "bitrate"     => 192,
          "description" => "Example station description.",
          "genre"       => "Various Genres",
          "name"        => "Untitled Station",
          "music"       => "/var/private/music",
          "preload"     => 3,
          "repeatfreq"  => 30,
          "samplerate"  => 44100,
          "url"         => "http://example.org/"
        ), JSON_PRETTY_PRINT));
        return $this->loadConfig();
      }
      return false;
    }

    public function receiveRaw($name, $data) {
      $connection = $data[0];
      $ex         = $data[2];
      $data       = $data[1];

      if ($connection->getType() == "1") {
        // Parse stream GET request for a mount point (unused)
        if (strtoupper($ex[0]) == "GET" && strtoupper($ex[2]) == "HTTP/1.1")
          $connection->setOption("stream", $ex[1]);
        // Parse metadata header which specifies if the client wants metadata
        if (strtolower($data) == "icy-metadata: 1")
          $connection->setOption("metadata", true);
        // Build response when end of request is reached
        if (trim($data) == null && $connection->getOption("stream") != false) {
          // Build an array of lines to send in the response header section
          $response = array(
            "HTTP/1.0 200 OK",
            "Content-Type: audio/mpeg",
            "Server: Radio-PHP (Modfwango v".__MODFWANGOVERSION__.")",
            "Cache-Control: no-cache",
            "icy-pub: -1",
            "icy-br: ".         $this->config["bitrate"],
            "icy-description: ".$this->config["description"],
            "icy-genre: ".      $this->config["genre"],
            "icy-name: ".       $this->config["name"],
            "icy-url: ".        $this->config["url"]
          );
          // If the client wants metadata, tell the client that it will be
          // receiving it
          if ($connection->getOption("metadata") == true)
            $response[] = "icy-metaint: ".$this->config["burstint"];

          // Flush the response header to the client
          $connection->send(trim(implode("\r\n", $response))."\r\n\r\n", false);

          // Set the given preload quantity as configured
          $connection->setOption("preload", intval($this->config["preload"] /
            $this->config["rate"]));

          // Signal the Stream module that this client is ready to receive the
          // broadcast
          $connection->setOption("ready", true);
        }
      }
    }

    public function isInstantiated() {
      // Intercept raw data from clients
      EventHandling::registerForEvent("rawEvent", $this, "receiveRaw");
      // Allow this module to be loaded if the config was loaded successfully
      return $this->loadConfig();
    }
  }
?>
