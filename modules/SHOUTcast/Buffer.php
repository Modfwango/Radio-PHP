<?php
  class __CLASSNAME__ extends Thread {
    public $depend = array("Stream", "Welcome");
    public $name = "Buffer";
    private $pipes = null;
    private $process = null;
    private $song = null;
    private $stream = null;
    private $welcome = null;

    public function flushEncoder() {
      // Check for EOF marker for the encoder output pipe
      if (!@feof($this->pipes[1])) {
        // Read "burstint" bytes from the encoder
        $length = $this->welcome->getOption("burstint");
        $data = @fread($this->pipes[1], $length);
        // Pad the data read from the encoder if necessary
        if (strlen($data) < $length)
          $data .= str_repeat(chr(0), $length - strlen($data));
        // Add the data to the stream pool
        $this->stream->putPool($data);
      }

      // If the encoder is finished ...
      if (!is_resource($this->process) || feof($this->pipes[1])) {
        // Close the process handle and reset the pipes
        @proc_close($this->process);
        $this->process = null;
        $this->pipes = null;

        // Switch to the next song
        $this->stream->nextSong();
        $this->song = $this->stream->getSong();

        // Prepare some clean pipes
        $pipes = array(
          0 => array("pipe", "r"),
          1 => array("pipe", "w"),
          2 => array("pipe", "a")
        );
        // Build the command to run the encoder
        $cmd = "avconv -v quiet -i ".escapeshellarg($this->song)." -c ".
          "libmp3lame -ar ".$this->welcome->getOption("samplerate")." -ab ".
          $this->welcome->getOption("bitrate")."k -minrate ".
          $this->welcome->getOption("bitrate")."k -maxrate ".
          $this->welcome->getOption("bitrate")."k -f mp3 -";
        Logger::debug($cmd);
        // Open the encoder process and set the pipes to non-blocking mode
        $this->process = proc_open($cmd, $pipes, $this->pipes);
        stream_set_blocking($this->pipes[0], 0);
        stream_set_blocking($this->pipes[1], 0);
        stream_set_blocking($this->pipes[2], 0);
      }
    }

    public function run() {
      $this->flushEncoder();
      usleep(10000);
    }

    public function isInstantiated() {
      // Fetch references to required modules
      $this->stream  = ModuleManagement::getModuleByName("Stream");
      $this->welcome = ModuleManagement::getModuleByName("Welcome");

      // Create a Worker object
      $GLOBALS['worker'] = new Worker();
      // Start the worker
      $GLOBALS['worker']->start();
      // Add work to the worker
      $GLOBALS['worker']->stack($this);

      return true;
    }
  }
?>
