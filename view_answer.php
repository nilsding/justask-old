<?php
if (file_exists('config.php')) {
  require_once('config.php');
} else {
  header('Location: install.php');
  exit();
}

session_start();

include 'gravatar.php';

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
$is_message = false;
$message = "";

$question_asked_by = "";
$asker_gravatar = get_gravatar_url("", 48);
$question_time_answered = "";
$question_time_asked = "";
$question_content = "";
$answer_text = "";

if (isset($_GET['id'])) {
  $answer_id = (int) $_GET['id'];
} else {
  $answer_id = false;
}
if ($answer_id == false) { 
  $message = "You have to provide an answer!";
  $is_message = true;
} else {
  $res = $sql->query('SELECT * FROM `' . $MYSQL_TABLE_PREFIX . 'answers` WHERE answer_id=' . $answer_id);
  if ($res->num_rows !== 1) { 
    $message = "Answer not found."; 
    $is_message = true;
  } else { 
    $question = $res->fetch_assoc();
    if ($question['asker_private']) {
      $question_asked_by = 'Anonymous';
    } else {
      $question_asked_by = htmlspecialchars($question['asker_name']);
    }
    $question_time_answered = date('l jS F Y G:i', strtotime($question['answer_timestamp']));
    $question_time_asked = date('l jS F Y G:i', strtotime($question['question_timestamp']));
    $asker_gravatar = get_gravatar_url($question['asker_gravatar'], 48);
    $question_content = str_replace("\n", "<br />", htmlspecialchars($question['question_content']));
    $answer_text = str_replace("\n", "<br />", htmlspecialchars($question['answer_text']));
  }
}

include 'include/rain.tpl.class.php';

raintpl::configure("base_url", null);
raintpl::configure("path_replace", false);
raintpl::configure("tpl_dir", "themes/$current_theme/");

$tpl = new RainTPL;

$tpl->assign("answer_text", $answer_text);
$tpl->assign("asker_gravatar", $asker_gravatar);
$tpl->assign("question_content", $question_content);
$tpl->assign("question_asked_by", $question_asked_by);
$tpl->assign("question_time_asked", $question_time_asked);
$tpl->assign("question_time_answered", $question_time_answered);
$tpl->assign("ss_de", (substr($question_asked_by, -1, 1) === 's' ? "'" : "s"));
$tpl->assign("ss_en", (substr($question_asked_by, -1, 1) === 's' ? "'" : "'s"));

/* everywhere variables */
$tpl->assign("message", $message);
$tpl->assign("gravatar", $gravatar);
$tpl->assign("user_name", $user_name);
$tpl->assign("is_message", $is_message);
$tpl->assign("file_name", "update_jak.php");
$tpl->assign("current_theme", $current_theme);
$tpl->assign("page_self", $_SERVER['PHP_SELF']);
$tpl->assign("anon_questions", $anon_questions);
$tpl->assign("question_count", $question_count);
$tpl->assign("logged_in", $_SESSION['logged_in']);
$tpl->assign("site_name", htmlspecialchars($site_name));
$tpl->assign("user_gravatar_email", get_gravatar_url($user_gravatar_email, 48));

$tpl->draw("single-answer");
?>