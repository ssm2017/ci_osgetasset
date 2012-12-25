# What is this ?
This is a library to use with [codeigniter](http://codeigniter.com) 2 to get an asset image from the database or from an url.

# Installation
Merge the folders with your codeigniter folders.

## Install external libraries
 * Install [imagemagick](http://www.imagemagick.org)
  * Depending on your needs, install :
   * [imagick](http://php.net/manual/fr/book.imagick.php)
   * [j2k_to_image](http://manpages.ubuntu.com/manpages/intrepid/man1/j2k_to_image.1.html)

## Configure files
 * Define the path to your CodeIgniter system folder in index.php
  *  $system_path = '/usr/local/share/CodeIgniter/system';
  *  if you are using this script in the same folder as CodeIgniter installation, replace it by 'system'.
 * Rename the file /application/config/ci_osgetasset.php.example to ci_osgetasset.php
  * Read the content of this file and define the values as you need.
 * Rename the file /application/config/config.php.example to config.php
  * Read the content of this file and define the values as you need.
 * If you set $config['source'] = 'db'; in ci_osgetasset.php rename the file /application/config/database.php.example to database.php
  * Read the content of this file and define the values as you need.

# Use
Read the example controller located in /application/controllers/getasset.php

# What else ?
If you think that this is not working or that this crap or that this is not finished, etc... instead of speaking around for nothing, please fill an issue to tell me what is wrong to make it better, or, if you are so smart and able to make a better one, make your own one and share it to the world. :)
I hope that this code will be helpfull for altruist people.

This library was inspired from https://github.com/alemansec/opensimWebAssets made by Anthony Le Mansec <a.lm@free.fr>
