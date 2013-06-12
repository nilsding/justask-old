<?php
if (file_exists('config.php')) {
  require_once('config.php');
} else {
  header('Location: install.php');
  exit();
}

function upgrade_to(MySQLi $sql, $ver, $MYSQL_TABLE_PREFIX) {
  switch ($ver) {
    case 3:
      /* new config values in config r3:
       * version = config version number
       * cfg_twitter = twitter enabled? [true/false]
       * cfg_twitter_ck = twitter consumer key
       * cfg_twitter_cs = twitter consumer secret
       * cfg_twitter_at = twitter consumer access token
       * cfg_twitter_ats = twitter consumer access token secret
       * cfg_twitter_callbk = twitter callback
       */
      
      $JUSTASK_CONFIG_VERSION = 3;
      $JUSTASK_TWITTER_CK = "ABr5S6jAB4RQYFYWm5Sq";
      $JUSTASK_TWITTER_CS = "ICM7eKAlu6PSPysQr7Sim0uFT4HoqK7d5asEpW1Qd6";
      $JUSTASK_TWITTER_CALLBACK = "http://" . $_SERVER['HTTP_HOST'] . "/callback.php";
      
      echo 'storing version value... ';
      $sql_str = 'INSERT INTO `' . $MYSQL_TABLE_PREFIX . 'config` (`config_id`, `config_value`) VALUES (\'version\', \'' . $JUSTASK_CONFIG_VERSION . '\');';
      if (!$sql->query($sql_str)) {
        if ($sql->errno == 1062) { 
          $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . $JUSTASK_CONFIG_VERSION . '\' WHERE `config_id`=\'version\'; ';
          $sql->query($sql_str);
        }
      }
      
      echo 'done<br />upgrading database... ';
      $sql_str = 'INSERT INTO `' . $MYSQL_TABLE_PREFIX . 'config` (`config_id`, `config_value`) VALUES (\'cfg_twitter\', \'false\'), (\'cfg_twitter_ck\', \'' . 
        strrev($JUSTASK_TWITTER_CK) . '\'), (\'cfg_twitter_cs\', \'' . strrev($JUSTASK_TWITTER_CS) . '\'), (\'cfg_twitter_at\', \'\'), (\'cfg_twitter_ats\', \'' .
        '\'), (\'cfg_twitter_callbk\', \'' . $sql->real_escape_string($JUSTASK_TWITTER_CALLBACK) . '\');';
      if (!$sql->query($sql_str)) {
        echo 'error<br />';
      } else {
        echo 'done<br />';
      }
      echo '<br />';
      break;
    case 4:
      /* new config values in config r4:
       * cfg_currtheme = current theme 
       */
      $JUSTASK_CONFIG_VERSION = 4;
      echo 'updating version value... ';
      $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . $JUSTASK_CONFIG_VERSION . '\' WHERE `config_id`=\'version\'; ';
      $sql->query($sql_str);
      
      echo 'done<br />upgrading database... ';
      $sql_str = 'INSERT INTO `' . $MYSQL_TABLE_PREFIX . 'config` (`config_id`, `config_value`) VALUES (\'cfg_currtheme\', \'' . $sql->real_escape_string("classic") . '\');';
      if (!$sql->query($sql_str)) {
        echo 'error<br />';
      } else {
        echo 'done<br />';
      }
      echo '<br />';
      break;
    default:
      die("Unknown \$ver given!");
  }
}

$content = "";

$sql = mysqli_connect($MYSQL_SERVER, $MYSQL_USER, $MYSQL_PASS, $MYSQL_DATABASE);
if ($sql->connect_errno) {
  $content .= "<p>Failed to connect to MySQL: (" . $sql->connect_errno . ") " . $sql->connect_error . "</p>";
  $content .= "<p>Please check your MySQL user/pass/server/whatever.</p>";
}

$res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_sitename\'');
$res = $res->fetch_assoc();
$site_name = $res['config_value'];
$res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_currtheme\'');
$res = $res->fetch_assoc();
$current_theme = $res['config_value'];
$res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_gravatar\'');
$res = $res->fetch_assoc();
$gravatar = ($res['config_value'] === 'true' ? true : false);
$res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_anon_questions\'');
$res = $res->fetch_assoc();
$anon_questions = ($res['config_value'] === 'true' ? true : false);
$res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_username\'');
$res = $res->fetch_assoc();
$user_name = $res['config_value'];
$res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_user_gravatar\'');
$res = $res->fetch_assoc();
$user_gravatar_email = $res['config_value'];

$res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'version\'');
$res = $res->fetch_assoc();
$config_version = $res['config_value'];

switch ($config_version) {
  case 3:
    $content .= "<p>upgrading to <strong>r4</strong>...<br />";
    upgrade_to($sql, 4, $MYSQL_TABLE_PREFIX);
    $content .= "Perfect.</p>";
    break;
  case 4:
    $content .= "<p>Your config is already up to date.</p>";
    break;
  default:
    $content .= "<p>upgrading to <strong>r4</strong>...<br />";
    upgrade_to($sql, 3, $MYSQL_TABLE_PREFIX);
    $content .= "<strong>r3 → r4</strong><br /><br />";
    upgrade_to($sql, 4, $MYSQL_TABLE_PREFIX);
    $content .= "Perfect.</p>";
}

include 'gravatar.php';
include 'include/rain.tpl.class.php';

raintpl::configure("base_url", null);
raintpl::configure("path_replace", false);
raintpl::configure("tpl_dir", "themes/$current_theme/");

$tpl = new RainTPL;

$tpl->assign("content", $content);
$tpl->assign("current_page", "justask Updater");

/* everywhere variables */
$tpl->assign("message", "");
$tpl->assign("is_message", false);
$tpl->assign("gravatar", $gravatar);
$tpl->assign("user_name", $user_name);
$tpl->assign("file_name", "update_jak.php");
$tpl->assign("current_theme", $current_theme);
$tpl->assign("page_self", $_SERVER['PHP_SELF']);
$tpl->assign("anon_questions", $anon_questions);
$tpl->assign("logged_in", $_SESSION['logged_in']);
$tpl->assign("site_name", htmlspecialchars($site_name));
$tpl->assign("user_gravatar_email", get_gravatar_url($user_gravatar_email, 48));

$tpl->draw("generic");

?>