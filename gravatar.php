<?php
/* 
 * justask
 * © 2013 nilsding
 * License: AGPLv3, read the LICENSE file for the license text.
 */

/** This function generates the URL for a Gravatar avatar.
 * @param $email - the email address to use
 * @param $size - the size of the gravatar to fetch
 */
function get_gravatar_url($email, $size) {  
  $default_image_url = "http://static.nilsding.org/nopic-48.png";
  
  return "https://www.gravatar.com/avatar/" . md5(strtolower(trim($email))) . "?d=" . urlencode($default_image_url) . "&s=" . $size;
}
