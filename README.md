Radio-PHP
=========

Radio-PHP is a streaming server that uses the SHOUTcast client protocol in order
to stream a directory of music files without the need for an external streaming
source (such as Traktor or SAM Broadcoaster).  Radio-PHP makes this possible by
using a few utilities available in many Linux package repositories to convert
your input files to a homogeneous MP3 format.

Install
=======

First, make sure that you have the necessary packages installed:
```sh
sudo apt-get install -y libav-tools libmp3lame-dev php5-cli
```

After installing the necessary packages, clone this repository and its
submodules:
```sh
git clone https://github.com/Modfwango/Radio-PHP.git
cd Radio-PHP && git submodule update --init
```

Next, configure the options in the `data/Welcome/config.json` file to your
liking. After you have configured Radio-PHP, place some media in the folder
specified in the config and start the daemon with `php main.php`. By default
Radio-PHP listens on all interfaces on port `8000`. This can be changed in
`conf/listen.conf`

Development
===========

In order to develop your own features and such, take a look at
[this link](http://modfwango.com/Modfwango/blob/master/README.md) for more
information.

Licensing
=========

This work is licensed under the Creative Commons Attribution-ShareAlike 4.0
International License. To view a copy of this license, visit
http://creativecommons.org/licenses/by-sa/4.0/ or send a letter to Creative
Commons, PO Box 1866, Mountain View, CA 94042, USA.
