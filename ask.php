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

$sql = mysqli_connect($MYSQL_SERVER, $MYSQL_USER, $MYSQL_PASS, $MYSQL_DATABASE);
$res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_gravatar\'');
$res = $res->fetch_assoc();
$gravatar = ($res['config_value'] === 'true' ? true : false);
$res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_anon_questions\'');
$res = $res->fetch_assoc();
$anon_questions = ($res['config_value'] === 'true' ? true : false);

$question_asked_anonymously = false;
$question = "";
$gravatar_address = "";
$question_asked_by = "";

if (!isset($_POST['question']) || !isset($_POST['asker_name'])) {
  header('Location: index.php?message=5');
  exit();
}
$question = $sql->real_escape_string($_POST['question']);
$question_asked_by = $sql->real_escape_string($_POST['asker_name']);
if ($gravatar) {
  if (!isset($_POST['gravatar_address'])) {
    $gravatar_address = "";
  } else {
    $gravatar_address = $sql->real_escape_string($_POST['gravatar_address']);
  }
}
if ($anon_questions) {
  if (!isset($_POST['anonymous'])) {
    $question_asked_anonymously = false;
  } else {
    $question_asked_anonymously = true;
    $gravatar_address = "";
    $question_asked_by = "";
  }
}

if (!isset($_POST['asker_id'])) {
  $asker_id = "none";
} else {
  $asker_id = $sql->real_escape_string($_POST['asker_id']);
}

if (trim($question) === '') {
  header('Location: index.php?message=1');
  exit();
}

if (strlen($gravatar_address) > 100) {
  header('Location: index.php?message=4');
  exit();
}

if (!$anon_questions && trim($question_asked_by) === '') {
  header('Location: index.php?message=3');
  exit();
}

if ($anon_questions && trim($question_asked_by) === '') {
  $gravatar_address = "";
  $question_asked_anonymously = true;
}

if (strlen($question_asked_by) > 100) {
  header('Location: index.php?message=2');
  exit();
}


$sql_str = 'INSERT INTO `' . $MYSQL_TABLE_PREFIX . 'inbox` (`question_content`, `asker_name`, `asker_gravatar`, `asker_private`, `asker_id`) VALUES (\'' . $question . '\', \'' . $question_asked_by . '\', \'' . $gravatar_address . '\', \'' . ($question_asked_anonymously ? '1' : '0') . '\', \'' . $asker_id . '\')';

if (!$sql->query($sql_str)) {
  echo "<p>The query <code>$sql_str</code> failed! :(</p>";
  echo "<p>The error was: (" . $sql->errno . ") " . $sql->error . "</p>";
  exit();
}

header('Location: index.php?message=6');
exit();
?>