<?php
/* 
 * justask
 * © 2013 nilsding
 * License: AGPLv3, read the LICENSE file for the license text.
 */

include_once('fixDir.php');
require_once 'generic_functions.php';

session_start();

if (file_exists('config.php')) {
  require_once('config.php');
} else {
  header('Location: install.php');
  exit();
}

if (!isset($_SESSION['logged_in'])) { 
  header('Location: ucp.php');
  exit();
}
else if ($_SESSION['logged_in'] !== true) { 
  header('Location: ucp.php');
  exit();
}

if (!isset($_POST['action']) || !isset($_POST['question_id'])) {
  echo "Listen. <br />\n";
  echo "I hate you.";
  exit();
}

$action = $_POST['action'];
$question_id = $_POST['question_id'];

if (!is_numeric($question_id)) {
  echo "?SYNTAX ERROR";
  exit();
}

$sql = mysqli_connect($MYSQL_SERVER, $MYSQL_USER, $MYSQL_PASS, $MYSQL_DATABASE);

$res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_twitter\'');
$res = $res->fetch_assoc();
$twitter_on = ($res['config_value'] === "true" ? true : false);

if ($twitter_on) {
  include_once 'include/oauth/twitteroauth.php';
  $res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_twitter_ck\'');
  $res = $res->fetch_assoc();
  $twitter_ck = $res['config_value'];
  $res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_twitter_cs\'');
  $res = $res->fetch_assoc();
  $twitter_cs = $res['config_value'];
  $res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_twitter_at\'');
  $res = $res->fetch_assoc();
  $twitter_at = $res['config_value'];
  $res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_twitter_ats\'');
  $res = $res->fetch_assoc();
  $twitter_ats = $res['config_value'];
}

switch ($action) {
  case 'delete':
    $sql_str = 'DELETE FROM `' . $MYSQL_TABLE_PREFIX . 'inbox` WHERE `question_id`=' . $question_id;
    if (!$sql->query($sql_str)) {
      header('Location: ucp.php?p=inbox&m=4');
      exit();
    }
    header('Location: ucp.php?p=inbox&m=1');
    break;
  case 'delete_answer':
    $sql_str = 'DELETE FROM `' . $MYSQL_TABLE_PREFIX . 'answers` WHERE `answer_id`=' . $question_id;
    if (!$sql->query($sql_str)) {
      header('Location: ucp.php?p=answers&m=2');
      exit();
    }
    header('Location: ucp.php?p=answers&m=1');
    break;
  case 'answer':
    if (!isset($_POST['answer'])) {
      header('Location: ucp.php?p=inbox&m=3');
      exit();
    }
    if (strlen(trim($_POST['answer'])) == 0) {
      header('Location: ucp.php?p=inbox&m=3');
      exit();
    }
    
    $answer = $sql->real_escape_string($_POST['answer']);
    $sql_str = 'SELECT * FROM `' . $MYSQL_TABLE_PREFIX . 'inbox` WHERE `question_id`=' . $question_id;
    $res = $sql->query($sql_str);
    $res = $res->fetch_assoc();
    
    $question_content = $sql->real_escape_string($res['question_content']);
    $asker_name = $sql->real_escape_string($res['asker_name']);
    $asker_gravatar = $sql->real_escape_string($res['asker_gravatar']);
    $asker_id = $sql->real_escape_string($res['asker_id']);
    $asker_private = $res['asker_private'];
    $question_timestamp = $res['question_timestamp'];
    
    $sql_str = 'INSERT INTO `' . $MYSQL_TABLE_PREFIX . 'answers` (`question_content`, `asker_name`, ' .
    '`asker_gravatar`, `asker_private`, `question_timestamp`, `answer_text`, `asker_id`) VALUES (\'' . $question_content . 
    '\', \'' . $asker_name . '\', \'' . $asker_gravatar . '\', \'' . $asker_private . '\', \'' . $question_timestamp . 
    '\', \'' . $answer . '\', \'' . $asker_id . '\');';
    
    if (!$sql->query($sql_str)) {
      header('Location: ucp.php?p=inbox&m=4');
      exit();
    }
    $answer_id = $sql->insert_id;
    
    $sql_str = 'DELETE FROM `' . $MYSQL_TABLE_PREFIX . 'inbox` WHERE `question_id`=' . $question_id;
    $sql->query($sql_str);
    
    if ($twitter_on) {
      if (isset($_POST['post_to_twitter'])) {
        $connection = new TwitterOAuth($twitter_ck, $twitter_cs, $twitter_at, $twitter_ats);
        $status = generate_tweet_text($sql, $MYSQL_TABLE_PREFIX, $answer_id);
        $connection->post('statuses/update', array('status' => $status));
      }
    }
    
    header('Location: ucp.php?p=inbox&m=2');
    
    break;
  default:
    die ('FcknDie!');
}

?>
