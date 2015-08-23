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
    private $timer = null;
    private $welcome = null;

    public function getPool($bytes = 0, $flush = true) {
      $buf = null;
      if ($bytes > 0) {
        // Build the buffer with the requested number of bytes
        $buf = substr($this->pool, 0, $bytes);
        // If a flush is desired, remove the requested buffer from the pool
        if ($flush == true)
          $this->pool = substr($this->pool, $bytes);
      }
      else {
        // Grab the entire pool
        $buf = $this->pool;
        // Empty the pool
        if ($flush == true)
          $this->pool = null;
      }
      return $buf;
    }

    public function getClients() {
      $clients = array();
      // Build a list of all clients ready to receive broadcast data
      foreach (ConnectionManagement::getConnections() as $client)
        if (is_object($client) && $client->isAlive() &&
            $client->getOption("ready") == true)
          $clients[] = $client;
      return $clients;
    }

    public function getSong() {
      // Get the currently playing song
      return (isset($this->history[0]) ? $this->history[0] : false);
    }

    public function getSongs() {
      $songs = array();
      // Build a list of all songs available in the configured music directory
      foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
          $this->welcome->getOption("music"))) as $file => $obj)
        if (is_file($file) && is_readable($file))
          $songs[] = $file;
      return $songs;
    }

    public function nextSong() {
      // Calculate the repeat frequency based on the current count of songs and
      // the configured value
      $repeatfreq = $this->welcome->getOption("repeatfreq");
      $repeatfreq = ($repeatfreq > count($this->getSongs()) ?
        count($this->getSongs()) : $repeatfreq);
      // Log the max history length calculated above and the play history before
      // pruning
      Logger::debug("Max history length: ".$repeatfreq);
      Logger::debug("History before prune:");
      Logger::debug(var_export($this->history, true));
      // Prune the play history to the size allowed by the repeat frequency
      while (count($this->history) >= $repeatfreq)
        array_pop($this->history);
      // Log the play history after pruning
      Logger::debug("History after prune:");
      Logger::debug(var_export($this->history, true));
      // Get an array of possible songs by excluding tracks in the updated play
      // history and randomize it
      $selections = array_diff($this->getSongs(), $this->history);
      shuffle($selections);
      // Log the current playlist
      Logger::debug("Possible songs:");
      Logger::debug(var_export($selections, true));
      // Play the first item on the list
      array_unshift($this->history, $selections[0]);
      $this->meta = $this->metadata->getMetadata($this->getSong());
      Logger::debug("Switching to song \"".$this->getSong()."\"...");
      Logger::debug($this->meta);
    }

    public function putPool($buf) {
      // Append the provided buffer to the pool of data waiting to be sent
      $this->pool .= $buf;
    }

    public function broadcast() {
      // // Schedule another broadcast period
      // $this->scheduleBroadcast();

      // If there are clients connected ...
      if (count($this->getClients()) > 0) {
        $burstint = $this->welcome->getOption("burstint");
        // and there is ample data to broadcast ...
        if (strlen($this->pool) >= $burstint) {
          // fetch the data associated with this broadcast ...
          $buf = $this->getPool($burstint);
          // and process the data for each client
          foreach ($this->getClients() as $client) {
            // If the client specified that it wants metadata, append the
            // associated metadata to the data
            $data = $buf.($client->getOption("metadata") ? $this->meta : null);
            // If the client is still waiting for preload data ...
            if ($client->getOption("preload") >= 0) {
              // and the client still has insufficient data for the configured
              // preload amount ...
              if ($client->getOption("preload") > 0)
                // append this broadcast to the preload buffer for this client
                $client->setOption("preloadbuf",
                  $client->getOption("preloadbuf").$data);
              else {
                // If the client's preload is fully prepared, send it and clear
                // the preload buffer
                $client->send($client->getOption("preloadbuf"), false);
                $client->setOption("preloadbuf", false);
              }
              // Decrement the preload quantity
              $client->setOption("preload", $client->getOption("preload") - 1);
            }
            // If the client is not waiting for preload data, send the data in a
            // regular fashion
            if ($client->getOption("preload") < 0) $client->send($data, false);
          }
        }
      }
      else if (count(ConnectionManagement::getConnections()) < 1)
        // Clear the pool if no clients are connected
        $this->getPool();
    }

    private function scheduleBroadcast() {
      // Create a timer to call $this->broadcast()
      $this->timer->newTimer($this->welcome->getOption("rate"), $this,
        "broadcast", null);
    }

    public function isInstantiated() {
      // Fetch references to required modules
      $this->metadata = ModuleManagement::getModuleByName("Metadata");
      $this->timer    = ModuleManagement::getModuleByName("Timer");
      $this->welcome  = ModuleManagement::getModuleByName("Welcome");

      // Fetch a null metadata payload
      $this->meta = $this->metadata->getMetadata(null);

      // // Schedule a broadcast to all clients
      // $this->scheduleBroadcast();

      // Register an event to periodically check the encoder state
      EventHandling::registerForEvent("connectionLoopEndEvent", $this,
        "broadcast");
      return true;
    }
  }
?>
