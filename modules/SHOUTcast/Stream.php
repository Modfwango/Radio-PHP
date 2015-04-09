<?php
  class __CLASSNAME__ {
    public $depend = array("Metadata", "Welcome");
    public $name = "Stream";
    private $countdown = 0;
    private $history = array();
    private $meta = null;
    private $metadata = null;
    private $pool = null;
    private $songs = array();

    public function getPool($bytes = 0, $flush = true) {
      $buf = null;
      if ($bytes > 0) {
        $buf = substr($this->pool, 0, $bytes);
        if ($flush == true)
          $this->pool = substr($this->pool, $bytes);
      }
      else {
        $buf = $this->pool;
        if ($flush == true)
          $this->pool = null;
      }
      return $buf;
    }

    public function getClients() {
      $clients = array();
      foreach (ConnectionManagement::getConnections() as $client) {
        if (is_object($client) && $client->isAlive() &&
            $client->getOption("ready") == true) {
          $clients[] = $client;
        }
      }
      return $clients;
    }

    public function getSong() {
      return $this->history[0];
    }

    public function getSongs() {
      $songs = array();
      foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
          $this->welcome->getOption("music"))) as $file => $obj) {
        if (is_file($file)) {
          $songs[] = $file;
        }
      }
      return $songs;
    }

    public function nextSong() {
      $repeatfreq = $this->welcome->getOption("repeatfreq");
      $repeatfreq = ($repeatfreq > count($this->getSongs()) ?
        count($this->getSongs()) : $repeatfreq);
      Logger::debug("Max history length: ".$repeatfreq);
      Logger::debug("History before prune:");
      Logger::debug(var_export($this->history, true));
      while (count($this->history) >= $repeatfreq)
        array_pop($this->history);
      Logger::debug("History after prune:");
      Logger::debug(var_export($this->history, true));
      $selections = array_diff($this->getSongs(), $this->history);
      shuffle($selections);
      Logger::debug("Possible songs:");
      Logger::debug(var_export($selections, true));
      array_unshift($this->history, $selections[0]);
      $this->meta = $this->metadata->getMetadata($this->getSong());
      Logger::debug("Switching to song \"".$this->getSong()."\"...");
      Logger::debug($this->meta);
    }

    public function putPool($buf) {
      // Logger::debug("Adding MP3 data... [".strlen($buf)."]");
      $this->pool .= $buf;
    }

    public function receiveConnectionLoopEnd() {
      if (count($this->getClients()) > 0) {
        $burstint = $this->welcome->getOption("burstint");
        if (strlen($this->pool) >= $burstint) {
          $buf = $this->getPool($burstint);
          foreach ($this->getClients() as $client) {
            if ($client->getOption("metadata")) {
              $client->send($buf.$this->meta, false);
            }
            else {
              $client->send($buf, false);
            }
          }
        }
      }
    }

    public function isInstantiated() {
      $this->metadata = ModuleManagement::getModuleByName("Metadata");
      $this->welcome = ModuleManagement::getModuleByName("Welcome");
      $this->meta = $this->metadata->getMetadata(null);
      $this->nextSong();
      EventHandling::registerForEvent("connectionLoopEndEvent", $this,
        "receiveConnectionLoopEnd");
      return true;
    }
  }
?>
