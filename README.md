Radio-PHP
=========
***

Radio-PHP is a project that I've always wanted to do.  It accomplishes a major goal of mine, which is to be able to stream audio like SHOUTcast or Icecast do, except without the need of a remote DSP (which usually requires Windows).  In this piece of software, you can define a directory from which to play music.  Currently, the song artist and title are retrieved from the filename of any particular song, but in the future I plan to implement the getID3 library to obtain the metadata that way.  Radio-PHP is based off of [Modfwango-Server](https://github.com/ClayFreeman/Modfwango-Server).  Modfwango-Server is a socket server framework I wrote in PHP.


Install
=======
***

This framework was tested under Ubuntu Linux and Mac OS X.  Windows compatibility is unknown, and probably unstable.  To use this framework, make sure that you have the latest version of PHP 5 CLI installed on your machine.  Configuration for this framework is inside the configuration directory `conf/` and also `moddata/ShoutcastConfig/`.  After you are done configuring it, just run the main file with `php main.php` and the application will start.  You can put this into a screen just by doing `screen php main.php`.  Also be sure to install `avconv` and support for MP3 encoding.


Support
=======
***

For support with this bot's framework, join our IRC channel at `irc.tinycrab.net` port `6667` channel `#modfwango`.
