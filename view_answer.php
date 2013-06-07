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
$res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_gravatar\'');
$res = $res->fetch_assoc();
$gravatar = ($res['config_value'] === 'true' ? true : false);
$res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_username\'');
$res = $res->fetch_assoc();
$user_name = $res['config_value'];
$res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_user_gravatar\'');
$res = $res->fetch_assoc();
$user_gravatar_email = $res['config_value'];
$res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_max_entries\'');
$res = $res->fetch_assoc();
if (!is_numeric($res['config_value'])) {
  $max_entries_per_page = 10;
} else {
  $max_entries_per_page = (int) $res['config_value'];
}

if (isset($_GET['id'])) {
  $answer_id = (int) $_GET['id'];
} else {
  $answer_id = false;
}

?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo htmlspecialchars($site_name); ?></title>
<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
<h1><?php echo htmlspecialchars($site_name); ?></h1>
<?php 
if ($answer_id == false) { ?>
  <p class="message">You have to provide an answer!</p> <?php
} else {
$res = $sql->query('SELECT * FROM `' . $MYSQL_TABLE_PREFIX . 'answers` WHERE answer_id=' . $answer_id);
if ($res->num_rows !== 1) { ?>
  <p class="message">Answer not found.</p><?php 
} else { 
$question = $res->fetch_assoc(); ?>
<h2>Response to <?php echo htmlspecialchars($question['asker_name']) . (substr($question['asker_name'], -1, 1) === 's' ? "'" : "'s"); ?> question</h2>
<?php 

$question_time_answered = strtotime($question['answer_timestamp']);
if ($question['asker_private']) {
  $question_asked_by = 'Anonymous';
} else {
  $question_asked_by = htmlspecialchars($question['asker_name']);
} ?>
<div class="question">
<img class="asker-gravatar" src="<?php echo get_gravatar_url($question['asker_gravatar'], 48); ?>" alt="<?php echo $question_asked_by; ?>"/>
<div class="question-text">
<div class="question-timestamp"><?php echo date('l jS F Y G:i', $question_time_answered); ?></div>
<div class="question-user-asked"><?php echo $question_asked_by; ?> asked:</div>
<div class="question-content"><?php echo str_replace("\n", "<br />", htmlspecialchars($question['question_content'])); ?></div>
</div><br />
<img class="asker-gravatar" src="<?php echo get_gravatar_url($user_gravatar_email, 48); ?>" alt="<?php echo $user_name; ?>"/>
<div class="question-text">
<div class="question-user-answered"><?php echo $user_name; ?> responded:</div>
<div class="answer-content"><?php echo str_replace("\n", "<br />", htmlspecialchars($question['answer_text'])); ?></div>
</div>
</div>
<?php } } ?>
<a href="/index.php">« Read all answers</a>
<hr />
<div class="footer">
<p style="font-size: small;"><?php echo htmlspecialchars($site_name); ?> is running <a href="https://github.com/nilsding/justask">justask</a>, which is
free software licensed under the <a href="http://www.gnu.org/licenses/agpl-3.0.html">GNU Affero General Public License
version 3</a>.</p>
</div>
</body>
</html>