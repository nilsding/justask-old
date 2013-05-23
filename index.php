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
$res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_anon_questions\'');
$res = $res->fetch_assoc();
$anon_questions = ($res['config_value'] === 'true' ? true : false);
$res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_username\'');
$res = $res->fetch_assoc();
$user_name = $res['config_value'];
$res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_user_gravatar\'');
$res = $res->fetch_assoc();
$user_gravatar_email = $res['config_value'];

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
<!-- Begin question box -->
<div class="question-box">
<h2>Ask me a question!</h2>
<form method="POST" action="ask.php">
<?php switch ($message) { 
case '1': ?>
<p class="message">You have to ask a question.</p>
<?php break;
case '2': ?>
<p class="message">The name you entered is too long! (100 characters max.)</p>
<?php break;
case '3': ?>
<p class="message">You have to provide a name<?php if ($anon_questions) { ?>, did you want to ask it anonymously?<?php } else { ?>.<?php } ?></p>
<?php break;
case '4': ?>
<p class="message">Gravatar address has to be shorter than 100 characters.</p>
<?php break;
case '5': ?>
<p class="message">You are a horrible person.</p>
<?php break;
case '6': ?>
<p class="message">Question asked successfully!</p>
<?php break;
case '0':
default: ?>
<?php break;
} ?>
<textarea name="question" class="question-box-question" cols="68">
</textarea><br />
<div class="question-box-data">
<input type="text" name="asker_name" placeholder="Your name"><br />
<?php if ($gravatar) { ?>
<input type="text" name="gravatar_address" placeholder="Your gravatar address">
<?php } ?>
</div>
<?php if ($anon_questions) { ?>
<div class="question-box-anon">
<input type="checkbox" name="anonymous"> <label for="anonymous">Ask anonymously</label>
</div>
<?php } ?><br />
<button>Ask!</button>
</form>
</div>
<!-- End question box -->
<h2>Questions I responded to</h2>
<!-- Begin answers -->
<?php 
$res = $sql->query('SELECT * FROM `' . $MYSQL_TABLE_PREFIX . 'answers` ORDER BY `answer_timestamp` DESC');
while ($question = $res->fetch_assoc()) { 
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
<?php } ?>
<!-- End answers -->
<hr />
<div class="footer">
<p style="font-size: small;"><?php echo htmlspecialchars($site_name); ?> is running <a href="https://github.com/nilsding/justask">justask</a>, which is
free software licensed under the <a href="http://www.gnu.org/licenses/agpl-3.0.html">GNU Affero General Public License
version 3</a>.</p>
</div>
</body>
</html>