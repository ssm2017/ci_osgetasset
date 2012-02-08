<?php

/**
  This file is part of ci_osgetasset.

  @copyright Copyright (C) 2012 ssm2017 Binder / wene (S.Massiaux). All rights reserved.

  ci_osgetasset is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  ci_osgetasset is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  See <http://www.gnu.org/licenses/>.

  This library was inspired from https://github.com/alemansec/opensimWebAssets made by Anthony Le Mansec <a.lm@free.fr>
 */
if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Defines the uuid zero
 */
define('UUID_ZERO', '00000000-0000-0000-0000-000000000000');

/**
 * The main class for the lib
 * @package ci_osgetasset
 * @subpackage classes
 */
class ci_osgetasset {

  /**
   * The code igniter core
   * @access private
   * @var object
   */
  private $ci;

  /**
   * The ci_osgetasset config array
   * @access private
   * @var array
   */
  private $config;

  /**
   * Constructor
   */
  function __construct() {
    // get ci core
    $this->ci = & get_instance();
    // load the config
    $this->ci->config->load('ci_osgetasset', true);
    $this->config = $this->ci->config->item('ci_osgetasset');
  }

  /**
   * Main function to return the image
   * @param string $uuid (the uuid of the asset)
   * @param integer|string $width (the width of the image (in pixels) or 'full' to get a full sized image)
   * @param string $format (the format of the image (jpeg, png, or gif))
   * @return string the image stream (getting it from the cache if exists in the cache)
   */
  function get_asset($uuid, $width = 'full', $format = NULL) {

    // get the format
    if (!$format) {
      $format = strtolower($this->config['image_default_format']);
    }

    // return the uuid_zero image if the uuid = 00000000-0000-0000-0000-000000000000
    if ($uuid == UUID_ZERO) {
      return $this->get_asset_zero($format);
    }

    // check if the file exists in the cache
    if ($this->cache_check($uuid, $format, $width)) {
      return $this->get_cached_file($uuid, $format, $width);
    }

    // get the raw asset content
    switch ($this->config['source']) {
      case 'db':
        $asset = $this->get_db_asset($uuid);
        break;
      case 'url':
        $asset = $this->get_url_asset($uuid);
        break;
    }

    // return the uuid_zero image if no asset was found
    if (!$asset) {
      return $this->get_asset_zero($format);
    }

    // convert the content
    $image = $this->convert_asset($uuid, $asset, $width, $format);

    // write the file in the cache
    $this->cache_write($uuid, $image, $format, $width);
    return $image;
  }

  /**
   * Get the asset from the database
   * @param string $uuid The asset uuid
   * @return null|string The asset raw j2k image stream
   */
  function get_db_asset($uuid) {

    // get the value in the database
    $this->ci->db->select('data');
    $query = $this->ci->db->get_where('assets', array('id' => $uuid, 'assetType' => 0));
    $result = $query->row();
    if (!is_object($result)) {
      return NULL;
    }

    // return the raw image
    return $result->data;
  }

  /**
   * Get the asset from an url
   * @param string $uuid The asset uuid
   * @return null|string The asset raw j2k image stream
   */
  function get_url_asset($uuid) {

    // try to get the content
    $h = @fopen($this->config['url'] . '/' . $uuid, "rb");
    if (!$h) {
      return NULL;
    }

    // set the timeout
    stream_set_timeout($h, $this->config['asset_server_timeout']);

    // download the content
    $file_content = stream_get_contents($h);
    fclose($h);

    // parse xml result to get the data
    try {
      $xml = new SimpleXMLElement($file_content);
    } catch (Exception $e) {
      return NULL;
    }

    // return the raw image
    return base64_decode($xml->Data);
  }

  /**
   * Convert an asset j2k string stream to an image stream using the defined format
   * @param string $uuid The asset uuid
   * @param string $asset The j2k asset stream
   * @param integer|string $width (the width of the image (in pixels) or 'full' to get a full sized image)
   * @param string $format (the format of the image (jpeg, png, or gif))
   * @return string The asset converted image stream
   */
  function convert_asset($uuid, $asset, $width = 'full', $format = NULL) {
    switch ($this->config['engine']) {
      case 'imagick':
        return $this->convert_asset_imagick($uuid, $asset, $width, $format);
        break;
      case 'j2k_to_image':
        return $this->convert_asset_j2k_to_image($uuid, $asset, $width, $format);
        break;
    }
  }

  /**
   * Convert and resize the image using imagick
   * @param string $uuid The asset uuid
   * @param string $asset The j2k asset stream
   * @param integer|string $width (the width of the image (in pixels) or 'full' to get a full sized image)
   * @param string $format (the format of the image (jpeg, png, or gif))
   * @return string The asset converted image stream
   */
  function convert_asset_imagick($uuid, $asset, $width = 'full', $format = NULL) {

    // check for the format
    if (!$format) {
      $format = strtolower($this->config['image_default_format']);
    }

    // build the image
    $_img = new Imagick();
    $_img->readImageBlob($asset);
    $_img->setImageFormat($format);

    // resize the image if needed
    if ($width != 'full') {
      $original_height = $_img->getImageHeight();
      $original_width = $_img->getImageHeight();
      $multiplier = $width / $original_width;
      $new_height = $original_height * $multiplier;
      $_img->resizeImage($width, $new_height, Imagick::FILTER_CUBIC, 1);
    }

    // return the image
    return $_img->getImageBlob();
  }

  /**
   * Convert and resize the image using j2j_to_image
   * @param string $uuid The asset uuid
   * @param string $asset The j2k asset stream
   * @param integer|string $width (the width of the image (in pixels) or 'full' to get a full sized image)
   * @param string $format (the format of the image (jpeg, png, or gif))
   * @return boolean|string The asset converted image stream or FALSE if there was an error
   */
  function convert_asset_j2k_to_image($uuid, $asset, $width = 'full', $format = NULL) {

    // define the temporary path
    $path = $this->config['tmp_folder'] . '/' . $uuid;

    // write asset to a temp file
    $h = fopen($path . '.j2k', "wb+");
    if (!$h) {
      return FALSE;
    }
    fwrite($h, $asset);
    fclose($h);

    // convert the temp file to tga
    exec('j2k_to_image -i ' . $path . '.j2k' . ' -o ' . $path . '.tga');
    $output = $path . '.jpg';

    // manage the resize if needed then convert to format
    if (!$format) {
      $format = strtolower($this->config['image_default_format']);
    }

    if ($width != 'full') {
      $geom = $width . 'x' . $width;
      $size = escapeshellarg($geom);
      $output = $path . '-' . $geom . '.jpg';
      exec('convert -scale ' . $size . ' ' . $path . '.tga ' . $output);
    }
    else {
      exec('convert ' . $path . '.tga ' . $output);
    }

    // delete temporary files
    unlink($path . '.j2k');
    unlink($path . '.tga');
    $fd = fopen($output, "rb");
    $data = fread($fd, filesize($output));
    fclose($fd);

    // delete the temporary file
    unlink($output);

    // return the image
    return $data;
  }

  /**
   * Check if the file exists in the cache
   * @param string $uuid The asset uuid
   * @param string $format (the format of the image (jpeg, png, or gif))
   * @param integer|string $width (the width of the image (in pixels) or 'full' to get a full sized image)
   * @return boolean TRUE if exists or FALSE if not exists
   */
  function cache_check($uuid, $format, $width = 'full') {

    // get file and folder path
    $folder_path = $this->config['cache_folder'] . '/' . $width;
    $file_path = $folder_path . '/' . $uuid . '.' . $format;

    // check for expiration
    $file_max_age = time() - $this->config['cache_max_age'];
    if (!file_exists($file_path)) {
      return FALSE;
    }

    if (filemtime($file_path) < $file_max_age) {

      // expired, delete the file
      unlink($file_path);

      // delete the folder if empty
      if (!scandir($folder_path)) {
        rmdir($folder_path);
      }
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Write the image to the cache folder
   * @param string $uuid The asset uuid
   * @param string $content The image stream
   * @param string $format (the format of the image (jpeg, png, or gif))
   * @param integer|string $width (the width of the image (in pixels) or 'full' to get a full sized image)
   * @return boolean TRUE if success or FALSE if error
   */
  function cache_write($uuid, $content, $format, $width = 'full') {

    // get file and folder path
    $folder_path = $this->config['cache_folder'] . '/' . $width;
    $file_path = $folder_path . '/' . $uuid . '.' . $format;

    // check if the folder exists and if not create it
    if (!is_dir($folder_path)) {
      mkdir($folder_path);
    }

    // write the file
    $h = fopen($file_path, "wb+");
    if (!$h) {
      return FALSE;
    }
    fwrite($h, $content);
    fclose($h);
    return TRUE;
  }

  /**
   * Get the file from the cache
   * @param string $uuid The asset uuid
   * @param string $format (the format of the image (jpeg, png, or gif))
   * @param integer|string $width (the width of the image (in pixels) or 'full' to get a full sized image)
   * @return string The image stream
   */
  function get_cached_file($uuid, $format, $width = 'full') {

    // get the file path
    $file_path = $this->config['cache_folder'] . '/' . $width . '/' . $uuid . '.' . $format;

    // get the file
    $h = fopen($file_path, "rb");
    $data = fread($h, filesize($file_path));
    fclose($h);

    // return the image
    return $data;
  }

  /**
   * Get the uuid zero image from its default folder
   * @param string $format (the format of the image (jpeg, png, or gif))
   * @return strin The image stream
   */
  function get_asset_zero($format) {

    // get the filepath
    $file_path = $this->config['uuid_zero'] . "." . strtolower($format);

    // get the file
    $h = fopen($file_path, "rb");
    $data = fread($h, filesize($file_path));
    fclose($h);

    // return the image
    return ($data);
  }

}
