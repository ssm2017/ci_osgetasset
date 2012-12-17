<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * $config['engine'] Defines the image conversion engine that you would like to use.
 * You need to browse the web to see how to install one of them on your server system.
 * In any case, you need to have imagemagick installed on your system
 * You can use :
 * - imagick (a php module to use imagemagick)
 * - j2k_to_image (a command line converter)
 */
$config['engine'] = 'imagick';
//$config['engine'] = 'j2k_to_image';

/*
 * $config['image_default_format'] Defines the default format to use for the output image.
 * available options are :
 * - jpeg
 * - png
 * - gif
 */
$config['image_default_format'] = 'jpeg';

/*
 * $config['source'] Defines the way to get the raw asset data.
 * You can use :
 * - db (to get the data directly from the database)
 * - url (ONLY AVAILABLE IN GRID MODE)
 */
$config['source'] = 'db';
//$config['source'] = 'url';

/*
 * $config['url'] Defines the url to get the assets from
 * the url should be like (assuming that the assets server is using port 8003) http://yourgridurl:8003/assets/UUID
 */
$config['url'] = 'http://assets.osgrid.org/assets';

/*
 * $config['asset_server_timeout'] Defines the timeout in seconds to retrieve the asset using the url method.
 */
$config['asset_server_timeout'] = 8;

/*
 * $config['tmp_folder'] Defines the temporary folder to use for conversion when needed.
 * This folder needs to be writable by the webserver engine.
 */
$config['tmp_folder'] = '/var/www/tmp';

/*
 * $config['cache_folder'] Defines the cache folder to use for caching already converted images.
 * This folder needs to be writable by the webserver engine.
 */
$config['cache_folder'] = '/var/www/getasset/application/cache/assets';

/*
 * $config['cache_duration'] Defines the time (in seconds) to keep converted images in the cache folder.
 * If a requested image is older than this time, it will be regenerated.
 */
$config['cache_max_age'] = 86400;

/*
 * $config['uuid_zero'] Defines the image path to send when there is an error getting the image.
 * Note that there is no file extension (it will be added by the script)
 */
$config['uuid_zero'] = '/var/www/getasset/application/assets/images/uuid_zero/uuid_zero';