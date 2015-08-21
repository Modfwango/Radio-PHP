<?php
  class __CLASSNAME__ {
    public $depend = array("Stream", "Welcome");
    public $name = "Buffer";
    private $pipes = null;
    private $process = null;
    private $song = null;
    private $stream = null;
    private $welcome = null;

    public function receiveConnectionLoopEnd() {
      // Check for data and process it if there is any
      if (!@feof($this->pipes[1])) {
        // Logger::debug("Reading MP3 data...");
        $this->stream->putPool(@fread($this->pipes[1],
          $this->welcome->getOption("burstint")));
      }

      if (!is_resource($this->process) || feof($this->pipes[1])) {
        @proc_close($this->process);
        $this->process = null;
        $this->pipes = null;

        // Switch song
        $this->stream->nextSong();
        $this->song = $this->stream->getSong();

        $pipes = array(
          0 => array("pipe", "r"),
          1 => array("pipe", "w"),
          2 => array("pipe", "a")
        );
        $cmd = "avconv -v quiet -i ".escapeshellarg($this->song)." -c ".
          "libmp3lame -ar ".$this->welcome->getOption("samplerate")." -ab ".
          $this->welcome->getOption("bitrate")."k -minrate ".
          $this->welcome->getOption("bitrate")."k -maxrate ".
          $this->welcome->getOption("bitrate")."k -f mp3 -";
        Logger::debug($cmd);
        $this->process = proc_open($cmd, $pipes, $this->pipes);
        stream_set_blocking($this->pipes[0], 0);
        stream_set_blocking($this->pipes[1], 0);
        stream_set_blocking($this->pipes[2], 0);
      }
    }

    public function isInstantiated() {
      $this->stream = ModuleManagement::getModuleByName("Stream");
      $this->welcome = ModuleManagement::getModuleByName("Welcome");
      EventHandling::registerForEvent("connectionLoopEndEvent", $this,
        "receiveConnectionLoopEnd");
      return true;
    }
  }
?>
