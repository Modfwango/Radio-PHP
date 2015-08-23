<?php
  class __CLASSNAME__ {
    public $depend = array();
    public $name = "Metadata";

    public function getMetadata($song) {
      // Remove the file extension from the song
      $song = explode(".", $song);
      unset($song[count($song) - 1]);
      // Calculate the basename of the given path
      $song = basename(implode(".", $song));
      // Prepare the payload of metadata for this song
      $payload = "StreamTitle='".$song."';";
      $metalength = ceil(strlen($payload) / 16);
      $metadata = chr($metalength).$payload;
      // Pad the payload if necessary
      if (strlen($payload) < ($metalength * 16))
        $metadata .= str_repeat(chr(0), ($metalength * 16) - strlen($payload));
      return $metadata;
    }

    public function isInstantiated() {
      return true;
    }
  }
?>
