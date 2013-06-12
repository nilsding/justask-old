<?php
/* 
 * justask
 * © 2013 nilsding
 * License: AGPLv3, read the LICENSE file for the license text.
 */

session_start();

if (!isset($_SESSION['logged_in']) || !isset($_SESSION['user'])) {
  header('Location: ucp.php');
  exit();
}

if (file_exists('config.php')) {
  require_once('config.php');
} else {
  header('Location: install.php');
  exit();
}
require_once('include/oauth/twitteroauth.php');

if (isset($_REQUEST['oauth_token']) && $_SESSION['oauth_token'] !== $_REQUEST['oauth_token']) {
  $_SESSION['oauth_status'] = 'oldtoken';
  header('Location: ./ucp.php?p=account');
}

$sql = mysqli_connect($MYSQL_SERVER, $MYSQL_USER, $MYSQL_PASS, $MYSQL_DATABASE);

$res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_twitter\'');
$res = $res->fetch_assoc();
$twitter_on = ($res['config_value'] === "true" ? true : false);

if ($twitter_on) {
  $res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_twitter_ck\'');
  $res = $res->fetch_assoc();
  $twitter_ck = $res['config_value'];
  $res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_twitter_cs\'');
  $res = $res->fetch_assoc();
  $twitter_cs = $res['config_value'];
} else {
  header('Location: ./ucp.php?p=account&m=4');
  exit();
}

$connection = new TwitterOAuth($twitter_ck, $twitter_cs, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);


$access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);
unset($_SESSION['oauth_token']);
unset($_SESSION['oauth_token_secret']);

if (200 == $connection->http_code) {
  $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . $sql->real_escape_string($access_token['oauth_token']) . '\' WHERE `config_id`=\'cfg_twitter_at\'; ';
  $sql->query($sql_str);
  $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . $sql->real_escape_string($access_token['oauth_token_secret']) . '\' WHERE `config_id`=\'cfg_twitter_ats\'; ';
  $sql->query($sql_str);

  header('Location: ./ucp.php?p=account&m=5');
} else {
  header('Location: ./ucp.php?p=account&m=3');
}
?>
You will be redirected...