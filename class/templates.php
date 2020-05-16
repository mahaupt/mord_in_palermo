<?php
/**
 * This file contains the templates class
 * @package pagepackage
 */

/**
 * The class to aid implementing HTML templates into the website
 */
class Template
{

  /**
   * Turns a relative URL into an absolute URL
   * @param  string $filename the relative URL to a file
   * @return string           the absolute URL to the file
   */
  static function getUrl($filename) {
    global $_CONFIG;
    return $_CONFIG['URL'] . $filename;
  }
}



 ?>
