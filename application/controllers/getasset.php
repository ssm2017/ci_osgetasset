<?php

/**
  This file is part of ci_osgetasset.

  @copyright	Copyright (C) 2012 ssm2017 Binder / wene (S.Massiaux). All rights reserved.

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

class GetAsset extends CI_Controller {

  public function index() {

    // load the library
    $this->load->library('ci_osgetasset');

    // define the header to display an image
    // in this example, we are using jpeg but this can be : jpeg, png or gif
    Header("Content-type: image/jpeg");

    /*
     * get_asset method can be used with arguments like :
     * get_asset($uuid, $width, $format);
     * only $uuid is mandatory
     * $width is optional and is an integer or a string containig 'full' (to get the full sized image)
     * $format is optional and can be : jpeg, png or gif
     */

    // return the output
    echo $this->ci_osgetasset->get_asset('00000000-0000-1111-9999-000000000001');

    // or (to get an image with a 50px width
    // echo $this->ci_osgetasset->get_asset('00000000-0000-1111-9999-000000000001', 50);
    // or (to get a full image as png
    // echo $this->ci_osgetasset->get_asset('00000000-0000-1111-9999-000000000001', 'full', 'png');
  }

}
