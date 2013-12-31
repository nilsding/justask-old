<?php
if (file_exists('config.php')) {
  require_once('config.php');
} else {
  header('Location: install.php');
  exit();
}

session_start();

function upgrade_to(MySQLi $sql, $ver, $MYSQL_TABLE_PREFIX, &$content) {
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
      
      $content .= 'storing version value... ';
      $sql_str = 'INSERT INTO `' . $MYSQL_TABLE_PREFIX . 'config` (`config_id`, `config_value`) VALUES (\'version\', \'' . $JUSTASK_CONFIG_VERSION . '\');';
      if (!$sql->query($sql_str)) {
        if ($sql->errno == 1062) { 
          $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . $JUSTASK_CONFIG_VERSION . '\' WHERE `config_id`=\'version\'; ';
          $sql->query($sql_str);
        }
      }
      
      $content .= 'done<br />upgrading database... ';
      $sql_str = 'INSERT INTO `' . $MYSQL_TABLE_PREFIX . 'config` (`config_id`, `config_value`) VALUES (\'cfg_twitter\', \'false\'), (\'cfg_twitter_ck\', \'' . 
        strrev($JUSTASK_TWITTER_CK) . '\'), (\'cfg_twitter_cs\', \'' . strrev($JUSTASK_TWITTER_CS) . '\'), (\'cfg_twitter_at\', \'\'), (\'cfg_twitter_ats\', \'' .
        '\'), (\'cfg_twitter_callbk\', \'' . $sql->real_escape_string($JUSTASK_TWITTER_CALLBACK) . '\');';
      if (!$sql->query($sql_str)) {
        $content .= 'error<br />';
      } else {
        $content .= 'done<br />';
      }
      $content .= '<br />';
      break;
    case 4:
      /* new config values in config r4:
       * cfg_currtheme = current theme 
       */
      $JUSTASK_CONFIG_VERSION = 4;
      $content .= 'updating version value... ';
      $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . $JUSTASK_CONFIG_VERSION . '\' WHERE `config_id`=\'version\'; ';
      $sql->query($sql_str);
      
      $content .= 'done<br />upgrading database... ';
      $sql_str = 'INSERT INTO `' . $MYSQL_TABLE_PREFIX . 'config` (`config_id`, `config_value`) VALUES (\'cfg_currtheme\', \'' . $sql->real_escape_string("classic") . '\');';
      if (!$sql->query($sql_str)) {
        $content .= 'error<br />';
      } else {
        $content .= 'done<br />';
      }
      $content .= '<br />';
      break;
    case 5:
      /* new config values in config r5:
       * cfg_twitter_chk = is the checkbox "Post to twitter" on by default?
       */
      $JUSTASK_CONFIG_VERSION = 5;
      $content .= 'updating version value... ';
      $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . $JUSTASK_CONFIG_VERSION . '\' WHERE `config_id`=\'version\'; ';
      $sql->query($sql_str);
      
      $content .= 'done<br />upgrading database... ';
      $sql_str = 'INSERT INTO `' . $MYSQL_TABLE_PREFIX . 'config` (`config_id`, `config_value`) VALUES (\'cfg_twitter_chk\', \'' . $sql->real_escape_string("true") . '\');';
      if (!$sql->query($sql_str)) {
        $content .= 'error<br />';
      } else {
        $content .= 'done<br />';
      }
      $content .= '<br />';
      break;
    case 6:
      /* new values in r6:
       * _inbox and _answers become a new `asker_id` column
       * 
       * cfg_show_user_id = show (generated) user id in inbox and answers
       */
       
      $JUSTASK_CONFIG_VERSION = 6;
      $content .= 'updating version value... ';
      $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . $JUSTASK_CONFIG_VERSION . '\' WHERE `config_id`=\'version\'; ';
      $sql->query($sql_str);
      
      $content .= 'done<br />upgrading database... <br />';
      
      $content .= 'modifying ' . $MYSQL_TABLE_PREFIX . 'inbox... ';
      $sql_str = 'ALTER TABLE `' . $MYSQL_TABLE_PREFIX . 'inbox` ADD `asker_id` TEXT NOT NULL;';
      if (!$sql->query($sql_str)) {
        $content .= 'error<br />';
      } else {
        $content .= 'done<br />';
      }
      
      $content .= 'modifying ' . $MYSQL_TABLE_PREFIX . 'answers... ';
      $sql_str = 'ALTER TABLE `' . $MYSQL_TABLE_PREFIX . 'answers` ADD `asker_id` TEXT NOT NULL;';
      if (!$sql->query($sql_str)) {
        $content .= 'error<br />';
      } else {
        $content .= 'done<br />';
      }
      
      $content .= 'adding new config value... ';
      $sql_str = 'INSERT INTO `' . $MYSQL_TABLE_PREFIX . 'config` (`config_id`, `config_value`) VALUES (\'cfg_show_user_id\', \'' . $sql->real_escape_string("true") . '\');';
      if (!$sql->query($sql_str)) {
        $content .= 'error<br />';
      } else {
        $content .= 'done<br />';
      }
      $content .= '<br />';
      break;
    case 7:
      /* new config values in config r7:
       * cfg_api_key = the API key other applications may use
       */
      $JUSTASK_CONFIG_VERSION = 7;
      $content .= 'updating version value... ';
      $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . $JUSTASK_CONFIG_VERSION . '\' WHERE `config_id`=\'version\'; ';
      $sql->query($sql_str);
      
      $api_key = substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',5)),0,12);;
      $content .= 'done<br />upgrading database... <br />adding new config value... <br />';
      $sql_str = 'INSERT INTO `' . $MYSQL_TABLE_PREFIX . 'config` (`config_id`, `config_value`) VALUES (\'cfg_api_key\', \'' . $sql->real_escape_string($api_key) . '\');';
      if (!$sql->query($sql_str)) {
        $content .= 'error<br />';
      } else {
        $content .= 'done<br />';
        $content .= '<code>Your API Key is <strong>' . $api_key . '</strong></code>';
      }
      $content .= '<br />';       
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
$res = $sql->query('SELECT * FROM `' . $MYSQL_TABLE_PREFIX . 'inbox`');
$question_count = $res->num_rows;
$res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'version\'');
$res = $res->fetch_assoc();
$config_version = $res['config_value'];

switch ($config_version) {
  case 3:
    $content .= "<p>upgrading to <strong>r7</strong>...<br />";
    $content .= "<strong>r3 → r4</strong><br />";
    upgrade_to($sql, 4, $MYSQL_TABLE_PREFIX, $content);
    $content .= "<strong>r4 → r5</strong><br />";
    upgrade_to($sql, 5, $MYSQL_TABLE_PREFIX, $content);
    $content .= "<strong>r5 → r6</strong><br />";
    upgrade_to($sql, 6, $MYSQL_TABLE_PREFIX, $content);
    $content .= "<strong>r6 → r7</strong><br />";
    upgrade_to($sql, 7, $MYSQL_TABLE_PREFIX, $content);
    $content .= "Perfect.</p>";
    break;
  case 4:
    $content .= "<p>upgrading to <strong>r7</strong>...<br />";
    $content .= "<strong>r4 → r5</strong><br />";
    upgrade_to($sql, 5, $MYSQL_TABLE_PREFIX, $content);
    $content .= "<strong>r5 → r6</strong><br />";
    upgrade_to($sql, 6, $MYSQL_TABLE_PREFIX, $content);
    $content .= "<strong>r6 → r7</strong><br />";
    upgrade_to($sql, 7, $MYSQL_TABLE_PREFIX, $content);
    $content .= "Perfect.</p>";
    break;
  case 5:
    $content .= "<p>upgrading to <strong>r7</strong>...<br />";
    $content .= "<strong>r5 → r6</strong><br />";
    upgrade_to($sql, 6, $MYSQL_TABLE_PREFIX, $content);
    $content .= "<strong>r6 → r7</strong><br />";
    upgrade_to($sql, 7, $MYSQL_TABLE_PREFIX, $content);
    $content .= "Perfect.</p>";
    break;
  case 6:
    $content .= "<p>upgrading to <strong>r7</strong>...<br />";
    $content .= "<strong>r6 → r7</strong><br />";
    upgrade_to($sql, 7, $MYSQL_TABLE_PREFIX, $content);
    $content .= "Perfect.</p>";
    break;
  case 7:
    $content .= "<p>Your config is already up to date.</p>";
    break;
  default:
    $content .= "<p>upgrading to <strong>r7</strong>...<br />";
    $content .= "<strong>r? → r4</strong><br />";
    upgrade_to($sql, 3, $MYSQL_TABLE_PREFIX, $content);
    $content .= "<strong>r3 → r4</strong><br />";
    upgrade_to($sql, 4, $MYSQL_TABLE_PREFIX, $content);
    $content .= "<strong>r4 → r5</strong><br /><br />";
    upgrade_to($sql, 5, $MYSQL_TABLE_PREFIX, $content);
    $content .= "<strong>r5 → r6</strong><br />";
    upgrade_to($sql, 6, $MYSQL_TABLE_PREFIX, $content);
    $content .= "<strong>r6 → r7</strong><br />";
    upgrade_to($sql, 7, $MYSQL_TABLE_PREFIX, $content);
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
$tpl->assign("question_count", $question_count);
$tpl->assign("logged_in", $_SESSION['logged_in']);
$tpl->assign("site_name", htmlspecialchars($site_name));
$tpl->assign("user_gravatar_email", get_gravatar_url($user_gravatar_email, 48));

$tpl->draw("generic");

?>