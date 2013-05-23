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
  header('Location: usercfg.php');
  exit();
}
else if ($_SESSION['logged_in'] !== true) { 
  header('Location: usercfg.php');
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

switch ($action) {
  case 'delete':
    $sql_str = 'DELETE FROM `' . $MYSQL_TABLE_PREFIX . 'inbox` WHERE `question_id`=' . $question_id;
    if (!$sql->query($sql_str)) {
      header('Location: usercfg.php?p=inbox&m=4');
      exit();
    }
    header('Location: usercfg.php?p=inbox&m=1');
    break;
  case 'delete_answer':
    $sql_str = 'DELETE FROM `' . $MYSQL_TABLE_PREFIX . 'answers` WHERE `answer_id`=' . $question_id;
    if (!$sql->query($sql_str)) {
      header('Location: usercfg.php?p=answers&m=2');
      exit();
    }
    header('Location: usercfg.php?p=answers&m=1');
    break;
  case 'answer':
    if (!isset($_POST['answer'])) {
      header('Location: usercfg.php?p=inbox&m=3');
    }
    if ($_POST['answer'] === '') {
      header('Location: usercfg.php?p=inbox&m=3');
    }
    
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
      header('Location: usercfg.php?p=inbox&m=4');
      exit();
    }
    
    $sql_str = 'DELETE FROM `' . $MYSQL_TABLE_PREFIX . 'inbox` WHERE `question_id`=' . $question_id;
    $sql->query($sql_str);
    header('Location: usercfg.php?p=inbox&m=2');
    
    break;
  default:
    die ('FcknDie!');
}

?>
