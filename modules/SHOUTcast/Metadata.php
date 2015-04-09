<?php
  class __CLASSNAME__ {
    public $depend = array();
    public $name = "Metadata";

    public function getMetadata($song) {
      $song = explode(".", $song);
      unset($song[count($song) - 1]);
      $song = basename(implode(".", $song));
      $payload = "StreamTitle='".$song."';";
      $metalength = ceil(strlen($payload) / 16);
      $metadata = chr($metalength).$payload;
      if (strlen($payload) < ($metalength * 16)) {
        $metadata .= str_repeat(chr(0), ($metalength * 16) - strlen($payload));
      }
      return $metadata;
    }

    public function isInstantiated() {
      return true;
    }
  }
?>
