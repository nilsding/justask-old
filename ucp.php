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

$shouldchangepassanduser = false;
$sql = mysqli_connect($MYSQL_SERVER, $MYSQL_USER, $MYSQL_PASS, $MYSQL_DATABASE);

if (isset($_GET['change'])) {
  switch ($_GET['change']) {
    case 'logindata':
      if (!isset($_SESSION['logged_in']) || !isset($_SESSION['user'])) {
        header('Location: usercfg.php');
        exit();
      }
      if (!isset($_POST['username']) || !isset($_POST['passwd'])) {
        header('Location: usercfg.php?p=account');
        exit();
      }
      
      if ($_POST['username'] === '') {
        header('Location: usercfg.php?p=account');
        exit();
      }
      if ($_POST['passwd'] === '') {
        header('Location: usercfg.php?p=account');
        exit();
      }
      
      $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . $sql->real_escape_string($_POST['username']) . '\' WHERE `config_id`=\'cfg_username\'';
      $sql->query($sql_str);
      
      $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . crypt($_POST['passwd'], '$2a$07$ifthisstringhasmorecharactersdoesitmakeitmoresecurequestionmark666$') . '\' WHERE `config_id`=\'cfg_password\'';
      $sql->query($sql_str);
      
      header('Location: usercfg.php?p=account');
      break;
    case 'userdetails':
      if (!isset($_SESSION['logged_in']) || !isset($_SESSION['user'])) {
        header('Location: usercfg.php');
        exit();
      }
      if (!isset($_POST['gravatar_email'])) {
        header('Location: usercfg.php?p=account');
        exit();
      }
      
      $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . $sql->real_escape_string($_POST['gravatar_email']) . '\' WHERE `config_id`=\'cfg_user_gravatar\'';
      $sql->query($sql_str);
      
      header('Location: usercfg.php?p=account');
      break;
    case 'justask':
      if (!isset($_SESSION['logged_in']) || !isset($_SESSION['user'])) {
        header('Location: usercfg.php');
        exit();
      }
      
      /* this is the same procedure as seen in install.php */
      if (!isset($_POST['jak_gravatar'])) {
        $jak_gravatar = false;
      } else {
        $jak_gravatar = true;
      }
      if (!isset($_POST['jak_anonymous_questions'])) {
        $jak_anonymous_questions = false;
      } else {
        $jak_anonymous_questions = true;
      }
      if (!isset($_POST['jak_name'])) { /* who would do this? */
        $jak_name = 'An instance of justask without a name';
      } else {
        if ($_POST['jak_name'] === '') {
          $jak_name = 'An instance of justask without a name';
        } else {
          $jak_name = $_POST['jak_name'];
        }
      }
      if (!isset($_POST['jak_entriesperpage'])) {
        $jak_entriesperpage = 10;
      } else {
        $jak_entriesperpage = $_POST['jak_entriesperpage'];
        if (!is_numeric($jak_entriesperpage)) { /* ... */
          $jak_entriesperpage = 10;
        }
        if ($jak_entriesperpage < 1) { /* ............ */
          $jak_entriesperpage = 10;
        }
      }
      
      $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . $sql->real_escape_string($jak_name) . '\' WHERE `config_id`=\'cfg_sitename\'; ';
//       if (!$sql->query($sql_str)) {
//         die('rip in pizza');
//       }
      $sql->query($sql_str);
      $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . ($jak_gravatar ? "true" : "false") . '\' WHERE `config_id`=\'cfg_gravatar\'; ';
      $sql->query($sql_str);
      $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . ($jak_anonymous_questions ? "true" : "false"). '\' WHERE `config_id`=\'cfg_anon_questions\';';
      $sql->query($sql_str);
      
      $sql_str = 'INSERT INTO `' . $MYSQL_TABLE_PREFIX . 'config` (`config_id`, `config_value`) VALUES (\'cfg_max_entries\', \'' . $jak_entriesperpage . '\');';
      if (!$sql->query($sql_str)) {
        if ($sql->errno == 1062) {
          $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . $jak_entriesperpage . '\' WHERE `config_id`=\'cfg_max_entries\';';
          $sql->query($sql_str);
        }
      }
      
      
      header('Location: usercfg.php?p=account');
      break;
  }
//   header('Location: usercfg.php');
  exit();
}

$res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_username\'');
$res = $res->fetch_assoc();
$t_user = $sql->real_escape_string($res['config_value']);
$res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_password\'');
$res = $res->fetch_assoc();
$t_pass = $res['config_value'];
if (strtolower($t_user) === strtolower('user') || $t_pass === crypt('password', '$2a$07$ifthisstringhasmorecharactersdoesitmakeitmoresecurequestionmark666$')) {
  $shouldchangepassanduser = true;
} 

if (isset($_POST['username'])) {
  $p_user = $sql->real_escape_string($_POST ['username']);
} else {
  $p_user = null;
}

if (isset($_POST['passwd'])) {
  $p_pass = $_POST ['passwd'];
} else {
  $p_pass = null;
}

if (strtolower($t_user) === strtolower($p_user) && $t_pass === crypt($p_pass, '$2a$07$ifthisstringhasmorecharactersdoesitmakeitmoresecurequestionmark666$')) {
  $_SESSION['logged_in'] = true;
  $_SESSION['user'] = $t_user;
}

unset($t_user);
unset($t_pass);
unset($p_user);
unset($p_pass);

$res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_sitename\'');
$res = $res->fetch_assoc();
$site_name = $res['config_value'];

$res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_user_gravatar\'');
$res = $res->fetch_assoc();
$user_gravatar_email = $res['config_value'];

$res = $sql->query('SELECT * FROM `' . $MYSQL_TABLE_PREFIX . 'inbox`');
$question_count = $res->num_rows;


$res = $sql->query('SELECT * FROM `' . $MYSQL_TABLE_PREFIX . 'answers`');
$answer_count = $res->num_rows;
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
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="style.css" />
<title>justask - user control panel</title>
</head>
<body>
<h1>User control panel</h1>
<?php if (!isset($_SESSION['logged_in'])) { ?>
<p>You may want to log in.</p>
<form method="POST" action="usercfg.php">
<input type="text" name="username" placeholder="User name"><br />
<input type="password" name="passwd" placeholder="Password"><br />
<button>Log in</button>
</form>
<?php } 
else if ($_SESSION['logged_in'] === true) { 
  if (!isset($_GET['p'])) {
    $page = "front";
  } else {
    $page = $_GET['p'];
  } ?>
<ul class="user-menu">
<li><a href="usercfg.php?p=front">Main page</a></li>
<li><a href="usercfg.php?p=inbox">Inbox<?php if ($question_count > 0) echo ' <span class="menu-counter">' . $question_count . '</span>'; ?></a></li>
<li><a href="usercfg.php?p=answers">Answers</a></li>
<li><a href="usercfg.php?p=account">Settings<?php if ($shouldchangepassanduser) echo ' <span class="menu-important">!</span>'; ?></a></li>
<li><a href="usercfg.php?p=logout">Logout</a></li>
</ul>
<!--<p><img src="<?php echo get_gravatar_url($user_gravatar_email, 48); ?>" alt="Your profile picture" />
Logged in as <?php echo $_SESSION['user']; ?></p> -->

<?php if ($shouldchangepassanduser) { ?>
<p>For security reasons, you should change your password and your user name immediately to something else.</p>
<?php } ?>

<?php

switch ($page) {
  default:
  case 'front':
if (!isset($_GET['m'])) {
  $m = '0';
} else {
  $m = $_GET['m'];
}
/* messages:
 *  0 - don't show a message
 *  1 - what the f- happened?
 */

switch ($m) {
  case '1':
    ?><p class="message">What the f- happened?</p><?php
    break;
  case '0':
  default:
}
?>
<p><b>Welcome to justask, <?php echo $_SESSION['user'] ?>!</b></p>
<p>So far, you have answered <?php echo $answer_count == 0 ? "no" : ($answer_count == 1 ? "one" : $answer_count); ?> question<?php echo $answer_count != 1 ? "s" : ""; ?>!</p>
<?php
    break;
  
  case 'inbox':
if (!isset($_GET['m'])) {
  $m = '0';
} else {
  $m = $_GET['m'];
}
/* messages:
 *  0 - don't show a message
 *  1 - successfully deleted question
 *  2 - successfully answered question
 *  3 - you have to write an answer
 *  4 - internal error
 */

  if ($question_count == 0) { ?>
<p class="message">No new questions.</p>
<?php } else { 

switch ($m) {
  case '1':
    ?><p class="message">Successfully deleted question.</p><?php
    break;
  case '2':
    ?><p class="message">Successfully answered question.</p><?php
    break;
  case '3':
    ?><p class="message">You have to write an answer!</p><?php
    break;
  case '4':
    ?><p class="message">Internal server error.</p><?php
    break;
  case '0':
  default:
}

$res = $sql->query('SELECT * FROM `' . $MYSQL_TABLE_PREFIX . 'inbox` ORDER BY `question_timestamp` DESC');

$last_page = ceil($res->num_rows / $max_entries_per_page); 
if ($pagenum > $last_page) {
  $pagenum = $last_page;
}
$max_sql_str_part_thing = ' LIMIT ' . ($pagenum - 1) * $max_entries_per_page . ',' . $max_entries_per_page; 

$res = $sql->query('SELECT * FROM `' . $MYSQL_TABLE_PREFIX . 'inbox` ORDER BY `question_timestamp` DESC' . $max_sql_str_part_thing);

while ($question = $res->fetch_assoc()) { 
$question_time_asked = strtotime($question['question_timestamp']);
if ($question['asker_private']) {
  $question_asked_by = 'Anonymous';
} else {
  $question_asked_by = htmlspecialchars($question['asker_name']);
} ?>
<form action="answer.php" method="POST">
<div class="question">
<img class="asker-gravatar" src="<?php echo get_gravatar_url($question['asker_gravatar'], 48); ?>" alt="<?php echo $question_asked_by; ?>"/>
<div class="question-text">
<div class="question-timestamp"><?php echo date('l jS F Y G:i', $question_time_asked); ?></div>
<div class="question-user-asked"><?php echo $question_asked_by; ?> asked:</div>
<div class="question-content"><?php echo str_replace("\n", "<br />", htmlspecialchars($question['question_content'])); ?></div>
</div>
<div class="question-answer-area">
<textarea name="answer" placeholder="Answer this question..." cols="65">
</textarea>
</div>
<div class="question-actions"><button class="nature" name="action" value="answer">Answer question</button><button class="danger" name="action" value="delete">Delete question</button></div>
</div>
<input type="hidden" name="question_id" value="<?php echo $question['question_id']; ?>">
</form>
<?php
} ?>
<!-- Begin page numbering thing -->
<div class="pages">
<ul class="pages_list">
<?php if ($pagenum > 1) { /* are we not on the first page? */ ?>
<li><a href="<?php echo $_SERVER['PHP_SELF'] . '?p=inbox&page=1' ?>">«</a></li>
<li><a href="<?php echo $_SERVER['PHP_SELF'] . '?p=inbox&page=' . ($pagenum == 1 ? 1 : $pagenum - 1); ?>">‹</a></li>
<?php } 
for ($i = 1; $i <= $last_page; $i++) {
  ?><li><a href="<?php echo $_SERVER['PHP_SELF'] . '?p=inbox&page=' . $i; if ($pagenum == $i) echo '" class="current-page'; ?>"><?php echo $i; ?></a></li><?php
}
if ($pagenum < $last_page) { /* are we not on the last page */ ?>
<li><a href="<?php echo $_SERVER['PHP_SELF'] . '?p=inbox&page=' . ($pagenum == $last_page ? $last_page : $pagenum + 1); ?>">›</a></li>
<li><a href="<?php echo $_SERVER['PHP_SELF'] . '?p=inbox&page=' . $last_page; ?>">»</a></li>
<?php } ?>
</ul>
</div>
<!-- End page numbering thing -->
<?php
}
    break;
  
  case 'answers':
if (!isset($_GET['m'])) {
  $m = '0';
} else {
  $m = $_GET['m'];
}
/* messages:
 *  0 - don't show a message
 *  1 - successfully deleted answer
 *  2 - internal error
 */

  if ($answer_count == 0) { ?>
<p class="message">You haven't answered any questions yet!.</p>
<?php } else { 

switch ($m) {
  case '1':
    ?><p class="message">Successfully deleted answer.</p><?php
    break;
  case '2':
    ?><p class="message">Internal server error.</p><?php
    break;
  case '0':
  default:
}
$res = $sql->query('SELECT * FROM `' . $MYSQL_TABLE_PREFIX . 'answers` ORDER BY `answer_timestamp` DESC');

$last_page = ceil($res->num_rows / $max_entries_per_page); 
if ($pagenum > $last_page) {
  $pagenum = $last_page;
}
$max_sql_str_part_thing = ' LIMIT ' . ($pagenum - 1) * $max_entries_per_page . ',' . $max_entries_per_page; 

$res = $sql->query('SELECT * FROM `' . $MYSQL_TABLE_PREFIX . 'answers` ORDER BY `answer_timestamp` DESC' . $max_sql_str_part_thing);

while ($question = $res->fetch_assoc()) { 
$question_time_answered = strtotime($question['answer_timestamp']);
if ($question['asker_private']) {
  $question_asked_by = 'Anonymous';
} else {
  $question_asked_by = htmlspecialchars($question['asker_name']);
} ?>
<form action="answer.php" method="POST">
<div class="question">
<img class="asker-gravatar" src="<?php echo get_gravatar_url($question['asker_gravatar'], 48); ?>" alt="<?php echo $question_asked_by; ?>"/>
<div class="question-text">
<div class="question-timestamp"><?php echo date('l jS F Y G:i', $question_time_answered); ?></div>
<div class="question-user-asked"><?php echo $question_asked_by; ?> asked:</div>
<div class="question-content"><?php echo str_replace("\n", "<br />", htmlspecialchars($question['question_content'])); ?></div>
</div><br />
<img class="asker-gravatar" src="<?php echo get_gravatar_url($user_gravatar_email, 48); ?>" alt="<?php echo $_SESSION['user']; ?>"/>
<div class="question-text">
<div class="question-user-answered"><?php echo $_SESSION['user']; ?> responded:</div>
<div class="answer-content"><?php echo str_replace("\n", "<br />", htmlspecialchars($question['answer_text'])); ?></div>
</div>
<div class="question-actions"><button class="danger" name="action" value="delete_answer">Delete answer</button></div>
</div>
<input type="hidden" name="question_id" value="<?php echo $question['answer_id']; ?>">
</form>
<?php }
?>
<!-- Begin page numbering thing -->
<div class="pages">
<ul class="pages_list">
<?php if ($pagenum > 1) { /* are we not on the first page? */ ?>
<li><a href="<?php echo $_SERVER['PHP_SELF'] . '?p=answers&page=1' ?>">«</a></li>
<li><a href="<?php echo $_SERVER['PHP_SELF'] . '?p=answers&page=' . ($pagenum == 1 ? 1 : $pagenum - 1); ?>">‹</a></li>
<?php } 
for ($i = 1; $i <= $last_page; $i++) {
  ?><li><a href="<?php echo $_SERVER['PHP_SELF'] . '?p=answers&page=' . $i; if ($pagenum == $i) echo '" class="current-page'; ?>"><?php echo $i; ?></a></li><?php
}
if ($pagenum < $last_page) { /* are we not on the last page */ ?>
<li><a href="<?php echo $_SERVER['PHP_SELF'] . '?p=answers&page=' . ($pagenum == $last_page ? $last_page : $pagenum + 1); ?>">›</a></li>
<li><a href="<?php echo $_SERVER['PHP_SELF'] . '?p=answers&page=' . $last_page; ?>">»</a></li>
<?php } ?>
</ul>
</div>
<!-- End page numbering thing -->
<?php
  }
    break;
  
  case 'account': 
$res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_gravatar\'');
$res = $res->fetch_assoc();
$gravatar = ($res['config_value'] === 'true' ? true : false);
$res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_anon_questions\'');
$res = $res->fetch_assoc();
$anon_questions = ($res['config_value'] === 'true' ? true : false);
?>
<h2>User details</h2>
<form method="POST" action="usercfg.php?change=userdetails">
<label for="gravatar_email">Gravatar email address: </label><input type="text" name="gravatar_email" placeholder="Gravatar email address" value="<?php echo $user_gravatar_email; ?>">
<button>Save</button>
</form>

<h2>justask settings</h2>
<p>These are settings for justask.</p>
<form method="POST" action="usercfg.php?change=justask">
<table>
<tr>
<td><label for="jak_name">Name:</label></td>
<td><input type="text" name="jak_name" value="<?php echo $site_name ?>"></td>
<td class="info">This is the name used along the site.</td>
</tr>
<tr>
<td><label for="jak_entriesperpage">Max. entries per page:</label></td>
<td><input type="number" name="jak_entriesperpage" value="<?php echo $max_entries_per_page; ?>"></td>
<td class="info">How many questions/answers will be shown on each page?</td>
</tr>
<tr>
<td><label for="jak_gravatar">Enable Gravatar:</label></td>
<td><input type="checkbox" name="jak_gravatar"<?php echo ($gravatar ? " checked" : ""); ?>></td>
<td class="info">Allow people to use their Gravatar email address as a profile picture.</td>
</tr>
<tr>
<td><label for="jak_anonymous_questions">Allow anonymous questions?</label></td>
<td><input type="checkbox" name="jak_anonymous_questions"<?php echo ($anon_questions ? " checked" : ""); ?>></td>
<td class="info">Allow people to ask you anonymous questions</td>
</tr>
</table>
<button>Save</button>
</form>

<h2<?php if ($shouldchangepassanduser) echo ' class="should_change"'; ?>>Login details</h2>
<p>Change your username and password here.</p>
<form method="POST" action="usercfg.php?change=logindata">
<input type="text" name="username" placeholder="User name" value="<?php echo $_SESSION['user']; ?>"><br />
<input type="password" name="passwd" placeholder="Password"><br />
<button>Save</button>
</form>


  <?php
    break;
  
  case 'logout':
    unset($_SESSION['logged_in']);
    unset($_SESSION['user']);
    header('Location: usercfg.php');
    break;
}

?>

<?php } else { ?>
<p>???</p>
<?php unset($_SESSION['logged_in']); } ?>
<hr />
<div class="footer">
<p style="font-size: small;"><?php echo htmlspecialchars($site_name); ?> is running <a href="https://github.com/nilsding/justask">justask</a>, which is
free software licensed under the <a href="http://www.gnu.org/licenses/agpl-3.0.html">GNU Affero General Public License
version 3</a>.</p>
</div>
</body>
</html>