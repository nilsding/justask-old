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

include_once 'gravatar.php';

$sql = mysqli_connect($MYSQL_SERVER, $MYSQL_USER, $MYSQL_PASS, $MYSQL_DATABASE);
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
$res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_show_user_id\'');
$res = $res->fetch_assoc();
$show_user_id = ($res['config_value'] === "true" ? true : false);
$res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_max_entries\'');
$res = $res->fetch_assoc();
if (!is_numeric($res['config_value'])) {
  $max_entries_per_page = 10;
} else {
  $max_entries_per_page = (int) $res['config_value'];
}

if (!isset($_GET['page'])) {
  $pagenum = 1;
} else {
  $pagenum = (int) $_GET['page'];
}
if ($pagenum < 1) {
  $pagenum = 1;
}

if (!isset($_GET['message'])) {
  $message = '0';
} else {
  $message = $_GET['message'];
}
/* $message:
 *   0 - no message
 *   1 - you have to ask a question
 *   2 - name has to be shorter than 100 chars
 *   3 - no name entered
 *   4 - gravatar address has to be shorter than 100 chars
 *   5 - illegal operation
 *   6 - successfully asked question, ready to be answered.
 */
$is_message = true;
$message_text = "";
switch ($message) { 
  case '1': 
    $message_text = "You have to ask a question.";
    break;
  case '2': 
    $message_text = "The name you have entered is too long! (100 characters max.)";
    break;
  case '3': 
    $message_text = "You have to provide a name";
    if ($anon_questions) {
      $message_text .= ", did you want to ask it anonymously?";
    } else {
      $message_text .= ".";
    }
    break;
  case '4': 
    $message_text = "Gravatar address has to be shorter than 100 characters.";
    break;
  case '5':
    $message_text = "You are a horrible person.";
    break;
  case '6':
    $message_text = "Question asked successfully!";
    break;
  case '0':
  default:
    $is_message = false;
}

$res = $sql->query('SELECT * FROM `' . $MYSQL_TABLE_PREFIX . 'answers` ORDER BY `answer_timestamp` DESC');

$last_page = ceil($res->num_rows / $max_entries_per_page); 
if ($pagenum > $last_page) {
  $pagenum = $last_page;
}
$max_sql_str_part_thing = ' LIMIT ' . ($pagenum - 1) * $max_entries_per_page . ',' . $max_entries_per_page; 

$res = $sql->query('SELECT * FROM `' . $MYSQL_TABLE_PREFIX . 'answers` ORDER BY `answer_timestamp` DESC' . $max_sql_str_part_thing);

$responses = array();
if ($res->num_rows <= 0) {
  $message_text = "No answers found :(";
  $is_message = true;
} else {
  while ($question = $res->fetch_assoc()) { 
    $question_time_answered = strtotime($question['answer_timestamp']);
    $question_time_asked = strtotime($question['question_timestamp']);
    if ($question['asker_private']) {
      $question_asked_by = 'Anonymous';
    } else {
      $question_asked_by = htmlspecialchars($question['asker_name']);
    }
    array_push($responses, array("question_asked_by" => htmlspecialchars($question_asked_by), 
                                    "asker_gravatar" => get_gravatar_url($question['asker_gravatar'], 48),
                                       "answer_text" => str_replace("\n", "<br />", htmlspecialchars($question['answer_text'])),
                            "question_time_answered" => htmlspecialchars(date('l jS F Y G:i', $question_time_answered)),
                               "question_time_asked" => htmlspecialchars(date('l jS F Y G:i', $question_time_asked)),
                                  "question_content" => str_replace("\n", "<br />", htmlspecialchars($question['question_content'])),
                                          "asker_id" => htmlspecialchars(strlen(trim($question['asker_id'])) == 0 ? "none" : $question['asker_id'])));
  }
}

$pages = array();
for ($i = 0; $i < $last_page; $i++) {
  array_push($pages, "PAGE");
}

if (!isset($_SESSION['u_id'])) {
  $n = rand(ip2long($_SERVER['REMOTE_ADDR']), 10e20);
  $_SESSION['u_id'] = base_convert($n, 10, 36);
}

/* template thing */
include 'include/rain.tpl.class.php';

raintpl::configure("base_url", null);
raintpl::configure("path_replace", false);
raintpl::configure("tpl_dir", "themes/$current_theme/");

$tpl = new RainTPL;
$tpl->assign("pages", $pages);
$tpl->assign("pagenum", $pagenum);
$tpl->assign("gravatar", $gravatar);
$tpl->assign("answers", $responses);
$tpl->assign("user_name", $user_name);
$tpl->assign("last_page", $last_page);
$tpl->assign("show_id", $show_user_id);
$tpl->assign("file_name", "index.php");
$tpl->assign("u_id", $_SESSION['u_id']);
$tpl->assign("is_message", $is_message);
$tpl->assign("current_theme", $current_theme);
$tpl->assign("anon_questions", $anon_questions);
$tpl->assign("question_count", $question_count);
$tpl->assign("page_self", $_SERVER['PHP_SELF']);
$tpl->assign("logged_in", $_SESSION['logged_in']);
$tpl->assign("site_name", htmlspecialchars($site_name));
$tpl->assign("message", htmlspecialchars($message_text));
$tpl->assign("user_gravatar_email", get_gravatar_url($user_gravatar_email, 48));

$tpl->draw("answers");

?>
