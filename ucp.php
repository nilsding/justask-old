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

include_once 'fixDir.php';
include_once 'gravatar.php';

$shouldchangepassanduser = false;
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
  $res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_twitter_callbk\'');
  $res = $res->fetch_assoc();
  $twitter_callback = $res['config_value'];
}

if (isset($_GET['change'])) {
  if (!isset($_SESSION['logged_in']) || !isset($_SESSION['user'])) {
    header('Location: ucp.php');
    exit();
  }
  switch ($_GET['change']) {
    case 'logindata':
      if (!isset($_POST['username']) || !isset($_POST['passwd'])) {
        header('Location: ucp.php?p=account');
        exit();
      }
      
      if ($_POST['username'] === '') {
        header('Location: ucp.php?p=account');
        exit();
      }
      if ($_POST['passwd'] === '') {
        header('Location: ucp.php?p=account');
        exit();
      }
      
      $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . $sql->real_escape_string($_POST['username']) . '\' WHERE `config_id`=\'cfg_username\'';
      $sql->query($sql_str);
      
      $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . crypt($_POST['passwd'], '$2a$07$ifthisstringhasmorecharactersdoesitmakeitmoresecurequestionmark666$') . '\' WHERE `config_id`=\'cfg_password\'';
      $sql->query($sql_str);
      
      header('Location: ucp.php?p=account');
      break;
    case 'userdetails':
      if (!isset($_SESSION['logged_in']) || !isset($_SESSION['user'])) {
        header('Location: ucp.php');
        exit();
      }
      if (!isset($_POST['gravatar_email'])) {
        header('Location: ucp.php?p=account');
        exit();
      }
      
      $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . $sql->real_escape_string($_POST['gravatar_email']) . '\' WHERE `config_id`=\'cfg_user_gravatar\'';
      $sql->query($sql_str);
      
      header('Location: ucp.php?p=account');
      break;
    case 'justask':
      if (!isset($_SESSION['logged_in']) || !isset($_SESSION['user'])) {
        header('Location: ucp.php');
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
      
      if (!isset($_POST['jak_twitter_on'])) {
        $twitter_on = false;
      } else {
        $twitter_on = true;
      }
      
      if (!isset($_POST['jak_theme'])) {
        $jak_theme = "classic";
      } else {
        $jak_theme = $sql->real_escape_string($_POST['jak_theme']);
      }
      
      if (!isset($_POST['jak_show_id'])) {
        $jak_show_id = false;
      } else {
        $jak_show_id = true;
      }
      
      $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . $sql->real_escape_string($jak_name) . '\' WHERE `config_id`=\'cfg_sitename\'; ';
//       if (!$sql->query($sql_str)) {
//         die('rip in pizza');
//       }
      $sql->query($sql_str);
      $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . ($jak_gravatar ? "true" : "false") . '\' WHERE `config_id`=\'cfg_gravatar\'; ';
      $sql->query($sql_str);
      $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . ($jak_anonymous_questions ? "true" : "false") . '\' WHERE `config_id`=\'cfg_anon_questions\';';
      $sql->query($sql_str);
      $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . $jak_entriesperpage . '\' WHERE `config_id`=\'cfg_max_entries\';';
      $sql->query($sql_str);
      $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . ($twitter_on ? "true" : "false") . '\' WHERE `config_id`=\'cfg_twitter\';';
      $sql->query($sql_str);
      $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . $jak_theme . '\' WHERE `config_id`=\'cfg_currtheme\';';
      $sql->query($sql_str);
      $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . ($jak_show_id ? "true" : "false") . '\' WHERE `config_id`=\'cfg_show_user_id\';';
      $sql->query($sql_str);
      
      header('Location: ucp.php?p=account&m=1');
      break;
      
    case 'twitter_signin':
      if (!$twitter_on) {
        header('Location: ucp.php?p=account&m=4');
        exit();
      }
      $connection = new TwitterOAuth($twitter_ck, $twitter_cs);
      $request_token = $connection->getRequestToken($twitter_callback);
      
      $_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
      $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

      switch ($connection->http_code) {
        case 200:
          $url = $connection->getAuthorizeURL($token);
          header('Location: ' . $url);
          break;
        default:
          header('Location: ucp.php?p=account&m=3');
          exit();
      }
      break;
      
    case 'generate_api_key':
      $api_key = substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',5)),0,12);;
      $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . $sql->real_escape_string($api_key) . '\' WHERE `config_id`=\'cfg_api_key\';';
      if (!$sql->query($sql_str)) {
        header('Location: ucp.php?p=account&m=7');
        exit();
      } else {
        header('Location: ucp.php?p=account&m=6');
        exit();
      }
      break;
      
    case 'twitter_tokens':
      if (!$twitter_on) {
        header('Location: ucp.php?p=account&m=4');
        exit();
      }
     /* ck, cs, at, ats, callback */
      if (!isset($_POST['default_check'])) {
        $default_check = false;
      } else {
        $default_check = true;
      }

    if (isset($_POST['ck'])) {
      $s = $sql->real_escape_string($_POST['ck']);
      $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . $s . '\' WHERE `config_id`=\'cfg_twitter_ck\';';
      $sql->query($sql_str);
    }
    if (isset($_POST['cs'])) {
      $s = $sql->real_escape_string($_POST['cs']);
      $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . $s . '\' WHERE `config_id`=\'cfg_twitter_cs\';';
      $sql->query($sql_str);
    }
    if (isset($_POST['at'])) {
      $s = $sql->real_escape_string($_POST['at']);
      $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . $s . '\' WHERE `config_id`=\'cfg_twitter_at\';';
      $sql->query($sql_str);
    }
    if (isset($_POST['ats'])) {
      $s = $sql->real_escape_string($_POST['ats']);
      $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . $s . '\' WHERE `config_id`=\'cfg_twitter_ats\';';
      $sql->query($sql_str);
    }
    if (isset($_POST['callback'])) {
      $s = $sql->real_escape_string($_POST['callback']);
      $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . $s . '\' WHERE `config_id`=\'cfg_twitter_callbk\';';
      $sql->query($sql_str);
    }
    $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . ($default_check ? "true" : "false") . '\' WHERE `config_id`=\'cfg_twitter_chk\';';
    $sql->query($sql_str);
    
    header('Location: ucp.php?p=account&m=1');
    break;
    
    case 'tweet':    
    if (!$twitter_on) {
      header('Location: ucp.php?p=front&m=3');
      exit();
    }
    
    if (!isset($_POST['tweet_text'])) {
      header('Location: ucp.php?p=front&m=7');
      exit();
    }
    $tweet_text = trim($_POST['tweet_text']);
    
    $tweet_text_c = preg_replace("/http:\/\/([\w\.]*)/", "http://t.co/xxxxxxxxxx", $tweet_text);
    $tweet_text_c = preg_replace("/https:\/\/([\w\.]*)/", "https://t.co/xxxxxxxxxx", $tweet_text_c);
    
    if (strlen($tweet_text_c) > 140) {
      header('Location: ucp.php?p=front&m=6');
      exit();
    }
    if (strlen($tweet_text_c) == 0) {
      header('Location: ucp.php?p=front&m=7');
      exit();
    }
    
    $connection = new TwitterOAuth($twitter_ck, $twitter_cs, $twitter_at, $twitter_ats);
    $res = $connection->post('statuses/update', array('status' => $tweet_text));
    
    if (isset($res->errors)) {
      header('Location: ucp.php?p=front&m=5');
      exit();
    }
    header('Location: ucp.php?p=front&m=4');
    break;
  }
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
$res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_currtheme\'');
$res = $res->fetch_assoc();
$current_theme = $res['config_value'];
$res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_gravatar\'');
$res = $res->fetch_assoc();
$gravatar = ($res['config_value'] === 'true' ? true : false);
$res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_anon_questions\'');
$res = $res->fetch_assoc();
$anon_questions = ($res['config_value'] === 'true' ? true : false);
$res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_twitter_chk\'');
$res = $res->fetch_assoc();
$twitter_check = ($res['config_value'] === 'true' ? true : false);
$res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_username\'');
$res = $res->fetch_assoc();
$user_name = $res['config_value'];
$res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_user_gravatar\'');
$res = $res->fetch_assoc();
$user_gravatar_email = $res['config_value'];
$res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_show_user_id\'');
$res = $res->fetch_assoc();
$show_user_id = ($res['config_value'] === "true" ? true : false);
$res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_api_key\'');
$res = $res->fetch_assoc();
$api_key = $res['config_value'];

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

$message = "";
$is_message = true;

if (!isset($_GET['m'])) {
  $m = '0';
} else {
  $m = $_GET['m'];
}

if (!isset($_GET['p'])) {
  $page = "front";
} else {
  $page = $_GET['p'];
}

$url = "http";
if (isset($_SERVER["HTTPS"])) {
  if ($_SERVER["HTTPS"] == "on") {
    $url .= 's';
  }
}
$url .= "://" . $_SERVER['HTTP_HOST'] . fixDir();

$last_page = 1;
$add_params = "";
$pages = array();
$themes = array();
$questions = array();
$responses = array();

switch ($page) {
  case 'inbox':
    /* messages for inbox:
    *  0 - don't show a message
    *  1 - successfully deleted question
    *  2 - successfully answered question
    *  3 - you have to write an answer
    *  4 - internal error
    */
    if ($question_count == 0) { 
      $message = "No new questions.";
    } else { 
      switch ($m) {
        case '1':
          $message = "Successfully deleted question.";
          break;
        case '2':
          $message = "Successfully answered question.";
          break;
        case '3':
          $message = "You have to write an answer!";
          break;
        case '4':
          $message = "Internal server error.";
          break;
        case '0':
        default:
          $is_message = false;
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
        }
        array_push($questions, array("question_asked_by" => htmlspecialchars($question_asked_by), 
                                        "asker_gravatar" => get_gravatar_url($question['asker_gravatar'], 48),
                                   "question_time_asked" => htmlspecialchars(date('l jS F Y G:i', $question_time_asked)),
                                      "question_content" => str_replace("\n", "<br />", htmlspecialchars($question['question_content'])),
                                              "asker_id" => htmlspecialchars(strlen(trim($question['asker_id'])) == 0 ? "none" : $question['asker_id']),
                                           "question_id" => $question['question_id'],
                                         "asker_private" => $question['asker_private']));
      }
      for ($i = 0; $i < $last_page; $i++) {
        array_push($pages, "PAGE");
      }
    }
    $add_params = "&p=inbox";
    break;
    
  case 'answers':
    /* messages for answer management:
    *  0 - don't show a message
    *  1 - successfully deleted answer
    *  2 - internal error
    */
    if ($answer_count == 0) { 
      $message = "You haven't answered any questions yet!";
    } else { 
      switch ($m) {
        case '1':
          $message = "Successfully deleted answer.";
          break;
        case '2':
          $message = "Internal server error.";
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
                                              "asker_id" => htmlspecialchars(strlen(trim($question['asker_id'])) == 0 ? "none" : $question['asker_id']),
                                             "answer_id" => $question['answer_id'],
                                         "asker_private" => $question['asker_private']));
      }
      for ($i = 0; $i < $last_page; $i++) {
        array_push($pages, "PAGE");
      }
    }
    $add_params = "&p=answers";
    break;
    
  case 'account':
  case 'settings':
    /* messages for settings:
    *  0 - don't show a message
    *  1 - successfully stored everything
    *  2 - wait, what
    *  3 - something went wrong
    *  4 - you need to activate twitter for that
    *  5 - hooray for we have twitter
    */

    switch ($m) {
      case '1':
        $message = "Successfully wrote config.";
        break;
      case '2':
        $message = "Wait, what.";
        break;
      case '3':
        $message = "Something went horribly wrong. Don't worry, this time it wasn't your fault. (And if it was your fault, I hate you now.)";
        break;
      case '4':
        $message = "To do that, you need to have The Twitter.";
        break;
      case '5':
        $message = "Successfully connected with Twitter.";
        break;
      case '6':
        $message = "I have generated a new API key, just for you.";
        break;
      case '7':
        $message = "Something went wrong.";
        break;
      case '0':
      default:
        $is_message = false;
    }
    $theme_dir = "themes/";
    $scdir = scandir($theme_dir);
    $items = count($scdir);
    for ($i = 0; $i < $items; $i++) {
      if (is_dir($theme_dir . $scdir[$i]) && ($scdir[$i][0] !== '.')) {
        array_push($themes, $scdir[$i]);
      }
    }
    
    $page = "settings";
    break;
    
  case 'logout':
    session_destroy();
    header('Location: index.php');
    exit();
    break;
  
  default:
  case 'front':
    /* messages for front:
    *  0 - don't show a message
    *  1 - what the f- happened?
    *  2 - usercfg was renamed
    */
    switch ($m) {
      case '1':
        $message = "What the f- happened?";
        break;
      case '2':
        $message = "<code>usercfg.php</code> was renamed to <code>ucp.php</code>, please update your bookmarks!";
        break;
      case '3': 
        $message = "To do that, you need to have The Twitter.";
        break;
      case '4': 
        $message = "Successfully posted tweet!";
        break;
      case '5': 
        $message = "There was a problem posting your tweet!";
        break;
      case '6': 
        $message = "The tweet you were trying to send was longer than 140 characters.";
        break;
      case '7': 
        $message = "The tweet you were trying to send was empty.";
        break;
      default:
        $is_message = false;
    }
    $page = 'front';
}

/* template thing */

include 'include/rain.tpl.class.php';

raintpl::configure("base_url", null);
raintpl::configure("path_replace", false);
raintpl::configure("tpl_dir", "themes/$current_theme/");

$menu = array(array("text" => "Main page", "url" => "ucp.php?p=front"),
              array("text" => "Inbox",     "url" => "ucp.php?p=inbox"),
              array("text" => "Answers",   "url" => "ucp.php?p=answers"),
              array("text" => "Settings",  "url" => "ucp.php?p=settings"),
              array("text" => "Logout",    "url" => "ucp.php?p=logout"));

$tpl = new RainTPL;

$tpl->assign("url", $url);
$tpl->assign("pages", $pages);
$tpl->assign("ucp_menu", $menu);
$tpl->assign("themes", $themes);
$tpl->assign("message", $message);
$tpl->assign("pagenum", $pagenum);
$tpl->assign("api_key", $api_key);
$tpl->assign("answers", $responses);
$tpl->assign("gravatar", $gravatar);
$tpl->assign("current_page", $page);
$tpl->assign("file_name", "ucp.php");
$tpl->assign("last_page", $last_page);
$tpl->assign("questions", $questions);
$tpl->assign("user_name", $user_name);
$tpl->assign("show_id", $show_user_id);
$tpl->assign("add_params", $add_params);
$tpl->assign("is_message", $is_message);
$tpl->assign("twitter_ck", $twitter_ck);
$tpl->assign("twitter_cs", $twitter_cs);
$tpl->assign("twitter_at", $twitter_at);
$tpl->assign("twitter_on", $twitter_on);
$tpl->assign("twitter_ats", $twitter_ats);
$tpl->assign("answer_count", $answer_count);
$tpl->assign("twitter_check", $twitter_check);
$tpl->assign("current_theme", $current_theme);
$tpl->assign("question_count", $question_count);
$tpl->assign("page_self", $_SERVER['PHP_SELF']);
$tpl->assign("anon_questions", $anon_questions);
$tpl->assign("logged_in", $_SESSION['logged_in']);
$tpl->assign("twitter_callback", $twitter_callback);
$tpl->assign("site_name", htmlspecialchars($site_name));
$tpl->assign("max_entries_per_page", $max_entries_per_page);
$tpl->assign("user_gravatar_emailaddr", $user_gravatar_email);
$tpl->assign("shouldchangepassanduser", $shouldchangepassanduser);
$tpl->assign("user_gravatar_email", get_gravatar_url($user_gravatar_email, 48));

$tpl->draw("ucp");

?>
