<?php
/* 
 * justask
 * © 2013 nilsding
 * License: AGPLv3, read the LICENSE file for the license text.
 */
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
    header('Location: ucp.php?p=inbox&hm=1');
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
    }
    if ($_POST['answer'] === '') {
      header('Location: ucp.php?p=inbox&m=3');
    }
    if (true);
    
    $answer = $sql->real_escape_string($_POST['answer']);
    $sql_str = 'SELECT * FROM `' . $MYSQL_TABLE_PREFIX . 'inbox` WHERE `question_id`=' . $question_id;
    $res = $sql->query($sql_str);
    $res = $res->fetch_assoc();
    
    $question_content = $sql->real_escape_string($res['question_content']);
    $asker_name = $sql->real_escape_string($res['asker_name']);
    $asker_gravatar = $sql->real_escape_string($res['asker_gravatar']);
    $asker_private = $res['asker_private'];
    $question_timestamp = $res['question_timestamp'];
    
    $sql_str = 'INSERT INTO `' . $MYSQL_TABLE_PREFIX . 'answers` (`question_content`, `asker_name`, ' .
    '`asker_gravatar`, `asker_private`, `question_timestamp`, `answer_text`) VALUES (\'' . $question_content . 
    '\', \'' . $asker_name . '\', \'' . $asker_gravatar . '\', \'' . $asker_private . '\', \'' . $question_timestamp . 
    '\', \'' . $answer . '\');';
    
    if (!$sql->query($sql_str)) {
      header('Location: ucp.php?p=inbox&m=4');
      exit();
    }
    
    $sql_str = 'DELETE FROM `' . $MYSQL_TABLE_PREFIX . 'inbox` WHERE `question_id`=' . $question_id;
    $sql->query($sql_str);
    
    if ($twitter_on) {
      if (isset($_POST['post_to_twitter'])) {
        $sql_str = "SELECT * FROM `jak_answers` ORDER BY `answer_id` DESC LIMIT 1"; /* we need the latest answer here... */
        $res = $sql->query($sql_str);
        $res = $res->fetch_assoc();
        $answer_id = $res['answer_id'];
        $question_content = $res['question_content'];
        $answer = $res['answer_text'];
        $url = 'http';
        if ($_SERVER["HTTPS"] == "on") {
          $url .= "s";
        }
        $url .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
          $url .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
        } else {
          $url .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
        }
        $url = substr($url, 0, (strlen($url) - strlen($_SERVER['SCRIPT_NAME'])));
        $url .= "/view_answer.php?id=" . $answer_id;
        if (strlen($question_content) > 56) {
          $question_content = substr($question_content, 0, 55) . '…'; 
        }
        if (strlen($answer) > 56) {
          $answer = substr($answer, 0, 55) . '…'; 
        }
        $tweet = $question_content . " — " . $answer . ' · ' . $url;
        
        $connection = new TwitterOAuth($twitter_ck, $twitter_cs, $twitter_at, $twitter_ats);
        $connection->post('statuses/update', array('status' => $tweet));
      }
    }
    
    header('Location: ucp.php?p=inbox&m=2');
    
    break;
  default:
    die ('FcknDie!');
}

?>
