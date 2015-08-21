<?php
  class __CLASSNAME__ {
    public $depend = array();
    public $name = "Welcome";
    private $config = array();

    public function getOption($name) {
      return (isset($this->config[$name]) ? $this->config[$name] : false);
    }

    public function loadConfig() {
      $config = @json_decode(StorageHandling::loadFile($this, "config.json"),
        true);
      if (is_array($config)) {
        $this->config = $config;
        $this->config["burstint"] = intval((($this->config["bitrate"] * 1000) /
          8) * (__DELAY__ / 970000));
        return true;
      }
      else {
        StorageHandling::saveFile($this, "config.json", json_encode(array(
          "bitrate" => 320,
          "description" => "Example station description tag",
          "genre" => "Liquid Drum and Bass",
          "name" => "LiquidStation",
          "music" => "/var/private/music",
          "preload" => 8,
          "repeatfreq" => 30,
          "samplerate" => 44100,
          "url" => "http://example.org"
        ), JSON_PRETTY_PRINT));
        return $this->loadConfig();
      }
      return false;
    }

    public function receiveRaw($name, $data) {
      $connection = $data[0];
      $ex = $data[2];
      $data = $data[1];

      if ($connection->getType() == "1") {
        if (strtoupper($ex[0]) == "GET" && strtoupper($ex[2]) == "HTTP/1.1") {
          // Set mount-point
          $connection->setOption("stream", $ex[1]); // Currently unused
        }
        if (strtolower($data) == "icy-metadata: 1") {
          $connection->setOption("metadata", true);
        }
        if (trim($data) == null && $connection->getOption("stream") != false) {
          $ending = "\n";
          $response = array(
            "HTTP/1.0 200 OK",
            "Content-Type: audio/mpeg",
            "Server: Radio-PHP (Modfwango v".__MODFWANGOVERSION__.")",
            "Cache-Control: no-cache",
            "icy-br: ".intval($this->config["bitrate"]),
            "icy-description: ".preg_replace("/[^\x20-\x7E]/", null,
              $this->config["description"]),
            "icy-genre: ".preg_replace("/[^\x20-\x7E]/", null,
              $this->config["genre"]),
            "icy-name: ".preg_replace("/[^\x20-\x7E]/", null,
              $this->config["name"]),
            "icy-pub: -1",
            "icy-url: ".preg_replace("/[^\x20-\x7E]/", null,
              $this->config["url"])
          );
          if ($connection->getOption("metadata") == true) {
            $response[] = "icy-metaint: ".intval($this->config["burstint"]);
          }
          $connection->send(trim(implode($ending, $response)).$ending.$ending,
            false);
          $connection->setOption("preload", $this->config["preload"] /
            (__DELAY__ / 1000000));
          $connection->setOption("ready", true);
        }
      }
    }

    public function isInstantiated() {
      EventHandling::registerForEvent("rawEvent", $this, "receiveRaw");
      return $this->loadConfig();
    }
  }
?>
